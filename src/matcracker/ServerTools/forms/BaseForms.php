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
use matcracker\ServerTools\ftp\FTPConnection;
use matcracker\ServerTools\ftp\SFTPConnection;
use matcracker\ServerTools\Main;
use matcracker\ServerTools\utils\Utils;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use UnexpectedValueException;
use function is_int;

abstract class BaseForms{

	public static function getMainForm() : Form{
		return (new Form(
			static function(Player $player, $data){
				if(!is_int($data)){
					throw new UnexpectedValueException("Unexpected value parsed from Form.");
				}

				switch((int) $data){
					case 0:
						if(!$player->hasPermission("st.ui.file-explorer")){
							$player->sendMessage(Main::formatMessage(TextFormat::RED . "You do not have permission to use this function"));

							return;
						}

						$player->sendForm(FileExplorerForms::getFileExplorerForm(Utils::getServerPath()));
						break;
					case 1:
						if(!$player->hasPermission("st.ui.clone")){
							$player->sendMessage(Main::formatMessage(TextFormat::RED . "You do not have permission to use this function"));

							return;
						}

						if(FTPConnection::hasExtension() || SFTPConnection::hasExtension()){
							$player->sendForm(CloneForms::getMainForm());
						}else{
							$player->sendForm(
								self::getConfirmForm(
									"Missing extensions!",
									TextFormat::RED . "The server is missing the following PHP extensions:\n" .
									"- ftp > for FTP feature\n" .
									"- openssl > for SFTP feature\n" .
									"- libssh2 > for SFTP feature\n",
									self::onClose(self::getMainForm())
								)
							);
						}
						break;
					case 2:
						if(!$player->hasPermission("st.ui.plugin-manager")){
							$player->sendMessage(Main::formatMessage(TextFormat::RED . "You do not have permission to use this function"));

							return;
						}
						$player->sendForm(PluginManagerForm::getMainForm());
						break;
					case 3:
						if(!$player->hasPermission("st.ui.poggit-downloader")){
							$player->sendMessage(Main::formatMessage(TextFormat::RED . "You do not have permission to use this function"));

							return;
						}
						$player->sendForm(PoggitDownloaderForms::getSearchForm());
						break;
					case 4:
						if(!$player->hasPermission("st.ui.restart")){
							$player->sendMessage(Main::formatMessage(TextFormat::RED . "You do not have permission to use this function"));

							return;
						}

						if(!Main::restartServer($player->getServer())){
							$player->sendMessage(Main::formatMessage(TextFormat::RED . "Could not restart the server."));
						}

						break;
				}
			}
		))->setTitle("Server Tools")
			->addClassicButton("File Explorer")
			->addClassicButton("Clone Server")
			->addClassicButton("Plugin Manager")
			->addClassicButton("Poggit Plugin Downloader")
			->addClassicButton("Restart Server");
	}

	public final static function getConfirmForm(string $title, string $message, ?Closure $onClose = null) : Form{
		return (new Form(
			static function(Player $player, $data){
			},
			$onClose
		))->setTitle($title)->setMessage($message);
	}

	protected final static function onClose(BaseForm $form) : Closure{
		return static function(Player $player) use ($form){
			$player->sendForm($form);
		};
	}
}