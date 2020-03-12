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


use DirectoryIterator;
use pocketmine\Server;
use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use function file_exists;
use function in_array;
use function mb_substr;
use function round;
use function str_replace;

final class Utils{

	public static function bytesToHuman(int $size, int $precision = 2) : string{
		static $units = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
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
	 * @return string[][]|null File paths
	 */
	public static function getSortedFileList(string $path) : ?array{
		if(!file_exists($path)){
			return null;
		}

		$fileList = [];
		$dirIterator = new DirectoryIterator($path);
		if(!$dirIterator->valid()){
			return $fileList;
		}

		$btnIdx = 0;
		foreach($dirIterator as $fileInfo){
			$name = $fileInfo->getFilename();
			if($fileInfo->isDir() && !$fileInfo->isDot()){
				$fileList["dir"][$btnIdx] = $name;
				$btnIdx++;
			}
		}

		$dirIterator->rewind();

		foreach($dirIterator as $fileInfo){
			$name = $fileInfo->getFilename();
			if(!$fileInfo->isDir()){
				$fileList["file"][$btnIdx] = $name;
				$btnIdx++;
			}
		}

		return $fileList;
	}

	public static function getServerPath() : string{
		return mb_substr(Server::getInstance()->getDataPath(), 0, -1);
	}

	public static function getUnixPath(string $path) : string{
		return str_replace("\\", "/", $path);
	}

	/**
	 * @param RecursiveIteratorIterator $iterator
	 *
	 * @return int
	 */
	public static function getIteratorSize(RecursiveIteratorIterator $iterator) : int{
		$bytes = 0;
		/**@var SplFileInfo $fileInfo */
		foreach($iterator as $fileInfo){
			$bytes += $fileInfo->getSize();
		}

		return $bytes;
	}

	/**
	 * @param string   $path
	 * @param string[] $filter
	 *
	 * @return RecursiveIteratorIterator
	 */
	public static function getRecursiveIterator(string $path, array $filter) : RecursiveIteratorIterator{
		return new RecursiveIteratorIterator(
			new RecursiveCallbackFilterIterator(
				new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
				static function(SplFileInfo $file, $key, RecursiveDirectoryIterator $iterator) use ($filter): bool{
					if($iterator->hasChildren() && !in_array($file->getFilename(), $filter)){
						return true;
					}

					return $file->isFile();
				}
			), RecursiveIteratorIterator::SELF_FIRST
		);
	}
}