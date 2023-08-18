<?php

/*
 *	  _________                              ___________           .__
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

use FTP\Connection;
use function extension_loaded;
use function ftp_chdir;
use function ftp_close;
use function ftp_connect;
use function ftp_login;
use function ftp_mkdir;
use function ftp_ssl_connect;

final class FTPConnection extends BaseFTPConnection{
	private bool $ssl;

	public function __construct(string $host, int $port, string $username, string $password, string $remoteHomePath, bool $ssl){
		parent::__construct($host, $port, $username, $password, $remoteHomePath);
		$this->ssl = $ssl;
	}

	public static function hasExtension() : bool{
		return extension_loaded("ftp");
	}

	public function getProtocolName() : string{
		return "FTP";
	}

	public function connect() : Connection|int{
		$ftpConn = $this->ssl ? ftp_ssl_connect($this->host, $this->port) : ftp_connect($this->host, $this->port);

		if($ftpConn === false){
			return self::ERR_CONNECT;
		}

		if(!ftp_login($ftpConn, $this->username, $this->password)){
			if(!ftp_close($ftpConn)){
				return self::ERR_DISCONNECT;
			}

			return self::ERR_LOGIN;
		}

		return $ftpConn;
	}

	public function putDirectory($connection, string $remoteDirPath, int $mode = 0644) : bool{
		if(!@ftp_chdir($connection, $remoteDirPath)){
			return ftp_mkdir($connection, $remoteDirPath) !== false;
		}

		return true;
	}

	public function putFile($connection, string $localFile, string $remoteFile, int $mode = 0644) : bool{
		return ftp_put($connection, $remoteFile, $localFile);
	}

	/**
	 * @param Connection $connection
	 */
	public function disconnect($connection) : bool{
		return ftp_close($connection);
	}
}