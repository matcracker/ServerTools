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

use matcracker\ServerTools\forms\files\FileEditorForm;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerEditBookEvent;
use pocketmine\nbt\NoSuchTagException;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\TextFormat;
use function basename;
use function file_put_contents;
use function is_writable;

final class EventListener implements Listener{

	public function __construct(private readonly Main $plugin){

	}

	public function onPlayerEditBook(PlayerEditBookEvent $event) : void{
		if($event->getAction() !== PlayerEditBookEvent::ACTION_SIGN_BOOK){
			return;
		}

		$oldBook = $event->getOldBook();
		try{
			$filePath = $oldBook->getNamedTag()->getString(FileEditorForm::FILE_TAG);
		}catch(NoSuchTagException){
			return;
		}

		$player = $event->getPlayer();

		$fileName = basename($filePath);
		if(!is_writable($filePath)){
			$player->sendMessage(Main::formatMessage(TextFormat::RED . "The file \"$fileName\" does not exist or is not writable."));

			return;
		}

		$newFileContent = "";
		foreach($oldBook->getPages() as $page){
			$newFileContent .= $page->getText();
		}

		if(file_put_contents($filePath, $newFileContent) === false){
			$player->sendMessage(Main::formatMessage(TextFormat::RED . "Error while saving the new content of file \"$fileName\"."));
		}else{
			$newBook = $event->getNewBook();
			//Remove the book from the hotbar
			$this->plugin->getScheduler()->scheduleDelayedTask(
				new ClosureTask(static fn() => $player?->getInventory()->remove($newBook)),
				1
			);

			$player->sendMessage(Main::formatMessage(TextFormat::GREEN . "File successfully edited!"));
		}

	}
}