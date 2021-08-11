<?php

declare(strict_types=1);

namespace matcracker\ServerTools\forms\files;

use matcracker\FormLib\ModalForm;
use matcracker\ServerTools\forms\FormManager;
use matcracker\ServerTools\Main;
use matcracker\ServerTools\utils\Utils;
use pocketmine\Player;
use pocketmine\plugin\PluginException;
use pocketmine\utils\TextFormat;
use function dirname;
use function is_dir;

final class DeleteFolderForm extends ModalForm{

	public function __construct(string $folderPath, Player $player){
		if(!is_dir($folderPath)){
			throw new PluginException("The $folderPath must be a folder.");
		}

		parent::__construct(
			function(Player $player, $data) use ($folderPath) : void{
				if($data){
					if(!Utils::removeAllFiles($folderPath)){
						$player->sendMessage(Main::formatMessage(TextFormat::RED . "Could not delete folder $folderPath."));

						return;
					}
					$form = new FileExplorerForm(dirname($folderPath, 1), $player);
				}else{
					$form = new FileExplorerForm($folderPath, $player);
				}
				$player->sendForm($form);
			},
			FormManager::onClose(new FileExplorerForm($folderPath, $player))
		);
		$this->setTitle("Confirm to delete folder.")
			->setMessage("Are you sure to delete the folder $folderPath and all its contents?")
			->setFirstButton("Yes")
			->setSecondButton("No");
	}
}