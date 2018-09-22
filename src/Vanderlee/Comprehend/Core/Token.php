<?php

namespace vanderlee\comprehend\core;

use vanderlee\comprehend\parser\terminal\Text;

/**
 * Class Token
 *
 * @package vanderlee\comprehend\core
 *
 * @property-read string $text
 */
class Token implements \JsonSerializable
{

    private $group  = null;
    private $name   = null;
    private $input  = null;
    private $offset = null;
    private $length = null;
    private $class  = null;

    /**
     * @var Token[]
     */
    private $children = [];

    public function __construct($group, $name, &$input, $offset, $length, &$children = [], $class = null)
    {
        $text = substr($input, $offset, $length);

        $this->group    = $group;
        $this->name     = $name;
        $this->input    = $input;
        $this->offset   = $offset;
        $this->length   = $length;
        $this->children = $children;
        $this->class    = $class;
    }

    public function __get($name)
    {
        switch ($name) {
            case 'text':
                return substr($this->input, $this->offset, $this->length);

            default:
                if (property_exists($this, $name)) {
                    return $this->$name;
                }
        }

        throw new \Exception("Undefined property `{$name}`");
    }

    private function toString($depth = 0)
    {
        $signature = ($this->group ? $this->group . '::' : '')
            . ($this->name ?? $this->class);

        $output = str_repeat('  ', $depth) . "{$signature} (`{$this->text}`)" . PHP_EOL;

        foreach ($this->children as $child) {
            $output .= $child->toString($depth + 1);
        }

        return $output;
    }

    public function __toString()
    {
        return $this->toString();
    }

    /**
     * @return \DOMDocument
     */
    public function toXml()
    {
        $document = new \DOMDocument();
        $document->appendChild($this->createXmlNode($document));
        $document->normalizeDocument();

        return $document;
    }

    /**
     * Create an XML node of this token
     *
     * @param \DOMDocument $document
     * @return \DOMElement
     */
    private function createXmlNode(\DOMDocument $document)
    {
        $name  = $this->name ?? preg_replace('/[^-_a-zA-Z0-9]/', '_', $this->class);
        $value = $this->children ? null : $this->text;

        if ($this->group !== null) {
            $element = $document->createElementNS($this->group, $this->group . ':' . $name, $value);
        } else {
            $element = $document->createElement($name, $value);
        }

        foreach ($this->children as $child) {
            $element->appendChild($child->createXmlNode($document));
        }

        return $element;
    }

    /**
     * Convert object to JSON for json_encode
     * Implements /JsonSerializable interface
     */
    public function jsonSerialize()
    {
        return $this->jsonSerialize();
    }

    /**
     * Output this node as an array.
     *
     * @return array
     */
    public function toArray()
    {
        $array = [
            'group'    => $this->group,
            'name'     => $this->name,
            'text'     => $this->text,
            'offset'   => $this->offset,
            'length'   => $this->length,
            'class'    => $this->class,
            'children' => [],
        ];

        foreach ($this->children as $child) {
            $array['children'][] = $child->toArray();
        }

        return $array;
    }

}