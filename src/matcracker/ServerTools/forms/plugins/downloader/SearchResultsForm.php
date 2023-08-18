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

use dktapps\pmforms\FormIcon;
use dktapps\pmforms\MenuForm;
use matcracker\ServerTools\forms\elements\TaggedMenuOption;
use matcracker\ServerTools\Main;
use matcracker\ServerTools\utils\FormUtils;
use pocketmine\player\Player;
use function count;

final class SearchResultsForm extends MenuForm{

	/** @var string[][] */
	private static array $resultsCache = [];

	public function __construct(Main $plugin, array $results){
		/** @var TaggedMenuOption[] $options */
		$options = [];

		foreach($results as $pluginName => $data){
			$options[] = new TaggedMenuOption($pluginName, $pluginName, new FormIcon($data["icon_url"]));
		}

		parent::__construct(
			count($results) . " Poggit Result(s)",
			"Select a plugin:",
			$options,
			static function(Player $player, int $selectedOption) use ($plugin, $results, $options) : void{
				$tag = $options[$selectedOption]->getTag();

				self::$resultsCache[$player->getName()] = $results;

				$player->sendForm(new DownloadPluginForm($plugin, $results[$tag], $player->getName()));
			},
			FormUtils::onClose(new SearchPluginForm($plugin))
		);
	}

	/**
	 * @param string $playerName
	 *
	 * @return string[][]
	 */
	public static function getResultsCache(string $playerName) : array{
		return self::$resultsCache[$playerName] ?? [];
	}
}