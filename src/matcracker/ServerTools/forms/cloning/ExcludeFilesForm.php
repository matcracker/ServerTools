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

namespace matcracker\ServerTools\forms\cloning;

use dktapps\pmforms\CustomForm;
use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\Label;
use dktapps\pmforms\element\Toggle;
use matcracker\ServerTools\forms\MainMenuForm;
use matcracker\ServerTools\ftp\BaseFTPConnection;
use matcracker\ServerTools\Main;
use matcracker\ServerTools\task\async\FTPConnectionTask;
use matcracker\ServerTools\utils\FormUtils;
use matcracker\ServerTools\utils\Utils;
use pocketmine\player\Player;
use function assert;
use function count;

final class ExcludeFilesForm extends CustomForm{

	private const KEY_EXCLUDE_FILES = "exclude_files";

	public function __construct(Main $plugin, BaseFTPConnection $ftpConnection){
		$elements = [
			new Label(self::KEY_EXCLUDE_FILES, "Do you want to exclude something from the clone?")
		];

		$files = Utils::getSortedFileList($plugin->getServerDataPath());

		foreach($files["dir"] as $dir){
			$elements[] = new Toggle($dir, $dir);
		}

		foreach($files["file"] as $file){
			$elements[] = new Toggle($file, $file);
		}

		assert(count($elements) > 1);

		parent::__construct(
			"Exclude files",
			$elements,
			static function(Player $player, CustomFormResponse $response) use ($plugin, $ftpConnection) : void{
				/** @var string[] $filter */
				$filter = [];

				foreach($response->getAll() as $fileNameKey => $toggled){
					if($fileNameKey === self::KEY_EXCLUDE_FILES){
						continue;
					}

					if($toggled){
						$filter[] = $fileNameKey;
					}
				}

				$worlds = $plugin->getServer()->getWorldManager()->getWorlds();

				foreach($worlds as $world){
					$world->save(true);
				}

				$plugin->getServer()->getAsyncPool()->submitTask(
					new FTPConnectionTask($ftpConnection, $plugin->getServerDataPath(), $filter, $player->getName())
				);
			},
			FormUtils::onClose(new MainMenuForm($plugin))
		);
	}
}