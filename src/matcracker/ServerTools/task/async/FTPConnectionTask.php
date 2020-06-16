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

namespace matcracker\ServerTools\task\async;

use matcracker\ServerTools\forms\BaseForms;
use matcracker\ServerTools\ftp\FTPBase;
use matcracker\ServerTools\ftp\FTPConnection;
use matcracker\ServerTools\ftp\SFTPConnection;
use matcracker\ServerTools\Main;
use matcracker\ServerTools\utils\Utils;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use RuntimeException;
use SplFileInfo;
use function count;
use function floor;
use function implode;
use function is_array;
use function is_int;
use function is_resource;
use function serialize;
use function str_repeat;
use function str_replace;
use function unserialize;

final class FTPConnectionTask extends AsyncTask{

	private const PROGRESSBAR_SIZE = 25;

	private $ftpConnection;
	private $serverPath;
	private $filter;
	private $playerName;
	private $protocol;

	/**
	 * AsyncFTPConnection constructor.
	 *
	 * @param FTPBase  $ftpConnection
	 * @param string   $serverPath
	 * @param string[] $filter
	 * @param string   $playerName
	 */
	public function __construct(FTPBase $ftpConnection, string $serverPath, array $filter, string $playerName){
		if(!$ftpConnection::hasExtension()){
			throw new RuntimeException("Missing extension");
		}
		$this->ftpConnection = serialize($ftpConnection);
		$this->serverPath = $serverPath;
		$this->filter = serialize($filter);
		$this->playerName = $playerName;
		$this->protocol = $ftpConnection::getProtocolName();
	}

	public function onRun() : void{
		$class = $this->protocol === "FTP" ? FTPConnection::class : SFTPConnection::class;
		/**@var FTPBase $ftp */
		$ftp = unserialize($this->ftpConnection, ["allowed_classes" => [$class]]);

		$ftpStream = $ftp->connect();

		if(is_resource($ftpStream)){
			/**@var string[] $filter */
			$filter = unserialize($this->filter);
			$iterator = Utils::getRecursiveIterator($this->serverPath, $filter);
			$totalBytes = Utils::getIteratorSize($iterator);

			$failedFiles = [];
			$sentBytes = 0;

			$this->publishProgress(0);

			/**@var SplFileInfo $fileInfo */
			foreach($iterator as $fileInfo){
				$localPath = $fileInfo->getRealPath();
				$remotePath = Utils::getUnixPath($ftp->getHomePath() . str_replace($this->serverPath, "", $localPath));

				$mode = $fileInfo->getPerms();

				if($fileInfo->isDir()){
					$ftp->putDirectory($ftpStream, $remotePath, $mode);
				}else{
					if(!$ftp->putFile($ftpStream, $localPath, $remotePath, $mode)){
						$failedFiles[] = $localPath;
					}

					$sentBytes += $fileInfo->getSize();
				}

				$progress = (int) floor(($sentBytes / $totalBytes) * self::PROGRESSBAR_SIZE);
				$this->publishProgress($progress);
			}

			if($ftp->disconnect($ftpStream)){
				$this->setResult($failedFiles);
			}else{
				$this->setResult(FTPConnection::ERR_DISCONNECT);
			}
		}else{
			$this->setResult($ftpStream);
		}
	}

	public function onProgressUpdate(Server $server, $progress) : void{
		$server = Server::getInstance();

		if(($player = $server->getPlayer($this->playerName)) === null){
			return;
		}

		$percentage = (int) floor(($progress / self::PROGRESSBAR_SIZE) * 100);

		$bar = TextFormat::YELLOW . "Cloning Progress" . TextFormat::EOL . //Title
			TextFormat::WHITE . "[" . TextFormat::GREEN . str_repeat("|", $progress) . //Current progress
			TextFormat::RED . str_repeat("|", self::PROGRESSBAR_SIZE - $progress) . TextFormat::WHITE . "] " .//Remaining progress
			TextFormat::AQUA . "{$percentage} %%"; //Percentage

		$player->sendTip($bar);
	}

	public function onCompletion(Server $server) : void{
		$server = Server::getInstance();

		if(($player = $server->getPlayerExact($this->playerName)) === null){
			return;
		}

		/**@var string[]|int $result */
		$result = $this->getResult();

		if(is_int($result)){
			switch($result){
				case FTPConnection::ERR_CONNECT:
					$player->sendMessage(Main::formatMessage(TextFormat::RED . "Could not connect to the " . $this->protocol . " server."));
					break;
				case FTPConnection::ERR_LOGIN:
					$player->sendMessage(Main::formatMessage(TextFormat::RED . "Wrong username or password for the " . $this->protocol . " server."));
					break;
				case FTPConnection::ERR_DISCONNECT:
					$player->sendMessage(Main::formatMessage(TextFormat::RED . "Could not disconnect from the " . $this->protocol . " server."));
					break;
			}
		}elseif(is_array($result)){
			if(count($result) > 0){
				$player->sendForm(BaseForms::getConfirmForm("Not uploaded files", TextFormat::RED . "- " . implode("\n- ", $result)));
			}else{
				$player->sendMessage(Main::formatMessage(TextFormat::GREEN . "Cloning process has been completed."));
			}
		}
	}
}