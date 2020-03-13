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

use JackMD\UpdateNotifier\UpdateNotifier;
use matcracker\ServerTools\commands\ServerToolsCommand;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Utils;
use function pcntl_exec;
use function register_shutdown_function;

final class Main extends PluginBase{

	public function onLoad() : void{
		UpdateNotifier::checkUpdate($this, $this->getName(), $this->getDescription()->getVersion());
	}

	public function onEnable() : void{
		@mkdir($this->getDataFolder());

		$this->getServer()->getCommandMap()->register('servertools', new ServerToolsCommand($this));
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
	}

	public static function restartServer(Server $server) : bool{
		if(Utils::getOS() !== "win"){
			register_shutdown_function(static function() : void{
				pcntl_exec("./start.sh");
			});

			$server->shutdown();

			return true;
		}

		return false;
	}

	public static function formatMessage(string $message) : string{
		return TextFormat::AQUA . "[ServerTools] " . TextFormat::RESET . $message;
	}

}