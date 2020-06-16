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

use matcracker\ServerTools\Main;
use matcracker\ServerTools\utils\PluginInfo;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use function basename;
use function file_get_contents;
use function file_put_contents;

final class DownloadPoggitPluginTask extends AsyncTask{

	/** @var string */
	private $playerName;
	/** @var string */
	private $pluginPath;
	/** @var PluginInfo */
	private $pluginInfo;

	public function __construct(string $playerName, string $pluginPath, PluginInfo $pluginInfo){
		$this->playerName = $playerName;
		$this->pluginPath = $pluginPath;
		$this->pluginInfo = $pluginInfo;
	}

	public function onRun() : void{
		$fileName = basename($this->pluginInfo->getDownloadLink());
		$path = $this->pluginPath . $fileName;

		if(file_put_contents($path, file_get_contents($this->pluginInfo->getDownloadLink()))){
			$this->setResult(true);
		}
	}

	public function onCompletion(Server $server) : void{
		$player = Server::getInstance()->getPlayer($this->playerName);
		if($player === null){
			return;
		}

		$pluginName = $this->pluginInfo->getPluginName();

		if($this->getResult() !== null){
			$player->sendMessage(Main::formatMessage(TextFormat::GREEN . "{$pluginName} successfully downloaded."));
		}else{
			$player->sendMessage(Main::formatMessage(TextFormat::RED . "Could not download {$pluginName} plugin."));
		}
	}
}