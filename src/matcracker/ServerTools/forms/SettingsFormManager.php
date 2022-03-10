<?php

/*
 *	  _________                              ___________           .__
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

use matcracker\FormLib\CustomForm;
use matcracker\FormLib\Form;

final class SettingsFormManager extends Form{

	public function __construct(){
		parent::__construct(
			static function(Player $player, $data) : void{

			},
			FormManager::onClose(FormManager::getMainMenu())
		);
		$this->setTitle("ServerTools Settings")
			->addClassicButton("File Explorer");
	}

	private function getFileExplorerSettings() : CustomForm{
		return (new CustomForm(
			static function(Player $player, $data) : void{

			},
			FormManager::onClose(FormManager::getMainMenu())
		))->addToggle("Show file extension");
	}
}