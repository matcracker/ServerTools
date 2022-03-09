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

use matcracker\FormLib\CustomForm;
use matcracker\ServerTools\ftp\FTPBase;
use matcracker\ServerTools\task\async\FTPConnectionTask;
use matcracker\ServerTools\utils\Utils;
use pocketmine\player\Player;
use pocketmine\Server;
use function is_string;

final class ExcludeFilesForm extends CustomForm{

	public function __construct(FTPBase $ftpConnection){
		parent::__construct(
			static function(Player $player, $data) use ($ftpConnection){
				/** @var string[] $filter */
				$filter = [];

				/** @var bool $flag */
				foreach($data as $file => $flag){
					if(is_string($file) && $flag){
						$filter[] = $file;
					}
				}

				Server::getInstance()->getAsyncPool()->submitTask(
					new FTPConnectionTask($ftpConnection, Utils::getServerPath(), $filter, $player->getName())
				);
			}
		);

		$this->setTitle("Exclude files")
			->addLabel("Do you want to exclude something from the clone?")
			->addLabel("Server Folders")
			->addToggle("Players Data", null, "players")
			->addToggle("Plugins", null, "plugins")
			->addToggle("Plugins Data", null, "plugins_data")
			->addToggle("Resource Packs", null, "resource_packs")
			->addToggle("Worlds", null, "worlds")
			->addLabel("Server Files")
			->addToggle("Banned Players", null, "banned-players.txt,banned-ips.txt")
			->addToggle("Operators (OP)", null, "ops.txt")
			->addToggle("Configuration", null, "pocketmine.yml")
			->addToggle("Logs", null, "server.log")
			->addToggle("Properties", null, "server.properties")
			->addToggle("White-list", null, "white-list.txt");
	}
}