<?php

declare(strict_types=1);

namespace matcracker\ServerTools\forms\files;

use Closure;
use matcracker\FormLib\CustomForm;

abstract class FileInputForm extends CustomForm{

	public function __construct(string $title, string $text, string $placeHolder, ?string $defaultText, Closure $onSubmit, ?Closure $onClose = null){
		parent::__construct($onSubmit, $onClose);

		$this->setTitle($title)
			->addInput($text, $placeHolder, $defaultText);
	}
}