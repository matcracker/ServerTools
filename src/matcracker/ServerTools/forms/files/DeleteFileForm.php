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
use InvalidArgumentException;
use matcracker\ServerTools\Main;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use function dirname;
use function is_file;
use function unlink;

final class DeleteFileForm extends ModalForm{

	public function __construct(Main $plugin, string $filePath){
		if(!is_file($filePath)){
			throw new InvalidArgumentException("The $filePath must be a file.");
		}

		parent::__construct(
			"Confirm to delete file.",
			"Are you sure to delete the file $filePath?",
			static function(Player $player, bool $choice) use ($plugin, $filePath) : void{
				if($choice){
					if(!unlink($filePath)){
						$player->sendMessage(Main::formatMessage(TextFormat::RED . "Could not delete file $filePath."));

						return;
					}
					$form = new FileExplorerForm($plugin, dirname($filePath), $player);
				}else{
					$form = new FileEditorForm($plugin, $filePath, $player);
				}
				$player->sendForm($form);
			}
		);
	}
}