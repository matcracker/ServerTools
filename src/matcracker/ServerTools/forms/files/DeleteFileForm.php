<?php

declare(strict_types=1);

namespace matcracker\ServerTools\forms\files;

use matcracker\FormLib\ModalForm;
use matcracker\ServerTools\forms\FormManager;
use matcracker\ServerTools\Main;
use pocketmine\Player;
use pocketmine\plugin\PluginException;
use pocketmine\utils\TextFormat;
use function dirname;
use function is_dir;
use function unlink;

final class DeleteFileForm extends ModalForm{

	public function __construct(string $filePath){
		if(is_dir($filePath)){
			throw new PluginException("The {$filePath} must be a file.");
		}

		parent::__construct(
			function(Player $player, $data) use ($filePath): void{
				if($data){
					if(!unlink($filePath)){
						$player->sendMessage(Main::formatMessage(TextFormat::RED . "Could not delete file {$filePath}."));

						return;
					}
					$form = new FileExplorerForm(dirname($filePath, 1));
				}else{
					$form = new FileEditorForm($filePath);
				}
				$player->sendForm($form);
			},
			FormManager::onClose(new FileExplorerForm(dirname($filePath)))
		);
		$this->setTitle("Delete file")
			->setMessage("Are you sure to delete the file {$filePath}?")
			->setFirstButton("Yes")
			->setSecondButton("No");
	}
}