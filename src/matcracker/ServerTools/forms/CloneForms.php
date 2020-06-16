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

namespace matcracker\ServerTools\forms;

use matcracker\FormLib\CustomForm;
use matcracker\FormLib\Form;
use matcracker\ServerTools\ftp\FTPBase;
use matcracker\ServerTools\ftp\FTPConnection;
use matcracker\ServerTools\ftp\SFTPConnection;
use matcracker\ServerTools\Main;
use matcracker\ServerTools\task\async\FTPConnectionTask;
use matcracker\ServerTools\utils\Utils;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use UnexpectedValueException;
use function is_array;
use function is_int;
use function is_numeric;

final class CloneForms extends BaseForms{

	private static function getExcludedFilesForm(FTPBase $ftpConnection) : CustomForm{
		return (new CustomForm(
			static function(Player $player, $data) use ($ftpConnection){
				if(!is_array($data)){
					throw new UnexpectedValueException("Unexpected value parsed from Form.");
				}

				Server::getInstance()->getAsyncPool()->submitTask(
					new FTPConnectionTask($ftpConnection, Utils::getServerPath(), self::getFileFilter($data), $player->getName())
				);
			}
		))->setTitle("Exclude files")
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

	private static function getFTPForm() : CustomForm{
		return self::getBaseFTPForm()->setTitle("FTP Settings")->addToggle("Use SSL", true);
	}

	private static function getSFTPForm() : CustomForm{
		return self::getBaseFTPForm()->setTitle("SFTP Settings");
	}

	public static function getMainForm() : Form{
		$form = (new Form(
			static function(Player $player, $data){
				if(!is_int($data)){
					throw new UnexpectedValueException("Unexpected value parsed from Form.");
				}

				if($data === 0){
					$player->sendForm(SFTPConnection::hasExtension() ? self::getSFTPForm() : self::getFTPForm());
				}elseif($data === 1){
					$player->sendForm(self::getFTPForm());
				}
			},
			self::onClose(BaseForms::getMainForm())
		))->setTitle("Transfer Mode")
			->setMessage("Select a mode to send your server data to another one.");

		if(SFTPConnection::hasExtension()){
			$form->addClassicButton("SFTP");
		}

		if(FTPConnection::hasExtension()){
			$form->addClassicButton("FTP");
		}

		return $form;
	}

	private static function getBaseFTPForm() : CustomForm{
		return (new CustomForm(
			static function(Player $player, $data){
				if(!is_array($data)){
					throw new UnexpectedValueException("Unexpected value parsed from Form.");
				}

				$host = (string) $data[1];

				if(!is_numeric($data[2])){
					$player->sendMessage(Main::formatMessage(TextFormat::RED . "You must insert a numeric value to the field \"Port\""));

					return;
				}

				$port = (int) $data[2];
				if($port < 0 || $port > 65535){
					$player->sendMessage(Main::formatMessage(TextFormat::RED . "Invalid port range! It must be between 0 and 65535."));

					return;
				}

				$username = (string) $data[3];
				$password = (string) $data[4];
				$remoteHomePath = (string) $data[5];

				if(isset($data[6])){
					$ssl = (bool) $data[6];
					$ftpConnection = new FTPConnection($host, $port, $username, $password, $remoteHomePath, $ssl);
				}else{
					$ftpConnection = new SFTPConnection($host, $port, $username, $password, $remoteHomePath);
				}

				$player->sendForm(self::getExcludedFilesForm($ftpConnection));
			},
			self::onClose(self::getMainForm())
		))->addLabel("The following form will not immediately validated.")
			->addInput("Host address")
			->addInput("Port", "21")
			->addInput("Username", "admin")
			->addInput("Password", "**********")
			->addInput("Remote home path", "/");
	}

	/**
	 * @param bool[] $data
	 *
	 * @return string[]
	 */
	private static function getFileFilter(array $data) : array{
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