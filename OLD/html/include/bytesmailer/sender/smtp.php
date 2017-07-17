<?php
// $Id: smtp.php,v 1.2 2003/06/23 16:55:46 haruki Exp $
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
 * Sends mail via SMTP using PhpSMTP (Author: Chris Ryan).
 * This is based on phpmailer's.
 * @access public
 * @return bool
 */
class mailSender_smtp extends mailSender
{
    /**
     *  Sets the SMTP hosts.
     *  format: [username:[pasword]@]hostname[:port]
     *  (e.g. "me:pass@smtp1.domain.com:25;smtp2.domain.com").
     *  Hosts will be tried in order.
     *  @access public
     *  @var string
     */
    var $host        = 'localhost';

    /**
     *  Sets the default SMTP server port.
     *  @access public
     *  @var int
     */
    var $port        = 25;

    /**
     *  Sets the SMTP HELO of the message.
     *  @access public
     *  @var string
     */
    var $helo        = 'localhost.localdomain';


    /**
     *  Sets the SMTP server timeout in seconds. This function will not
     *  work with the win32 version.
     *  @access public
     *  @var int
     */
    var $timeout      = 10;

    /**
     *  Sets SMTP class debugging on or off.
     *  @access public
     *  @var bool
     */
    var $debug;

    var $smtp;

    function mailSender_smtp($host ='localhost')
    {
        if($host == '') $host='localhost';
        $this->debug = _BYTESMAILER_DEBUG;
        $this->host = $host;
    }

    /**
     * Sends mail via SMTP using PhpSMTP (Author:
     * Chris Ryan).  Returns bool.  Returns false if there is a
     * bad MAIL FROM, RCPT, or DATA input.
     * @access public
     * @return bool
     */
    function send($obj_header, $obj_body)
    {
        $header = $obj_header->getHeader();

        if(! $this->_recipient_exists($header)) return false;

        if(! is_array($this->host)) $this->host = array($this->host);
        foreach($this->host as $val)
        {
            if(preg_match('/^(?:([^:@]+)(?::([^:@]+))?@)?([^:@]+)(?::([0-9]+))?$/', $val, $match))
            {
                if($match[4] == '' ) $match[4] = $this->port;
                $hosts[] =  array(
                                'host' => $match[3],
                                'port' => intval($match[4]),
                                'user' => $match[1],
                                'pass' => $match[2]
                            );
            }
        }

        $smtp = new SMTP;
        $smtp->do_debug = $this->debug;

        // Try to connect to all SMTP servers
        //$hosts = explode(';', $this->host);
        $index = 0;
        $connection = false;

        // Retry while there is no connection
        while($index < count($hosts) && $connection == false){
            if($smtp->Connect($hosts[$index]['host'], $hosts[$index]['port'], $this->timeout))
                $connection = true;
            //printf("%s host could not connect<br>", $hosts[$index]); //debug only
            $index++;
        }
        if(! $connection){
            $this->_trigger_error('SMTP Error: could not connect to SMTP host server(s).');
            return false;
        }
        // Must perform HELO before authentication
        $smtp->Hello($this->helo);

        // If user requests SMTP authentication
        if($hosts[$index]['user'] != ''){
            if(!$smtp->Authenticate($hosts[$index]['user'], $hosts[$index]['pass'])){
                $this->_trigger_error('SMTP Error: Could not authenticate to %s:%s.', $hosts[$index]['host'], $hosts[$index]['port']);
                return false;
            }
        }

        if(! $smtp->Mail($header['Return-Path'])){
            $this->_trigger_error(sprintf('SMTP Error: From address [%s] failed', $header['Return-Path']));
            return false;
        }

        // Attempt to send mail all recipients
        $recipients = array_merge($obj_header->to->getAddrspec()
                                    ,$obj_header->cc->getAddrspec()
                                    ,$obj_header->bcc->getAddrspec());

        $bad_rcpt = array();
        foreach($recipients as $recipt){
            if(! $smtp->Recipient(sprintf('<%s>', $recipt)))
                $bad_rcpt[] = $recipt;
        }

        // Create error message
        if(count($bad_rcpt) > 0){
            $this->_trigger_error(sprintf('SMTP Error: The following recipients failed [%s].'
                            , implode(', ', $bad_rcpt)));
            return false;
        }

        if(isset($header['Bcc'])) $header['Bcc'] = '';
        if(isset($header['Return-Path'])) unset($header['Return-Path']);
        $data = $this->_make_header_string($header).$obj_body->getBoth();

        if(! $smtp->Data(sprintf('%s', $data))){
            $this->_trigger_error('SMTP Error: Data not accepted');
            return false;
        }
        $smtp->Quit();

        return true;
    }
}

