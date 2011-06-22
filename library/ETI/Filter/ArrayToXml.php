<?php
/**
 * @see Zend_Filter_Interface
 */
require_once 'Zend/Filter/Interface.php';
/**
 * Annual Checklist Interface
 *
 * Class Eti_Filter_ArrayToXml
 * Converts an array to XML
 *
 * @category    Eti
 * @package     Eti_Filter
 *
 */
class Eti_Filter_ArrayToXml implements Zend_Filter_Interface
{
    protected $_version = '1.0';
    protected $_encoding = 'UTF-8';
    protected $_preserveWhiteSpace = false;
    protected $_formatOutput = true;
    protected $_defaultNodeName = 'result';
    protected $_nodeNameMapping = array();
    protected $_dom;
    
    /**
     * Set the XML version attribute
     * Defaults to 1.0
     *
     * @param string $version
     * @return Eti_Filter_ArrayToXml $this
     */
    public function setVersion($version)
    {
        $this->_version = (string)$version;
        return $this;
    }

    /**
     * Set the input encoding for the given string
     * Defaults to UTF-8
     *
     * @param  string $encoding
     * @return Eti_Filter_ArrayToXml $this
     */
    public function setEncoding($encoding)
    {
        $this->_encoding = (string)$encoding;
        return $this;
    }
    
    /**
     * Enable/Disable the preserveWhiteSpace option
     * Defaults to false
     *
     * @param bool $op
     * @return Eti_Filter_ArrayToXml $this
     */
    public function preserveWhiteSpace(/*bool*/$op)
    {
        $this->_preserveWhiteSpace = (bool)$op;
        return $this;
    }
    
    /**
     * Enable/Disable the formatOutput option
     * Defaults to true
     *
     * @param bool $op
     * @return Eti_Filter_ArrayToXml $this
     */
    public function formatOutput(/*bool*/$op)
    {
        $this->_formatOutput = (bool)$op;
        return $this;
    }
    
    /**
     * Set the mapping to name the nodes based on the parent node name
     *
     * @param array $nodeNameMapping
     * @return Eti_Filter_ArrayToXml $this
     */
    public function setNodeNameMapping(array $nodeNameMapping)
    {
        $this->_nodeNameMapping = $nodeNameMapping;
        return $this;
    }

    /**
     * Defined by Zend_Filter_Interface
     *
     * Returns the array $value as an xml string
     *
     * @param  array $value
     * @return string
     */
    public function filter($value)
    {
        if (!is_array($value)) {
            throw new Zend_Filter_Exception('Given value is not an array');
        }
        
        $this->_dom = new DOMDocument($this->_version, $this->_encoding);
        $this->_dom->preserveWhiteSpace = $this->_preserveWhiteSpace;
        $this->_dom->formatOutput = $this->_formatOutput;
        
        $xml = $this->_dom->createElement($this->_getNodeName());
        
        foreach ($value as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $i => $result) {
                    $xml = $this->_arrayKeysToNodes(
                        $xml, $result, is_int($i) ? $this->_getNodeName($k) : $i
                    );
                }
            } else {
                $xml->setAttribute($k, $v);
            }
        }
        $this->_dom->appendChild($xml);
        return htmlspecialchars_decode($this->_dom->saveXML(), ENT_NOQUOTES);
    }
    
    /**
     * Converts an array to an XML tree element by recursive invoking
     *
     * @param DOMElement $xml
     * @param array $array
     * @param string $nodeName
     * @return DOMElement
     */
    protected function _arrayKeysToNodes(DOMElement $xml, array $array,
        $nodeName)
    {
        $node = $this->_dom->createElement($nodeName);
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                $this->_arrayKeysToNodes(
                    $node, $v, is_int($k) ? $this->_getNodeName($nodeName) : $k
                );
            } else {
                try {
                    $el = $this->_dom->createElement($k);
                    // clean value
                    $v = $this->_cleanStr($v);
                    $el->appendChild(
                        // if the value is not valid XML, create a CDATA section
                        // instead of a text node
                        $this->_isValidXml($v) ?
                            $this->_dom->createTextNode($v) :
                            $this->_dom->createCDATASection($v)
                    );
                    $node->appendChild($el);
                }
                catch(DOMException $e) {
                    // this exception may occur if the data is corrupted
                    // e.g. trying to create an element of name 0
                    // (skip node)
                }
            }
        }
        $xml->appendChild($node);
        return $xml;
    }
    
    /**
     * Gets the node name from the parent => child mapping
     *
     * @param string $parentNodeName
     * @return string
     */
    protected function _getNodeName($parentNodeName = null)
    {
        if(is_null($parentNodeName)) {
            $parentNodeName = 'root';
        }
        return isset($this->_nodeNameMapping[$parentNodeName]) ?
            $this->_nodeNameMapping[$parentNodeName] : $this->_defaultNodeName;
    }
    
    /**
     * Encodes to UTF-8
     * Removes trailing and leading whitespaces
     * Replaces chr(11) vertical tabs with chr(32) spaces
     *
     * @param string $str
     * @return string
     */
    protected function _cleanStr($str)
    {
        return trim(utf8_encode(str_replace(chr(11), chr(32), $str)));
    }
    
    /**
     * Checks whether a string contains allowed XML characters by attempting
     * to create an instance of SimpleXMLElement
     *
     * @param string $str
     * @return boolean
     */
    protected function _isValidXml($str)
    {
        return @simplexml_load_string('<x>' . $str . '</x>')
            instanceof SimpleXMLElement;
    }
}