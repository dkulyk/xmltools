<?php

namespace DKulyk\XMLTools;

use DOMCharacterData;
use DOMDocument;
use DOMNode;

/**
 * Class Parser.
 *
 * @package DKulyk\XMLTools
 */
class Parser
{
    /**
     * Get node as list.
     *
     * @param mixed $node
     * @return array
     */
    public static function &toList(&$node)
    {
        $res = is_array($node) && key($node) === 0 ? $node : array(&$node);

        return $res;
    }

    /**
     * Parse xml to array.
     *
     * @param string $xml
     * @param boolean $namespaces Keep namespaces
     * @return mixed
     */
    public static function parse($xml, $namespaces = true)
    {
        $dom = new DOMDocument();
        $dom->loadXML($xml);

        return static::_parse($dom, $namespaces);
    }

    /**
     * Sub parse node.
     *
     * @param DOMNode $node
     * @param bool $namespaces
     * @return array|string
     */
    protected static function _parse(DOMNode $node, $namespaces = true)
    {
        if ($node instanceof DOMCharacterData) {
            return $node->nodeValue;
        } else {
            $result = array();
            if ($node->attributes) {
                for ($i = 0, $l = $node->attributes->length; $i < $l; ++$i) {
                    $item = $node->attributes->item($i);
                    $nodeName = $namespaces ? $item->nodeName : static::cleanNamespace($item->nodeName);
                    $result['@' . $nodeName] = $item->nodeValue;
                }
            }
            $l = $node->childNodes->length;
            if ($l === 1 && ($item = $node->childNodes->item(0)) && $item->nodeType === XML_TEXT_NODE) {
                if (count($result)) {
                    $result['#'] = $node->nodeValue;
                } else {
                    $result = $node->nodeValue;
                }
            } else {
                if ($l) {
                    $multi = array();
                    for ($i = 0; $i < $l; ++$i) {
                        $item = $node->childNodes->item($i);
                        $nodeName = $namespaces ? $item->nodeName : static::cleanNamespace($item->nodeName);
                        if ($item->nodeType === XML_ELEMENT_NODE) {
                            if (array_key_exists($nodeName, $result)) {
                                if (empty($multi[$nodeName])) {
                                    $multi[$nodeName] = true;
                                    $result[$nodeName] = array($result[$nodeName]);
                                }
                                $result[$nodeName][] = static::_parse($item, $namespaces);
                            } else {
                                $result[$nodeName] = static::_parse($item, $namespaces);
                            }
                        }
                    }
                }
            }

            return $result;
        }
    }

    /**
     * Clean namespace.
     *
     * @param string $name
     * @return string
     */
    public static function cleanNamespace($name)
    {
        $names = explode(':', $name);

        return end($names);
    }
}