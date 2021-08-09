<?php

/*
 *	  _________                              ___________           .__
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
use pocketmine\utils\Internet;
use function explode;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function filemtime;
use function is_array;
use function json_decode;
use function json_encode;
use function max;
use function time;
use const DIRECTORY_SEPARATOR;

abstract class GetPoggitReleases extends AsyncTask{
	protected const POGGIT_JSON_ID = "PoggitJSON";
	protected const POGGIT_RELEASES_URL = "https://poggit.pmmp.io/releases.min.json";

	private int $timeout;
	private int $invalidateCacheTime;
	private string $poggitCacheFile;

	public function __construct(){
		$this->poggitCacheFile = Main::getInstance()->getDataFolder() . DIRECTORY_SEPARATOR . "poggit-api.json";
		$this->timeout = (int) Main::getInstance()->getConfig()->getNested("poggit.timeout", 30);
		$this->invalidateCacheTime = (int) max(1, Main::getInstance()->getConfig()->getNested("poggit.invalidate-cache", 24));
	}

	public function onRun() : void{
		if(file_exists($this->poggitCacheFile)){
			$lastModified = filemtime($this->poggitCacheFile);
			if($lastModified !== false){
				$downloadApi = time() - $lastModified > $this->invalidateCacheTime * 3600; //Hours -> Seconds
			}else{
				$downloadApi = true;
			}
		}else{
			$downloadApi = true;
		}

		if($downloadApi){
			$rawJson = Internet::getUrl(self::POGGIT_RELEASES_URL, $this->timeout);
			if($rawJson !== false){
				$jsonAssoc = json_decode($rawJson, true);
				if(is_array($jsonAssoc)){
					$poggitJson = [];
					foreach($jsonAssoc as $data){
						$pluginName = $data["name"];

						if(!isset($poggitJson[$pluginName])){
							$poggitJson[$pluginName] = [
								"name" => $pluginName,
								"authors" => explode("/", $data["repo_name"])[0],
								"icon_url" => $data["icon_url"] ?? "",
								"short_description" => $data["tagline"],
								"license" => $data["license"] ?? "Unknown"
							];
						}

						$poggitJson[$pluginName]["versions"][$data["version"]] = [
							"artifact_url" => $data["artifact_url"],
							"api-from" => $data["api"][0]["from"] ?? "Unknown",
							"api-to" => $data["api"][0]["to"] ?? "Unknown"
						];
					}
					file_put_contents($this->poggitCacheFile, json_encode($poggitJson));
					$this->worker->saveToThreadStore(self::POGGIT_JSON_ID, $poggitJson);
				}
			}
		}else{
			$rawJson = file_get_contents($this->poggitCacheFile);
			if($rawJson !== false){
				$poggitJson = json_decode($rawJson, true);
				if(is_array($poggitJson)){
					$this->worker->saveToThreadStore(self::POGGIT_JSON_ID, $poggitJson);
				}
			}
		}
	}

}