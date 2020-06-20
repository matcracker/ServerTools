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

use matcracker\ServerTools\forms\plugins\downloader\SearchPluginForm;
use matcracker\ServerTools\forms\plugins\downloader\SearchResultsForm;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\Internet;
use function count;
use function mb_strtolower;
use function strlen;
use function strpos;

final class SearchPluginTask extends AsyncTask{

	private const POGGIT_RELEASES_URL = "https://poggit.pmmp.io/releases.min.json";
	/** @var string */
	private $pluginToSearch;
	/** @var string */
	private $playerName;

	public function __construct(string $pluginToSearch, string $playerName){
		$this->pluginToSearch = $pluginToSearch;
		$this->playerName = $playerName;
	}

	public function onRun() : void{
		$json = Internet::getURL(self::POGGIT_RELEASES_URL);

		if($json !== false){
			$poggitJson = json_decode($json, true);
			if(is_array($poggitJson)){
				$results = [];
				foreach($poggitJson as $jsonData){
					$exist = false;
					$name = $jsonData["name"];
					foreach($results as $result){
						if($result["name"] === $name){
							$exist = true;
							break;
						}
					}

					if(!$exist &&
						(strlen($this->pluginToSearch) === 0 || strpos(mb_strtolower($name), mb_strtolower($this->pluginToSearch)) !== false)
					){
						$results[] = [
							"name" => $name,
							"url" => $jsonData["icon_url"] ?? ""
						];
					}
				}

				$this->setResult($results);
			}
		}
	}

	public function onCompletion(Server $server) : void{
		$player = Server::getInstance()->getPlayer($this->playerName);
		if($player === null){
			return;
		}
		/** @var string[][]|null $results */
		$results = $this->getResult();
		if($results === null || count($results) === 0){
			$player->sendForm(new SearchPluginForm($this->pluginToSearch));

			return;
		}

		$player->sendForm(new SearchResultsForm($results));
	}
}