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

final class PluginInfo{

	/** @var string */
	private $pluginName;
	/** @var string */
	private $authors;
	/** @var string */
	private $version;
	/** @var string */
	private $apiFrom;
	/** @var string */
	private $apiTo;
	/** @var string */
	private $artifactUrl;
	/** @var string */
	private $shortDescription;
	/** @var string */
	private $license;
	/** @var string */
	private $iconUrl;

	public function __construct(string $pluginName, string $authors, string $version, string $apiFrom, string $apiTo, string $artifactUrl, string $iconUrl, string $shortDescription, string $license){
		$this->pluginName = $pluginName;
		$this->authors = $authors;
		$this->version = $version;
		$this->apiFrom = $apiFrom;
		$this->apiTo = $apiTo;
		$this->artifactUrl = $artifactUrl;
		$this->iconUrl = $iconUrl;
		$this->shortDescription = $shortDescription;
		$this->license = $license;
	}

	public function getPluginName() : string{
		return $this->pluginName;
	}

	public function getAuthors() : string{
		return $this->authors;
	}

	public function getVersion() : string{
		return $this->version;
	}

	public function getApiFrom() : string{
		return $this->apiFrom;
	}

	public function getApiTo() : string{
		return $this->apiTo;
	}

	public function getArtifactUrl() : string{
		return $this->artifactUrl;
	}

	public function getIconUrl() : string{
		return $this->iconUrl;
	}

	public function getShortDescription() : string{
		return $this->shortDescription;
	}

	public function getLicense() : string{
		return $this->license;
	}

	public function getDownloadLink() : string{
		return "{$this->artifactUrl}/{$this->pluginName}.phar";
	}
}