<?php

namespace vanderlee\comprehend\parser\structure;

use \vanderlee\comprehend\parser\Parser;
use \vanderlee\comprehend\core\Context;
use \vanderlee\comprehend\ArgumentsTrait;

/**
 * Match the first parser but not the second.
 * Essentially the same as (A - B) = (A + !B)
 *
 * @author Martijn
 */
class Except extends Parser {
	
	use ArgumentsTrait;

	private $parser_match = null;
	private $parser_not = null;

	/**
	 * 
	 * @param Parser|string $match
	 * @param Parser|string $not
	 */
	public function __construct($match, $not)
	{
		$this->parser_match = self::getArgument($match);
		$this->parser_not = self::getArgument($not);
	}

	protected function parse(string &$in, int $offset, Context $context)
	{
		$match = $this->parser_match->parse($in, $offset, $context);
		$not = $this->parser_not->parse($in, $offset, $context);

		if ($match->match && !$not->match) {
			return $this->success($in, $offset, $match->length, $match);
		}

		return $this->failure($in, $offset, min($match->length, $not->length));
	}

	public function __toString()
	{
		return '( ' . $this->parser_match . ' - ' . $this->parser_not . ' )';
	}

}
