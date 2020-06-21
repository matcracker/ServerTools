<?php

declare(strict_types=1);

namespace matcracker\ServerTools\forms\files;

use matcracker\FormLib\ModalForm;
use matcracker\ServerTools\forms\FormManager;
use matcracker\ServerTools\utils\Utils;
use pocketmine\Player;
use pocketmine\plugin\PluginException;
use function dirname;
use function is_dir;

final class DeleteFolderForm extends ModalForm{

	public function __construct(string $folderPath){
		if(!is_dir($folderPath)){
			throw new PluginException("The {$folderPath} must be a folder.");
		}

		parent::__construct(
			function(Player $player, $data) use ($folderPath): void{
				if($data){
					Utils::removeAllFiles($folderPath);
					$form = new FileExplorerForm(dirname($folderPath, 1));
				}else{
					$form = new FileExplorerForm($folderPath);
				}
				$player->sendForm($form);
			},
			FormManager::onClose(new FileExplorerForm($folderPath))
		);
		$this->setTitle("Delete folder")
			->setMessage("Are you sure to delete the folder {$folderPath} and all its contents?")
			->setFirstButton("Yes")
			->setSecondButton("No");
	}
}