/**
 * smtp class
 * modified by Haruki Setoyama <haruki@planewave.org>
 * for bytesmailer
 * @package bytesmailer
 * @version $Id: smtp.php,v 1.2 2003/06/23 16:55:46 haruki Exp $
 */
/*
 * File: smtp.php
 *
 * Description: Define an SMTP class that can be used to connect
 *              and communicate with any SMTP server. It implements
 *              all the SMTP functions defined in RFC821 except TURN.
 *
 * Creator: Chris Ryan <chris@greatbridge.com>
 * Created: 03/26/2001
 *
 */
/*
 * STMP is rfc 821 compliant and implements all the rfc 821 SMTP
 * commands except TURN which will always return a not implemented
 * error. SMTP also provides some utility methods for sending mail
 * to an SMTP server.
 */
class SMTP
{
    var $SMTP_PORT = 25; // the default SMTP PORT
    var $CRLF = "\r\n";  // CRLF pair

    var $smtp_conn;      // the socket to the server

    var $helo_rply;      // the reply the server sent to us for HELO

    var $error = array();          // error if any on the last call
    var $log = array();

    var $do_debug;       // the level of debug to perform

    /*
     * SMTP()
     *
     * Initialize the class so that the data is in a known state.
     */
    function SMTP()
    {
        $this->smtp_conn = 0;
        $this->helo_rply = null;
        $this->do_debug = 0;
    }

    /************************************************************
     *                    CONNECTION FUNCTIONS                  *
     ***********************************************************/

    /*
     * Connect($host, $port=0, $tval=30)
     *
     * Connect to the server specified on the port specified.
     * If the port is not specified use the default SMTP_PORT.
     * If tval is specified then a connection will try and be
     * established with the server for that number of seconds.
     * If tval is not specified the default is 30 seconds to
     * try on the connection.
     *
     * SMTP CODE SUCCESS: 220
     * SMTP CODE FAILURE: 421
     */
    function Connect($host,$port=0,$tval=30)
    {
        // make sure we are __not__ connected
        if($this->connected()) {
            // ok we are connected! what should we do?
            // for now we will just give an error saying we
            // are already connected
            $this->_set_error('Already connected to a server');
            return false;
        }

        if(empty($port)) {
            $port = $this->SMTP_PORT;
        }

        //connect to the smtp server
        $this->smtp_conn = fsockopen($host,    // the host of the server
                                     $port,    // the port to use
                                     $errno,   // error number if any
                                     $errstr,  // error message if any
                                     $tval);   // give up after ? secs
        // verify we connected properly
        if(empty($this->smtp_conn)) {
            $this->_set_error('Failed to connect to server: '.$errstr.' ('.$errno.')');
            return false;
        }

        // sometimes the SMTP server takes a little longer to respond
        // so we will give it a longer timeout for the first read
        // Windows still does not have support for this timeout function
        if(substr(PHP_OS, 0, 3) != 'WIN')
           socket_set_timeout($this->smtp_conn, 1, 0);

        // get any announcement stuff
        $announce = $this->get_lines();

        // set the timeout  of any socket functions at 1/10 of a second
        //if(function_exists('socket_set_timeout'))
        //   socket_set_timeout($this->smtp_conn, 0, 100000);

        return true;
    }

    /*
     * Authenticate()
     *
     * Performs SMTP authentication.  Must be run after running the
     * Hello() method.  Returns true if successfully authenticated.
     */
    function Authenticate($username, $password)
    {
        // Start authentication
        $this->put_lines('AUTH LOGIN');

        $rply = $this->get_lines();
        $code = substr($rply,0,3);

        if($code != 334) {
            $this->_set_error('AUTH not accepted from server: '.$rply);
            return false;
        }

        // Send encoded username
        $this->put_lines(base64_encode($username));

        $rply = $this->get_lines();
        $code = substr($rply,0,3);

        if($code != 334) {
            $this->_set_error('Username not accepted from server: '.$rply);
            return false;
        }

        // Send encoded password
        $this->put_lines(base64_encode($password));

        $rply = $this->get_lines();
        $code = substr($rply,0,3);

        if($code != 235) {
            $this->_set_error('Password not accepted from server: '.$rply);
            return false;
        }

        return true;
    }

