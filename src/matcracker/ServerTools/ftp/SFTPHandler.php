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

use UnexpectedValueException;
use function extension_loaded;
use function fclose;
use function fopen;
use function ssh2_auth_password;
use function ssh2_connect;
use function ssh2_disconnect;
use function ssh2_sftp;
use function ssh2_sftp_mkdir;
use function stream_copy_to_stream;

final class SFTPHandler extends BaseFTPHandler{

	public static function hasExtension() : bool{
		return extension_loaded("openssl") && extension_loaded("ssh2");
	}

	public function getProtocolName() : string{
		return "SFTP";
	}

	public function connect() : int{
		$session = @ssh2_connect($this->getHost(), $this->getPort());

		if($session === false){
			return self::ERR_CONNECT;
		}

		if(!@ssh2_auth_password($session, $this->getUsername(), $this->getPassword())){
			if(!@ssh2_disconnect($session)){
				return self::ERR_DISCONNECT;
			}

			return self::ERR_LOGIN;
		}

		$stream = @ssh2_sftp($session);

		if($stream === false){
			return self::ERR_CONNECT;
		}

		$this->session = $stream;

		return self::NO_ERROR;
	}

	public function putDirectory(string $remoteDirPath, int $mode = 0644) : bool{
		if($this->session === null){
			throw new UnexpectedValueException("The session has not been initialized, call connect method before.");
		}

		return @ssh2_sftp_mkdir($this->session, $remoteDirPath, $mode);
	}

	public function putFile(string $localFile, string $remoteFile) : bool{
		if($this->session === null){
			throw new UnexpectedValueException("The session has not been initialized, call connect method before.");
		}

		$localRes = @fopen($localFile, "rb");
		if($localRes === false){
			return false;
		}

		$remoteRes = @fopen("ssh2.sftp://$this->session$remoteFile", "wb");
		if($remoteRes === false){
			return false;
		}

		if(@stream_copy_to_stream($localRes, $remoteRes) === false){
			return false;
		}

		return @fclose($remoteRes) && @fclose($localRes);
	}

	public function disconnect() : bool{
		if($this->session === null){
			throw new UnexpectedValueException("The session has not been initialized, call connect method before.");
		}

		$result = @ssh2_disconnect($this->session);

		$this->session = null;

		return $result;
	}
}