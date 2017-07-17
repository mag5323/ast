<?php
// $Id: mailtextencoder.php,v 1.2 2003/06/24 15:35:32 haruki Exp $
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
 * text encoder for mail.
 * @access public
 */
class mailTextEncoder
{
    var $internal_encoding  = '';
    var $internal_multibyte = true;

    var $mail_encoding = '';
    var $mail_multibyte = true;

    var $language = '';

    var $converter = null;

    function mailTextEncoder()
    {

    }

    function setInternalEncoding($encoding, $multibyte =true)
    {
        $this->_known_encoding($encoding, $multibyte);

        $this->internal_encoding = $encoding;
        $this->internal_multibyte = $multibyte;
        if(empty($this->mail_encoding))
        {
            $this->mail_encoding = $encoding;
            $this->mail_multibyte = $multibyte;
            //$this->_load_spliter();
        }
        $converter = encodingConverterFactory::get($this->internal_encoding, $encoding);
        if($converter === false)
        {
            $this->mail_encoding = $encoding;
            $this->mail_multibyte = $multibyte;
            //$this->_load_spliter();
            $this->converter = new encodingConverter;
            return false;
        }
        $this->converter = $converter;
        return true;
    }

    function setMailEncoding($encoding, $multibyte =true)
    {
        $this->_known_encoding($encoding, $multibyte);

        if(empty($this->internal_encoding))
        {
            $this->internal_encoding = $encoding;
            $this->internal_multibyte = $multibyte;
        }
        $converter = encodingConverterFactory::get($this->internal_encoding, $encoding);
        if($converter === false) {
            return false;
        }

        $this->converter = $converter;
        $this->mail_encoding = $encoding;
        $this->mail_multibyte = $multibyte;

        //$this->_load_spliter();
        return true;
    }

    function _known_encoding(&$encoding, &$multibyte)
    {
        if(preg_match('/^ISO-8859-[0-9]+$/i', $encoding))
            $multibyte = false;
        if(preg_match('/^US-ASCII$/i', $encoding))
            $multibyte = false;

        if(preg_match('/^ISO-2022-/i', $encoding))
            $multibyte = true;
        if(preg_match('/^EUC-/i', $encoding))
            $multibyte = true;
        if(preg_match('/^UTF-/i', $encoding))
            $multibyte = true;
    }

    function setLanguage($lang)
    {
        $this->language = $lang;
    }

    function defaultEncoding()
    {
        $this->setMailEncoding(
            _BYTESMAILER_DEFAULT_ENCODING,
            _BYTESMAILER_DEFAULT_MULTIBYTE
        );
    }

    ///// for BODY

    function bodyText($text)
    {
        if(empty($this->mail_encoding)) $this->defaultEncoding();

        $ret = array();

        if(is_string($text))
        {
            $text = $this->converter->convert($text);
            $ret['encoding'] = $this->mail_encoding;
            $ret['language'] = $this->language;
            $multibyte = $this->mail_multibyte;
        }
        if(is_array($text))
        {
            $ret['encoding'] = $text['encoding'];
            $ret['language'] = $text['language'];
            $multibyte = (isset($text['multibyte'])) ? $text['multibyte'] : true;
            $text = $text['text'];
        }


        if(preg_match('/[\x80-\xFF]/', $text))
        {
            if(! $multibyte)
            {
                $ret['type'] = 'quoted-printable';
                $ret['body'] = $this->_qp_encode_for_body($text);
            }
            else
            {
                $ret['type'] = 'base64';
                $ret['body'] = chunk_split(base64_encode($text), 76, _BYTESMAILER_LE);
            }
        }
        else
        {
            $ret['type'] = '7bit';
            $ret['body'] = $text;
        }

        return $ret;
    }

