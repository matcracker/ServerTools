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

use dktapps\pmforms\CustomForm;
use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\CustomFormElement;
use dktapps\pmforms\element\Input;
use dktapps\pmforms\element\Label;
use dktapps\pmforms\element\Slider;
use matcracker\ServerTools\forms\MainMenuForm;
use matcracker\ServerTools\ftp\BaseFTPConnection;
use matcracker\ServerTools\Main;
use matcracker\ServerTools\utils\FormUtils;
use pocketmine\player\Player;

abstract class BaseFTPForm extends CustomForm{
	protected const FORM_KEY_LABEL = "label";
	protected const FORM_KEY_HOST = "host";
	protected const FORM_KEY_PORT = "port";
	protected const FORM_KEY_USERNAME = "username";
	protected const FORM_KEY_PWD = "password";
	protected const FORM_KEY_PATH = "remote_path";

	public function __construct(Main $plugin, string $title){
		parent::__construct(
			$title,
			$this->getFormElements(),
			fn(Player $player, CustomFormResponse $response) => $player->sendForm(new ExcludeFilesForm($this->getConnection($response))),
			FormUtils::onClose(new MainMenuForm($plugin))
		);
	}

	protected abstract function getConnection(CustomFormResponse $response) : BaseFTPConnection;

	/**
	 * @return CustomFormElement[]
	 */
	protected function getFormElements() : array{
		return [
			new Label(self::FORM_KEY_LABEL, "The following form will not immediately validated."),
			new Input(self::FORM_KEY_HOST, "Host address"),
			new Slider(self::FORM_KEY_PORT, "Port", 1, 65535, default: 21),
			new Input(self::FORM_KEY_USERNAME, "Username", "admin"),
			new Input(self::FORM_KEY_PWD, "Password"),
			new Input(self::FORM_KEY_PATH, "Remote home path", defaultText: "/")
		];
	}
}