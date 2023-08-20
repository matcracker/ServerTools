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

use matcracker\ServerTools\ftp\BaseFTPConnection;
use matcracker\ServerTools\Main;
use matcracker\ServerTools\utils\FormUtils;
use matcracker\ServerTools\utils\Utils;
use pmmp\thread\ThreadSafeArray;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use RuntimeException;
use SplFileInfo;
use Symfony\Component\Filesystem\Path;
use function count;
use function igbinary_serialize;
use function igbinary_unserialize;
use function implode;
use function is_array;
use function is_int;
use function iterator_to_array;
use function round;
use function str_repeat;

final class FTPConnectionTask extends AsyncTask{

	private const PROGRESSBAR_SYMBOL = "■";
	private const PROGRESSBAR_SIZE = 25;

	private string $ftpConnection;
	private string $serverPath;
	private ThreadSafeArray $filter;
	private string $playerName;
	private string $protocol;

	/**
	 * AsyncFTPConnection constructor.
	 *
	 * @param BaseFTPConnection $ftpConnection
	 * @param string            $serverPath
	 * @param string[]          $filter
	 * @param string            $playerName
	 */
	public function __construct(BaseFTPConnection $ftpConnection, string $serverPath, array $filter, string $playerName){
		if(!$ftpConnection::hasExtension()){
			throw new RuntimeException("Missing extension");
		}
		$this->ftpConnection = igbinary_serialize($ftpConnection);
		$this->serverPath = $serverPath;
		$this->filter = ThreadSafeArray::fromArray($filter);
		$this->playerName = $playerName;
		$this->protocol = $ftpConnection->getProtocolName();
	}

	public function onRun() : void{
		/**@var BaseFTPConnection $ftpConnection */
		$ftpConnection = igbinary_unserialize($this->ftpConnection);

		/** @var int|resource $ftpStream */
		$ftpStream = $ftpConnection->connect();

		if(!is_int($ftpStream)){
			/** @var SplFileInfo[] $files */
			$files = iterator_to_array(Utils::getRecursiveIterator(
				$this->serverPath, iterator_to_array($this->filter)
			));

			$totalBytes = 0;

			foreach($files as $fileInfo){
				$totalBytes += $fileInfo->getSize();
			}

			$failedFiles = [];
			$sentBytes = 0;

			$this->publishProgress(0);

			foreach($files as $fileInfo){
				$localPath = $fileInfo->getRealPath();
				$remotePath = Path::join($ftpConnection->getHomePath(), Path::makeRelative($localPath, $this->serverPath));

				$mode = $fileInfo->getPerms();

				if($fileInfo->isDir()){
					if(!$ftpConnection->putDirectory($ftpStream, $remotePath, $mode)){
						$failedFiles[] = $remotePath;
					}
				}else{
					if(!$ftpConnection->putFile($ftpStream, $localPath, $remotePath)){
						$failedFiles[] = $localPath;
					}

					$sentBytes += $fileInfo->getSize();
				}

				$percentage = round(($sentBytes / $totalBytes) * 100.0, 1);
				$this->publishProgress($percentage);
			}

			$this->setResult($failedFiles);

			$ftpConnection->disconnect($ftpStream);
		}else{
			$this->setResult($ftpStream);
		}
	}

	public function onProgressUpdate($progress) : void{
		if(($player = Server::getInstance()->getPlayerExact($this->playerName)) === null){
			return;
		}

		$steps = (int) (($progress / 100.0) * self::PROGRESSBAR_SIZE);

		$bar = TextFormat::YELLOW . "Cloning Progress" . TextFormat::EOL . //Title
			TextFormat::WHITE . "[" . TextFormat::GREEN . str_repeat(self::PROGRESSBAR_SYMBOL, $steps) . //Current progress
			TextFormat::RED . str_repeat(self::PROGRESSBAR_SYMBOL, self::PROGRESSBAR_SIZE - $steps) . TextFormat::WHITE . "] " .//Remaining progress
			TextFormat::AQUA . "$progress %%"; //Percentage

		$player->sendTip($bar);
	}

	public function onCompletion() : void{
		if(($player = Server::getInstance()->getPlayerExact($this->playerName)) === null){
			return;
		}

		/**@var string[]|int $result */
		$result = $this->getResult();

		if(is_int($result)){
			switch($result){
				case BaseFTPConnection::ERR_CONNECT:
					$player->sendMessage(Main::formatMessage(TextFormat::RED . "Could not connect to the " . $this->protocol . " server."));
					break;
				case BaseFTPConnection::ERR_LOGIN:
					$player->sendMessage(Main::formatMessage(TextFormat::RED . "Wrong username or password for the " . $this->protocol . " server."));
					break;
				case BaseFTPConnection::ERR_DISCONNECT:
					$player->sendMessage(Main::formatMessage(TextFormat::RED . "Could not disconnect from the " . $this->protocol . " server."));
					break;
			}
		}elseif(is_array($result)){
			if(count($result) > 0){
				$player->sendForm(FormUtils::getConfirmForm("Not uploaded files", TextFormat::RED . "- " . implode("\n- ", $result)));
			}else{
				$player->sendMessage(Main::formatMessage(TextFormat::GREEN . "Cloning process has been completed."));
			}
		}
	}
}