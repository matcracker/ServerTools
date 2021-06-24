<?php

declare(strict_types=1);

namespace matcracker\ServerTools\forms\files;

use Exception;
use matcracker\ServerTools\forms\FormManager;
use matcracker\ServerTools\Main;
use pocketmine\Player;
use pocketmine\plugin\PluginException;
use pocketmine\utils\TextFormat;
use function is_dir;
use function mkdir;
use function strlen;
use function trim;
use const DIRECTORY_SEPARATOR;

final class NewFolderForm extends FileInputForm{

	public function __construct(string $filePath, Player $player, ?string $error = null){
		if(!is_dir($filePath)){
			throw new PluginException("The $filePath must be a folder.");
		}

		parent::__construct(
			"New folder",
			"Insert new folder name:",
			"e.g. MyFolder",
			null,
			$error,
			function(Player $player, $data) use ($filePath): void{
				$folderName = $data[self::FILE_NAME] ?? "";
				if(strlen(trim($folderName)) === 0 || strpbrk($folderName, "\\/?%*:|\"<>") !== false){
					$player->sendForm(new self($filePath, $player, "Invalid name \"$folderName\" for this folder. Try again."));

					return;
				}

				$newFilePath = $filePath . DIRECTORY_SEPARATOR . $folderName;
				try{
					if(mkdir($newFilePath)){
						$player->sendForm(new FileExplorerForm($newFilePath, $player));
					}else{
						$player->sendMessage(Main::formatMessage(TextFormat::RED . "Could not create " . $newFilePath));
					}
				}catch(Exception $e){
					$player->sendForm(new self($filePath, $player, "Error: " . $e->getMessage()));
				}
			},
			FormManager::onClose(new FileExplorerForm($filePath, $player))
		);
	}
}