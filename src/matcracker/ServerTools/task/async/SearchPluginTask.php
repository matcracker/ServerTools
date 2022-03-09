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
use pocketmine\Server;
use function array_filter;
use function count;
use function mb_strtolower;
use function mb_substr;
use function strlen;
use function strpos;
use const ARRAY_FILTER_USE_BOTH;

final class SearchPluginTask extends GetPoggitReleases{


	public function __construct(private string $nameToSearch, private string $playerName){
		parent::__construct();
	}

	public function onRun() : void{
		parent::onRun();
		/** @var array $poggitJson */
		$poggitJson = $this->worker->getFromThreadStore(self::POGGIT_JSON_ID);

		if(strlen($this->nameToSearch) > 0){
			$result = array_filter(
				$poggitJson,
				function(array $data, string $pluginName) : bool{
					//Search by author
					if(mb_substr($this->nameToSearch, 0, 1) === "@"){
						return mb_strtolower($data["authors"]) === mb_strtolower(mb_substr($this->nameToSearch, 1));
					}else{
						return str_contains(mb_strtolower($pluginName), mb_strtolower($this->nameToSearch));
					}
				},
				ARRAY_FILTER_USE_BOTH);
		}else{
			$result = $poggitJson;
		}

		$this->setResult($result);
	}

	public function onCompletion() : void{
		$player = Server::getInstance()->getPlayerExact($this->playerName);
		if($player === null){
			return;
		}

		/** @var string[][] $results */
		$results = $this->getResult();
		if(count($results) === 0){
			$player->sendForm(new SearchPluginForm($this->nameToSearch));
		}else{
			$player->sendForm(new SearchResultsForm($results));
		}
	}
}