    /*
     * Connected()
     *
     * Returns true if connected to a server otherwise false
     */
    function Connected()
    {
        if(!empty($this->smtp_conn)) {
            $sock_status = socket_get_status($this->smtp_conn);
            if($sock_status['eof']) {
                // hmm this is an odd situation... the socket is
                // valid but we aren't connected anymore
                $this->_set_error('SMTP -> NOTICE: EOF caught while checking if connected');
                $this->Close();
                return false;
            }
            return true; // everything looks good
        }
        return false;
    }

    /*
     * Close()
     *
     * Closes the socket and cleans up the state of the class.
     * It is not considered good to use this function without
     * first trying to use QUIT.
     */
    function Close()
    {
        $this->helo_rply = null;
        if(!empty($this->smtp_conn)) {
            // close the connection and cleanup
            fclose($this->smtp_conn);
            $this->smtp_conn = 0;
        }
    }


    /**************************************************************
     *                        SMTP COMMANDS                       *
     *************************************************************/

    /*
     * Data($msg_data)
     *
     * Issues a data command and sends the msg_data to the server
     * finializing the mail transaction. $msg_data is the message
     * that is to be send with the headers. Each header needs to be
     * on a single line followed by a <CRLF> with the message headers
     * and the message body being seperated by and additional <CRLF>.
     *
     * Implements rfc 821: DATA <CRLF>
     *
     * SMTP CODE INTERMEDIATE: 354
     *     [data]
     *     <CRLF>.<CRLF>
     *     SMTP CODE SUCCESS: 250
     *     SMTP CODE FAILURE: 552,554,451,452
     * SMTP CODE FAILURE: 451,554
     * SMTP CODE ERROR  : 500,501,503,421
     */
    function Data($msg_data)
    {
        if(!$this->connected()) {
            $this->_set_error('Called Data() without being connected');
            return false;
        }

        $this->put_lines('DATA');

        $rply = $this->get_lines();
        $code = substr($rply,0,3);

        if($code != 354) {
            $this->_set_error('DATA command not accepted from server: '.$rply);
            return false;
        }

        // the server is ready to accept data!
        // according to rfc 821 we should not send more than 1000
        // including the CRLF
        // characters on a single line so we will break the data up
        // into lines by \r and/or \n then if needed we will break
        // each of those into smaller lines to fit within the limit.
        // in addition we will be looking for lines that start with
        // a period '.' and append and additional period '.' to that
        // line. NOTE: this does not count towards are limit.

        // normalize the line breaks so we know the explode works
        $msg_data = str_replace("\r\n","\n",$msg_data);
        $msg_data = str_replace("\r","\n",$msg_data);
        $lines = explode("\n",$msg_data);

        // we need to find a good way to determine is headers are
        // in the msg_data or if it is a straight msg body
        // currently I'm assuming rfc 822 definitions of msg headers
        // and if the first field of the first line (':' sperated)
        // does not contain a space then it _should_ be a header
        // and we can process all lines before a blank "" line as
        // headers.
        $field = substr($lines[0],0,strpos($lines[0],':'));
        $in_headers = false;
        if(!empty($field) && !strstr($field,' ')) {
            $in_headers = true;
        }

        $max_line_length = 998; // used below; set here for ease in change

        while(list(,$line) = @each($lines)) {
            $lines_out = null;
            if($line == '' && $in_headers) {
                $in_headers = false;
            }
            // ok we need to break this line up into several
            // smaller lines
            while(strlen($line) > $max_line_length) {
                $pos = strrpos(substr($line,0,$max_line_length),' ');
                $lines_out[] = substr($line,0,$pos);
                $line = substr($line,$pos + 1);
                // if we are processing headers we need to
                // add a LWSP-char to the front of the new line
                // rfc 822 on long msg headers
                if($in_headers) {
                    $line = "\t" . $line;
                }
            }
            $lines_out[] = $line;

            // now send the lines to the server
            while(list(,$line_out) = @each($lines_out)) {
                if(strlen($line_out) > 0)
                {
                    if(substr($line_out, 0, 1) == '.') {
                        $line_out = '.' . $line_out;
                    }
                }
                $this->put_lines($line_out);
            }
        }

        // ok all the message data has been sent so lets get this
        // over with aleady
        $this->put_lines($this->CRLF.'.');

        $rply = $this->get_lines();
        $code = substr($rply,0,3);

        if($code != 250) {
            $this->_set_error('DATA not accepted from server: '.$rply);
            return false;
        }
        return true;
    }

