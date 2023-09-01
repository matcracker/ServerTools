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
use dktapps\pmforms\element\Label;
use dktapps\pmforms\element\Toggle;
use matcracker\ServerTools\ftp\BaseFTPHandler;
use matcracker\ServerTools\Main;
use matcracker\ServerTools\task\async\FTPThread;
use matcracker\ServerTools\utils\FormUtils;
use matcracker\ServerTools\utils\Utils;
use pocketmine\player\Player;
use function assert;
use function count;

final class ExcludeFilesForm extends CustomForm{

	private const KEY_EXCLUDE_FILES = "exclude_files";

	public function __construct(Main $plugin, BaseFTPForm $form, BaseFTPHandler $ftpHandler){
		$elements = [
			new Label(self::KEY_EXCLUDE_FILES, "Do you want to exclude something from the clone?")
		];

		$fileList = Utils::getSortedFileList($plugin->getServerDataPath());
		/** @var array<string, string> $files */
		$files = [];
		foreach($fileList as $file){
			$fileName = $file->getFilename();
			$files[$fileName] = $file->getRealPath();
			$elements[] = new Toggle($fileName, $fileName);
		}

		assert(count($elements) > 1);

		parent::__construct(
			"Exclude files",
			$elements,
			static function(Player $player, CustomFormResponse $response) use ($plugin, $ftpHandler, $files) : void{
				foreach($response->getAll() as $fileNameKey => $toggled){
					if($fileNameKey === self::KEY_EXCLUDE_FILES){
						continue;
					}

					if($toggled){
						unset($files[$fileNameKey]);
					}
				}

				new FTPThread($plugin, $ftpHandler, $files);
			},
			FormUtils::onClose($form)
		);
	}
}