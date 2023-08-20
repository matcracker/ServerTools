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

namespace matcracker\ServerTools\forms\cloning;

use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use matcracker\ServerTools\forms\MainMenuForm;
use matcracker\ServerTools\ftp\FTPConnection;
use matcracker\ServerTools\ftp\SFTPConnection;
use matcracker\ServerTools\Main;
use matcracker\ServerTools\utils\FormUtils;
use pocketmine\form\FormValidationException;
use pocketmine\player\Player;

final class CloneForm extends MenuForm{

	public function __construct(Main $plugin){
		$options = [];

		if(SFTPConnection::hasExtension()){
			$options[] = new MenuOption("SFTP");
		}

		if(FTPConnection::hasExtension()){
			$options[] = new MenuOption("FTP");
		}

		parent::__construct(
			"Transfer Mode",
			"Select a mode to send your server data to another one.",
			$options,
			function(Player $player, int $selectedOption) use($plugin) : void{
				$form = match ($selectedOption) {
					0 => SFTPConnection::hasExtension() ? new SFTPForm($plugin) : new FTPForm($plugin),
					1 => new SFTPForm($plugin),
					default => throw new FormValidationException("Unexpected option $selectedOption"),
				};
				$player->sendForm($form);
			},
			FormUtils::onClose(new MainMenuForm($plugin))
		);
	}
}