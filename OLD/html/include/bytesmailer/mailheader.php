<?php
// $Id: mailheader.php,v 1.5 2003/06/24 15:35:32 haruki Exp $
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
 * mail header class
 *
 * This class holds infomations for mail sendig,
 * like destination address, subjects, and so on.
 *
 * @access public
 */
class mailHeader extends bytesmailerFellow
{
    /**
     * variables for destination address fields
     *
     *  @access public
     *  @var address $to      Holds all "To" addresses. address-list.
     *  @var address $cc      Holds all "CC" addresses. address-list.
     *  @var address $bcc     Holds all "CC" addresses. address-list.
     */
    var $to;
    var $cc;
    var $bcc;

    /**
     *  variables for originator fields
     *
     *  @access public
     *  @var address $sender      Holds a "sender" address. mailbox.
     *  @var address $from        Holds all "from" addresses. mailbox-list.
     *  @var address $replyTo     Holds all "from" addresses. address-list.
     */
    var $sender;
    var $from;
    var $replyTo;

    /**
     *  variables for optional
     *
     *  @access public
     *  @var address $confirmReadingTo      mailbox-list. rfc2298
     *  @var int $priority
     */
    var $confirmReadingTo;
    var $priority           = 3;

    /**
     *  variables for identification fields
     *
     *  @access public
     *  @var array $inReplyTo      Holds all "InReplyTo" msg-ids
     *  @var array $references     Holds all "References" msg-ids
     *  @var string $messageId     Holds a msg-id
     */
    var $inReplyTo      = array();
    var $references     = array();
    var $messageId      = '';

    /**
     *  variables for Informational fields
     *
     *  @access public
     *  @var string $subject
     *  @var string $comments
     *  @var array of string $keywords
     */
    var $subject;
    var $comments;
    var $keywords   = array();

    /**
     *  variables for Trace fields
     *
     *  @access public
     *  @var string $returnPath  addr-spec, that is just hoge@hoge.com style
     */
    var $returnPath;

    /**
     *  custom headers. ex. Reply-By: X-Message-Flag:
     *
     *  @access public
     *  @var array $custom
     */
    var $custom    = array();

    /**
     *  private variables
     *
     *  @access private
     */
    var $_priority_word      = array('urgent','normal','normal','normal','non-urgent');
    var $_importance_word    = array('high','high','normal','low','non-low');

    /**
     * Constructor.
     */
    function mailHeader(&$leader)
    {
        $this->bytesmailerFellow($leader);

        //$this->mailContent($mailText);
        $this->to   = new addresslist($this);
        $this->cc   = new addresslist($this);
        $this->bcc  = new addresslist($this);
        $this->from = new mailboxlist($this);
        $this->sender = new nameaddr($this);
        $this->replyTo = new addresslist($this);
        $this->returnPath = new nameaddr($this);
        $this->confirmReadingTo = new mailboxlist($this);

        $this->subject = new unstructuredMailText($this);
        $this->comments = new unstructuredMailText($this);
    }

    /**
     * Assembles message header.
     *
     * @access public
     * @return array
     */
    function getHeader()
    {
        $header = array();

        if($this->returnPath->count() > 0){
            $addr = $this->returnPath->getAddrspec();
            $header['Return-Path'] = '<'.$addr[0].'>';
        }
        elseif($this->sender->count() > 0){
            $addr = $this->sender->getAddrspec();
            $header['Return-Path'] = '<'.$addr[0].'>';
        }
        elseif($this->from->count() > 0){
            $addr = $this->from->getAddrspec();
            $header['Return-Path'] = '<'.$addr[0].'>';
        }

        $header['Received'] = sprintf("from %s by bytesmailer [%s] with HTTP;%s%s",
               $this->_get_server_var('REMOTE_ADDR'),
               $this->_get_server_var('SERVER_NAME'),
               _BYTESMAILER_FWS,
               date('r'));

        if($this->to->count() > 0) {
            $header['To'] = $this->to->getForHeader();
        }
        if($this->cc->count() > 0) {
            $header['Cc'] = $this->cc->getForHeader();
        }
        if($this->bcc->count() > 0) {
            $header['Bcc'] = $this->bcc->getForHeader();
        }

        if($this->from->count() > 0){
            $header['From'] = $this->from->getForHeader();
        }else{
            $header['From'] = 'unknown';
        }

        if($this->sender->count() > 0)
        {
            $header['Sender'] = $this->sender->getOneForHeader();
        }
        elseif($this->from->count() >= 2)
        {
            $header['Sender'] = $this->from->getOneForHeader();
        }

        if($this->replyTo->count() > 0)
            $header['Reply-to'] = $this->replyTo->getForHeader();

        $header['Date'] = date('r');

        if($this->subject->isTextSet())
	  {
	  	global $make_subject;
		$header['Subject'] = $make_subject;
		}
        if($this->comments->isTextSet() != '' )
            $header['Comments'] = $this->comments->getForHeader();

        if($this->messageId != '')
            $header['Message-ID'] = $this->messageId;
        if(!empty($this->inReplyTo))
            $header['In-Reply-To'] = implode(_BYTESMAILER_FWS, $this->inReplyTo);
        if(!empty($this->references))
            $header['References'] = implode(_BYTESMAILER_FWS, $this->references);

        $this->priority = intval($this->priority);
        if($this->priority < 1 || $this->priority > 5)  $this->priority=3;
        if($this->priority != 3){
            $header['X-Priority'] = sprintf("%d",$this->priority);
            $header['Priority'] = $this->_priority_word[$this->priority-1];
            $header['Importance'] = $this->_importance_word[$this->priority-1];
        }

        $header['X-Mailer'] = sprintf("bytesmailer [version %s]", _BYTESMAILER_VERSION );

        if($this->confirmReadingTo->count() > 0)
            $header['Disposition-Notification-To']
                = $this->confirmReadingTo->getForHeader();
        if(! empty($this->custom))
            array_merge($header, $this->custom);
            // custom header will be not mime encoded.

        $header['MIME-Version'] = '1.0';

        return $header;
    }

    /**
     * Returns the appropriate server variable.
     *
     * @access private
     * @return mixed
     */
    function _get_server_var($varName)
    {
        if(!isset($_SERVER))
        {
            $_SERVER =& $GLOBALS['HTTP_SERVER_VARS'];
        }
        return (isset($_SERVER[$varName])) ? $_SERVER[$varName] : '';
    }

}

/**
* unstructuredMailText (ex subject)
*/
class unstructuredMailText extends bytesmailerFellow
{
    var $mail_text = null;

    function unstructuredMailText(&$leader)
    {
        $this->bytesmailerFellow($leader);
    }

    function set($data)
    {
        $this->mail_text = $this->_textEncoder->headerText($data, false);
    }

    function getForHeader()
    {
        return $this->mail_text;
    }

    function isTextSet()
    {
        return isset($this->mail_text);
    }
}
?>