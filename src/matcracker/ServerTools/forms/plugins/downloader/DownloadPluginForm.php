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
use matcracker\ServerTools\utils\PluginInfo;
use pocketmine\Player;
use pocketmine\plugin\PluginException;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use function count;
use function mb_strtoupper;

final class DownloadPluginForm extends CustomForm{

	/**
	 * DownloadPluginForm constructor.
	 *
	 * @param PluginInfo[] $pluginsInfo
	 * @param string       $playerName
	 */
	public function __construct(array $pluginsInfo, string $playerName){
		if(count($pluginsInfo) === 0){
			throw new PluginException();
		}

		$firstInfo = $pluginsInfo[0];
		$pluginName = $firstInfo->getPluginName();

		$versions = [];
		foreach($pluginsInfo as $pluginInfo){
			$versions[] = "v{$pluginInfo->getVersion()} (API: {$pluginInfo->getApiFrom()} - {$pluginInfo->getApiTo()})";
		}

		$serverApi = Server::getInstance()->getApiVersion();

		parent::__construct(
			function(Player $player, $data) use($pluginsInfo) : void{
				$pluginInfo = $pluginsInfo[$player->getName()][$data[3]];

				$server = Server::getInstance();
				$server->getAsyncPool()->submitTask(
					new DownloadPluginTask($player->getName(), $server->getPluginPath(), $pluginInfo)
				);
			},
			FormManager::onClose(new SearchResultsForm(SearchResultsForm::getResultsCache($playerName)))
		);

		$this->setTitle("{$pluginName} Information")
			->addLabel(TextFormat::BOLD . $firstInfo->getShortDescription())
			->addLabel("Author(s): " . $firstInfo->getAuthors())
			->addLabel("License: " . mb_strtoupper($firstInfo->getLicense()))
			->addDropdown("Select the version", $versions)
			->addLabel(TextFormat::BOLD . TextFormat::GOLD . "Server API version: {$serverApi}")
			->addLabel("Press \"Submit\" to start the download.");
	}

}