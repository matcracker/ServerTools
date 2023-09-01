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

use dktapps\pmforms\FormIcon;
use dktapps\pmforms\MenuForm;
use matcracker\ServerTools\forms\elements\TaggedMenuOption;
use matcracker\ServerTools\forms\MainMenuForm;
use matcracker\ServerTools\Main;
use matcracker\ServerTools\utils\FormUtils;
use matcracker\ServerTools\utils\Utils;
use pocketmine\player\Player;
use pocketmine\plugin\PluginException;
use pocketmine\utils\TextFormat;
use Symfony\Component\Filesystem\Path;
use function count;
use function dirname;
use function is_dir;

final class FileExplorerForm extends MenuForm{

	private const KEY_BACK = "back";
	private const KEY_NEW_FILE = "new_file";
	private const KEY_NEW_FOLDER = "new_folder";
	private const KEY_RENAME_FOLDER = "rename_folder";
	private const KEY_DELETE_FOLDER = "delete_folder";

	public function __construct(Main $plugin, string $filePath, Player $player){
		if(!is_dir($filePath)){
			throw new PluginException("The $filePath must be a folder.");
		}

		static $BACK_OPTION = new TaggedMenuOption(
			self::KEY_BACK,
			"Back",
			new FormIcon("textures/ui/arrow_dark_left_stretch.png", FormIcon::IMAGE_TYPE_PATH)
		);

		$fileList = Utils::getSortedFileList($filePath);

		/** @var TaggedMenuOption[] $options */
		$options = [];

		$hasPermission = $player->hasPermission("st.ui.file-explorer.write") || $plugin->canBypassPermission($player);

		if($filePath !== $plugin->getServerDataPath()){
			$options[] = $BACK_OPTION;

			if($hasPermission){
				$options[] = new TaggedMenuOption(
					self::KEY_RENAME_FOLDER,
					"Rename current folder",
					new FormIcon("textures/ui/pencil_edit_icon.png", FormIcon::IMAGE_TYPE_PATH)
				);
				$options[] = new TaggedMenuOption(
					self::KEY_DELETE_FOLDER,
					"Delete current folder",
					new FormIcon("textures/ui/trash.png", FormIcon::IMAGE_TYPE_PATH)
				);
			}
		}

		if($hasPermission){
			$options[] = new TaggedMenuOption(
				self::KEY_NEW_FOLDER,
				"New folder",
				new FormIcon("textures/ui/book_addpicture_default.png", FormIcon::IMAGE_TYPE_PATH)
			);
			$options[] = new TaggedMenuOption(
				self::KEY_NEW_FILE,
				"New file",
				new FormIcon("textures/ui/book_addtextpage_default.png", FormIcon::IMAGE_TYPE_PATH)
			);
		}

		if(count($fileList) === 0){
			$message = $filePath . TextFormat::EOL .
				TextFormat::BOLD . TextFormat::GOLD . "[EMPTY FOLDER]";

		}else{
			$message = $filePath;

			static $dirIcon = new FormIcon("textures/ui/storageIconColor.png", FormIcon::IMAGE_TYPE_PATH);
			static $fileIcon = new FormIcon("textures/items/map_filled.png", FormIcon::IMAGE_TYPE_PATH);

			foreach($fileList as $fileInfo){
				$name = $fileInfo->getFilename();
				$options[] = new TaggedMenuOption(
					$name,
					$name,
					$fileInfo->isDir() ? $dirIcon : $fileIcon
				);
			}
		}

		parent::__construct(
			"File Explorer",
			$message,
			$options,
			static function(Player $player, int $selectedOption) use ($plugin, $options, $filePath) : void{
				$tag = $options[$selectedOption]->getTag();

				$nextPath = Path::join($filePath, $tag);

				$form = match ($tag) {
					self::KEY_BACK => new self($plugin, dirname($filePath), $player),
					self::KEY_NEW_FOLDER => new NewFolderForm($plugin, $filePath, $player),
					self::KEY_NEW_FILE => new NewFileForm($plugin, $filePath, $player),
					self::KEY_DELETE_FOLDER => new DeleteFolderForm($plugin, $filePath),
					self::KEY_RENAME_FOLDER => new RenameFolderForm($plugin, $filePath, $player),
					default => is_dir($nextPath) ? new self($plugin, $nextPath, $player) : new FileEditorForm($plugin, $nextPath, $player)
				};

				$player->sendForm($form);
			},
			FormUtils::onClose(new MainMenuForm($plugin))
		);
	}
}