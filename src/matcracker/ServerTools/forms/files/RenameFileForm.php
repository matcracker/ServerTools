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
use InvalidArgumentException;
use matcracker\ServerTools\Main;
use matcracker\ServerTools\utils\FormUtils;
use matcracker\ServerTools\utils\Utils;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use Symfony\Component\Filesystem\Path;
use function basename;
use function dirname;
use function is_file;
use function rename;
use function strlen;
use function trim;

final class RenameFileForm extends FileInputForm{

	public function __construct(Main $plugin, string $filePath, Player $player, string $error = ""){
		if(!is_file($filePath)){
			throw new InvalidArgumentException("The $filePath must be a file.");
		}

		parent::__construct(
			"Rename file",
			"Insert new file name:",
			basename($filePath),
			basename($filePath),
			$error,
			function(Player $player, CustomFormResponse $response) use ($plugin, $filePath) : void{
				$fileName = $response->getString(self::FORM_KEY_FILE_NAME);
				if(strlen(trim($fileName)) === 0 || !Utils::isValidFileName($fileName)){
					$player->sendForm(new self($plugin, $filePath, $player, "Invalid name \"$fileName\" for this file. Try again"));

					return;
				}

				$newPath = Path::join(dirname($filePath), $fileName);
				if(rename($filePath, $newPath)){
					$player->sendForm(new FileEditorForm($plugin, $newPath, $player));
				}else{
					$player->sendMessage(Main::formatMessage(TextFormat::RED . "Could not rename the file $filePath"));
				}
			},
			FormUtils::onClose(new FileExplorerForm($plugin, dirname($filePath), $player))
		);
	}
}