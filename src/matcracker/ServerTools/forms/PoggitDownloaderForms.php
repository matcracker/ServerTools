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

namespace matcracker\ServerTools\forms;

use InvalidStateException;
use matcracker\FormLib\CustomForm;
use matcracker\FormLib\Form;
use matcracker\ServerTools\task\async\DownloadPoggitPluginTask;
use matcracker\ServerTools\task\async\FetchPoggitDataTask;
use matcracker\ServerTools\task\async\SearchPoggitPluginTask;
use matcracker\ServerTools\utils\PluginInfo;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use UnexpectedValueException;
use function array_key_exists;
use function array_rand;
use function count;
use function is_array;
use function is_int;
use function mb_strtoupper;

final class PoggitDownloaderForms extends BaseForms{

	private const SPONSOR_PLUGINS = [
		"BedcoreProtect", "ServerTools", "BlocksConverter"
	];

	/** @var PluginInfo[][] */
	public static $pluginInfoCache = [];
	/** @var string[][] */
	public static $pluginResultsCache = [];

	public static function getSearchForm(?string $pluginName = null) : CustomForm{
		$form = (new CustomForm(
			function(Player $player, $data){
				if(!is_array($data)){
					throw new UnexpectedValueException("Unexpected value parsed from Form.");
				}

				$pluginName = (string) $data[0];
				Server::getInstance()->getAsyncPool()->submitTask(
					new SearchPoggitPluginTask($pluginName, $player->getName())
				);
			},
			self::onClose(BaseForms::getMainForm())
		))->setTitle("Search Poggit Plugin")
			->addInput("Insert the plugin name to search:", "e.g. " . self::SPONSOR_PLUGINS[array_rand(self::SPONSOR_PLUGINS)]);

		if($pluginName !== null){
			$form->addLabel(TextFormat::RED . $pluginName . " does not exist on Poggit!");
		}

		return $form;
	}

	public static function showSearchResults() : Form{
		$form = (new Form(
			function(Player $player, $data){
				if(!is_int($data)){
					throw new UnexpectedValueException("Unexpected value parsed from Form.");
				}

				if(!array_key_exists($data, self::$pluginResultsCache)){
					throw new InvalidStateException();
				}

				Server::getInstance()->getAsyncPool()->submitTask(
					new FetchPoggitDataTask(self::$pluginResultsCache[$data]["name"], $player->getName())
				);
			},
			self::onClose(self::getSearchForm())
		))->setTitle(count(self::$pluginResultsCache) . " Poggit Result(s)")
			->setMessage("Select a plugin:");

		foreach(self::$pluginResultsCache as $result){
			$form->addWebImageButton($result["name"], $result["url"]);
		}

		return $form;
	}

	public static function showPluginInfo(string $playerName) : CustomForm{
		$pluginName = self::getPluginCacheName($playerName);

		$versions = [];
		foreach(self::$pluginInfoCache[$playerName] as $pluginInfo){
			$versions[] = "v{$pluginInfo->getVersion()} (API: {$pluginInfo->getApiFrom()} - {$pluginInfo->getApiTo()})";
		}

		$tmpInfo = self::$pluginInfoCache[$playerName][0];
		$serverApi = Server::getInstance()->getApiVersion();

		return (new CustomForm(
			function(Player $player, $data){
				if(!is_array($data)){
					throw new UnexpectedValueException("Unexpected value parsed from Form.");
				}

				$pluginInfo = self::$pluginInfoCache[$player->getName()][$data[3]];

				$server = Server::getInstance();
				$server->getAsyncPool()->submitTask(
					new DownloadPoggitPluginTask($player->getName(), $server->getPluginPath(), $pluginInfo)
				);
			},
			self::onClose(self::showSearchResults())
		))->setTitle("{$pluginName}'s Information")
			->addLabel(TextFormat::BOLD . $tmpInfo->getShortDescription())
			->addLabel("Authors: " . $tmpInfo->getAuthors())
			->addLabel("License: " . mb_strtoupper($tmpInfo->getLicense()))
			->addDropdown("Select the version", $versions)
			->addLabel(TextFormat::BOLD . TextFormat::GOLD . "Server API version: {$serverApi}")
			->addLabel("Press \"Submit\" to start the download.");
	}

	private static function getPluginCacheName(string $playerName) : string{
		if(!array_key_exists($playerName, self::$pluginInfoCache)){
			throw new InvalidStateException();
		}

		return self::$pluginInfoCache[$playerName][0]->getPluginName();
	}

}