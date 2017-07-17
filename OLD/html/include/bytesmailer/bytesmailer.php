<?php
// $Id: bytesmailer.php,v 1.5 2003/06/24 15:35:32 haruki Exp $
//
// bytesmailer - enhanced PHP email transport class
//  that can handle non ascii charactors properly.
// Copyright (C) Haruki Setoyama <haruki@planewave.org>
//
// This library is free software; you can redistribute it and/or
// modify it under the terms of the GNU Lesser General Public
// License as published by the Free Software Foundation; either
// version 2.1 of the License, or any later version.
//
// This library is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
// Lesser General Public License for more details.
//
// You should have received a copy of the GNU Lesser General Public
// License along with this library; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
//
/**
 * bytesmailer - enhanced PHP email transport class.
 *
 * This class can handle non ascii charactors properly.
 * Partly based on phpmailer 1.62 (author: Brent R. Matzelle) http://phpmailer.sourceforge.net/,
 * and other open source scripts.
 * See comments.
 *
 * @license LGPL, see LICENSE
 * @author Haruki Setoyama <haruki@planewave.org>
 * @package bytesmailer
 * @version 0.2
 */
 /**
 * define _BYTESMAILER_VERSION.
 */
define('_BYTESMAILER_VERSION', '0.2');
/**
 * bytesmailer - enhanced PHP email transport class.
 * @access public
 */
class bytesmailer extends bytesmailerLeader
{

    /**
     * mail header and body class
     * @access public
     */
    var $header;
    var $body;

    /**
     * method to send the mail
     * @access public
     */
    var $sender = null;

    /**
     * object of main message part. multipart/altanative.
     * @access public
     */
    var $message;

    /**
     * constructor
     * @access public
     */
    function bytesmailer()
    {
        $this->bytesmailerLeader();

        $this->header = new mailheader($this);
        $this->body = new mailBody_multipart($this);
    }

    /**
     * transfar mail.
     * @access public
     */
    function send()
    {
        if(! isset($this->sender)) $this->sender = new mailSender_mail;

        return $this->sender->send($this->header, $this->body);
    }

    /**
     * set the way to send mail.
     * use like bytesmailer::setSendMethod('smtp', 'localhost');
     * befor you get instance of this class
     * @access public
     */
    function setSendMethod($type, $param='')
    {
        $type = strtolower($type);
        $class_name = 'mailSender_'.$type;
        if(! class_exists($class_name))
        {
            include _BYTESMAILER_DIR.'sender/'.$type.'.php';
            if(! class_exists($class_name))
            {
                $this->_trigger_error('invalid SendMethod. '.$type);
                return false;
            }
        }
        $this->sender = new $class_name($param);
        return true;
    }

    ///// setter

    /**
     * add mail address to 'to' 'cc' etc.
     * @access public
     * @return object   address class. see class.address.php
     */
    function addAddr($addrspec, $displayname='', $type='to')
    {
        if(is_a($this->header->$type, 'address'))
            return $this->header->$type->addAddr($addrspec, $displayname);
        else
            return false;
    }

    /**
     * add mail group to 'to' 'cc' etc.
     * @access public
     * @return object   group class. see class.address.php
     */
    function &addGroup($name, $type='to')
    {
        if(is_a($this->header->$type, 'addresslist'))
        {
            $group = new addressgroup($this);
            $group->setName($name);
            $this->header->$type->add($group);
            return $group;
        }
        else
            return false;
    }

    /**
     * set subject string
     * @access public
     * @return object  bm_text_mail class.
     */
    function setSubject($text)
    {
        $this->header->subject->set($text);
    }

    /**
     * add plane text mail message
     * @access public
     */
    function &addTextMessage($text, $subtype ='plain')
    {
        if(empty($this->message))
        {
            $this->message = new mailBody_multipart($this);
            $this->message->setContentSubType('alternative');
            $this->body->add($this->message);
        }
        $msg = new mailBody_singlepart($this);
        $msg->setData($text, 'text', $subtype);
        if(! $this->message->add($msg))
            return false;
        else
            return $msg;
    }

    /**
     * add attachment
     * @access public
     */
    function &addAttachment($data, $filename, $type, $subtype)
    {
        $obj = new mailBody_singlepart($this);
        $obj->setData($data, $type, $subtype);
        $obj->beAttachment($filename);
        if(! $this->body->add($obj))
            return false;
        else
            return $obj;
    }

    /**
     * add attachment from file
     * @access public
     */
    function &addAttachmentFromFile($path, $filename='')
    {
        $obj = new mailBody_singlepart($this);
        $ret = $obj->setDataFromFile($path);
        if(! $ret) return false;
        if($filename == '') $filename = basename($path);
        $obj->beAttachment($filename);
        if(! $this->body->add($obj))
            return false;
        else
            return $obj;
    }

    /**
     * add mail content
     * @access public
     */
    function &addContent($data, $type, $subtype)
    {
        $obj = new mailBody_singlepart($this);
        $obj->setData($data, $type, $subtype);
        if(! $this->body->add($obj))
            return false;
        else
            return $obj;
    }


}

///////////////////////////////////////////////////////////

/*
* base class of  mail transfar class.
* @access private
*/
class mailSender extends bytesmailerFellow
{
    function send($obj_header, $obj_body) {}

    function _make_header_string($header)
    {
        $str = '';
        foreach($header as $key => $val)
        {
            if(! is_array($val))
                $str .= wordwrap($key.': '.$val, 78, _BYTESMAILER_FWS)._BYTESMAILER_LE;
        }
        return $str;
    }

    function _recipient_exists(&$header)
    {
        if(!isset($header['To']) && !isset($header['Cc']) && !isset($header['Bcc']))
        {
            $this->_trigger_error('No recipient email address.', E_USER_WARNING);
            return false;
        }
        return true;
    }

    function _split_header_body($text)
    {
        $bline = strpos($text, _BYTESMAILER_LE._BYTESMAILER_LE);
        $header = substr($text, 0, $bline);
        $body = substr($text, $bline+2*strlen(_BYTESMAILER_LE));
        return array($header, $body);
    }
}
/**
 * Sends mail using the PHP mail() function.
 * This is based on phpmailer's.
 * @access public
 * @return bool
 */
class mailSender_mail extends mailSender
{
    function mailSender_mail($param ='')
    {
    }

    function send($obj_header, $obj_body)
    {
        $header = $obj_header->getHeader();
        if(! $this->_recipient_exists($header)) return false;

        $to = implode(',', $obj_header->to->getAddrspec());

        if (isset($header['To'])) unset($header['To']);

        $subject = '';
        if (isset($header['Subject']))
        {
            $subject = str_replace(_BYTESMAILER_LE, '', $header['Subject']);
            unset($header['Subject']);
        }

        if (isset($header['Return-Path']))
        {
            $old_from = ini_get('sendmail_from');
            ini_set(
                'sendmail_from',
                substr($header['Return-Path'],1,strlen($header['Return-Path'])-2)
            );
            unset($header['Return-Path']);
        }

        if (isset($header['Received'])) unset($header['Received']);

        $msg = $this->_make_header_string($header);
        $msg .= $obj_body->getBoth();
        list($headerstr, $body) = $this->_split_header_body($msg);

        $rt = @mail($to, $subject, $body, $headerstr);

        if(isset($old_from)) ini_set('sendmail_from', $old_from);

        if(! $rt)
        {
            $this->_trigger_error(
                'Could not instantiate mail() function.',
                E_USER_WARNING
            );
            return false;
        }

        return true;
    }

}
?>