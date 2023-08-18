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

namespace matcracker\ServerTools\utils;

use Closure;
use dktapps\pmforms\MenuForm;
use pocketmine\form\Form;
use pocketmine\player\Player;

final class FormUtils{

	private function __construct(){
		//NOOP
	}

	public static function getConfirmForm(string $title, string $message, ?Closure $onClose = null) : MenuForm{
		return (new MenuForm(
			$title,
			$message,
			[],
			static function(Player $player, int $selectedOption) : void{
			},
			$onClose
		));
	}

	public static function onClose(Form $form) : Closure{
		return static function(Player $player) use ($form) : void{
			$player->sendForm($form);
		};
	}
}