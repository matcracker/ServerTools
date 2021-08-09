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
use matcracker\ServerTools\task\async\DownloadPluginTask;
use pocketmine\Player;
use pocketmine\plugin\PluginException;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use function array_values;
use function count;

final class DownloadPluginForm extends CustomForm{

	private const SELECTED_VERSION = "version";

	/**
	 * DownloadPluginForm constructor.
	 *
	 * @param array  $poggitData
	 * @param string $playerName
	 */
	public function __construct(array $poggitData, string $playerName){
		if(count($poggitData) === 0){
			throw new PluginException();
		}

		$versions = [];
		/**
		 * @var string   $version
		 * @var string[] $apiData
		 */
		foreach($poggitData["versions"] as $version => $apiData){
			$versions[] = "v$version (API: {$apiData["api-from"]} - {$apiData["api-to"]})";
		}

		parent::__construct(
			function(Player $player, $data) use ($poggitData) : void{
				$versionData = array_values($poggitData["versions"])[(int) $data[self::SELECTED_VERSION]];

				$server = Server::getInstance();
				$server->getAsyncPool()->submitTask(
					new DownloadPluginTask($poggitData["name"], $versionData["artifact_url"], $player->getName(), $server->getPluginPath())
				);
			},
			FormManager::onClose(new SearchResultsForm(SearchResultsForm::getResultsCache($playerName)))
		);

		$serverApi = Server::getInstance()->getApiVersion();

		$this->setTitle("{$poggitData["name"]} Information")
			->addLabel(TextFormat::BOLD . $poggitData["short_description"])
			->addLabel("Author(s): {$poggitData["authors"]}")
			->addLabel("License: {$poggitData["license"]}")
			->addDropdown("Select the version", $versions, null, self::SELECTED_VERSION)
			->addLabel(TextFormat::BOLD . TextFormat::GOLD . "Server API version: $serverApi")
			->addLabel("Press \"Submit\" to start the download.");
	}

}