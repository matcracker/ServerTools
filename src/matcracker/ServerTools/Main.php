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
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginException;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Utils;
use Symfony\Component\Filesystem\Path;
use function file_exists;
use function mb_strtolower;
use function pcntl_exec;
use function register_shutdown_function;
use function sprintf;

final class Main extends PluginBase{
	private const CONFIG_VERSION = 2;
	public const PLUGIN_NAME = "ServerTools";

	public static function formatMessage(string $message) : string{
		return TextFormat::AQUA . sprintf("[%s] ", self::PLUGIN_NAME) . TextFormat::RESET . $message;
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
			register_shutdown_function(static fn() => pcntl_exec("./$startFileName"));
		}

		$this->getServer()->shutdown();

		return true;
	}

	public function onLoad() : void{
		UpdateNotifier::checkUpdate($this->getName(), $this->getDescription()->getVersion());
	}

	public function onEnable() : void{
		ConfigUpdater::checkUpdate($this, $this->getConfig(), "config-version", self::CONFIG_VERSION);

		$this->getServer()->getCommandMap()->register(mb_strtolower($this->getName()), new ServerToolsCommand($this));
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
	}

	public function getServerDataPath() : string{
		return Path::canonicalize($this->getServer()->getDataPath());
	}

	public function canBypassPermission(Player $player) : bool{
		$isOp = $this->getServer()->isOp($player->getName());

		return $isOp && $this->getConfig()->get("op-bypass-permissions");
	}

	public static function getInstance() : Main{
		$instance = Server::getInstance()->getPluginManager()->getPlugin(self::PLUGIN_NAME);

		if(!($instance instanceof Main)){
			throw new PluginException(sprintf("%s instance could not be accessed because it is disabled or not loaded yet.", self::PLUGIN_NAME));
		}

		return $instance;
	}

}