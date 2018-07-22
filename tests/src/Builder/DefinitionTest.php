<?php

use \vanderlee\comprehend\parser\Parser;
use \vanderlee\comprehend\parser\structure\Sequence;
use \vanderlee\comprehend\parser\structure\Repeat;
use \vanderlee\comprehend\parser\terminal\Set;
use \vanderlee\comprehend\parser\structure\Choice;
use \vanderlee\comprehend\core\Context;
use \vanderlee\comprehend\match\Match;
use \vanderlee\comprehend\parser\terminal\Range;
use \vanderlee\comprehend\builder\Definition;

/**
 * @group structure
 * @group parser
 */
class DefinitionTest extends TestCase {

	const CSV_RECORD = [__CLASS__, 'makeCsvRecordParser'];
	const QUOTED_STRING = [__CLASS__, 'makeQuotedStringParser'];

	public static function makeCsvRecordParser($item, $delimiter = ',')
	{
		return new Sequence($item, new Repeat(new Sequence($delimiter, $item)));
	}

	public static function makeQuotedStringParser($enclosures = '"')
	{
		if (mb_strlen($enclosures) === 1) {
			return new Sequence(new Set($enclosures), new Repeat(new Set($enclosures, false)), new Set($enclosures));
		} else {
			return new Choice(array_map(function($enclosure) {
						return new Sequence(new Set($enclosure), new Repeat(new Set($enclosure, false)), new Set($enclosure));
					}, str_split($enclosures)));
		}
	}

	public function testDefinition()
	{
		$definition = new Definition(self::CSV_RECORD);

		$List = $definition('x');
		$this->assertResult(true, 5, $List('x,x,x'));

		$List = $definition->build('x');
		$this->assertResult(true, 5, $List('x,x,x'));
	}

	public function testDefinitionSetParser()
	{
		$definition = (new Definition)->parser(self::CSV_RECORD);

		$List = $definition->build('x');
		$this->assertResult(true, 5, $List('x,x,x'));
	}

	public function testQuotedList()
	{
		$qs = (new Definition(self::QUOTED_STRING))();
		$this->assertResult(true, 5, $qs('"foo"'));
		$this->assertResult(false, 5, $qs('"foo`'));

		$qs = (new Definition(self::QUOTED_STRING))('e');
		$this->assertResult(true, 4, $qs('emoe'));
		$this->assertResult(false, 3, $qs('emo'));

		$qs = (new Definition(self::QUOTED_STRING))('"/');
		$this->assertResult(true, 5, $qs('/foo/'));
		$this->assertResult(true, 5, $qs('"foo"'));
		$this->assertResult(false, 5, $qs('"foo/'));
		$this->assertResult(false, 5, $qs('/foo"'));
		$this->assertResult(true, 6, $qs('/foo"/'));
	}

	public function testOddNumbers()
	{
		$d = new Definition(new Repeat(new Range('0', '9'), 1));
		$number = $d();
		$this->assertResult(true, 2, $number('11'));
		$this->assertResult(true, 2, $number('12'));

		$d->validator(function($text) {
			return intval($text) % 2 === 1;
		});
		$number = $d();
		$this->assertResult(true, 2, $number('11'));
		$this->assertResult(false, 2, $number('12'));
	}

	public function testFirstDigitOdd()
	{
		$d = new Definition(new Sequence((new Range('1', '9'))->resultAs('first'), new Repeat(new Range('0', '9'))));
		$number = $d();
		$this->assertResult(true, 2, $number('11'));
		$this->assertResult(true, 2, $number('21'));

		$d->validator(function($text, $results) {
			return intval($results['first']) % 2 === 1;
		});
		$number = $d();
		$this->assertResult(true, 2, $number('11'));
		$this->assertResult(false, 2, $number('21'));
	}

	public function testWrap()
	{
		// Must be odd
		$original = (new Definition(new Repeat(new Range('0', '9'), 1)))
				->validator(function($text) {
			return intval($text) % 2 === 1;
		});

		$inherited = new Definition($original);
		
		// Must be multiple of three
		$inherited->validator(function($text) {
			return intval($text) % 3 === 0;
		});

		// Assert original is untouched (odd)
		$number = $original();
		$this->assertResult(false, 2, $number('10')); // not odd
		$this->assertResult(true, 2, $number('11')); // odd
		$this->assertResult(false, 2, $number('12')); // not odd
		$this->assertResult(true, 2, $number('15')); // odd
		//
		// Test inherited ; both odd and multiple of three
		$number = $inherited();
		$this->assertResult(false, 2, $number('10')); // odd and no multiple of 3
		$this->assertResult(false, 2, $number('11')); // odd, but no multiple of 3
		$this->assertResult(false, 2, $number('12')); // multiple of 3, but not odd
		$this->assertResult(true, 2, $number('15')); // odd and multiple of 3
	}

	public function testClone()
	{
		// Must be odd
		$original = (new Definition(new Repeat(new Range('0', '9'), 1)))
				->validator(function($text) {
			return intval($text) % 2 === 1;
		});

		$clone = clone $original; // works as is!
		//
		// Must be multiple of three
		$clone->validator(function($text) {
			return intval($text) % 3 === 0;
		});

		// Assert original is untouched (odd)
		$number = $original();
		$this->assertResult(false, 2, $number('10')); // not odd
		$this->assertResult(true, 2, $number('11')); // odd
		$this->assertResult(false, 2, $number('12')); // not odd
		$this->assertResult(true, 2, $number('15')); // odd
		//
		// Test clone; multiple of three, don't care about odd
		$number = $clone();
		$this->assertResult(false, 2, $number('10')); // no multiple of 3
		$this->assertResult(false, 2, $number('11')); // no multiple of 3
		$this->assertResult(true, 2, $number('12')); // multiple of 3
		$this->assertResult(true, 2, $number('15')); // multiple of 3
	}

}