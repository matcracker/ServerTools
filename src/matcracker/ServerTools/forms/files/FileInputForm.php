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

namespace matcracker\ServerTools\forms\files;

use Closure;
use dktapps\pmforms\CustomForm;
use dktapps\pmforms\element\Input;
use dktapps\pmforms\element\Label;
use pocketmine\utils\TextFormat;
use function strlen;

abstract class FileInputForm extends CustomForm{

	protected const FORM_KEY_ERROR_MSG = "ERROR_MSG";
	protected const FORM_KEY_FILE_NAME = "FILE_NAME";

	public function __construct(string $title, string $text, string $hintText, string $defaultText, string $outputError, Closure $onSubmit, ?Closure $onClose = null){
		$elements = [];

		if(strlen($outputError) > 0){
			$elements[] = new Label(self::FORM_KEY_ERROR_MSG, TextFormat::RED . $outputError);
		}

		$elements[] = new Input(
			self::FORM_KEY_FILE_NAME,
			$text,
			$hintText,
			$defaultText
		);

		parent::__construct($title, $elements, $onSubmit, $onClose);
	}

}