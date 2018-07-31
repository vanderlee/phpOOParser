<?php

namespace vanderlee\comprehend\parser;

use \vanderlee\comprehend\parser\Parser;
use \vanderlee\comprehend\core\Context;
use \vanderlee\comprehend\core\ArgumentsTrait;

/**
 * Description of StubParser
 *
 * @author Martijn
 */
class Stub extends Parser {
	
	use ArgumentsTrait;

	/**
	 * @var Parser|null
	 */
	private $parser = null;

	public function __set($name, $parser)
	{
		if ($name == 'parser') {
			return $this->parser = self::getArgument($parser);
		}
		
		throw new \Exception("Property `{$name}` does not exist");
	}

	public function __get($name)
	{
		if ($name == 'parser') {
			return $this->parser;
		}
		
		throw new \Exception("Property `{$name}` does not exist");
	}

	protected function parse(string &$in, int $offset, Context $context)
	{
		if ($this->parser === null) {
			throw new \Exception('Missing parser');
		}

		$match = $this->parser->parse($in, $offset, $context);
		if ($match->match) {
			return $this->success($in, $offset, $match->length, $match);
		} else {
			return $this->failure($in, $offset, $match->length);
		}
	}

	public function __toString()
	{
		return (string) $this->parser;
	}

}