    /*
     * Expand($name)
     *
     * Expand takes the name and asks the server to list all the
     * people who are members of the _list_. Expand will return
     * back and array of the result or false if an error occurs.
     * Each value in the array returned has the format of:
     *     [ <full-name> <sp> ] <path>
     * The definition of <path> is defined in rfc 821
     *
     * Implements rfc 821: EXPN <SP> <string> <CRLF>
     *
     * SMTP CODE SUCCESS: 250
     * SMTP CODE FAILURE: 550
     * SMTP CODE ERROR  : 500,501,502,504,421
     */
    function Expand($name)
    {
        if(!$this->connected()) {
            $this->_set_error('Called Expand() without being connected');
            return false;
        }

        $this->put_lines('EXPN ' . $name);

        $rply = $this->get_lines();
        $code = substr($rply,0,3);

        if($code != 250) {
            $this->_set_error('EXPN not accepted from server: '.$rply);
            return false;
        }

        // parse the reply and place in our array to return to user
        $entries = explode($this->CRLF, $rply);
        while(list(,$l) = @each($entries)) {
            $list[] = substr($l,4);
        }

        return $rval;
    }

    /*
     * Hello($host='')
     *
     * Sends the HELO command to the smtp server.
     * This makes sure that we and the server are in
     * the same known state.
     *
     * Implements from rfc 821: HELO <SP> <domain> <CRLF>
     *
     * SMTP CODE SUCCESS: 250
     * SMTP CODE ERROR  : 500, 501, 504, 421
     */
    function Hello($host='') {
        if(!$this->connected()) {
            $this->_set_error('Called Hello() without being connected');
            return false;
        }

        // if a hostname for the HELO wasn't specified determine
        // a suitable one to send
        if($host == '') {
            // we need to determine some sort of appopiate default
            // to send to the server
            $host = 'localhost';
        }

        $this->put_lines('HELO ' . $host);

        $rply = $this->get_lines();
        $code = substr($rply,0,3);

        if($code != 250) {
            $this->set_error('HELO not accepted from server: '.$rply);
            return false;
        }

        $this->helo_rply = $rply;

        return true;
    }

    /*
     * Help($keyword='')
     *
     * Gets help information on the keyword specified. If the keyword
     * is not specified then returns generic help, ussually contianing
     * A list of keywords that help is available on. This function
     * returns the results back to the user. It is up to the user to
     * handle the returned data.
     *
     * Implements rfc 821: HELP [ <SP> <string> ] <CRLF>
     *
     * SMTP CODE SUCCESS: 211,214
     * SMTP CODE ERROR  : 500,501,502,504,421
     *
     */
    function Help($keyword='')
    {
        if(!$this->connected()) {
            $this->_set_error('Called Help() without being connected');
            return false;
        }

        $extra = '';
        if($keyword != '') {
            $extra = ' ' . $keyword;
        }

        $this->put_lines('HELP' . $extra);

        $rply = $this->get_lines();
        $code = substr($rply,0,3);

        if($code != 211 && $code != 214) {
            $this->_set_error('HELP not accepted from server'.$rply);
            return false;
        }

        return $rply;
    }

    /*
     * Mail($from)
     *
     * Starts a mail transaction from the email address specified in
     * $from. Returns true if successful or false otherwise. If True
     * the mail transaction is started and then one or more Recipient
     * commands may be called followed by a Data command.
     *
     * Implements rfc 821: MAIL <SP> FROM:<reverse-path> <CRLF>
     *
     * SMTP CODE SUCCESS: 250
     * SMTP CODE SUCCESS: 552,451,452
     * SMTP CODE SUCCESS: 500,501,421
     */
    function Mail($from)
    {
        if(!$this->connected()) {
            $this->_set_error('Called Mail() without being connected');
            return false;
        }

        $this->put_lines('MAIL FROM:' . $from);

        $rply = $this->get_lines();
        $code = substr($rply,0,3);

        if($code != 250) {
            $this->_set_error('MAIL not accepted from server: '.$rply);
            return false;
        }
        return true;
    }

