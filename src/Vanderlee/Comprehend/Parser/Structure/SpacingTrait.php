<?php

namespace vanderlee\comprehend\parser\structure;

use \vanderlee\comprehend\parser\Parser;
use \vanderlee\comprehend\core\Context;
use \vanderlee\comprehend\core\ArgumentsTrait;

/**
 * Classes implementing this can scan
 * 
 * @author Martijn
 */
trait SpacingTrait {

	use ArgumentsTrait;
	
	/**
	 * Parser used for scanning the text
	 * @var Parser 
	 */
	private $spacer = false;

	private function pushSpacer(Context $context)
	{
		if ($this->spacer !== false) {
			$context->pushSpacer($this->spacer);
		}
	}

	private function popSpacer(Context $context)
	{
		if ($this->spacer !== false) {
			$context->popSpacer();
		}
	}

	/**
	 * Set a spacing parser for this parser or disable or enable (if a previous
	 * spacing parser is enabled) spacing parsing.
	 * 
	 * @param Parser|bool $spacer
	 */
	public function spacing($spacer = true)
	{
		$this->spacer = $spacer === false ? null : ($spacer === true ? false : self::getArgument($spacer));
	}

}