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

use Closure;
use matcracker\FormLib\BaseForm;
use matcracker\FormLib\Form;
use matcracker\FormLib\ModalForm;
use pocketmine\Player;

final class FormManager{
	public const BACK_LABEL = "/back";

	private function __construct(){
		//NOOP
	}

	final public static function getConfirmForm(string $title, string $message, ?Closure $onClose = null) : Form{
		return (new Form(
			static function(Player $player, $data){
			},
			$onClose
		))->setTitle($title)->setMessage($message);
	}

	final public static function getYesNoForm(string $title, string $message, Closure $onSubmit, ?Closure $onClose = null) : ModalForm{
		return (new ModalForm(
			$onSubmit,
			$onClose
		))->setTitle($title)
			->setMessage($message)
			->setFirstButton("Yes")
			->setSecondButton("No");
	}

	final public static function onClose(BaseForm $form) : Closure{
		return static function(Player $player) use ($form){
			$player->sendForm($form);
		};
	}

	final public static function getMainMenu() : Form{
		return new MainMenuForm();
	}
}