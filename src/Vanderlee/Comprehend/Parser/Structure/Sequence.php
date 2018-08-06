<?php

namespace vanderlee\comprehend\parser\structure;

use \vanderlee\comprehend\parser\Parser;
use \vanderlee\comprehend\core\Context;

/**
 * Description of SequenceParser
 *
 * @author Martijn
 */
class Sequence extends IterableParser {

	use SpacingTrait;

	public function __construct(...$arguments)
	{
		if (empty($arguments)) {
			throw new \InvalidArgumentException('No arguments');
		}
		
		$this->parsers = self::getArguments($arguments, false);
	}

	protected function parse(&$input, $offset, Context $context)
	{
		$child_matches = [];

		$this->pushSpacer($context);

		$total = 0;
		foreach ($this->parsers as $parser) {
			if ($total > 0) {
				$total += $context->skipSpacing($input, $offset + $total);
			}
			$match = $parser->parse($input, $offset + $total, $context);
			$total += $match->length;

			if (!$match->match) {  // must match
				$this->popSpacer($context);
				
				return $this->failure($input, $offset, $total);
			}

			$child_matches[] = $match;
		}

		$this->popSpacer($context);

		return $this->success($input, $offset, $total, $child_matches);
	}

	/**
	 * Add one or more parsers to the end of this sequence
	 * 
	 * @param string[]|int[]|Parser[] $arguments
	 */
	public function add(...$arguments)
	{
		$this->parsers = array_merge($this->parsers, self::getArguments($arguments));
		
		return $this;		
	}

	public function __toString()
	{
		return '( ' . join(' ', $this->parsers) . ' )';
	}

}
