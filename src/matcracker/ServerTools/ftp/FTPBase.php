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

namespace matcracker\ServerTools\ftp;

use function str_repeat;
use function strlen;

abstract class FTPBase{

	public const ERR_CONNECT = -1;
	public const ERR_DISCONNECT = -2;
	public const ERR_LOGIN = -3;

	protected string $host;
	protected int $port;
	protected string $username;
	protected string $password;
	protected string $remoteHomePath;

	public function __construct(string $host, int $port, string $username, string $password, string $remoteHomePath){
		$this->host = $host;
		$this->port = $port;
		$this->username = $username;
		$this->password = $password;
		$this->remoteHomePath = $remoteHomePath;
	}

	public abstract static function hasExtension() : bool;

	public abstract static function getProtocolName() : string;

	public abstract function connect();

	/**
	 * @param resource $connection
	 */
	public abstract function disconnect($connection) : bool;

	public abstract function putFile($connection, string $localFile, string $remoteFile, int $mode = 0644) : bool;

	public abstract function putDirectory($connection, string $remoteDirPath, int $mode = 0644) : bool;

	public final function getHost() : string{
		return $this->host;
	}

	public final function getPort() : int{
		return $this->port;
	}

	public final function getHomePath() : string{
		return $this->remoteHomePath;
	}

	/**
	 * Produces a human-readable output without leaking password
	 */
	public final function __toString() : string{
		return "$this->username@$this->host:$this->port";
	}

	/**
	 * Prepares value to be var_dump()'ed without leaking password
	 */
	public final function __debugInfo() : array{
		return [
			"host" => $this->host,
			"port" => $this->port,
			"username" => $this->username,
			"password" => str_repeat("*", strlen($this->password))
		];
	}
}