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

use matcracker\FormLib\Form;
use matcracker\ServerTools\forms\cloning\CloneForm;
use matcracker\ServerTools\forms\files\FileExplorerForm;
use matcracker\ServerTools\forms\plugins\downloader\SearchPluginForm;
use matcracker\ServerTools\forms\plugins\manager\PluginManagerForm;
use matcracker\ServerTools\ftp\FTPConnection;
use matcracker\ServerTools\ftp\SFTPConnection;
use matcracker\ServerTools\Main;
use matcracker\ServerTools\utils\Utils;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

final class MainMenuForm extends Form{

	public function __construct(){
		parent::__construct(
			function(Player $player, $data) : void{
				if(!$player->hasPermission("st.ui.{$data}")){
					$player->sendMessage(Main::formatMessage(TextFormat::RED . "You do not have permission to use this function"));

					return;
				}

				switch($data){
					case "file-explorer":
						$player->sendForm(new FileExplorerForm(Utils::getServerPath()));
						break;
					case "clone":
						if(FTPConnection::hasExtension() || SFTPConnection::hasExtension()){
							$player->sendForm((new CloneForm()));
						}else{
							$player->sendForm(
								FormManager::getConfirmForm(
									"Missing extensions!",
									TextFormat::RED . "The server is missing the following PHP extensions:\n" .
									"- ftp > for FTP feature\n" .
									"- openssl > for SFTP feature\n" .
									"- libssh2 > for SFTP feature\n",
									FormManager::onClose($this)
								)
							);
						}
						break;
					case "plugin-manager":
						$player->sendForm(new PluginManagerForm());
						break;
					case "poggit-downloader":
						$player->sendForm(new SearchPluginForm());
						break;
					case "restart":
						if(!Main::restartServer($player->getServer())){
							$player->sendMessage(Main::formatMessage(TextFormat::RED . "Could not restart the server."));
						}

						break;
				}
			},
			null
		);
		$this->setTitle("Server Tools")
			->addClassicButton("File Explorer", "file-explorer")
			->addClassicButton("Clone Server", "clone")
			->addClassicButton("Plugin Manager", "plugin-manager")
			->addClassicButton("Poggit Plugin Downloader", "poggit-downloader")
			->addClassicButton("Restart Server", "restart");
	}
}