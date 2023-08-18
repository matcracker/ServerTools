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
use matcracker\ServerTools\Main;
use pocketmine\Server;
use function array_filter;
use function count;
use function mb_strtolower;
use function mb_substr;
use function strlen;
use const ARRAY_FILTER_USE_BOTH;

final class SearchPluginTask extends GetPoggitReleases{

	private const TLS_KEY_PLUGIN = "PluginInstance";

	public function __construct(
		Main $plugin,
		private readonly string $nameToSearch,
		private readonly string $playerName
	){
		parent::__construct($plugin);
		$this->storeLocal(self::TLS_KEY_PLUGIN, $plugin);
	}

	public function onRun() : void{
		parent::onRun();
		/** @var array $poggitJson */
		$poggitJson = $this->fetchLocal(self::POGGIT_JSON_ID);

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

		/** @var Main $plugin */
		$plugin = $this->fetchLocal(self::TLS_KEY_PLUGIN);

		/** @var string[][] $results */
		$results = $this->getResult();
		if(count($results) === 0){
			$form = new SearchPluginForm($plugin, $this->nameToSearch);
		}else{
			$form = new SearchResultsForm($plugin, $results);
		}

		$player->sendForm($form);
	}
}