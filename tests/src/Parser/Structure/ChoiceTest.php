<?php

use \vanderlee\comprehend\parser\structure\Choice;
use \vanderlee\comprehend\parser\terminal\Text;
use \vanderlee\comprehend\core\Context;

/**
 * @group structure
 * @group parser
 */
class ChoiceTest extends TestCase {

	/**
	 * @param Choice $parser
	 * @param string $flag
	 * @param string $input
	 * @param int $offset
	 * @param bool $match
	 * @param int $length
	 * 
	 * @covers Choice
	 * @dataProvider choiceData
	 */
	public function testChoice(Choice $parser, string $input, int $offset, bool $match, int $length)
	{
		$this->assertResult($match, $length, $parser->match($input, $offset), (string) $parser);
	}

	public function choiceData()
	{
		return [
			[new Choice('a', 'b'), '', 0, false, 0],
			[new Choice('a', 'b'), 'a', 0, true, 1],
			[new Choice('a', 'b'), 'b', 0, true, 1],
			[new Choice('a', 'b'), 'c', 0, false, 0],
			[new Choice('a', 'b'), 'za', 0, false, 0],
			[new Choice('a', 'b'), 'za', 1, true, 1],
			[(new Choice('a', 'b'))->preferFirst(), '', 0, false, 0],
			[(new Choice('a', 'b'))->preferFirst(), 'a', 0, true, 1],
			[(new Choice('a', 'b'))->preferFirst(), 'b', 0, true, 1],
			[(new Choice('a', 'b'))->preferFirst(), 'c', 0, false, 0],
			[(new Choice('a', 'b'))->preferFirst(), 'za', 0, false, 0],
			[(new Choice('a', 'b'))->preferFirst(), 'za', 1, true, 1],
			[(new Choice('a', 'ab'))->preferFirst(), 'ab', 0, true, 1],
			[(new Choice('ab', 'a'))->preferFirst(), 'ab', 0, true, 2],
			[(new Choice('abc', 'aaa'))->preferFirst(), 'ab', 0, false, 2],
			[(new Choice('aaa', 'abc'))->preferFirst(), 'ab', 0, false, 2],
			[(new Choice('a', 'b'))->preferLongest(), '', 0, false, 0],
			[(new Choice('a', 'b'))->preferLongest(), 'a', 0, true, 1],
			[(new Choice('a', 'b'))->preferLongest(), 'b', 0, true, 1],
			[(new Choice('a', 'b'))->preferLongest(), 'c', 0, false, 0],
			[(new Choice('a', 'b'))->preferLongest(), 'za', 0, false, 0],
			[(new Choice('a', 'b'))->preferLongest(), 'za', 1, true, 1],
			[(new Choice('a', 'ab'))->preferLongest(), 'ab', 0, true, 2],
			[(new Choice('ab', 'a'))->preferLongest(), 'ab', 0, true, 2],
			[(new Choice('abc', 'aaa'))->preferLongest(), 'ab', 0, false, 2],
			[(new Choice('aaa', 'abc'))->preferLongest(), 'ab', 0, false, 2],
			[(new Choice('a', 'b'))->preferShortest(), '', 0, false, 0],
			[(new Choice('a', 'b'))->preferShortest(), 'a', 0, true, 1],
			[(new Choice('a', 'b'))->preferShortest(), 'b', 0, true, 1],
			[(new Choice('a', 'b'))->preferShortest(), 'c', 0, false, 0],
			[(new Choice('a', 'b'))->preferShortest(), 'za', 0, false, 0],
			[(new Choice('a', 'b'))->preferShortest(), 'za', 1, true, 1],
			[(new Choice('a', 'ab'))->preferShortest(), 'ab', 0, true, 1],
			[(new Choice('ab', 'a'))->preferShortest(), 'ab', 0, true, 1],
			[(new Choice('abc', 'aaa'))->preferShortest(), 'ab', 0, false, 1],
			[(new Choice('aaa', 'abc'))->preferShortest(), 'ab', 0, false, 1],
				//@todo all things being equal, prefer first.
		];
	}

	public function testResultAs()
	{
		$parser = (new Choice(
				(new Text('a'))->resultAs('valueA'), (new Text('b'))->resultAs('valueB')
				))->resultAs('choice');

		$match = $parser->match('a');
		$this->assertTrue($match->match);
		$this->assertTrue($match->hasResult('choice'));
		$this->assertEquals('a', $match->getResult('choice'));
		$this->assertTrue($match->hasResult('valueA'));
		$this->assertEquals('a', $match->getResult('valueA'));
		$this->assertFalse($match->hasResult('valueB'));
		$this->assertEquals(null, $match->getResult('valueB'));

		$match = $parser->match('b');
		$this->assertTrue($match->match);
		$this->assertTrue($match->hasResult('choice'));
		$this->assertEquals('b', $match->getResult('choice'));
		$this->assertFalse($match->hasResult('valueA'));
		$this->assertEquals(null, $match->getResult('valueA'));
		$this->assertTrue($match->hasResult('valueB'));
		$this->assertEquals('b', $match->getResult('valueB'));

		$match = $parser->match('c');
		$this->assertFalse($match->match);
		$this->assertFalse($match->hasResult('choice'));
		$this->assertEquals(null, $match->getResult('choice'));
		$this->assertFalse($match->hasResult('valueA'));
		$this->assertEquals(null, $match->getResult('valueA'));
		$this->assertFalse($match->hasResult('valueB'));
		$this->assertEquals(null, $match->getResult('valueB'));
	}

	public function testAssignTo()
	{
		$a = $b = $choice = null;

		$parser = (new Choice(
				(new Text('a'))->assignTo($a), (new Text('b'))->assignTo($b)
				))->assignTo($choice);

		$a = $b = $choice = null;
		$match = $parser->match('a');
		$this->assertTrue($match->match);
		$this->assertEquals('a', $choice);
		$this->assertEquals('a', $a);
		$this->assertEquals(null, $b);

		$a = $b = $choice = null;
		$match = $parser->match('b');
		$this->assertTrue($match->match);
		$this->assertEquals('b', $choice);
		$this->assertEquals(null, $a);
		$this->assertEquals('b', $b);

		$a = $b = $choice = null;
		$match = $parser->match('c');
		$this->assertFalse($match->match);
		$this->assertEquals(null, $choice);
		$this->assertEquals(null, $a);
		$this->assertEquals(null, $b);
	}

}