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

namespace matcracker\ServerTools;

use JackMD\UpdateNotifier\UpdateNotifier;
use matcracker\ServerTools\commands\ServerToolsCommand;
use matcracker\ServerTools\forms\FormManager;
use matcracker\ServerTools\task\thread\RestartServerThread;
use matcracker\ServerTools\utils\Utils;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use function file_exists;
use const DIRECTORY_SEPARATOR;

final class Main extends PluginBase{

	/** @var FormManager */
	private $formManager;

	public static function formatMessage(string $message) : string{
		return TextFormat::AQUA . "[ServerTools] " . TextFormat::RESET . $message;
	}

	public function restartServer() : bool{
		$startFile = $this->getConfig()->getNested("restart.file-name");
		$path = Utils::getServerPath() . DIRECTORY_SEPARATOR . $startFile;

		if(!file_exists($path)){
			return false;
		}

		new RestartServerThread($path);

		$this->getServer()->shutdown();

		return true;
	}

	public function onLoad() : void{
		UpdateNotifier::checkUpdate($this->getName(), $this->getDescription()->getVersion());
	}

	public function onEnable() : void{
		@mkdir($this->getDataFolder());

		$this->getServer()->getCommandMap()->register('servertools', new ServerToolsCommand($this));
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
		$this->formManager = new FormManager($this);
	}

	public function getFormManager() : FormManager{
		return $this->formManager;
	}

}