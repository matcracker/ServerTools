<?php

declare(strict_types=1);

namespace matcracker\ServerTools\forms\files;

use Closure;
use matcracker\FormLib\CustomForm;
use pocketmine\utils\TextFormat;

abstract class FileInputForm extends CustomForm{

	protected const FILE_NAME = "file_name";

	public function __construct(string $title, string $text, string $placeHolder, ?string $defaultText, ?string $error, Closure $onSubmit, ?Closure $onClose = null){
		parent::__construct($onSubmit, $onClose);

		$this->setTitle($title);
		if($error !== null){
			$this->addLabel(TextFormat::RED . $error);
		}
		$this->addInput($text, $placeHolder, $defaultText, self::FILE_NAME);
	}
}