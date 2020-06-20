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
use pocketmine\Player;
use pocketmine\Server;
use UnexpectedValueException;
use function is_array;

final class ExcludeFilesForm extends CustomForm{

	public function __construct(FTPBase $ftpConnection){
		parent::__construct(
			static function(Player $player, $data) use ($ftpConnection){
				if(!is_array($data)){
					throw new UnexpectedValueException("Unexpected value parsed from Form.");
				}

				Server::getInstance()->getAsyncPool()->submitTask(
					new FTPConnectionTask($ftpConnection, Utils::getServerPath(), $this->getFileFilter($data), $player->getName())
				);
			}
		);

		$this->setTitle("Exclude files")
			->addLabel("Do you want to exclude something from the clone?")
			->addLabel("Server Folders")
			->addToggle("Players Data") //2
			->addToggle("Plugins") //3
			->addToggle("Plugins Data") //4
			->addToggle("Resource Packs") //5
			->addToggle("Worlds") //6
			->addLabel("Server Files")
			->addToggle("Banned Players") //8
			->addToggle("Operators (OP)") //9
			->addToggle("Configuration") //10
			->addToggle("Logs") //11
			->addToggle("Properties") //12
			->addToggle("White-list"); //13
	}

	/**
	 * @param bool[] $data
	 *
	 * @return string[]
	 */
	private function getFileFilter(array $data) : array{
		$filter = [];

		if($data[2]){
			$filter[] = "players";
		}
		if($data[3]){
			$filter[] = "plugins";
		}
		if($data[4]){
			$filter[] = "plugin_data";
		}
		if($data[5]){
			$filter[] = "resource_packs";
		}
		if($data[6]){
			$filter[] = "worlds";
		}
		if($data[8]){
			$filter[] = "banned-players.txt";
			$filter[] = "banned-ips.txt";
		}
		if($data[9]){
			$filter[] = "ops.txt";
		}
		if($data[10]){
			$filter[] = "pocketmine.yml";
		}
		if($data[11]){
			$filter[] = "server.log";
		}
		if($data[12]){
			$filter[] = "server.properties";
		}
		if($data[13]){
			$filter[] = "white-list.txt";
		}

		return $filter;
	}

}