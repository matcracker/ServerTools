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
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\Internet;
use pocketmine\utils\TextFormat;
use function file_put_contents;

final class DownloadPluginTask extends AsyncTask{
	private string $pluginName;
	private string $artifactUrl;
	private string $playerName;
	private string $pluginPath;
	private int $timeout;

	public function __construct(string $pluginName, string $artifactUrl, string $playerName, string $pluginPath){
		$this->pluginName = $pluginName;
		$this->artifactUrl = $artifactUrl;
		$this->playerName = $playerName;
		$this->pluginPath = $pluginPath;
		$this->timeout = (int) Main::getInstance()->getConfig()->getNested("poggit.timeout", 30);
	}

	public function onRun() : void{
		$request = Internet::getURL("$this->artifactUrl/$this->pluginName.phar", $this->timeout);

		if($request !== null){
			$this->setResult(file_put_contents($this->pluginPath . $this->pluginName . ".phar", $request->getBody()) !== false);
		}else{
			$this->setResult(false);
		}


	}

	public function onCompletion() : void{
		$player = Server::getInstance()->getPlayerExact($this->playerName);
		if($player === null){
			return;
		}

		/** @var bool $success */
		$success = $this->getResult();
		if($success){
			$player->sendMessage(Main::formatMessage(TextFormat::GREEN . "$this->pluginName successfully downloaded."));
		}else{
			$player->sendMessage(Main::formatMessage(TextFormat::RED . "Could not download $this->pluginName plugin."));
		}
	}
}