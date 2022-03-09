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

namespace matcracker\ServerTools;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerEditBookEvent;
use pocketmine\item\ItemFactory;
use pocketmine\item\WrittenBook;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\TextFormat;
use function basename;
use function count;
use function file_put_contents;
use function is_writable;

final class EventListener implements Listener{

	private Main $plugin;

	public function __construct(Main $plugin){
		$this->plugin = $plugin;
	}

	public function onPlayerEditBook(PlayerEditBookEvent $event) : void{
		if($event->getAction() !== PlayerEditBookEvent::ACTION_SIGN_BOOK){
			return;
		}

		$oldBook = $event->getOldBook();
		$filePath = $oldBook->getNamedTag()->getString("ServerTools_FilePath", "null");

		if($filePath === "null"){
			return;
		}

		$player = $event->getPlayer();

		$fileName = basename($filePath);
		if(!is_writable($filePath)){
			$player->sendMessage(Main::formatMessage(TextFormat::RED . "The file \"$fileName\" does not exist or is not writable."));

			return;
		}

		$newFileContent = "";
		for($pageId = 0; $pageId < count($oldBook->getPages()); $pageId++){
			$newFileContent .= $oldBook->getPageText($pageId) ?? "";
		}

		if(file_put_contents($filePath, $newFileContent) === false){
			$player->sendMessage(Main::formatMessage(TextFormat::RED . "Error while saving the new content of file \"$fileName\"."));
		}else{
			$bookSlot = $player->getInventory()->getHeldItemIndex();
			//Remove the book from the hotbar
			$this->plugin->getScheduler()->scheduleDelayedTask(
				new ClosureTask(
					static function() use ($player, $bookSlot) : void{
						if($player !== null){
							$book = $player->getInventory()->getHotbarSlotItem($bookSlot);
							if($book instanceof WrittenBook){
								$player->getInventory()->setItem($bookSlot, ItemFactory::air());
							}
						}
					}
				), 1
			);

			$player->sendMessage(Main::formatMessage(TextFormat::GREEN . "File successfully edited!"));
		}

	}
}