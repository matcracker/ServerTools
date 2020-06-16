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

use matcracker\ServerTools\forms\PoggitDownloaderForms;
use matcracker\ServerTools\Main;
use matcracker\ServerTools\utils\PluginInfo;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\Internet;
use pocketmine\utils\TextFormat;
use function count;
use function implode;

final class FetchPoggitDataTask extends AsyncTask{

	private const POGGIT_RELEASES_URL = "https://poggit.pmmp.io/releases.min.json?name=";
	/** @var string */
	private $pluginName;
	/** @var string */
	private $playerName;

	public function __construct(string $pluginName, string $playerName){
		$this->pluginName = $pluginName;
		$this->playerName = $playerName;
	}

	public function onRun() : void{
		$json = Internet::getURL(self::POGGIT_RELEASES_URL . $this->pluginName);

		if($json !== false){
			$poggitJson = json_decode($json, true);
			if(is_array($poggitJson)){
				$pluginInfo = [];

				foreach($poggitJson as $jsonData){
					$pluginInfo[] = new PluginInfo(
						$jsonData["name"],
						implode(", ", $jsonData["producers"]["Collaborator"] ?? ["Unknown"]),
						$jsonData["version"],
						$jsonData["api"][0]["from"],
						$jsonData["api"][0]["to"],
						$jsonData["artifact_url"],
						$jsonData["icon_url"] ?? "",
						$jsonData["tagline"],
						$jsonData["license"]
					);
				}

				$this->setResult($pluginInfo);
			}
		}
	}

	public function onCompletion(Server $server) : void{
		$player = Server::getInstance()->getPlayer($this->playerName);
		if($player === null){
			return;
		}
		/** @var PluginInfo[] $pluginInfo */
		if(($pluginInfo = $this->getResult()) === null){
			$player->sendMessage(Main::formatMessage(TextFormat::RED . "Could not connect to Poggit."));

			return;
		}

		PoggitDownloaderForms::$pluginInfoCache[$this->playerName] = $pluginInfo;

		if(count($pluginInfo) === 0){
			$player->sendForm(PoggitDownloaderForms::getSearchForm($this->pluginName));

			return;
		}

		/*$versions = [];
		foreach($poggitData as $index => $data){
			$versions[$index] = $data["version"];
			/*$artifactUrl = $data["artifact_url"];
			$api = $data["api"][0]["from"] . " - " . $data["api"][0]["to"];
		}*/

		$player->sendForm(PoggitDownloaderForms::showPluginInfo($this->playerName));
	}
}