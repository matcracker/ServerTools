<?php

declare(strict_types=1);

namespace matcracker\ServerTools\forms\files;

use matcracker\ServerTools\forms\FormManager;
use matcracker\ServerTools\Main;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use function mkdir;
use const DIRECTORY_SEPARATOR;

final class NewFolderForm extends FileInputForm{

	public function __construct(string $filePath){
		parent::__construct(
			"New folder",
			"Insert new folder name:",
			"e.g. MyFolder",
			null,
			function(Player $player, $data) use ($filePath): void{
				$newFilePath = $filePath . DIRECTORY_SEPARATOR . (string) $data[0];
				if(mkdir($newFilePath)){
					$player->sendForm(new FileExplorerForm($newFilePath));
				}else{
					$player->sendMessage(Main::formatMessage(TextFormat::RED . "Could not create " . $newFilePath));
				}
			},
			FormManager::onClose(new FileExplorerForm($filePath))
		);
	}
}