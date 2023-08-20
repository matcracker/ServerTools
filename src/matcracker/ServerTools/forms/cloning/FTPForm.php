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

use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\Toggle;
use matcracker\ServerTools\ftp\BaseFTPConnection;
use matcracker\ServerTools\ftp\FTPConnection;
use matcracker\ServerTools\Main;

class FTPForm extends BaseFTPForm{

	protected const FORM_KEY_SSL = "ssl";

	public function __construct(Main $plugin){
		parent::__construct($plugin, "FTP Settings");
	}

	protected function getConnection(CustomFormResponse $response) : BaseFTPConnection{
		return new FTPConnection(
			$response->getString(self::FORM_KEY_HOST),
			(int) $response->getString(self::FORM_KEY_PORT),
			$response->getString(self::FORM_KEY_USERNAME),
			$response->getString(self::FORM_KEY_PWD),
			$response->getString(self::FORM_KEY_PATH),
			$response->getBool(self::FORM_KEY_SSL)
		);
	}

	protected function getFormElements() : array{
		$elements = parent::getFormElements();

		$elements[] = new Toggle(self::FORM_KEY_SSL, "Use SSL", true);

		return $elements;
	}
}