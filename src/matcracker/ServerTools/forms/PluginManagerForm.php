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

use matcracker\FormLib\Form;
use matcracker\ServerTools\Main;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use UnexpectedValueException;
use function array_key_exists;
use function array_values;
use function basename;
use function glob;
use function is_int;

final class PluginManagerForm extends BaseForms{

	public static function getMainForm() : Form{
		return (new Form(
			function(Player $player, $data) : void{
				if(!is_int($data)){
					throw new UnexpectedValueException("Unexpected value parsed from Form.");
				}

				switch($data){
					case 0:
						$player->sendForm(self::getEnableDisableForm());
						break;
					case 1:
						$player->sendForm(self::getLoadPluginForm());
						break;
				}

			},
			parent::onClose(BaseForms::getMainForm())
		))->setTitle("Plugin Manager")
			->addClassicButton("Enable/Disable plugin")
			->addClassicButton("Load plugin from file");
	}

	private static function getEnableDisableForm() : Form{
		$pluginManager = Server::getInstance()->getPluginManager();
		$plugins = array_values($pluginManager->getPlugins());

		$form = (new Form(
			function(Player $player, $data) use ($pluginManager, $plugins): void{
				if(!is_int($data)){
					throw new UnexpectedValueException("Unexpected value parsed from Form.");
				}

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
			parent::onClose(self::getMainForm())
		))->setTitle("Enable/Disable Plugins")
			->setMessage(
				TextFormat::BOLD . TextFormat::GOLD . "WARNING: " . TextFormat::EOL .
				"- USE THIS FUNCTION CAREFULLY" . TextFormat::EOL .
				TextFormat::RESET . TextFormat::GOLD . "- The plugin commands will still remain usable (they could cause a crash if used)." . TextFormat::EOL .
				"- Disabling and re-enabling a plugin could cause your server to crash."
			);

		foreach($plugins as $plugin){
			$version = $plugin->getDescription()->getVersion();
			$form->addClassicButton(($plugin->isEnabled() ? TextFormat::DARK_GREEN : TextFormat::RED) . "{$plugin->getName()} v{$version}");
		}

		return $form;
	}

	private static function getLoadPluginForm() : Form{
		$pluginPath = Server::getInstance()->getPluginPath();
		$files = glob($pluginPath . "*.phar");

		$form = (new Form(
			function(Player $player, $data) use ($files) : void{
				if(!is_int($data)){
					throw new UnexpectedValueException("Unexpected value parsed from Form.");
				}

				$pluginMng = Server::getInstance()->getPluginManager();
				if(($plugin = $pluginMng->loadPlugin($files[$data])) === null){
					$player->sendMessage(Main::formatMessage(TextFormat::RED . "Could not load " . basename($files[$data])));

					return;
				}

				$pluginMng->enablePlugin($plugin);
				foreach(Server::getInstance()->getOnlinePlayers() as $player){
					$player->sendCommandData();
				}

				$player->sendMessage(Main::formatMessage(TextFormat::GREEN . "{$plugin->getName()} successfully loaded and enabled. Check console for eventual errors."));
			},
			parent::onClose(self::getMainForm())
		))->setTitle("Load Plugin")->setMessage($pluginPath);

		foreach($files as $file){
			$form->addClassicButton(basename($file));
		}

		return $form;
	}
}