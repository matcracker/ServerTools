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

namespace matcracker\ServerTools\forms\plugins\manager;

use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use matcracker\ServerTools\forms\MainMenuForm;
use matcracker\ServerTools\Main;
use matcracker\ServerTools\utils\FormUtils;
use pocketmine\player\Player;
use UnexpectedValueException;

final class PluginManagerForm extends MenuForm{

	public function __construct(Main $plugin){
		parent::__construct(
			"Plugin Manager",
			"",
			[new MenuOption("Enable/Disable plugin")],
			static function(Player $player, int $selectedOption) use ($plugin) : void{
				$form = match ($selectedOption) {
					0 => new PluginEnablerForm($plugin),
					default => throw new UnexpectedValueException("Unexpected option $selectedOption")
				};

				$player->sendForm($form);
			},
			FormUtils::onClose(new MainMenuForm($plugin))
		);
	}
}