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

use dktapps\pmforms\CustomFormResponse;
use matcracker\ServerTools\Main;
use matcracker\ServerTools\utils\FormUtils;
use pocketmine\player\Player;
use pocketmine\plugin\PluginException;
use pocketmine\utils\TextFormat;
use function basename;
use function dirname;
use function is_dir;
use function rename;
use function strlen;
use function strpbrk;
use function trim;
use const DIRECTORY_SEPARATOR;

final class RenameFolderForm extends FileInputForm{

	public function __construct(Main $plugin, string $filePath, Player $player, string $error = ""){
		if(!is_dir($filePath)){
			throw new PluginException("The $filePath must be a folder.");
		}

		parent::__construct(
			"Rename folder",
			"Insert new folder name:",
			basename($filePath),
			basename($filePath),
			$error,
			function(Player $player, CustomFormResponse $response) use ($plugin, $filePath) : void{
				$folderName = $response->getString(self::FORM_KEY_FILE_NAME);
				if(strlen(trim($folderName)) === 0 || strpbrk($folderName, "\\/?%*:|\"<>") !== false){
					$player->sendForm(new self($plugin, $filePath, $player, "Invalid name \"$folderName\" for this folder. Try again."));

					return;
				}
				$newPath = dirname($filePath) . DIRECTORY_SEPARATOR . $folderName;
				if(rename($filePath, $newPath)){
					$player->sendForm(new FileExplorerForm($plugin, $newPath, $player));
				}else{
					$player->sendMessage(Main::formatMessage(TextFormat::RED . "Could not rename the folder $filePath"));
				}
			},
			FormUtils::onClose(new FileExplorerForm($plugin, $filePath, $player))
		);
	}
}