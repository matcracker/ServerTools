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

namespace matcracker\ServerTools\task\thread;

use pocketmine\thread\Thread;
use pocketmine\thread\ThreadException;
use pocketmine\utils\Utils;
use function proc_close;
use function proc_open;

final class RestartWindowsServer extends Thread{

	public function __construct(private readonly string $fileName){
		if(($os = Utils::getOS()) !== Utils::OS_WINDOWS){
			throw new ThreadException("Could not use this thread on $os OS");
		}
	}

	public function quit() : void{
		parent::quit();
		$res = proc_open("start cmd.exe /c \"timeout /t 5 & $this->fileName\"", [], $pipes);
		if($res !== false){
			proc_close($res);
		}
	}

	protected function onRun() : void{
		// Do nothing
	}
}