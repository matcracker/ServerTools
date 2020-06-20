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
use matcracker\ServerTools\forms\FormManager;
use matcracker\ServerTools\ftp\FTPConnection;
use matcracker\ServerTools\ftp\SFTPConnection;
use matcracker\ServerTools\Main;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use function is_numeric;

final class BaseFTPForm extends CustomForm{

	public function __construct(string $title){
		parent::__construct(
			static function(Player $player, $data): void{
				$host = (string) $data["host"];

				if(!is_numeric($data["port"])){
					$player->sendMessage(Main::formatMessage(TextFormat::RED . "You must insert a numeric value to the field \"Port\""));

					return;
				}

				$port = (int) $data["port"];
				if($port < 0 || $port > 65535){
					$player->sendMessage(Main::formatMessage(TextFormat::RED . "Invalid port range! It must be between 0 and 65535."));

					return;
				}

				$username = (string) $data["username"];
				$password = (string) $data["password"];
				$remoteHomePath = (string) $data["remote_path"];

				if(isset($data[6])){
					$ssl = (bool) $data[6];
					$ftpConnection = new FTPConnection($host, $port, $username, $password, $remoteHomePath, $ssl);
				}else{
					$ftpConnection = new SFTPConnection($host, $port, $username, $password, $remoteHomePath);
				}

				$player->sendForm(new ExcludeFilesForm($ftpConnection));
			},
			FormManager::onClose(FormManager::getInstance()->getMainMenu())
		);

		$this->setTitle($title)
			->addLabel("The following form will not immediately validated.")
			->addInput("Host address", "host")
			->addInput("Port", "21", "port")
			->addInput("Username", "admin", "username")
			->addInput("Password", "**********", "password")
			->addInput("Remote home path", "/", "remote_path");
	}
}