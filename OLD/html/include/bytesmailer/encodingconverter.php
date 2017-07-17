<?php
// $Id: encodingconverter.php,v 1.3 2003/06/23 16:55:45 haruki Exp $
/**
 * bytesmailer - enhanced PHP email transport class.
 *
 * This class can handle non ascii charactors properly.
 *
 * @license LGPL, see LICENSE
 * @author Haruki Setoyama <haruki@planewave.org>
 * @package bytesmailer
 */
/**
* encodingConverterFactory
*
* @access public
*/
class encodingConverterFactory
{
    function get($from, $to)
    {
        if($from == $to) return new encodingConverter;

        if(encodingConverterFactory::mb_acceptable($from, $to))
        {
            return new encodingConverter_mb($from, $to);
        }

        $mdl = str_replace('-', '', strtolower($from)).'_'
                    .str_replace('-', '', strtolower($to));

        $file = _BYTESMAILER_DIR.'converter/'.$mdl.'.php';
        @include_once $file;
        $class = 'encodingConverter_'.$mdl;
        if(class_exists($class))
        {
            return new $class;
        }

        trigger_error(
            'encodingConverterFactory::get()::invalid encoding ('.$from.' -> '.$to.')',
            E_USER_WARNING
        );
        return false;
    }

    function mb_acceptable($from, $to)
    {
        if(! _BYTESMAILER_USE_MB_FUNCTION) return false;
        if(! function_exists('mb_convert_encoding')) return false;
        return @mb_convert_encoding('1', $to, $from);
    }
}

/**
* encodingConverter base class (null class)
*
* @access public
*/
class encodingConverter
{
    function convert(&$text)
    {
        return $text;
    }
}

/**
* encodingConverter_mb: ues mb_functions
*
* @access public
*/
class encodingConverter_mb extends encodingConverter
{
    var $from;
    var $to;

    function encodingConverter_mb($from, $to)
    {
        $this->from = $from;
        $this->to = $to;
    }

    function convert(&$text)
    {
//echo 'to '.$this->to.' from '.$this->from.'<br>';
        return mb_convert_encoding($text, $this->to, $this->from);
    }
}


?>