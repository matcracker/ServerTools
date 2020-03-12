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

use function extension_loaded;
use function fclose;
use function fopen;
use function is_resource;
use function ssh2_auth_password;
use function ssh2_connect;
use function ssh2_disconnect;
use function ssh2_sftp;
use function ssh2_sftp_mkdir;
use function stream_copy_to_stream;

final class SFTPConnection extends FTPBase{

	/**
	 * @return int|resource
	 */
	public function connect(){
		$ftpConn = @ssh2_connect($this->host, $this->port);

		if(!is_resource($ftpConn)){
			return self::ERR_CONNECT;
		}

		if(!@ssh2_auth_password($ftpConn, $this->username, $this->password)){
			if(!@ssh2_disconnect($ftpConn)){
				return self::ERR_DISCONNECT;
			}

			return self::ERR_LOGIN;
		}

		return ssh2_sftp($ftpConn);
	}

	public function putDirectory($connection, string $remoteDir, int $mode = 0644) : bool{
		return @ssh2_sftp_mkdir($connection, $remoteDir, $mode);
	}

	public function putFile($connection, string $localFile, string $remoteFile, int $mode = 0644) : bool{
		$localRes = @fopen($localFile, 'rb');
		if($localRes === false){
			return false;
		}

		$remoteRes = @fopen("ssh2.sftp://{$connection}{$remoteFile}", 'wb');
		if($remoteRes === false){
			return false;
		}

		if(@stream_copy_to_stream($localRes, $remoteRes) === false){
			return false;
		}

		return @fclose($remoteRes) && @fclose($localRes);
	}

	/**
	 * @param resource $connection
	 *
	 * @return bool
	 */
	public function disconnect($connection) : bool{
		return @ssh2_disconnect($connection);
	}

	public static function hasExtension() : bool{
		return extension_loaded("openssl") && extension_loaded("ssh2");
	}

	public static function getProtocolName() : string{
		return "SFTP";
	}
}