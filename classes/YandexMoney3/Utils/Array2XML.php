<?php

namespace YandexMoney3\Utils;

use \DOMDocument;
use YandexMoney3\Exception;

/*
 * Array2xml: a class to convert a PHP array to XML
 * It also takes into account attributes and cdata unlike SimpleXML in PHP
 * It returns the XML in the form of a DOMDocument object for further manipulation
 * It throws an exception if a tag or attribute name has illegal characters
 *
 * Original Array2xml
 * ------------------
 * Author:  Lalit Patel
 * Website: http://www.lalit.org/lab/convert-php-array-to-xml-with-attributes
 * License: Apache License 2.0
 *          http://www.apache.org/licenses/LICENSE-2.0
 * Version: 0.1 (10 July 2011)
 * Version: 0.2 (16 August 2011)
 *          - Replaced htmlentities() with htmlspecialchars() (Thanks to Liel Dulev)
 *          - Fixed an edge case where root node has a false/null/0 value. (Thanks to Liel Dulev)
 * Version: 0.3 (22 August 2011)
 *          - Fixed tag sanitize regex which didn't allow tagnames with single character.
 * Version: 0.4 (18 September 2011)
 *          - Added support for CDATA section using @cdata instead of @value.
 *
 * Magento Anny_Array2xml
 * ----------------------
 * Author:  Aneurin "Anny" Barker Snook
 * Website: http://anny.fm/
 * Repo:    https://github.com/annyfm/Anny_Array2xml
 * License: Apache License 2.0 as above
 * Version: 0.5 (23 October 2011)
 *          - Adapted for Magento along with slight changes to structure and tidying up up of code.
 * Usage:
 *   In Magento this class should be installed at /app/code/community/Anny/Array2xml.
 *   To get a converter (as this is model-based, every instance is distinct),
 *     $converter = Mage::getModel('array2xml/converter');
 *   To manually init,
 *     $converter->init( $version[, $encoding[, $formatOutput]] );
 *   You can choose to skip init to use defaults. To import xml,
 *     $converter->setTopNodeName($topNodeName)->importArray($data);
 *   And to export it, just
 *     echo $converter;
 *   To reuse outside of Magento, just remove the 'extends' declaration and the class should run
 *   independently without issue.
 *
 *
**/

class Array2XML
{
  private
    $xml = null,
    $encoding = null,
    $topNodeName = null,
    $convertFromEncoding = null;

  /*
   * Initialize the root xml node (optional)
   * @access public
   * @param1 string xml version
   * @param2 string encoding
   * @param3 bool create attractive output?
   * @return this
   *
  **/
  public  function init( $version='1.0', $encoding='UTF-8', $formatOutput=true )
  {
    $this->xml = new DOMDocument( $version, $encoding );
    $this->xml->formatOutput = $formatOutput;
    $this->encoding = $encoding;
    return $this;
  }

  /*
   * Set top node name (must be called prior to array import)
   * @access public
   * @param1 string top node name
   * @return this
   *
  **/
  public function setTopNodeName($topNodeName)
  {
    if( ! $this->xml )
      $this->init();

    $this->topNodeName = $topNodeName;
    return $this;
  }

  /*
   * Parse an array to xml (entry point)
   * @param1 array to parse
   * @return this
   *
  **/
  public function importArray( $data=array() )
  {
    if( ! $this->xml || ! $this->topNodeName )
      throw new Exception('[Array2xml] Top node name not set');

    $this->xml->appendChild( $this->parse( $this->topNodeName, $data ));
    return $this;
  }

  
  public function setConvertFromEncoding($encoding = 'windows-1251')
  {
      $this->convertFromEncoding = $encoding;
  }
  

  private function convert($value)
  {
      return ($this->convertFromEncoding)?iconv($this->convertFromEncoding, $this->encoding, $value):$value; 
  }




  /*
   * Recursively parse an array to xml
   * Convert an Array to XML
   * @param string $topNodeName - name of the root node to be converted
   * @param array $data - aray to be converterd
   * @return DOMNode
  **/
  private function parse( $topNodeName, $data )
  {
    $node = $this->xml->createElement($topNodeName);

    if( is_array($data))
    {
      // Loop through attributes first
      if( isset($data['@attributes']))
      {
        foreach( $data['@attributes'] as $key=>$value )
        {
          if( ! $this->tagNameIsValid($key))
          {
            throw new Exception('[Array2xml] Illegal character in attribute name: attribute '.$key.' in node '.$topNodeName);
          }
          $node->setAttribute( $key, htmlspecialchars($this->bool2str($value), ENT_QUOTES, $this->encoding) );
        }
        unset($data['@attributes']);
      }
      // If a hard value is set in @value or @cdata, set value and escape (values can't contain child nodes)
      if( isset($data['@value']))
      {
        $node->appendChild( $this->xml->createTextNode(htmlspecialchars( $this->bool2str($data['@value']), ENT_QUOTES, $this->encoding )));
        return $node;
      }
      else if( isset($data['@cdata']))
      {
        $node->appendChild( $this->xml->createCDATASection($this->bool2str($data['@cdata']) ));
        return $node;
      }

      // Not escaped, loop through child nodes
      foreach( $data as $key=>$value )
      {
        if( ! $this->tagNameIsValid($key))
        {
          throw new Exception('[Array2xml] Illegal character in tag name: tag '.$key.' in node '.$topNodeName);
        }
        // Loop through numerically indexed nodes
        if( is_array($value) && isset($value[0]) )
        {
          foreach( $value as $key2=>$value2 )
          {
            $node->appendChild( $this->parse( $key, $value2 ));
          }
        }
        else
          // Only one node of its kind
          $node->appendChild( $this->parse($key, $value));
      }
    }
    // If not an array, check for a text value to append
    else
      $node->appendChild( $this->xml->createTextNode(htmlspecialchars( $this->bool2str($data), ENT_QUOTES, $this->encoding )));

    return $node;
  }

  /*
   * Get string representation of a boolean value
   * @access private
   * @param1 mixed value
   * @return string representation of bool or original value if not bool
   *
  **/
  private function bool2str($v)
  {
    return $v === true || $v === false ? (bool)$v : $this->convert($v);
  }

  /*
   * Check whether tag or attribute name contains illegal characters
   * @access private
   * @param1 string tag name
   * @return bool is valid tag name
   * @reference http://www.w3.org/TR/xml/#sec-common-syn
   *
  **/
  private function tagNameIsValid($tag)
  {
    return preg_match( '/^[a-z_]+[a-z0-9\:\-\.\_]*[^:]*$/i', $tag, $matches ) && $matches[0] == $tag;
  }

  /*
   * Flatten DOMDocument element to xml string
   * @access public
   * @return string xml
   * @note magic method __tostring() is a shortcut to this method
   *
  **/
  public function saveXml()
  {
    return $this->xml->saveXML();
  }

  public function __tostring() { return $this->saveXml(); }
}