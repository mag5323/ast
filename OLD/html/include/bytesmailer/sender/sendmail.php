<?php
// $Id: sendmail.php,v 1.2 2003/06/23 16:55:46 haruki Exp $
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
 * Sends mail using the $Sendmail program.
 * This is based on phpmailer's.
 * @access public
 * @return bool
 */
class mailSender_sendmail extends mailSender{

    /**
     * Sets the path of the sendmail program.
     * @access public
     * @var string
     */
    var $sendmail          = '/usr/sbin/sendmail';

    function mailSender_sendmail($sendmail_path ='')
    {
        if($sendmail_path != '') $this->sendmail = $sendmail_path;
    }

    /**
     * Sends mail using the $Sendmail program.
     * @access public
     * @return bool
     */
    function send($obj_header, $obj_body)
    {
        $header = $obj_header->getHeader();

        if(! $this->_recipient_exists($header)) return false;

        if (! empty($header['Return-Path']))
            $sendmail = sprintf("%s -oi -f %s -t", $this->sendmail, substr($header['Return-Path'],1,strlen($header['Return-Path'])-2));
        else
            $sendmail = sprintf("%s -oi -t", $this->sendmail);

        if(! (@$mail = popen($sendmail, 'w'))){
            $this->_trigger_error(sprintf("Could not open pipe to %s", $this->sendmail));
            return false;
        }

        $data = $this->_make_header_string($header).$obj_body->getBoth();

        fputs($mail, $data, strlen($data));

        $result = pclose($mail) >> 8 & 0xFF;
        if($result != 0){
            $this->_trigger_error(sprintf("Could not close pipe to %s", $this->sendmail));
            return false;
        }

        return true;
    }
}

?>