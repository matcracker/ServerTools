<?php

declare(strict_types=1);

namespace matcracker\ServerTools\forms\files;

use matcracker\ServerTools\forms\FormManager;
use matcracker\ServerTools\Main;
use pocketmine\Player;
use pocketmine\plugin\PluginException;
use pocketmine\utils\TextFormat;
use function basename;
use function dirname;
use function is_dir;
use function rename;
use function strlen;
use function strpbrk;
use function trim;
use const DIRECTORY_SEPARATOR;

final class RenameFolderForm extends FileInputForm{

	public function __construct(string $filePath, Player $player, ?string $error = null){
		if(!is_dir($filePath)){
			throw new PluginException("The $filePath must be a folder.");
		}

		parent::__construct(
			"Rename folder",
			"Insert new folder name:",
			basename($filePath),
			basename($filePath),
			$error,
			function(Player $player, $data) use ($filePath) : void{
				$folderName = $data[self::FILE_NAME] ?? "";
				if(strlen(trim($folderName)) === 0 || strpbrk($folderName, "\\/?%*:|\"<>") !== false){
					$player->sendForm(new self($filePath, $player, "Invalid name \"$folderName\" for this folder. Try again."));

					return;
				}
				$newPath = dirname($filePath) . DIRECTORY_SEPARATOR . $folderName;
				if(rename($filePath, $newPath)){
					$player->sendForm(new FileExplorerForm($newPath, $player));
				}else{
					$player->sendMessage(Main::formatMessage(TextFormat::RED . "Could not rename the folder $filePath"));
				}
			},
			FormManager::onClose(new FileExplorerForm($filePath, $player))
		);
	}
}