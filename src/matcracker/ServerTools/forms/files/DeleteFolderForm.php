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

use dktapps\pmforms\ModalForm;
use matcracker\ServerTools\Main;
use pocketmine\player\Player;
use pocketmine\plugin\PluginException;
use pocketmine\utils\Filesystem;
use function dirname;
use function is_dir;

final class DeleteFolderForm extends ModalForm{

	public function __construct(Main $plugin, string $folderPath){
		if(!is_dir($folderPath)){
			throw new PluginException("The $folderPath must be a folder.");
		}

		parent::__construct(
			"Confirm to delete folder.",
			"Are you sure to delete the folder $folderPath and all its contents?",
			static function(Player $player, bool $choice) use ($plugin, $folderPath) : void{
				if($choice){
					Filesystem::recursiveUnlink($folderPath);
					$form = new FileExplorerForm($plugin, dirname($folderPath), $player);
				}else{
					$form = new FileExplorerForm($plugin, $folderPath, $player);
				}
				$player->sendForm($form);
			}
		);
	}
}