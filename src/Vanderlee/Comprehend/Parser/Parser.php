<?php

namespace vanderlee\comprehend\parser;

use \vanderlee\comprehend\core\Context;
use \vanderlee\comprehend\match\Success;
use \vanderlee\comprehend\match\Failure;
use \vanderlee\comprehend\traits\Assign;
use \vanderlee\comprehend\parser\terminal\Char;
use \vanderlee\comprehend\parser\terminal\Text;

abstract class Parser {

	use ResultTrait;
	use AssignTrait;

	/**
	 * List of callbacks to call when this parser has matched a part of the
	 * full parse.
	 * @var type 
	 */
	private $callbacks = [];

	protected static function parseCharacter($character)
	{
		if ($character === '' || $character === null) {
			throw new \Exception('Empty argument');
		}

		if (is_int($character)) {
			return chr($character);
		} elseif (mb_strlen($character) > 1) {
			throw new \Exception('Non-character argument');
		}

		return $character;
	}

	/**
	 * @return \vanderlee\comprehend\match\Match;
	 */
	abstract protected function parse(string &$in, int $offset, Context $context);

	/**
	 * @param string $in
	 * @param integer $offset
	 * @return Match;
	 */
	public function match(string $in, int $offset = 0)
	{
		if ($offset < 0) {
			throw new \Exception("Negative offset");
		}

		return $this->parse($in, $offset, new Context())->resolve();
	}

	public function __invoke(string $in, int $offset = 0)
	{
		return $this->match($in, $offset);
	}

	/**
	 * Create a succesful match
	 * 
	 * @param string $in
	 * @param int $offset
	 * @param int $length
	 * @param Success[]|Success $successes
	 * @return Success
	 */
	protected function success(string &$in, int $offset, int $length = 0, &$successes = [])
	{
		$callbacks = $this->callbacks;

		$successes = is_array($successes) ? $successes : [$successes];

		return (new Success($length, $successes))
						->addResultCallback(function(&$results) use($in, $offset, $length, $callbacks) {
							$text = substr($in, $offset, $length);

							$this->resolveResultCallbacks($results, $text);
						})->addCustomCallback(function() use($in, $offset, $length, $callbacks) {
					$text = substr($in, $offset, $length);

					$this->resolveAssignCallbacks($text);

					foreach ($callbacks as $callback) {
						$callback($text, $in, $offset, $length);
					}
				});
	}

	/**
	 * Create a failed match
	 * 
	 * @param string $in
	 * @param int $offset
	 * @param int $length
	 * @return Failure
	 */
	protected function failure(string &$in, int $offset, int $length = 0)
	{
		return new Failure($length);
	}

	public function callback(callable $callback)
	{
		$this->callbacks[] = $callback;
		return $this;
	}

	abstract public function __toString();
}