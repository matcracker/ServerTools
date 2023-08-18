<?php

/*
 *    _________                              ___________           .__
 *	 /   _____/ ______________  __ __________\__    ___/___   ____ |  |   ______
 *	 \_____  \_/ __ \_  __ \  \/ // __ \_  __ \|    | /  _ \ /  _ \|  |  /  ___/
 *	 /        \  ___/|  | \/\   /\  ___/|  | \/|    |(  <_> |  <_> )  |__\___ \
 *	/_______  /\___  >__|    \_/  \___  >__|   |____| \____/ \____/|____/____  >
 *			\/     \/                 \/                                     \/
 *
 * Copyright (C) 2020
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author matcracker
 * @link https://www.github.com/matcracker/ServerTools
 *
*/

declare(strict_types=1);

namespace matcracker\ServerTools\forms\files;

use dktapps\pmforms\FormIcon;
use dktapps\pmforms\MenuForm;
use InvalidArgumentException;
use matcracker\ServerTools\forms\elements\TaggedMenuOption;
use matcracker\ServerTools\Main;
use matcracker\ServerTools\utils\FormUtils;
use matcracker\ServerTools\utils\Utils;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\plugin\PluginException;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use function basename;
use function dirname;
use function fclose;
use function fgets;
use function filesize;
use function fopen;
use function is_dir;
use function is_readable;
use function is_resource;
use function is_writable;
use function mb_convert_encoding;
use function str_repeat;
use function str_replace;
use function strlen;

final class FileEditorForm extends MenuForm{
	/**
	 * Max number of rows in a book.
	 */
	private const BOOK_ROW_MAX = 14;
	/**
	 * Max number of characters allowed in a row
	 *
	 * NOTE: I decreased the max number because
	 * the client aligns the text to left.
	 * By doing so, it creates some empty whitespaces (~5)
	 * and it uses more rows.
	 */
	private const BOOK_COL_MAX = 20 - 5;
	/**
	 * Max number of allowed pages in a book.
	 */
	private const BOOK_PAGES_MAX = 50;
	/**
	 * Max number characters allowed in a book
	 */
	private const BOOK_MAX_SIZE = self::BOOK_ROW_MAX * self::BOOK_COL_MAX * self::BOOK_PAGES_MAX;

	public const FILE_TAG = Main::PLUGIN_NAME . "_FilePath";
	private const FORM_KEY_READ_FILE = "read_file";
	private const FORM_KEY_EDIT_FILE = "edit_file";
	private const FORM_KEY_RENAME_FILE = "rename_file";
	private const FORM_KEY_DELETE_FILE = "delete_file";

	private string $fileName;

	public function __construct(Main $plugin, private readonly string $filePath, private readonly Player $player){
		if(is_dir($filePath)){
			throw new InvalidArgumentException("The path \"$filePath\" must be a file.");
		}

		$this->fileName = basename($filePath);

		/** @var TaggedMenuOption[] $options */
		$options = [];

		if(!is_readable($filePath)){
			$message = TextFormat::RED . "The file \"$this->fileName\" does not exist or is not readable.";
		}else{
			$message = "Name: $this->fileName" . TextFormat::EOL .
				"Size: " . Utils::bytesToHuman(filesize($filePath));

			if($canBeOpen = (filesize($filePath) <= self::BOOK_MAX_SIZE)){
				$options[] = new TaggedMenuOption(self::FORM_KEY_READ_FILE, "Read", new FormIcon("textures/items/book_normal.png", FormIcon::IMAGE_TYPE_PATH));
			}

			if(is_writable($filePath) && ($player->hasPermission("st.ui.file-explorer.write") || $plugin->canBypassPermission($player))){
				if($canBeOpen){
					$options[] = new TaggedMenuOption(self::FORM_KEY_EDIT_FILE, "Edit", new FormIcon("textures/ui/text_color_paintbrush.png", FormIcon::IMAGE_TYPE_PATH));
				}

				$options[] = new TaggedMenuOption(self::FORM_KEY_RENAME_FILE, "Rename", new FormIcon("textures/ui/pencil_edit_icon.png", FormIcon::IMAGE_TYPE_PATH));
				$options[] = new TaggedMenuOption(self::FORM_KEY_DELETE_FILE, "Delete", new FormIcon("textures/ui/trash.png", FormIcon::IMAGE_TYPE_PATH));
			}
		}

		parent::__construct(
			"File Editor",
			$message,
			$options,
			function(Player $player, int $selectedOption) use ($plugin, $options, $filePath) : void{
				if(!is_readable($filePath)){
					$player->sendMessage(Main::formatMessage(TextFormat::RED . "The file \"$this->fileName\" does not exist or is not readable."));

					return;
				}

				$tag = $options[$selectedOption]->getTag();

				switch($tag){
					case self::FORM_KEY_DELETE_FILE:
						$player->sendForm(new DeleteFileForm($plugin, $filePath));
						break;
					case self::FORM_KEY_RENAME_FILE:
						$player->sendForm(new RenameFileForm($plugin, $filePath, $player));
						break;
					case self::FORM_KEY_EDIT_FILE:
						$this->fileToBook(true);
						break;
					case self::FORM_KEY_READ_FILE:
						$this->fileToBook(false);
						break;
					default:
						throw new PluginException(); //TODO
				}
			},
			FormUtils::onClose(new FileExplorerForm($plugin, dirname($filePath), $player))
		);
	}

	private function fileToBook(bool $writableBook) : void{
		$stream = fopen($this->filePath, "r");

		if(!is_resource($stream)){
			$this->player->sendMessage(Main::formatMessage(TextFormat::RED . "Cannot open the file stream of \"$this->filePath\""));

			return;
		}

		if($writableBook){
			$book = VanillaItems::WRITABLE_BOOK();
		}else{
			$book = VanillaItems::WRITTEN_BOOK()
				->setTitle($this->fileName)
				->setAuthor(Server::getInstance()->getName());
		}

		$tag = $book->getNamedTag()->setString(self::FILE_TAG, $this->filePath);

		$pageId = 0;
		$pageContent = "";
		$book->setNamedTag($tag)->setCustomName($this->fileName)->addPage($pageId);

		$tab = str_repeat(" ", 4);

		while(($line = fgets($stream)) !== false){
			$text = mb_convert_encoding(str_replace(["\r", "\t"], ["", $tab], $line), "UTF-8");

			if(strlen($pageContent) > self::BOOK_ROW_MAX * self::BOOK_COL_MAX){
				if($pageId + 1 >= 50){
					break;
				}

				$pageContent = $text;
				$pageId++;
			}else{
				$pageContent .= $text;
			}

			$book->setPageText($pageId, $pageContent);
		}

		if(!fclose($stream)){
			$this->player->sendMessage(Main::formatMessage(TextFormat::RED . "Cannot close the file stream of \"$this->filePath\""));

			return;
		}

		$this->player->getInventory()->setItemInHand($book);
	}
}