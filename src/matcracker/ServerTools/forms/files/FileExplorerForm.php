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

use matcracker\FormLib\Form;
use matcracker\ServerTools\forms\FormManager;
use matcracker\ServerTools\utils\Utils;
use pocketmine\Player;
use pocketmine\plugin\PluginException;
use pocketmine\utils\TextFormat;
use function count;
use function dirname;
use function is_dir;
use const DIRECTORY_SEPARATOR;

final class FileExplorerForm extends Form{

	private const NEW_FILE = "/new_file";
	private const NEW_FOLDER = "/new_folder";
	private const RENAME_FOLDER = "/rename_folder";
	private const DELETE_FOLDER = "/delete_folder";

	public function __construct(string $filePath, Player $player){
		if(!is_dir($filePath)){
			throw new PluginException("The $filePath must be a folder.");
		}

		$fileList = Utils::getSortedFileList($filePath);

		parent::__construct(
			static function(Player $player, $data) use ($filePath){
				if($data === FormManager::BACK_LABEL){
					$form = new self(dirname($filePath, 1), $player);
				}elseif($data === self::NEW_FOLDER){
					$form = new NewFolderForm($filePath, $player);
				}elseif($data === self::NEW_FILE){
					$form = new NewFileForm($filePath, $player);
				}elseif($data === self::DELETE_FOLDER){
					$form = new DeleteFolderForm($filePath, $player);
				}elseif($data === self::RENAME_FOLDER){
					$form = new RenameFolderForm($filePath, $player);
				}else{
					$nextPath = $filePath . DIRECTORY_SEPARATOR . $data;
					if(is_dir($nextPath)){
						$form = new self($nextPath, $player);
					}else{
						$form = new FileEditorForm($nextPath, $player);
					}
				}

				$player->sendForm($form);
			},
			FormManager::onClose(FormManager::getMainMenu())
		);
		$this->setTitle("File Explorer")
			->setMessage($filePath);

		if($fileList === null){
			$this->setMessage(TextFormat::RED . "The directory does not exist.")
				->addLocalImageButton("Back", "textures/ui/arrow_dark_left_stretch.png", FormManager::BACK_LABEL);

			return;
		}

		$hasPermission = $player->hasPermission("st.ui.file-explorer.write");
		if($filePath !== Utils::getServerPath()){
			$this->addLocalImageButton("Back", "textures/ui/arrow_dark_left_stretch.png", FormManager::BACK_LABEL);

			if($hasPermission){
				$this->addLocalImageButton("Rename current folder", "textures/ui/pencil_edit_icon.png", self::RENAME_FOLDER)
					->addLocalImageButton("Delete current folder", "textures/ui/trash.png", self::DELETE_FOLDER);
			}
		}

		if($hasPermission){
			$this->addLocalImageButton("New folder", "textures/ui/book_addpicture_default.png", self::NEW_FOLDER)
				->addLocalImageButton("New file", "textures/ui/book_addtextpage_default.png", self::NEW_FILE);
		}

		if(count($fileList) === 0){
			$this->setMessage($filePath . TextFormat::EOL . TextFormat::BOLD . TextFormat::GOLD . " (Empty Folder)");

			return;
		}

		if(isset($fileList["dir"])){
			foreach($fileList["dir"] as $dir){
				$this->addLocalImageButton($dir, "textures/ui/storageIconColor.png", $dir);
			}
		}

		if(isset($fileList["file"])){
			foreach($fileList["file"] as $file){
				$this->addLocalImageButton($file, "textures/items/map_filled.png", $file);
			}
		}
	}
}