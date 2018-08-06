<?php

namespace vanderlee\comprehend\core;

use \vanderlee\comprehend\parser\Parser;
use \vanderlee\comprehend\parser\terminal\Char;
use \vanderlee\comprehend\parser\terminal\Text;
use \vanderlee\comprehend\parser\structure\Sequence;

/**
 * Process arguments
 *
 * @author Martijn
 */
trait ArgumentsTrait
{

    /**
     * Convert the argument to a parser
     *
     * @param $argument
     * @param bool $arrayToSequence if argument is an array, convert to Sequence (`true`) or Choice (`false`)
     * @return Parser
     */
    protected static function getArgument($argument, $arrayToSequence = true)
    {
        if (is_array($argument)) {
            if (empty($argument)) {
                throw new \InvalidArgumentException('Empty array argument');
            } elseif (count($argument) === 1) {
                return self::getArgument(reset($argument));
            }

            return $arrayToSequence ? new Sequence(...$argument) : new Choice(...$arrayToSequence);
        } elseif (is_string($argument)) {
            switch (strlen($argument)) {
                case 0:
                    throw new \InvalidArgumentException('Empty argument');
                case 1:
                    return new Char($argument);
                default:
                    return new Text($argument);
            }
        } elseif (is_int($argument)) {
            return new Char($argument);
        } elseif ($argument instanceof Parser) {
            return $argument;
        }

        throw new \InvalidArgumentException(sprintf('Invalid argument type `%1$s`', is_object($argument) ? get_class($argument) : gettype($argument)));
    }

    protected static function getArguments($arguments, $arrayToSequence = true)
    {
        return array_map(function ($argument) use ($arrayToSequence) {
            return self::getArgument($argument, $arrayToSequence);
        }, $arguments);
    }

    private static function array_flatten($a, $f = [])
    {
        if (!$a || !is_array($a)) {
            return [];
        }
        foreach ($a as $k => $v) {
            if (is_array($v)) {
                $f = self::array_flatten($v, $f);
            } else {
                $f[$k] = $v;
            }
        }

        return $f;
    }

}
