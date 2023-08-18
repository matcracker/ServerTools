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

use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use matcracker\ServerTools\Main;
use matcracker\ServerTools\utils\FormUtils;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use function array_values;

final class PluginEnablerForm extends MenuForm{

	public function __construct(Main $plugin){
		$pluginManager = $plugin->getServer()->getPluginManager();
		$plugins = array_values($pluginManager->getPlugins());

		$options = [];
		foreach($plugins as $pl){
			$version = $pl->getDescription()->getVersion();
			$options[] = new MenuOption(($pl->isEnabled() ? TextFormat::DARK_GREEN : TextFormat::RED) . "{$pl->getName()} v$version");
		}

		parent::__construct(
			"Enable/Disable Plugins",
			TextFormat::BOLD . TextFormat::GOLD . "WARNING! USE THIS FUNCTION CAREFULLY:" . TextFormat::EOL .
			TextFormat::RESET . TextFormat::GOLD . "- The plugin commands will still remain usable (they could cause a crash if used)." . TextFormat::EOL .
			"- Disabling and re-enabling a plugin could cause your server to crash.",
			$options,
			static function(Player $player, int $selectedOption) use ($pluginManager, $plugins) : void{
				$plugin = $plugins[$selectedOption];
				if($plugin->isEnabled()){
					$pluginManager->disablePlugin($plugin);
					$player->sendMessage(Main::formatMessage(TextFormat::RED . "The plugin {$plugin->getName()} has been disabled."));
				}else{
					$pluginManager->enablePlugin($plugin);
					$player->sendMessage(Main::formatMessage(TextFormat::GREEN . "The plugin {$plugin->getName()} has been enabled."));
				}
			},
			FormUtils::onClose(new PluginManagerForm($plugin))
		);
	}
}