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
use function explode;
use function mb_strtolower;
use function strlen;
use function strpos;
use function substr;

final class SearchPluginTask extends AsyncTask{

	private const POGGIT_RELEASES_URL = "https://poggit.pmmp.io/releases.min.json";
	/** @var string */
	private $nameToSearch;
	/** @var string */
	private $playerName;

	public function __construct(string $nameToSearch, string $playerName){
		$this->nameToSearch = $nameToSearch;
		$this->playerName = $playerName;
	}

	public function onRun() : void{
		$json = Internet::getURL(self::POGGIT_RELEASES_URL);

		$results = [];
		if($json !== false){
			$poggitJson = json_decode($json, true);
			if(is_array($poggitJson)){
				$emptyInputSearch = strlen($this->nameToSearch) === 0;
				$searchByAuthor = false;
				$searchedAuthor = "";

				if(!$emptyInputSearch){
					$searchByAuthor = substr($this->nameToSearch, 0, 1) === "@";
					$searchedAuthor = mb_strtolower(substr($this->nameToSearch, 1));
					$searchByAuthor = $searchByAuthor && strlen($searchedAuthor) > 0;
				}

				foreach($poggitJson as $jsonData){
					$exist = false;
					$authorName = mb_strtolower(explode("/", $jsonData["repo_name"])[0]);

					$name = $jsonData["name"];
					foreach($results as $result){
						if($result["name"] === $name){
							$exist = true;
							break;
						}
					}

					if(!$exist){
						if
						(
							$emptyInputSearch ||
							($searchByAuthor && strpos($authorName, $searchedAuthor) !== false) ||
							strpos(mb_strtolower($name), mb_strtolower($this->nameToSearch)) !== false
						){
							$results[] = [
								"name" => $name,
								"url" => $jsonData["icon_url"] ?? ""
							];
						}
					}
				}
			}
		}

		$this->setResult($results);
	}

	public function onCompletion(Server $server) : void{
		$player = Server::getInstance()->getPlayer($this->playerName);
		if($player === null){
			return;
		}

		/** @var string[][] $results */
		$results = $this->getResult();
		if(count($results) === 0){
			$player->sendForm(new SearchPluginForm($this->nameToSearch));

			return;
		}

		$player->sendForm(new SearchResultsForm($results));
	}
}