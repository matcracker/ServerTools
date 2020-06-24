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

use pocketmine\Thread;
use pocketmine\utils\Utils;
use function proc_close;
use function proc_open;
use function shell_exec;

class RestartServerThread extends Thread{

	/** @var string */
	private $filePath;

	public function __construct(string $filePath){
		$this->filePath = $filePath;
		$this->start();
	}

	public function run(){
		//NOOP
	}

	public function quit(){
		parent::quit();
		$os = Utils::getOS();
		if($os === Utils::OS_WINDOWS){
			proc_close(proc_open("start cmd.exe /c \"{$this->filePath}\"", [], $pipes));
		}else{
			shell_exec("{$this->filePath} > /dev/null 2>&1 &");
		}
	}
}