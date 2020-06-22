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

namespace matcracker\ServerTools\forms\plugins\downloader;

use matcracker\FormLib\CustomForm;
use matcracker\ServerTools\forms\FormManager;
use matcracker\ServerTools\task\async\SearchPluginTask;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use function array_rand;

final class SearchPluginForm extends CustomForm{

	private const SPONSOR_PLUGINS = [
		"BedcoreProtect", "ServerTools", "BlocksConverter"
	];

	public function __construct(?string $pluginNotFound = null){
		parent::__construct(
			function(Player $player, $data) : void{
				Server::getInstance()->getAsyncPool()->submitTask(
					new SearchPluginTask($data[0] ?? "", $player->getName())
				);
			},
			FormManager::onClose(FormManager::getMainMenu())
		);
		$this->setTitle("Search Poggit Plugin")
			->addInput("Insert the plugin name to search:", "e.g. " . self::SPONSOR_PLUGINS[array_rand(self::SPONSOR_PLUGINS)]);

		if($pluginNotFound !== null){
			$this->addLabel(TextFormat::RED . $pluginNotFound . " does not exist on Poggit.");
		}
	}
}