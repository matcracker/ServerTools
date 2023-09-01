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

use UnexpectedValueException;
use function extension_loaded;
use function ftp_chdir;
use function ftp_close;
use function ftp_connect;
use function ftp_login;
use function ftp_mkdir;
use function ftp_pasv;
use function ftp_ssl_connect;

class FTPHandler extends BaseFTPHandler{
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

	public function connect() : int{
		$ftpConn = $this->ssl ? @ftp_ssl_connect($this->getHost(), $this->getPort()) : @ftp_connect($this->getHost(), $this->getPort());

		if($ftpConn === false){
			return self::ERR_CONNECT;
		}

		if(!@ftp_login($ftpConn, $this->getUsername(), $this->getPassword())){
			if(!@ftp_close($ftpConn)){
				return self::ERR_DISCONNECT;
			}

			return self::ERR_LOGIN;
		}

		@ftp_pasv($ftpConn, true);

		$this->session = $ftpConn;

		return self::NO_ERROR;
	}

	public function putDirectory(string $remoteDirPath, int $mode = 0644) : bool{
		if($this->session === null){
			throw new UnexpectedValueException("The session has not been initialized, call connect method before.");
		}

		if(!@ftp_chdir($this->session, $remoteDirPath)){
			return @ftp_mkdir($this->session, $remoteDirPath) !== false;
		}

		return true;
	}

	public function putFile(string $localFile, string $remoteFile) : bool{
		if($this->session === null){
			throw new UnexpectedValueException("The session has not been initialized, call connect method before.");
		}

		return @ftp_put($this->session, $remoteFile, $localFile);
	}

	public function disconnect() : bool{
		if($this->session === null){
			throw new UnexpectedValueException("The session has not been initialized, call connect method before.");
		}

		$result = @ftp_close($this->session);
		$this->session = null;

		return $result;
	}
}