    /*
     * Noop()
     *
     * Sends the command NOOP to the SMTP server.
     *
     * Implements from rfc 821: NOOP <CRLF>
     *
     * SMTP CODE SUCCESS: 250
     * SMTP CODE ERROR  : 500, 421
     */
    function Noop()
    {
        if(!$this->connected()) {
            $this->_set_error('Called Noop() without being connected');
            return false;
        }

        $this->put_lines('NOOP');

        $rply = $this->get_lines();
        $code = substr($rply,0,3);

        if($code != 250) {
            $this->_set_error('NOOP not accepted from server: '.$rply);
            return false;
        }
        return true;
    }

    /*
     * Quit($close_on_error=true)
     *
     * Sends the quit command to the server and then closes the socket
     * if there is no error or the $close_on_error argument is true.
     *
     * Implements from rfc 821: QUIT <CRLF>
     *
     * SMTP CODE SUCCESS: 221
     * SMTP CODE ERROR  : 500
     */
    function Quit($close_on_error=true)
    {
        if(!$this->connected()) {
            $this->_set_error('Called Quit() without being connected');
            return false;
        }

        // send the quit command to the server
        $this->put_lines('quit');

        // get any good-bye messages
        $byemsg = $this->get_lines();

        $rval = true;
        $code = substr($byemsg,0,3);
        if($code != 221) {
            $this->_set_error('SMTP server rejected quit command: '.$byemsg);
            $rval = false;
        }

        if(!$rval || $close_on_error) {
            $this->Close();
        }

        return $rval;
    }

    /*
     * Recipient($to)
     *
     * Sends the command RCPT to the SMTP server with the TO: argument of $to.
     * Returns true if the recipient was accepted false if it was rejected.
     *
     * Implements from rfc 821: RCPT <SP> TO:<forward-path> <CRLF>
     *
     * SMTP CODE SUCCESS: 250,251
     * SMTP CODE FAILURE: 550,551,552,553,450,451,452
     * SMTP CODE ERROR  : 500,501,503,421
     */
    function Recipient($to)
    {
        if(!$this->connected()) {
            $this->_set_error('Called Recipient() without being connected');
            return false;
        }

        $this->put_lines('RCPT TO:' . $to);

        $rply = $this->get_lines();
        $code = substr($rply,0,3);

        if($code != 250 && $code != 251) {
            $this->_set_error('RCPT not accepted from server: '.$rply);
            return false;
        }
        return true;
    }

    /*
     * Reset()
     *
     * Sends the RSET command to abort and transaction that is
     * currently in progress. Returns true if successful false
     * otherwise.
     *
     * Implements rfc 821: RSET <CRLF>
     *
     * SMTP CODE SUCCESS: 250
     * SMTP CODE ERROR  : 500,501,504,421
     */
    function Reset()
    {
        if(!$this->connected()) {
            $this->_set_error('Called Reset() without being connected');
            return false;
        }

        $this->put_lines('RSET');

        $rply = $this->get_lines();
        $code = substr($rply,0,3);

        if($code != 250) {
            $this->_set_error('RSET failed: '.$rply);
            return false;
        }

        return true;
    }

    /*
     * Send($from)
     *
     * Starts a mail transaction from the email address specified in
     * $from. Returns true if successful or false otherwise. If True
     * the mail transaction is started and then one or more Recipient
     * commands may be called followed by a Data command. This command
     * will send the message to the users terminal if they are logged
     * in.
     *
     * Implements rfc 821: SEND <SP> FROM:<reverse-path> <CRLF>
     *
     * SMTP CODE SUCCESS: 250
     * SMTP CODE SUCCESS: 552,451,452
     * SMTP CODE SUCCESS: 500,501,502,421
     */
    function Send($from)
    {
        if(!$this->connected()) {
            $this->_set_error('Called Send() without being connected');
            return false;
        }

        $this->put_lines('SEND FROM:' . $from);

        $rply = $this->get_lines();
        $code = substr($rply,0,3);

        if($code != 250) {
            $this->_set_error('SEND not accepted from server: '.$rply);
            return false;
        }
        return true;
    }

