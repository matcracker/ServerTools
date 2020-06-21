<?php

declare(strict_types=1);

namespace matcracker\ServerTools\forms\files;

use matcracker\ServerTools\forms\FormManager;
use matcracker\ServerTools\Main;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use function touch;
use const DIRECTORY_SEPARATOR;

final class NewFileForm extends FileInputForm{

	public function __construct(string $filePath){
		parent::__construct(
			"New file",
			"Insert new file name:",
			"e.g. MyFile.txt",
			null,
			function(Player $player, $data) use ($filePath): void{
				$newFilePath = $filePath . DIRECTORY_SEPARATOR . (string) $data[0];
				if(touch($newFilePath)){
					$player->sendForm(new FileEditorForm($newFilePath));
				}else{
					$player->sendMessage(Main::formatMessage(TextFormat::RED . "Could not create " . $newFilePath));
				}
			},
			FormManager::onClose(new FileExplorerForm($filePath))
		);
	}
}