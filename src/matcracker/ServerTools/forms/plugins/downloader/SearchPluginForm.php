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
use dktapps\pmforms\element\Input;
use dktapps\pmforms\element\Label;
use matcracker\ServerTools\forms\MainMenuForm;
use matcracker\ServerTools\Main;
use matcracker\ServerTools\task\async\SearchPluginTask;
use matcracker\ServerTools\utils\FormUtils;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use function array_rand;

final class SearchPluginForm extends CustomForm{

	private const SPONSOR_PLUGINS = [
		"BedcoreProtect", "ServerTools", "BlocksConverter", "Elevator"
	];
	private const FORM_KEY_HINT = "hint";
	private const FORM_KEY_SEARCH = "search";
	private const FORM_KEY_PL_NOT_FOUND = "plugin_not_found";

	public function __construct(Main $plugin, ?string $pluginNotFound = null){
		$elements = [
			new Label(
				self::FORM_KEY_HINT,
				"Hint:" . TextFormat::EOL .
				"- Use \"@\" to search by author (e.g. @matcracker)"
			),
			new Input(
				self::FORM_KEY_SEARCH,
				"Insert the plugin name to search:",
				"e.g. " . self::SPONSOR_PLUGINS[array_rand(self::SPONSOR_PLUGINS)]
			)
		];

		if($pluginNotFound !== null){
			$elements[] = new Label(
				self::FORM_KEY_PL_NOT_FOUND,
				TextFormat::RED . $pluginNotFound . " does not exist on Poggit."
			);
		}

		parent::__construct(
			"Search Poggit Plugin",
			$elements,
			static function(Player $player, CustomFormResponse $response) use ($plugin) : void{
				$player->getServer()->getAsyncPool()->submitTask(
					new SearchPluginTask($plugin, $response->getString(self::FORM_KEY_SEARCH), $player->getName())
				);
			},
			FormUtils::onClose(new MainMenuForm($plugin))
		);
	}
}