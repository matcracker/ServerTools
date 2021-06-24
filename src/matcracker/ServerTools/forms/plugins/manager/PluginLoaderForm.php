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
use function basename;
use function glob;

final class PluginLoaderForm extends Form{

	public function __construct(){
		$pluginPath = Server::getInstance()->getPluginPath();
		$files = glob($pluginPath . "*.phar");

		parent::__construct(
			static function(Player $player, $data) use ($files) : void{
				$pluginMng = Server::getInstance()->getPluginManager();
				if(($plugin = $pluginMng->loadPlugin($files[$data])) === null){
					$player->sendMessage(Main::formatMessage(TextFormat::RED . "Could not load " . basename($files[$data])));

					return;
				}

				$pluginMng->enablePlugin($plugin);
				foreach(Server::getInstance()->getOnlinePlayers() as $p){
					$p->sendCommandData();
				}

				$player->sendMessage(Main::formatMessage(TextFormat::GREEN . "{$plugin->getName()} successfully loaded and enabled. Check console for eventual errors."));
			},
			FormManager::onClose(new PluginManagerForm())
		);

		$this->setTitle("Load Plugin")->setMessage($pluginPath);

		foreach($files as $file){
			$this->addClassicButton(basename($file));
		}
	}
}