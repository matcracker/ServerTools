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

use FilesystemIterator;
use matcracker\ServerTools\ftp\BaseFTPHandler;
use matcracker\ServerTools\ftp\FTPHandler;
use matcracker\ServerTools\Main;
use matcracker\ServerTools\utils\Utils;
use pmmp\thread\ThreadSafeArray;
use pocketmine\snooze\SleeperHandlerEntry;
use pocketmine\thread\log\AttachableThreadSafeLogger;
use pocketmine\thread\Thread;
use pocketmine\utils\Terminal;
use pocketmine\utils\TextFormat;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use Symfony\Component\Filesystem\Path;
use UnexpectedValueException;
use function count;
use function fileperms;
use function filesize;
use function igbinary_serialize;
use function igbinary_unserialize;
use function implode;
use function is_dir;
use function microtime;
use function round;
use function str_repeat;

final class FTPThread extends Thread{
	private const PROGRESS_BAR_SYMBOL_COMPLETE = "■";
	private const PROGRESS_BAR_SYMBOL_NO_COMPLETE = " ";
	private const NOT_CONNECTED = 127;
	private const PROGRESSBAR_SIZE = 25;
	private SleeperHandlerEntry $sleeperHandlerEntry;
	private AttachableThreadSafeLogger $logger;
	private string $serverPath;
	private string $ftpHandler;
	private ThreadSafeArray $filePaths;
	private int $error = self::NOT_CONNECTED;

	/**
	 * FTPThread constructor.
	 *
	 * @param Main           $plugin
	 * @param BaseFTPHandler $ftpHandler
	 * @param string[]       $filePaths
	 */
	public function __construct(
		Main $plugin,
		BaseFTPHandler $ftpHandler,
		array $filePaths
	){
		if(!$ftpHandler::hasExtension()){
			throw new RuntimeException("Missing extension");
		}

		$server = $plugin->getServer();
		$plugin->isCloning = true;
		$worldManager = $server->getWorldManager();
		$loadedWorlds = [];

		foreach($worldManager->getWorlds() as $world){
			$loadedWorlds[] = $world->getFolderName();
			$worldManager->unloadWorld($world, true);
		}

		$this->sleeperHandlerEntry = $server->getTickSleeper()->addNotifier(
			function() use ($plugin, $ftpHandler, $worldManager, $loadedWorlds) : void{
				$protocolName = $ftpHandler->getProtocolName();

				$message = match ($this->error) {
					BaseFTPHandler::NO_ERROR => "Cloning process has been completed.",
					BaseFTPHandler::ERR_CONNECT => TextFormat::RED . "Could not connect to the $protocolName server.",
					BaseFTPHandler::ERR_DISCONNECT => TextFormat::RED . "Could not disconnect from the $protocolName server.",
					BaseFTPHandler::ERR_LOGIN => TextFormat::RED . "Wrong username or password for the $protocolName server.",
					default => throw new UnexpectedValueException("Unhandled error code: $this->error")
				};

				$plugin->getLogger()->info($message);

				foreach($loadedWorlds as $world){
					$worldManager->loadWorld($world);
				}
				$plugin->isCloning = false;
			}
		);

		$this->logger = $server->getLogger();
		$this->serverPath = $server->getDataPath();
		$this->ftpHandler = igbinary_serialize($ftpHandler);
		$this->filePaths = ThreadSafeArray::fromArray($filePaths);

		$this->start();
	}

	public function onRun() : void{
		$notifier = $this->sleeperHandlerEntry->createNotifier();
		/** @var FTPHandler $ftpHandler */
		$ftpHandler = igbinary_unserialize($this->ftpHandler);
		$this->error = $ftpHandler->connect();

		if($this->error !== BaseFTPHandler::NO_ERROR){
			$notifier->wakeupSleeper();

			return;
		}

		$totalBytes = $sentBytes = 0;

		/** @var string[] $files */
		$files = [];
		foreach($this->filePaths as $filePath){
			$files[] = $filePath;

			if(is_dir($filePath)){
				$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($filePath, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);

				/** @var RecursiveDirectoryIterator $file */
				foreach($iterator as $file){
					$files[] = $file->getRealPath();
					if($file->isFile()){
						$totalBytes += $file->getSize() ?: 0;
					}
				}
			}else{
				$totalBytes += filesize($filePath);
			}
		}

		$memTime = microtime(true);
		$oldBytes = $bytesSpeed = 0;

		/** @var string[] $failedFiles */
		$failedFiles = [];

		foreach($files as $filePath){
			$remotePath = Path::join($ftpHandler->getHomePath(), Path::makeRelative($filePath, $this->serverPath));

			if(is_dir($filePath)){
				$perms = fileperms($filePath);

				if(!$ftpHandler->putDirectory($remotePath, $perms !== false ? $perms : 0644)){
					$failedFiles[] = $remotePath;
				}
			}else{
				if(!$ftpHandler->putFile($filePath, $remotePath)){
					$failedFiles[] = $remotePath;
				}else{
					$sentBytes += filesize($filePath);
				}
			}

			$currTime = microtime(true);

			if($currTime - $memTime >= 1){
				$memTime = $currTime;
				$bytesSpeed = $sentBytes - $oldBytes;
				$oldBytes = $sentBytes;
			}

			$bytesRatio = $sentBytes / $totalBytes;

			$percentage = round($bytesRatio * 100.0, 1);
			$steps = (int) ($bytesRatio * self::PROGRESSBAR_SIZE);

			$bar = TextFormat::YELLOW . Main::PLUGIN_NAME . " cloning: " .
				TextFormat::WHITE . "[" . TextFormat::GREEN . str_repeat(self::PROGRESS_BAR_SYMBOL_COMPLETE, $steps) . //Current progress
				TextFormat::RED . str_repeat(self::PROGRESS_BAR_SYMBOL_NO_COMPLETE, self::PROGRESSBAR_SIZE - $steps) . TextFormat::WHITE . "] " .//Remaining progress
				TextFormat::AQUA . Utils::bytesToHuman($sentBytes) . "/" . Utils::bytesToHuman($totalBytes) . " - $percentage% " . //Percentage
				"(Speed: " . Utils::bytesToHuman($bytesSpeed) . "/s)";

			Terminal::write("\r$bar");
		}

		Terminal::write(TextFormat::EOL);

		$ftpHandler->disconnect();

		$notifier->wakeupSleeper();

		if(count($failedFiles) > 0){
			$this->logger->warning("The following files has not been cloned due to error:" . implode(TextFormat::EOL . "- ", $failedFiles));
		}
	}
}