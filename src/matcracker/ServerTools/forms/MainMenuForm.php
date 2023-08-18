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

use dktapps\pmforms\MenuForm;
use matcracker\ServerTools\forms\cloning\CloneForm;
use matcracker\ServerTools\forms\elements\PermissibleMenuOption;
use matcracker\ServerTools\forms\files\FileExplorerForm;
use matcracker\ServerTools\forms\plugins\downloader\SearchPluginForm;
use matcracker\ServerTools\forms\plugins\manager\PluginManagerForm;
use matcracker\ServerTools\ftp\FTPConnection;
use matcracker\ServerTools\ftp\SFTPConnection;
use matcracker\ServerTools\Main;
use matcracker\ServerTools\utils\FormUtils;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

final class MainMenuForm extends MenuForm{

	public function __construct(Main $plugin){
		static $options = [
			new PermissibleMenuOption("st.ui.file-explorer", "File Explorer"),
			new PermissibleMenuOption("st.ui.clone", "Clone Server"),
			new PermissibleMenuOption("st.ui.plugin-manager", "Plugin Manager"),
			new PermissibleMenuOption("st.ui.poggit-downloader", "Poggit Plugin Downloader"),
			new PermissibleMenuOption("st.ui.restart", "Restart Server")
		];

		parent::__construct(
			$plugin->getName(),
			"Select an option",
			$options,
			static function(Player $player, int $selectedOption) use ($plugin, $options) : void{
				if(!$player->hasPermission($options[$selectedOption]->getPermission()) && !$plugin->canBypassPermission($player)){
					$player->sendMessage(Main::formatMessage(TextFormat::RED . "You do not have permission to use this function"));

					return;
				}

				switch($selectedOption){
					case 0:
						$player->sendForm(new FileExplorerForm($plugin, $plugin->getServerDataPath(), $player));
						break;
					case 1:
						if(FTPConnection::hasExtension() || SFTPConnection::hasExtension()){
							$player->sendForm(new CloneForm($plugin));
						}else{
							$player->sendForm(FormUtils::getConfirmForm(
								"Missing extensions!",
								TextFormat::RED . "The server is missing the following PHP extensions:\n" .
								"- ftp > for FTP feature\n" .
								"- openssl > for SFTP feature\n" .
								"- libssh2 > for SFTP feature\n",
								FormUtils::onClose(new self($plugin))
							));
						}
						break;
					case 2:
						$player->sendForm(new PluginManagerForm($plugin));
						break;
					case 3:
						$player->sendForm(new SearchPluginForm($plugin));
						break;
					case 4:
						if(!$plugin->restartServer()){
							$player->sendMessage(Main::formatMessage(TextFormat::RED . "Could not restart the server."));
						}

						break;
				}
			}
		);
	}
}