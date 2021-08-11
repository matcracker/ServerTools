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

namespace matcracker\ServerTools;

use JackMD\ConfigUpdater\ConfigUpdater;
use JackMD\UpdateNotifier\UpdateNotifier;
use matcracker\ServerTools\commands\ServerToolsCommand;
use matcracker\ServerTools\task\thread\RestartWindowsServer;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginException;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Utils;
use function file_exists;
use function pcntl_exec;
use function register_shutdown_function;

final class Main extends PluginBase{
	private const CONFIG_VERSION = 2;
	private static ?Main $instance;

	public static function getInstance() : Main{
		if(self::$instance === null){
			throw new PluginException("ServerTools instance could not be accessed because it is disabled or not loaded yet.");
		}

		return self::$instance;
	}

	public function restartServer() : bool{
		$startFileName = $this->getConfig()->getNested("restart.file-name");

		if(!file_exists($startFileName)){
			return false;
		}

		$this->getLogger()->notice(self::formatMessage("Restarting the server..."));

		if(Utils::getOS() === Utils::OS_WINDOWS){
			(new RestartWindowsServer($startFileName))->start();
		}else{
			register_shutdown_function(
				static function() use ($startFileName) : void{
					pcntl_exec("./$startFileName");
				}
			);
		}

		$this->getServer()->shutdown();

		return true;
	}

	public static function formatMessage(string $message) : string{
		return TextFormat::AQUA . "[ServerTools] " . TextFormat::RESET . $message;
	}

	public function onLoad() : void{
		self::$instance = $this;

		UpdateNotifier::checkUpdate($this->getName(), $this->getDescription()->getVersion());
	}

	public function onEnable() : void{
		ConfigUpdater::checkUpdate($this, $this->getConfig(), "config-version", self::CONFIG_VERSION);

		$this->getServer()->getCommandMap()->register("servertools", new ServerToolsCommand($this));
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
	}

	public function onDisable(){
		self::$instance = null;
	}

}