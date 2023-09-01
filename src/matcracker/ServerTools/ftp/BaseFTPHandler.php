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

abstract class BaseFTPHandler{
	public const NO_ERROR = 0;
	public const ERR_CONNECT = -1;
	public const ERR_DISCONNECT = -2;
	public const ERR_LOGIN = -3;

	protected mixed $session = null;

	public function __construct(
		private readonly string $host,
		private readonly int $port,
		private readonly string $username,
		private readonly string $password,
		private readonly string $remoteHomePath
	){
	}

	public abstract static function hasExtension() : bool;

	public abstract function getProtocolName() : string;

	public abstract function connect() : int;

	public abstract function disconnect() : bool;

	public abstract function putFile(string $localFile, string $remoteFile) : bool;

	public abstract function putDirectory(string $remoteDirPath, int $mode = 0644) : bool;

	public final function getHost() : string{
		return $this->host;
	}

	public final function getPort() : int{
		return $this->port;
	}

	final public function getUsername() : string{
		return $this->username;
	}

	final public function getPassword() : string{
		return $this->password;
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
			"username" => $this->username
		];
	}
}