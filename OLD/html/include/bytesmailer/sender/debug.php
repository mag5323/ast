<?php
// $Id: debug.php,v 1.3 2003/06/24 15:35:32 haruki Exp $
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
 * Display mail message for debug.
 * @access public
 * @return bool
 */
class mailSender_debug extends mailSender
{
    function mailSender_debug($param ='')
    {
    }
    
    function send($obj_header, $obj_body)
    {
        $header = $obj_header->getHeader();
        $body = $obj_body->getBoth();

        echo '<h1>bytesmailer</h1>';

        echo '<h2>mail message</h2><pre>';
        echo htmlspecialchars($this->_make_header_string($header));
        //echo '-----------------------------------<br />';
        echo $body;
        echo '</pre>';

        return true;
    }
}

?>