    /*
     * SendAndMail($from)
     *
     * Starts a mail transaction from the email address specified in
     * $from. Returns true if successful or false otherwise. If True
     * the mail transaction is started and then one or more Recipient
     * commands may be called followed by a Data command. This command
     * will send the message to the users terminal if they are logged
     * in and send them an email.
     *
     * Implements rfc 821: SAML <SP> FROM:<reverse-path> <CRLF>
     *
     * SMTP CODE SUCCESS: 250
     * SMTP CODE SUCCESS: 552,451,452
     * SMTP CODE SUCCESS: 500,501,502,421
     */
    function SendAndMail($from)
    {
        if(!$this->connected()) {
            $this->_set_error('Called SendAndMail() without being connected');
            return false;
        }

        $this->put_lines('SAML FROM:' . $from);

        $rply = $this->get_lines();
        $code = substr($rply,0,3);

        if($code != 250) {
            $this->_set_error('SAML not accepted from server: '.$rply);
            return false;
        }
        return true;
    }

    /*
     * SendOrMail($from)
     *
     * Starts a mail transaction from the email address specified in
     * $from. Returns true if successful or false otherwise. If True
     * the mail transaction is started and then one or more Recipient
     * commands may be called followed by a Data command. This command
     * will send the message to the users terminal if they are logged
     * in or mail it to them if they are not.
     *
     * Implements rfc 821: SOML <SP> FROM:<reverse-path> <CRLF>
     *
     * SMTP CODE SUCCESS: 250
     * SMTP CODE SUCCESS: 552,451,452
     * SMTP CODE SUCCESS: 500,501,502,421
     */
    function SendOrMail($from)
    {
        if(!$this->connected()) {
            $this->_set_error('Called SendOrMail() without being connected');
            return false;
        }

        $this->put_lines('SOML FROM:' . $from);

        $rply = $this->get_lines();
        $code = substr($rply,0,3);

        if($code != 250) {
            $this->_set_error('SOML not accepted from server: '. $rply);
            return false;
        }
        return true;
    }

    /*
     * Turn()
     *
     * This is an optional command for SMTP that this class does not
     * support. This method is here to make the RFC821 Definition
     * complete for this class and __may__ be implimented in the future
     *
     * Implements from rfc 821: TURN <CRLF>
     *
     * SMTP CODE SUCCESS: 250
     * SMTP CODE FAILURE: 502
     * SMTP CODE ERROR  : 500, 503
     */
    function Turn() {
        $this->_set_error('This method, TURN, of the SMTP is not implemented');
        return false;
    }

    /*
     * Verify($name)
     *
     * Verifies that the name is recognized by the server.
     * Returns false if the name could not be verified otherwise
     * the response from the server is returned.
     *
     * Implements rfc 821: VRFY <SP> <string> <CRLF>
     *
     * SMTP CODE SUCCESS: 250,251
     * SMTP CODE FAILURE: 550,551,553
     * SMTP CODE ERROR  : 500,501,502,421
     */
    function Verify($name)
    {
        if(!$this->connected()) {
            $this->_set_error('Called Verify() without being connected');
            return false;
        }

        $this->put_lines('VRFY ' . $name);

        $rply = $this->get_lines();
        $code = substr($rply,0,3);

        if($code != 250 && $code != 251) {
            $this->_set_error("VRFY failed on name '$name': ". $rply);
            return false;
        }
        return $rply;
    }

    /******************************************************************
     *                       INTERNAL FUNCTIONS                       *
     ******************************************************************/

    /*
     * get_lines()
     *
     * __internal_use_only__: read in as many lines as possible
     * either before eof or socket timeout occurs on the operation.
     * With SMTP we can tell if we have more lines to read if the
     * 4th character is '-' symbol. If it is a space then we don't
     * need to read anything else.
     */
    function get_lines()
    {
        $data = '';
        while($str = fgets($this->smtp_conn,515)) {
            $data .= $str;
            // if the 4th character is a space then we are done reading
            // so just break the loop
            if(substr($str,3,1) == ' ') { break; }
        }
        $this->_set_log('FROM SMTP: '.$data);
        return $data;
    }

    function put_lines($lines)
    {
        fputs($this->smtp_conn, $lines.$this->CRLF);
        $this->_set_log('TO SMTP  : '.$lines);
    }

    /*
     * error handling
     */
    function _set_error($msg){
        $this->error[] = $msg;
        if($this->do_debug != false){
            trigger_error(get_class($this).'::'.$msg, E_USER_WARNING);
        }
    }

    /*
     * log handling
     */
    function _set_log($msg)
    {
        if($this->do_debug != false){
            $this->log[] = $msg;
        }
    }

    function getLog()
    {
        return implode('<br />', $this->log);
    }
}

?>