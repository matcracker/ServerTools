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

namespace matcracker\ServerTools\commands;

use matcracker\ServerTools\forms\MainMenuForm;
use matcracker\ServerTools\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\utils\TextFormat;
use function mb_strtolower;

final class ServerToolsCommand extends Command implements PluginOwned{

	public function __construct(private readonly Main $plugin){
		$pluginName = $plugin->getName();
		parent::__construct(
			mb_strtolower($pluginName),
			"Main command for $pluginName plugin.",
			"/servertools",
			["servertool", "st"]
		);
		$this->setPermission("st.command.servertools");
		$this->setPermissionMessage(Main::formatMessage(TextFormat::RED . "You do not have permission to use this command"));
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : bool{
		if(!($sender instanceof Player)){
			$sender->sendMessage(Main::formatMessage("The command must be run in-game."));

			return false;
		}

		if(!$this->testPermission($sender)){
			return false;
		}

		$sender->sendForm(new MainMenuForm($this->plugin));

		return true;
	}

	public function getOwningPlugin() : Main{
		return $this->plugin;
	}
}