    /**
     * quoted-printable
     * only for SINGLE BYTE ENCODE
     * @access private
     */
    function _qp_encode_for_body($text)
    {
        // Replace every high ascii, control and = characters
        $text =     preg_replace(
                        "/([\001-\010\013\014\016-\037\075\177-\377])/e",
                        "'='.sprintf('%02X', ord('\\1'))",
                        $text
                    );
        // !"#$@[\]^`{|}~
        if(_BYTESMAILER_QP_STRICT != false)
        {
            $text =     preg_replace(
                            "/([\041-\044\100\133-\140\173-\176])/e",
                            "'='.sprintf('%02X', ord('\\1'))",
                            $text
                        );
        }
        // Replace every spaces and tabs when it's the last character on a line
        $text =     preg_replace(
                        "/([\011\040])"._BYTESMAILER_LE."/e",
                        "'='.sprintf('%02X', ord('\\1'))._BYTESMAILER_LE",
                        $text
                    );
        // Maximum line length of 76 characters before CRLF (74 + space + '=')
        return wordwrap($text, 76, ' ='._BYTESMAILER_LE);
    }

    ///// for HEADER

    function headerText($text, $structured =true)
    {
        if(empty($this->mail_encoding)) $this->defaultEncoding();

        $text = $this->_fix_eol($text, ' ');

        if(is_string($text))
        {
            $encoding = $this->mail_encoding;
            $multibyte = $this->mail_multibyte;

            list($prefix, $text)
                = $this->_getAsciiPrefixAndLest($text, $structured);
            if($text == '')
                return $prefix;
            else
                $prefix .= ' ';

            $text = $this->converter->convert($text);
        }
        if(is_array($text))
        {
            $encoding = $text['encoding'];
            $multibyte = (isset($text['multibyte'])) ? $text['multibyte'] : true;
            $text = $text['text'];
            $prefix = '';
        }

        if(! $multibyte)
        {
            $encoded = $this->_qp_encode_for_header($text, $encoding, $multibyte);
        }
        else
        {
            $encoded = $this->_base64_encode_for_header($text, $encoding, $multibyte);
        }

        return $prefix.$encoded;
    }

    function _base64_encode_for_header($text, $characterEncoding, $multibyte)
    {
        /* mb_encode_mimeheader() seems have some bug.
        if( _BYTESMAILER_USE_MB_FUNCTION
            && function_exists('mb_encode_mimeheader'))
        {
            $encoded = mb_encode_mimeheader($text, $characterEncoding, 'B', _BYTESMAILER_LE);
            if($encoded !== false) return $encoded;
        }*/

        $max_length = floor((75-8-strlen($characterEncoding))/4)*3;

        if(strlen($text) > $max_length)
        {
            $spliter =& textSpliterFactory::get($characterEncoding, $multibyte);
            $lines = $spliter->split($text, $max_length);
            $encoded_line = array();
            foreach($lines as $line)
                $encoded_line[] = '=?'.$characterEncoding.'?B?'.base64_encode($line).'?=';
            return implode(_BYTESMAILER_FWS, $encoded_line);
        }

        return '=?'.$characterEncoding.'?B?'.base64_encode($text).'?=';

    }

    function _qp_encode_for_header($text, $characterEncoding, $multibyte)
    {
        /* mb_encode_mimeheader() seems have some bug.
        if( _BYTESMAILER_USE_MB_FUNCTION
            && function_exists('mb_encode_mimeheader'))
        {
            $encoded = mb_encode_mimeheader($text, $characterEncoding, 'Q', _BYTESMAILER_LE);
            if($encoded !== false) return $encoded;
        }*/

        $parts = array();
        if(_BYTESMAILER_QP_STRICT != false)
        {   // below plus !"#$@[\]^`{|}~
            $chars = '\001-\011\013\014\016-\044\075\077\100\133-\140\173-\377';
        }
        else
        {   // high ascii, control , spaces, tabs and = ? _ characters
            $chars = '\001-\011\013\014\016-\040\075\077\137\177-\377';
        }
        while($text != '')
        {

            if(preg_match('/^([^'.$chars.']*)(['.$chars.'])(.*)$/', $text, $match))
            {
                if($match[1] != '')
                    $parts[] = $match[1];
                $parts[] = sprintf('=%02X', ord($match[2]));
                $text = $match[3];
            }
            else
            {
                $parts[] = $text;
                $text = '';
            }

        }

        $max_length = 75 - 8 - strlen($characterEncoding) - 3;
        $length = 0;
        $line = 0;
        $encoded = array();
        foreach($parts as $part)
        {
            if(($length + strlen($part)) <= $max_length)
            {
                $encoded[$line] .= $part;
                $length = $length + strlen($part);
            }
            else
            {
                $line++;
                $encoded[$line] = $part;
                $length = strlen($part);
            }
        }

        $ret = '=?'.$characterEncoding.'?Q?'
                .implode('?='._BYTESMAILER_FWS.'=?'.$characterEncoding.'?Q?', $encoded )
                .'?=';

        return $ret;
    }

