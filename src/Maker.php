<?php

namespace DKulyk\XMLTools;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Class XMLMaker.
 * @package DKulyk\XMLTools
 */
class Maker
{
    /**
     * Make xml from structured array.
     *
     * @example
     * <code>
     * XMLMaker::make(array('root' => array(
     *     'item' => array(
     *         array('key'=>'value'),
     *         array('key'=>'value2'),
     *         array(
     *             '@attr'=>'attrValue'
     *             '#'=>'nodeValue'
     *         )
     *     )
     * )));
     * </code>
     * @param array $data
     * @param string $version
     * @param string $encoding
     * @return string
     */
    public static function make(array $data, $version = '1.0', $encoding = 'utf-8')
    {
        $dom = new DOMDocument($version, $encoding);
        $dom->formatOutput = true;

        foreach ($data as $key => $value) {
            static::_make($dom, $dom, $key, $value);
        }

        return $dom->saveXML();
    }

    /**
     * Create new node.
     *
     * @param DOMDocument $dom
     * @param DOMNode $parent
     * @param $key
     * @param $value
     */
    protected static function _make(DOMDocument $dom, DOMNode $parent, $key, $value)
    {
        if ($key === '#') {
            $parent->appendChild($dom->createTextNode($value));
        } elseif (preg_match('/^@(.+)$/', $key, $m)) {
            if ($parent instanceof DOMElement) {
                $parent->setAttribute($m[1], $value);
            }
        } elseif (is_array($value)) {
            if (key($value) === 0) {
                foreach ($value as $item) {
                    static::_make($dom, $parent, $key, $item);
                }
            } else {
                $parent->appendChild($element = $dom->createElement($key));
                foreach ($value as $key2 => $item) {
                    static::_make($dom, $element, $key2, $item);
                }
            }
        } else {
            $parent->appendChild($dom->createElement($key, $value));
        }
    }
}