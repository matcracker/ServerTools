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

use dktapps\pmforms\CustomForm;
use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\Dropdown;
use dktapps\pmforms\element\Label;
use matcracker\ServerTools\Main;
use matcracker\ServerTools\task\async\DownloadPluginTask;
use matcracker\ServerTools\utils\FormUtils;
use pocketmine\player\Player;
use pocketmine\plugin\PluginException;
use pocketmine\utils\TextFormat;
use function array_values;
use function count;

final class DownloadPluginForm extends CustomForm{

	private const KEY_DESCRIPTION = "description";
	private const KEY_AUTHOR = "author";
	private const KEY_LICENSE = "license";
	private const KEY_VERSION = "version";
	private const KEY_API = "api";
	private const KEY_SUBMIT = "submit";

	/**
	 * DownloadPluginForm constructor.
	 *
	 * @param Main   $plugin
	 * @param array  $poggitData
	 * @param string $playerName
	 */
	public function __construct(Main $plugin, array $poggitData, string $playerName){
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

		$serverApi = $plugin->getServer()->getApiVersion();

		parent::__construct(
			"{$poggitData["name"]} Information",
			[
				new Label(self::KEY_DESCRIPTION, TextFormat::BOLD . $poggitData["short_description"]),
				new Label(self::KEY_AUTHOR, "Author(s): {$poggitData["authors"]}"),
				new Label(self::KEY_LICENSE, "License: {$poggitData["license"]}"),
				new Dropdown(self::KEY_VERSION, "Select the version", $versions),
				new Label(self::KEY_API, TextFormat::BOLD . TextFormat::GOLD . "Server API version: $serverApi"),
				new Label(self::KEY_SUBMIT, "Press \"Submit\" to start the download.")
			],
			static function(Player $player, CustomFormResponse $response) use ($plugin, $poggitData) : void{
				$versionData = array_values($poggitData["versions"])[$response->getInt(self::KEY_VERSION)];

				$server = $player->getServer();
				$server->getAsyncPool()->submitTask(new DownloadPluginTask(
														$plugin,
														$poggitData["name"],
														$versionData["artifact_url"],
														$player->getName(),
														$server->getPluginPath()
													));
			},
			FormUtils::onClose(new SearchResultsForm($plugin, SearchResultsForm::getResultsCache($playerName)))
		);
	}

}