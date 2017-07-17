<?php
// $Id: mailbody.php,v 1.4 2003/06/24 15:35:32 haruki Exp $
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
 * data handling classes for mail body.
 */
class mailBody_abstract extends bytesmailerFellow
{
    function mailBody_abstract(&$leader)
    {
        $this->bytesmailerFellow($leader);
    }

    /**
     * return headers by array.
     * ex. $arr['Content-Type'] = 'text/plane; charset=us-ascii'
     *
     * @access public
     * @return array
     */
    function getHeader()
    {
        return array();
    }

    /**
     * return body of the mail content.
     *
     * @access public
     * @return string
     */
    function getBody()
    {
        return '';
    }

    /**
     * return formated messages.
     *
     * @access public
     * @return string
     */
    function getBoth()
    {
        $body = $this->getBody();
        $header = '';
        foreach($this->getHeader() as $key => $val)
        {
            $header .= wordwrap($key.': '.$val._BYTESMAILER_LE, 78);
        }
        if($header != '')
            $header .= _BYTESMAILER_LE;

        return $header.$body;
    }
}

/**
 * content of mail. All 'Content-Type' except 'multipart' is allowed.
 *
 * @access public
 */
class mailBody_singlepart extends mailBody_abstract
{
    var $data;

    /*
    encoding := "Content-Transfer-Encoding" ":" mechanism
    mechanism := "7bit" / "8bit" / "binary" / "quoted-printable" / "base64" / ietf-token / x-token
    */
    var $transfer_encoding      = '';

    /*
    content := "Content-Type" ":" type "/" subtype *(";" parameter)
        ; Matching of media type and subtype
        ; is ALWAYS case-insensitive.
    type := discrete-type / composite-type
    discrete-type := "text" / "image" / "audio" / "video" / "application" / extension-token
    composite-type := "message" / "multipart" / extension-token
    */
    var $content_type           = '';
    var $content_subtype        = '';

    /*
    disposition-parm := filename-parm
        / creation-date-parm
        / modification-date-parm
        / read-date-parm
        / size-parm
        / parameter
    filename-parm := "filename" "=" value
    creation-date-parm := "creation-date" "=" quoted-date-time
    modification-date-parm := "modification-date" "=" quoted-date-time
    read-date-parm := "read-date" "=" quoted-date-time
    size-parm := "size" "=" 1*DIGIT
    quoted-date-time := quoted-string
        ; contents MUST be an RFC 822 `date-time'
        ; numeric timezones (+HHMM or -HHMM) MUST be used

    */
    var $content_type_param      = array();

    var $content_language       = '';

    /*
    disposition := "Content-Disposition" ":" disposition-type *(";" disposition-parm)
    disposition-type := "inline" / "attachment" / extension-token
        ; values are not case-sensitive
    */
    var $disposition_type       = '';
    var $disposition_type_param  = array();

    var $content_id             = '';
    var $custom                 = array();

    var $domain = 'bytesmailer';


    function mailBody_singlepart(&$leader)
    {
        $this->mailBody_abstract($leader);
    }

    function getHeader()
    {
        $header = array();
        if($this->content_type != '' && $this->content_subtype != '')
        {
            $header['Content-Type'] = $this->content_type.'/'.$this->content_subtype;
            $header['Content-Type'] .= $this->_get_param_string($this->content_type_param);
        }

        if(! empty($this->content_language))
        {
            $header['Content-Language'] = $this->content_language;
        }

        if($this->transfer_encoding != '')
        {
            $header['Content-Transfer-Encoding'] .= $this->transfer_encoding;
        }

        if($this->disposition_type != '')
        {
            $header['Content-Disposition'] .= $this->disposition_type;
            $header['Content-Disposition'] .= $this->_get_param_string($this->disposition_type_param);
        }

        if($this->content_id != '')
        {
            $header['Content-ID'] .= '<'.$this->content_id.'>';
        }

        array_merge($header, $this->custom);

        return $header;
    }

    function _get_param_string(&$params)
    {
        $ret = '';
        foreach($params as $param_name => $text)
        {
            $ret .= ';'._BYTESMAILER_FWS;
            $ret .= $this->_textEncoder->paramText($text, $param_name);
        }
        return $ret;
    }

