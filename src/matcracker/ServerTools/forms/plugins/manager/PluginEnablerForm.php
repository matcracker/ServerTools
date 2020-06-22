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

namespace matcracker\ServerTools\forms\plugins\manager;

use matcracker\FormLib\Form;
use matcracker\ServerTools\forms\FormManager;
use matcracker\ServerTools\Main;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use function array_key_exists;
use function array_values;

final class PluginEnablerForm extends Form{

	public function __construct(){
		$pluginManager = Server::getInstance()->getPluginManager();
		$plugins = array_values($pluginManager->getPlugins());

		parent::__construct(
			static function(Player $player, $data) use ($pluginManager, $plugins): void{
				if(!array_key_exists((int) $data, $plugins)){
					$player->sendMessage(Main::formatMessage(TextFormat::RED . "Plugin does not exist."));

					return;
				}

				$plugin = $plugins[$data];
				if($en = $plugin->isEnabled()){
					$pluginManager->disablePlugin($plugin);
					$player->sendMessage(Main::formatMessage(TextFormat::RED . "The plugin {$plugin->getName()} has been disabled."));
				}else{
					$pluginManager->enablePlugin($plugin);
					$player->sendMessage(Main::formatMessage(TextFormat::GREEN . "The plugin {$plugin->getName()} has been enabled."));
				}
			},
			FormManager::onClose(new PluginManagerForm())
		);
		$this->setTitle("Enable/Disable Plugins")
			->setMessage(
				TextFormat::BOLD . TextFormat::GOLD . "WARNING! USE THIS FUNCTION CAREFULLY:" . TextFormat::EOL .
				TextFormat::RESET . TextFormat::GOLD . "- The plugin commands will still remain usable (they could cause a crash if used)." . TextFormat::EOL .
				"- Disabling and re-enabling a plugin could cause your server to crash."
			);

		foreach($plugins as $plugin){
			$version = $plugin->getDescription()->getVersion();
			$this->addClassicButton(($plugin->isEnabled() ? TextFormat::DARK_GREEN : TextFormat::RED) . "{$plugin->getName()} v{$version}");
		}
	}
}