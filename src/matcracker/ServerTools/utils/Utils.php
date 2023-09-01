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

namespace matcracker\ServerTools\utils;

use DirectoryIterator;
use pocketmine\utils\Utils as PMUtils;
use SplFileInfo;
use function array_merge;
use function file_exists;
use function preg_match;
use function round;

final class Utils{

	public static function bytesToHuman(int $size, int $precision = 2) : string{
		static $units = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
		$step = 1024;
		$i = 0;
		while(($size / $step) > 0.9){
			$size = $size / $step;
			$i++;
		}

		return round($size, $precision) . " " . $units[$i];
	}

	/**
	 * Returns a sorted files list by type.
	 * 1) Folders
	 * 2) Files
	 * Both alphabetically sorted.
	 *
	 * @param string $path
	 *
	 * @return SplFileInfo[] File paths
	 */
	public static function getSortedFileList(string $path) : array{
		if(!file_exists($path)){
			return [];
		}

		$dirIterator = new DirectoryIterator($path);
		if(!$dirIterator->valid()){
			return [];
		}

		$fileList = [
			"dir" => [],
			"file" => []
		];

		/** @var DirectoryIterator $iterator */
		foreach($dirIterator as $iterator){
			$fileInfo = $iterator->getFileInfo();
			if($iterator->isDot()){
				continue;
			}elseif($iterator->isDir()){
				$fileList["dir"][] = $fileInfo;
			}else{
				$fileList["file"][] = $fileInfo;
			}
		}

		return array_merge($fileList["dir"], $fileList["file"]);
	}

	public static function isValidFileName(string $fileName) : bool{
		return match (PMUtils::getOS()) {
			PMUtils::OS_WINDOWS => self::isValidWindowsFileName($fileName),
			PMUtils::OS_MACOS, PMUtils::OS_IOS => self::isValidMacFileName($fileName),
			default => self::isValidUnixFileName($fileName),
		};
	}

	public static function isValidWindowsFileName(string $fileName) : bool{
		$regex = <<<'EOREGEX'
			~                               # start of regular expression
			^                               # Anchor to start of string.
			(?!                             # Assert filename is not: CON, PRN, AUX, NUL, COM1, COM2, COM3, COM4, COM5, COM6, COM7, COM8, COM9, LPT1, LPT2, LPT3, LPT4, LPT5, LPT6, LPT7, LPT8, and LPT9.
				(CON|PRN|AUX|NUL|COM[1-9]|LPT[1-9])
				(\.[^.]*)?                  # followed by optional extension
				$                           # and end of string
			)                               # End negative lookahead assertion.
			[^<>:"/\\|?*\x00-\x1F]*         # Zero or more valid filename chars.
			[^<>:"/\\|?*\x00-\x1F\ .]       # Last char is not a space or dot.
			$                               # Anchor to end of string.
											#
											# tilde = end of regular expression.
											# i = pattern modifier PCRE_CASELESS. Make the match case insensitive.
											# x = pattern modifier PCRE_EXTENDED. Allows these comments inside the regex.
											# D = pattern modifier PCRE_DOLLAR_ENDONLY. A dollar should not match a newline if it is the final character.
			~ixD
			EOREGEX;

		return preg_match($regex, $fileName) === 1;
	}

	public static function isValidMacFileName(string $fileName) : bool{
		return preg_match("/[\/:]/", $fileName) === 0;
	}

	public static function isValidUnixFileName(string $fileName) : bool{
		return !str_contains($fileName, "\x00") && preg_match("/[\/]/", $fileName) === 0;
	}
}