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
use pocketmine\utils\TextFormat;
use function count;
use function dirname;
use function is_dir;
use const DIRECTORY_SEPARATOR;

final class FileExplorerForm extends Form{

	public function __construct(string $filePath){
		$fileList = Utils::getSortedFileList($filePath);

		parent::__construct(
			static function(Player $player, $data) use ($filePath, $fileList){
				if($data === FormManager::BACK_LABEL){
					$form = new self(dirname($filePath, 1));
				}else{
					$nextPath = $filePath . DIRECTORY_SEPARATOR . $data;
					if(is_dir($nextPath)){
						$form = new self($nextPath);
					}else{
						$form = new FileEditorForm($nextPath);
					}
				}

				$player->sendForm($form);
			},
			FormManager::onClose(FormManager::getInstance()->getMainMenu())
		);
		$this->setTitle("File Explorer")
			->setMessage($filePath);

		if($fileList === null){
			$this->setMessage(TextFormat::RED . "The directory does not exist.")
				->addLocalImageButton("Back", "textures/ui/arrow_dark_left_stretch.png", FormManager::BACK_LABEL);

			return;
		}

		if($filePath !== Utils::getServerPath()){
			$this->addLocalImageButton("Back", "textures/ui/arrow_dark_left_stretch.png", FormManager::BACK_LABEL)
				->addLocalImageButton("Rename current folder", "textures/ui/pencil_edit_icon.png", "rename")
				->addLocalImageButton("Delete current folder", "textures/ui/cancel.png", "delete");
		}

		$this->addLocalImageButton("New folder", "", "/new_folder")
			->addLocalImageButton("New file", "", "/new_file");

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