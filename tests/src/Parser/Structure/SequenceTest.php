<?php

use \vanderlee\comprehend\parser\structure\Sequence;
use \vanderlee\comprehend\parser\terminal\Text;
use \vanderlee\comprehend\parser\terminal\Char;

/**
 * @group structure
 * @group parser
 */
class SequenceTest extends TestCase {

	/**
	 * @covers Char
	 */
	public function testEmpty()
	{
		$this->expectExceptionMessage("No arguments");
		new Sequence();
	}
	
	/**
	 * @covers Sequence
	 * @dataProvider sequenceData
	 */
	public function testSequence(Sequence $parser, string $input, int $offset, bool $match, int $length)
	{
		$this->assertResult($match, $length, $parser->match($input, $offset), (string) $parser);
	}

	public function sequenceData()
	{
		return [
			[new Sequence('a'), 'a', 0, true, 1],
			[new Sequence('a'), 'aa', 0, true, 1],
			[new Sequence('a'), 'b', 0, false, 0],
			[new Sequence('a'), 'B', 0, false, 0],
			[new Sequence('abc'), 'abc', 0, true, 3],
			[new Sequence('a', 'b'), 'ab', 0, true, 2],
			[new Sequence('b', 'a'), 'ab', 0, false, 0],
			[new Sequence('a', 'a'), 'ab', 0, false, 1],
			[new Sequence('a'), '', 0, false, 0],
			[new Sequence(new Text('abc')), 'abc', 0, true, 3],
			[new Sequence(new Char('a')), 'abc', 0, true, 1],
			[new Sequence(new Char('a'), new Text('bc')), 'abc', 0, true, 3],
		];
	}

	public function testset()
	{
		$parser = (new Sequence(
				(new Text('a'))->setResult('valueA'), (new Text('b'))->setResult('valueB')
				))->setResult('word');

		$match = $parser->match('a');
		$this->assertFalse($match->match);
		$this->assertFalse($match->hasResult('word'));
		$this->assertEquals(null, $match->getResult('word'));
		$this->assertFalse($match->hasResult('valueA'));
		$this->assertEquals(null, $match->getResult('valueA'));
		$this->assertFalse($match->hasResult('valueB'));
		$this->assertEquals(null, $match->getResult('valueB'));

		$match = $parser->match('ab');
		$this->assertTrue($match->match);
		$this->assertTrue($match->hasResult('word'));
		$this->assertEquals('ab', $match->getResult('word'));
		$this->assertTrue($match->hasResult('valueA'));
		$this->assertEquals('a', $match->getResult('valueA'));
		$this->assertTrue($match->hasResult('valueB'));
		$this->assertEquals('b', $match->getResult('valueB'));
	}

	public function testAssignTo()
	{
		$a = $b = $sequence = null;

		$parser = (new Sequence(
				(new Text('a'))->assignTo($a), (new Text('b'))->assignTo($b)
				))->assignTo($sequence);

		$a = $b = $sequence = null;
		$match = $parser->match('a');
		$this->assertFalse($match->match);
		$this->assertEquals(null, $sequence);
		$this->assertEquals(null, $a);
		$this->assertEquals(null, $b);

		$a = $b = $sequence = null;
		$match = $parser->match('ab');
		$this->assertTrue($match->match);
		$this->assertEquals('ab', $sequence);
		$this->assertEquals('a', $a);
		$this->assertEquals('b', $b);
	}

}
