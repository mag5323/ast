<?php
// $Id: textspliter.php,v 1.3 2003/06/24 15:35:32 haruki Exp $
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
* textSpliterFactory
*
* @access public
*/
class textSpliterFactory
{
    function &get($encoding, $multibyte)
    {
        static $spliter;

        $name = str_replace('-', '', strtolower($encoding));

        if(! isset($spliter[$name]))
        {
            if($multibyte != true && ini_get('mbstring.func_overload') == 0)
            //if($multibyte != true)
            {
                $spliter[$name] = new textSpliter_sb;
            }
            elseif(class_exists($class = 'textSpliter_'.$name))
            {
                $spliter[$name] = new $class;
            }
            elseif(file_exists(_BYTESMAILER_DIR.'spliter/'.$name.'.php'))
            {
                include_once _BYTESMAILER_DIR.'spliter/'.$name.'.php';
                $spliter[$name] = new $class;
            }
            elseif(textSpliterFactory::_mb_acceptable($encoding))
            {
                $spliter[$name] = new textSpliter_mb($encoding);
            }
            else
            {
                trigger_error(
                    'textSpliterFactory::unsupported ecording ('.$encoding.')',
                    E_USER_NOTICE
                );
                $spliter[$name] = new textSpliter;
            }
        }

        return $spliter[$name];
    }

    function _mb_acceptable($encoding)
    {
        if(! _BYTESMAILER_USE_MB_FUNCTION) return false;
        if(! function_exists(mb_convert_encoding) || !function_exists(mb_substr))
            return false;
        $text = mb_convert_encoding('12',  $encoding, 'UTF-8');
        return @mb_substr($text, 0, 1, $encoding);
    }
}

/**
* textSpliter base class (and Null class)
*
* @access public
*/
class textSpliter
{
    /**
     * split $string text
     *
     * splits $string into $length byte parts.
     * @access public
     * @return array
     */
    function split($string, $length, $first_part_length=0)
    {
        return array($string);
    }
}

/**
* textSpliter for single bytes encoding
*
* @access public
*/
class textSpliter_sb extends textSpliter
{
    function split($string, $length, $first_part_length=0)
    {
        // this function can not split multibyte text
        $word_length = strlen($string);

        $st=0;
        $ret = array();

        // first line
        if($first_part_length > 0)
        {
            if($word_length <= $first_part_length)
            {
                return array($string);
            }
            else
            {
                $ret[] = substr($string, $st, $first_part_length);
                $st += $first_part_length;
            }
        }

        // after the first line
        while ($word_length - $st >= $length)
        {
            $ret[] = substr($string, $st, $length);
            $st += $length;
        }
        // left
        if($st < $word_length)
            $ret[] = substr($string, $st);

        return $ret;
    }
}

/**
 * textSpliter_mb
 * uses mb functions
 */
class textSpliter_mb extends textSpliter
{
    var $encoding;
    var $func_overload;

    function textSpliter_mb($encoding)
    {
        $this->encoding = $encoding;
    }

    function split($string, $length, $first_line_length=0)
    {
        $ret = array();

        if($first_line_length > 0)
            $ret[] = $this->_left_text($string, $first_line_length);

        while($string != '')
        {
            $ret[] = $this->_left_text($string, $length);
        }

        return $ret;
    }

    function _bytelen(&$str)
    {
        return count( unpack('C*', $str) );
    }

    function _left_text(&$str, $length)
    {
        $size = $this->_bytelen($str);
        if($size <= $length)
        {
            $ret = $str;
            $str = '';
        }
        else
        {
    
            $st = floor($length/2);
            while(abs($diff = ($this->_bytelen(mb_substr($str, 0, $st, $this->encoding))-$length))>4)
            {
                $st -= floor($diff/2);
            }

            if($diff > 0)
            {
                $st--;
                while($this->_bytelen(mb_substr($str, 0, $st, $this->encoding)) > $length)
                {
                    $st--;
                }
    
                $ret = mb_substr($str, 0, $st, $this->encoding);
                $str = mb_substr($str, $st, $size-$st, $this->encoding);
            }
            elseif($diff < 0)
            {
                $st++;
                while($this->_bytelen(mb_substr($str, 0, $st, $this->encoding)) < $width)
                {
                    $st++;
                }
                $st--;
            }
    
            $ret = mb_substr($str, 0, $st, $this->encoding);
            $str = mb_substr($str, $st, $size-$st, $this->encoding);
        }
        return $ret;
    }
}

/**
* textSpliter for UTF-8
*
* @access public
*/
class textSpliter_utf8 extends textSpliter
{
    function split($string, $length, $first_part_length=0)
    {
        $b = unpack('C*', $string);
        $n = count($b);

        $ret = array();
        $lines = 0;
        $line_byte = 0;
        if($first_part_length > 0)
            $max_length = $first_part_length;
        else
            $max_length = $length;

        $st = 1;
        $char_byte = 0;
        while($st <= $n)
        {
            if($char_byte > 0)
            {
                if($b[$st] > 0xBF || 0x80 > $b[$st])
                {
                    // this is not UTF-8
                    trigger_error('textSpliter_utf8::split()::not UTF-8', E_USER_NOTICE);
                    return array($string);
                }
            }
            else
            {
                if(0x00 <= $b[$st] && $b[$st] <= 0x7F)
                {
                    $char_byte = 1;
                }
                elseif(0xC0 <= $b[$st] && $b[$st] <= 0xDF)
                {
                    $char_byte = 2;
                }
                elseif(0xE0 <= $b[$st] && $b[$st] <= 0xEF)
                {
                    $char_byte = 3;
                }
                elseif(0xF0 <= $b[$st] && $b[$st] <= 0xF7)
                {
                    $char_byte = 4;
                }
                elseif(0xF8 <= $b[$st] && $b[$st] <= 0xFB)
                {
                    $char_byte = 5;
                }
                elseif(0xFC <= $b[$st] && $b[$st] <= 0xFD)
                {
                    $char_byte = 6;
                }
                else
                {
                    // this is not UTF-8
                    trigger_error('textSpliter_utf8::split()::not UTF-8', E_USER_NOTICE);
                    return array($string);
                }

                if($line_byte + $char_byte > $max_length)
                {
                    $max_length = $length;
                    $lines++;
                    $ret[$lines] = '';
                    $line_byte = 0;
                }
            }

            $ret[$lines] .= chr($b[$st]);
            $line_byte++;
            $char_byte--;
            $st++;
        }

        return $ret;
    }
}

?>