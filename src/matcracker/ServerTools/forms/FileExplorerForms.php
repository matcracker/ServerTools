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

namespace matcracker\ServerTools\forms;

use InvalidArgumentException;
use matcracker\FormLib\Form;
use matcracker\ServerTools\Main;
use matcracker\ServerTools\utils\Utils;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\item\WritableBook;
use pocketmine\item\WrittenBook;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use UnexpectedValueException;
use function basename;
use function count;
use function dirname;
use function fclose;
use function fgets;
use function filesize;
use function fopen;
use function is_dir;
use function is_int;
use function is_readable;
use function is_writable;
use function str_repeat;
use function str_replace;
use function strlen;
use const DIRECTORY_SEPARATOR;

final class FileExplorerForms extends BaseForms{

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

	public static function getFileExplorerForm(string $path) : Form{
		$fileList = Utils::getSortedFileList($path);

		$form = (new Form(
			static function(Player $player, $data) use ($path, $fileList){
				if(!is_int($data)){
					throw new UnexpectedValueException("Unexpected value parsed from Form.");
				}

				$countDirs = 0;
				$countFiles = 0;

				if(isset($fileList["dir"])){
					$countDirs = count($fileList["dir"]);
				}

				if(isset($fileList["file"])){
					$countFiles = count($fileList["file"]);
				}

				$countList = $countDirs + $countFiles;

				if(isset($fileList["dir"][$data])){
					$nextPath = $path . DIRECTORY_SEPARATOR . $fileList["dir"][$data];
					$recursiveForm = self::getFileExplorerForm($nextPath);
					$player->sendForm($recursiveForm);

				}elseif(isset($fileList["file"][$data])){
					$filePath = $path . DIRECTORY_SEPARATOR . $fileList["file"][$data];
					$player->sendForm(self::getFileEditorForm($filePath));

				}elseif($data === $countList){ //Back button
					$recursiveForm = self::getFileExplorerForm(dirname($path, 1));
					$player->sendForm($recursiveForm);
				}
			},
			self::onClose(BaseForms::getMainForm())
		))->setTitle("File Explorer")
			->setMessage($path);

		if($fileList === null){
			return $form
				->setMessage(TextFormat::RED . "The directory does not exist.")
				->addLocalImageButton("Back", "textures/ui/arrow_dark_left_stretch.png");
		}

		if(count($fileList) === 0){
			return $form
				->setMessage("Empty directory")
				->addLocalImageButton("Back", "textures/ui/arrow_dark_left_stretch.png");
		}

		if(isset($fileList["dir"])){
			foreach($fileList["dir"] as $dir){
				$form->addLocalImageButton($dir, "textures/ui/storageIconColor.png");
			}
		}

		if(isset($fileList["file"])){
			foreach($fileList["file"] as $file){
				$form->addLocalImageButton($file, "textures/items/map_filled.png");
			}
		}

		if($path !== Utils::getServerPath()){
			$form->addLocalImageButton("Back", "textures/ui/arrow_dark_left_stretch.png");
		}

		return $form;
	}

	public static function getFileEditorForm(string $filePath) : Form{
		if(is_dir($filePath)){
			throw new InvalidArgumentException("The file path must be a file not a directory.");
		}

		$fileName = basename($filePath);
		$form = (new Form(
			static function(Player $player, $data) use ($filePath, $fileName){
				if(!is_int($data)){
					throw new UnexpectedValueException("Unexpected value parsed from Form.");
				}

				if(!is_readable($filePath)){
					$player->sendMessage(Main::formatMessage(TextFormat::RED . "The file \"{$fileName}\" does not exist or is not readable."));

					return;
				}

				/**@var WrittenBook $book */
				$book = ItemFactory::get(ItemIds::WRITTEN_BOOK);

				if($data === 1){
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
			},
			self::onClose(self::getFileExplorerForm(dirname($filePath)))
		))->setTitle("File Editor");

		if(!is_readable($filePath)){
			return $form->setMessage(TextFormat::RED . "The file \"{$fileName}\" does not exist or is not readable.");
		}

		if(filesize($filePath) > self::BOOK_MAX_SIZE){
			return $form->setMessage(TextFormat::RED . "Cannot open the file \"{$fileName}\" because it exceeds the max allowed size of ~" . Utils::bytesToHuman(self::BOOK_MAX_SIZE));
		}

		$fileInfoMessage =
			"Name: {$fileName}" . TextFormat::EOL .
			"Size: " . Utils::bytesToHuman(filesize($filePath));

		$form->setMessage($fileInfoMessage)->addClassicButton("Read File");

		if(is_writable($filePath)){
			$form->addClassicButton("Edit File");
		}

		return $form;
	}

	/**
	 * @param WritableBook $book
	 * @param string       $filePath
	 *
	 * @return WritableBook|WrittenBook
	 */
	public static function setupFileBook(WritableBook $book, string $filePath){
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