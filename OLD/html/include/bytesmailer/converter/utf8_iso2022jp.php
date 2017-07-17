<?php
// $Id: utf8_iso2022jp.php,v 1.2 2003/06/22 16:19:03 haruki Exp $
/**
 * bytesmailer - enhanced PHP email transport class.
 *
 * This class can handle non ascii charactors properly.
 *
 * @license LGPL, see LICENSE
 * @author Haruki Setoyama <haruki@planewave.org>
 *
*/
/**
* encodingConverter_utf8_iso2022jp
*
* @access public
*/
include_once 'class.jcode.php';

class encodingConverter_utf8_iso2022jp extends encodingConverter
{
    var $converter;

    function encodingConverter_utf8_iso2022jp()
    {
        $this->converter = new jcode_utf8_jis;
    }

    function convert($text)
    {
        return $this->converter->convert($text);
    }
}

?>