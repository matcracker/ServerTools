<?php

declare(strict_types=1);

namespace matcracker\ServerTools\forms\files;

use matcracker\ServerTools\forms\FormManager;
use matcracker\ServerTools\Main;
use matcracker\ServerTools\utils\Utils;
use pocketmine\Player;
use pocketmine\plugin\PluginException;
use pocketmine\utils\TextFormat;
use function is_dir;
use function strlen;
use function touch;
use function trim;
use const DIRECTORY_SEPARATOR;

final class NewFileForm extends FileInputForm{

	public function __construct(string $filePath, Player $player, ?string $error = null){
		if(!is_dir($filePath)){
			throw new PluginException("The $filePath must be a folder.");
		}

		parent::__construct(
			"New file",
			"Insert new file name:",
			"e.g. MyFile.txt",
			null,
			$error,
			function(Player $player, $data) use ($filePath): void{
				$fileName = $data[self::FILE_NAME] ?? "";
				if(strlen(trim($fileName)) === 0 || !Utils::isValidFileName($fileName)){
					$player->sendForm(new self($filePath, $player, "Invalid name \"$fileName\" for this folder. Try again"));

					return;
				}

				$newFilePath = $filePath . DIRECTORY_SEPARATOR . $fileName;
				if(touch($newFilePath)){
					$player->sendForm(new FileEditorForm($newFilePath, $player));
				}else{
					$player->sendMessage(Main::formatMessage(TextFormat::RED . "Could not create " . $newFilePath));
				}
			},
			FormManager::onClose(new FileExplorerForm($filePath, $player))
		);
	}
}