    ///// for PARAMETER

    function paramText($text, $param_name)
    {
        if(empty($this->mail_encoding)) $this->defaultEncoding();

        $encoded = $this->headerText($text, false);

        // "(" / ")" / "<" / ">" / "@" / "," / ";" / ":" / "\" / <"> / "/" / "[" / "]" / "?" / "="
        // \x28\x29\x3C\x3E\x40\x2C\x3B\x3A\x5C\x22\x2F\x5B\x5D\x3F\x3D
        $tspecials = '/[\x22\x28\x29\x2C\x2F\x3A-\x3F\x40\x5B-\x5D]/';
        if(preg_match($tspecials, $encoded))
            $encoded = '"'.str_replace(array('"','\\'), array('\"', '\\\\'), $encoded).'"'; // "

        return $param_name.'='.$encoded;
    }


    /////

    function create($data)
    {
        if(! isset($this->mail_encoding)) $this->defaultEncoding();

        if(mailText_mb::acceptable($this->mail_encoding))
        {
            $obj =& new mailText_mb;
            if(! $this->mail_multibyte) $obj->type = 'Q';
        }

        if($this->mail_multibyte)
        {
            $obj =& new mailText_mb_native;
        }
        else
        {
            $obj =& new mailText_sb;
        }


        //$obj->spliter = $this->spliter;
        $obj->characterEncoding = $this->mail_encoding;
        $obj->multibyte = $this->mail_multibyte;
        $obj->language = $this->language;
        $obj->data = $this->converter->convert($this->_fix_eol($data));
        return $obj;
    }

    function createFromFile($path)
    {
        $fp = fopen($path, 'rb');
        if(! $fp) return new mailText;
        $obj = $this->create(fread($fp, filesize($path)));
        $fclose($fp);
        return $obj;
    }

    /**
     * Changes every end of line CR or LF or CRLF to $eol.
     * _BYTESMAILER_LE
     * @access private
     */
    function _fix_eol($text, $eol =_BYTESMAILER_LE)
    {
    /* TODO: which is better?
        return  str_replace(
                    array("\r\n", "\r", "\n"),
                    array($eol, $eol, $eol),
                    $text
                );
    */
        $text = str_replace("\r\n", "\n", $text);
        $text = str_replace("\r", "\n", $text);
        return str_replace("\n", $eol, $text);
    }

////////////////////

    function _notAtext($text)
    {
        // for structured header text
        // atext A-Za-z0-9!#$%&'*+-/=?^_`{|}~
        return preg_match('/[^\043-\047\052\053\055\057\060-\071\075\077\101-\132\136-\176]/',$text);
    }

    function _getAsciiPrefixAndLest($lest, $structured =true)
    {
        if(preg_match('/^([\040-\176]*)$/s', $lest, $matches))
        {
            $prefix = $matches[1];
            $lest = '';
        }
        elseif(preg_match('/^([\040-\176]+)\040(.*)$/s', $lest, $matches))
        {
            $prefix = $matches[1];
            $lest = $matches[2];
        }

        if($prefix != '')
        {
            if($structured
                && $this->_notAtext($prefix))
            {
                // not atext A-Za-z0-9!#$%&'*+-/=?^_`{|}~
                $prefix = '"'.str_replace(array('"','\\'), array('\"', '\\\\'), $prefix).'"'; // "
            }
        }

        return array($prefix, $lest);
    }
}



?>