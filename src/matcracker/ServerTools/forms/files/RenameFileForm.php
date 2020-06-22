<?php

declare(strict_types=1);

namespace matcracker\ServerTools\forms\files;

use matcracker\ServerTools\forms\FormManager;
use matcracker\ServerTools\Main;
use matcracker\ServerTools\utils\Utils;
use pocketmine\Player;
use pocketmine\plugin\PluginException;
use pocketmine\utils\TextFormat;
use function basename;
use function dirname;
use function is_file;
use function rename;
use function strlen;
use function trim;
use const DIRECTORY_SEPARATOR;

final class RenameFileForm extends FileInputForm{

	public function __construct(string $filePath, Player $player, ?string $error = null){
		if(!is_file($filePath)){
			throw new PluginException("The {$filePath} must be a file.");
		}

		parent::__construct(
			"Rename file",
			"Insert new file name:",
			basename($filePath),
			basename($filePath),
			$error,
			function(Player $player, $data) use ($filePath): void{
				$fileName = $data[self::FILE_NAME] ?? "";
				if(strlen(trim($fileName)) === 0 || !Utils::isValidFileName($fileName)){
					$player->sendForm(new self($filePath, $player, "Invalid name \"{$fileName}\" for this folder. Try again"));

					return;
				}

				$newPath = dirname($filePath) . DIRECTORY_SEPARATOR . $fileName;
				if(rename($filePath, $newPath)){
					$player->sendForm(new FileEditorForm($newPath, $player));
				}else{
					$player->sendMessage(Main::formatMessage(TextFormat::RED . "Could not rename the file {$filePath}"));
				}
			},
			FormManager::onClose(new FileExplorerForm(dirname($filePath), $player))
		);
	}
}