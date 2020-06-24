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

use InvalidArgumentException;
use matcracker\FormLib\Form;
use matcracker\ServerTools\forms\FormManager;
use matcracker\ServerTools\Main;
use matcracker\ServerTools\utils\Utils;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\item\WritableBook;
use pocketmine\item\WrittenBook;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
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
use function is_file;
use function is_readable;
use function is_writable;
use function str_repeat;
use function str_replace;
use function strlen;

final class FileEditorForm extends Form{
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

	private const READ_FILE = "/read_file";
	private const EDIT_FILE = "/edit_file";
	private const RENAME_FILE = "/rename_file";
	private const DELETE_FILE = "/delete_file";

	public function __construct(string $filePath, Player $player){
		if(!is_file($filePath)){
			throw new PluginException("The {$filePath} must be a file.");
		}

		$fileName = basename($filePath);
		parent::__construct(
			static function(Player $player, $data) use ($filePath, $fileName){
				if(!is_readable($filePath)){
					$player->sendMessage(Main::formatMessage(TextFormat::RED . "The file \"{$fileName}\" does not exist or is not readable."));

					return;
				}

				if($data === self::DELETE_FILE){
					$player->sendForm(new DeleteFileForm($filePath, $player));

				}elseif($data === self::RENAME_FILE){
					$player->sendForm(new RenameFileForm($filePath, $player));

				}else{
					/**@var WrittenBook $book */
					$book = ItemFactory::get(ItemIds::WRITTEN_BOOK);

					if($data === self::EDIT_FILE){
						/**@var WritableBook $book */
						$book = ItemFactory::get(ItemIds::WRITABLE_BOOK);
					}

					$book = self::setupFileBook($book, $filePath);

					if($fileResource = fopen($filePath, "r")){
						$pageCount = 0;
						$pageContent = "";

						while(($buff = fgets($fileResource)) !== false){
							$line = str_replace("\r", "", $buff);
							$line = str_replace("\t", str_repeat(" ", 4), $line);

							if(strlen($pageContent) > self::BOOK_ROW_MAX * self::BOOK_COL_MAX){
								$book->setPageText($pageCount, $pageContent);

								$pageContent = $line;
								$pageCount++;

								if($pageCount >= 50){
									break;
								}
							}else{
								$pageContent .= $line;
							}
						}

						if(!fclose($fileResource)){
							$player->sendMessage(Main::formatMessage(TextFormat::RED . "Cannot close the file stream of {$fileName}"));

							return;
						}

						if($pageCount < 50){
							$book->setPageText($pageCount, $pageContent);
						}

						$player->getInventory()->setItemInHand($book);
					}else{
						$player->sendMessage(Main::formatMessage(TextFormat::RED . "Cannot open the file stream of {$fileName}"));
					}
				}
			},
			FormManager::onClose(new FileExplorerForm(dirname($filePath), $player))
		);

		$this->setTitle("File Editor");

		if(!is_readable($filePath)){
			$this->setMessage(TextFormat::RED . "The file \"{$fileName}\" does not exist or is not readable.");

			return;
		}

		$fileInfoMessage =
			"Name: {$fileName}" . TextFormat::EOL .
			"Size: " . Utils::bytesToHuman(filesize($filePath));

		$this->setMessage($fileInfoMessage);

		if($canBeOpen = (filesize($filePath) <= self::BOOK_MAX_SIZE)){
			$this->addLocalImageButton("Read", "textures/items/book_normal.png", self::READ_FILE);
		}

		if(is_writable($filePath) && $player->hasPermission("st.ui.file-explorer.write")){
			if($canBeOpen){
				$this->addLocalImageButton("Edit", "textures/ui/text_color_paintbrush.png", self::EDIT_FILE);
			}

			$this->addLocalImageButton("Rename", "textures/ui/pencil_edit_icon.png", self::RENAME_FILE)
				->addLocalImageButton("Delete", "textures/ui/trash.png", self::DELETE_FILE);
		}


	}

	public static function setupFileBook(WritableBook $book, string $filePath) : WritableBook{
		if(is_dir($filePath)){
			throw new InvalidArgumentException("The file path must be a file not a directory.");
		}

		$fileName = basename($filePath);

		if($book instanceof WrittenBook){
			$book->setTitle($fileName);
			$book->setAuthor(Server::getInstance()->getName());
		}

		$book->setCustomName($fileName);
		$book->setNamedTagEntry(new CompoundTag("ServerTools", [
			new StringTag("FilePath", $filePath)
		]));

		return $book;
	}
}