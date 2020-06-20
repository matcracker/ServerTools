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

use matcracker\FormLib\Form;
use matcracker\ServerTools\forms\FormManager;
use pocketmine\Player;

final class PluginManagerForm extends Form{

	public function __construct(){
		parent::__construct(
			function(Player $player, $data) : void{
				switch((int) $data){
					case 0:
						$player->sendForm(new PluginEnablerForm());
						break;
					case 1:
						$player->sendForm(new PluginLoaderForm());
						break;
				}

			},
			FormManager::onClose(FormManager::getInstance()->getMainMenu())
		);
		$this->setTitle("Plugin Manager")
			->addClassicButton("Enable/Disable plugin")
			->addClassicButton("Load plugin from file");
	}
}