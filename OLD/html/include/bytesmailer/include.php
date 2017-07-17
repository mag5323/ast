<?php
// $Id: include.php,v 1.3 2003/06/23 16:55:45 haruki Exp $
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
 * defines.
 */
if(! defined('_BYTESMAILER_DIR'))
    define('_BYTESMAILER_DIR', dirname(__FILE__).'/');
if(! defined('_BYTESMAILER_DEBUG'))
    define('_BYTESMAILER_DEBUG', 0);

define('_BYTESMAILER_LE', "\r\n");
define('_BYTESMAILER_FWS', _BYTESMAILER_LE.' ');
define('_BYTESMAILER_MAX_LINE_LENGTH', 998); // 998

define('_BYTESMAILER_HEADER_BASE64', true);
define('_BYTESMAILER_BODY_8BIT', false);

define('_BYTESMAILER_DEFAULT_ENCODING', 'UTF-8');
define('_BYTESMAILER_DEFAULT_MULTIBYTE', true);

if(! defined('_BYTESMAILER_USE_MB_FUNCTION'))
    define('_BYTESMAILER_USE_MB_FUNCTION', false);
/**
 * include class files
 */
include _BYTESMAILER_DIR.'common.php';
include _BYTESMAILER_DIR.'bytesmailer.php';
include _BYTESMAILER_DIR.'encodingconverter.php';
include _BYTESMAILER_DIR.'textspliter.php';
//include _BYTESMAILER_DIR.'datahandler.php';
include _BYTESMAILER_DIR.'mailtextencoder.php';

include _BYTESMAILER_DIR.'address.php';
include _BYTESMAILER_DIR.'mailheader.php';
include _BYTESMAILER_DIR.'mailbody.php';

?>