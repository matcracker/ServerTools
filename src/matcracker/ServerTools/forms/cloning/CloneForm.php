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

use matcracker\FormLib\CustomForm;
use matcracker\FormLib\Form;
use matcracker\ServerTools\forms\FormManager;
use matcracker\ServerTools\ftp\FTPConnection;
use matcracker\ServerTools\ftp\SFTPConnection;
use pocketmine\Player;
use UnexpectedValueException;
use function is_int;

final class CloneForm extends Form{

	public function __construct(){
		parent::__construct(
			function(Player $player, $data) : void{
				if(!is_int($data)){
					throw new UnexpectedValueException("Unexpected value parsed from Form.");
				}

				if($data === 0){
					$player->sendForm(SFTPConnection::hasExtension() ? $this->getSFTPForm() : $this->getFTPForm());
				}elseif($data === 1){
					$player->sendForm($this->getFTPForm());
				}
			},
			FormManager::onClose(FormManager::getMainMenu())
		);
		$this->setTitle("Transfer Mode")
			->setMessage("Select a mode to send your server data to another one.");

		if(SFTPConnection::hasExtension()){
			$this->addClassicButton("SFTP");
		}

		if(FTPConnection::hasExtension()){
			$this->addClassicButton("FTP");
		}
	}

	private function getSFTPForm() : CustomForm{
		return (new BaseFTPForm("SFTP Settings"));
	}

	private function getFTPForm() : CustomForm{
		return (new BaseFTPForm("FTP Settings"))->addToggle("Use SSL", true);
	}
}