    function getBody()
    {
        $body = $this->data;
        if($body != '' && substr($body, -strlen(_BYTESMAILER_LE)) != _BYTESMAILER_LE)
        {
            $body .= _BYTESMAILER_LE;
        }
        return $body;
    }

    /////////////////////////////////////////

    function setData($data, $type, $subtype)
    {
        if($type == 'text')
        {
            $body = $this->_textEncoder->bodyText($data);
            $this->data = $body['body'];
            $this->transfer_encoding = $body['type'];
    
            if(! empty($body['encoding']))
                $this->content_type_param['charset'] = $body['encoding'];
            if(! empty($body['language']))
                $this->content_language = $body['language'];
        }
        else
        {
            $this->data = chunk_split(base64_encode($data), 76, _BYTESMAILER_LE);
            $this->transfer_encoding = 'base64';

            //$body = mailBinary_factory::create($data);
        }

        $this->content_type = $type;
        $this->content_subtype = $subtype;

        return true;
    }

    function setDataFromFile($path)
    {
        $fd = fopen($path, 'r');
        if($fd === false)
        {
            $this->_trigger_error(sprintf("Could not open file [%s]", $path));
            return false;
        }
        $data = fread($fd, filesize($path));
        fclose($fd);

        include_once _BYTESMAILER_DIR.'filemimetype.php';
        list($type, $subtype) = fileMimeType::get($path);

        return $this->setData($data, $type, $subtype);
    }

    function getContentType()
    {
        return array($this->content_type, $this->content_subtype);
    }

    function addContentTypeParam($name, $text)
    {
        $this->content_type_param[trim($name)] = $text;
    }

    function setDispositionType($text)
    {
        $this->disposition_type = trim($text);
    }

    function addDispositionTypeParam($name, $text)
    {
        $this->disposition_type_param[trim($name)] = $text;
    }

    function setContentId($id)
    {
        $this->content_id = $id;
    }

    function addCustomHeader($name, $value)
    {
        $this->custom = array_merge($this->custom, array(trim($name), $value));
    }

    /////////////////////////////////////////

    function beAttachment($filename)
    {
        $this->setDispositionType('attachment');
        $this->addContentTypeParam('name', $filename);
        $this->addDispositionTypeParam('filename', $filename);
    }

    function beInline($id)
    {
        $this->setDispositionType('inline');
        $this->content_id = $id;
    }

}

/**
 * class for multipart mail content.
 *
 * @access public
 */
class mailBody_multipart extends mailBody_abstract
{

    var $boundary;
    var $type = 'multipart';
    var $subtype = 'mixed';

    var $contents = array();

    var $msg = false;

    /**
     * Constructor
     */
    function mailBody_multipart(&$leader)
    {
        $this->mailBody_abstract($leader);
        $this->boundary = md5(uniqid(time()));
        //$this->_msg = $msg;
    }

    function add(&$obj)
    {
        if(is_a($obj, 'mailBody_abstract'))
        {
            $this->contents[] =& $obj;
            return true;
        }
        return false;
    }

    /**
     * set a subtype of the "multipart" Content-Type.
     *  mixed / alternative / parallel (/ digest)
     *  encoding -> 7bit, 8bit, binary only
     * @param sting $str
     */
    function setContentSubType($subtype)
    {
        $this->subtype = $subtype;
        return true;
    }

    /////

    function getHeader()
    {
        if( count($this->contents) >1 )
        {
            return
                array(
                    'Content-Type' =>
                    'multipart/'.$this->subtype.';'._BYTESMAILER_FWS.
                    'boundary="'.$this->boundary.'"'
                );
        }
        else
        {
            return array();
        }
    }

    function getBody()
    {
        switch(count($this->contents))
        {
        case 0:
            return '';
        case 1:
            return $this->contents[0]->getBoth();

        default:
            $body = '';
            if($this->msg != false)
                $body .= 'This is a multi-part message in MIME format.'
                            ._BYTESMAILER_LE._BYTESMAILER_LE;
            foreach($this->contents as $obj)
            {
                $body .= '--'.$this->boundary._BYTESMAILER_LE
                        .$obj->getBoth()._BYTESMAILER_LE;
            }
            $body .= '--'.$this->boundary.'--'._BYTESMAILER_LE;
            return $body;
        }
    }
}

?>