<?php
// $Id: iso2022jp.php,v 1.2 2003/06/24 15:35:03 haruki Exp $
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
* textSpliter for iso-2022-jp encoding
*
* @access public
*/
class textSpliter_iso2022jp extends textSpliter
{
    function split($string, $length, $first_part_length=0)
    {
    /*
        if($length < 8 && $first_part_length < 8)
        {
            return array($string);
        }
    */

        if($first_part_length > 0)
            $max_length = $first_part_length;
        else
            $max_length = $length;

        // TODO: locking shift

        $b = unpack('C*', $string);
        $n = count($b);

        $ret = array();
        $lines = 0;
        $line_byte = 0;

        $i = 1;
        $mode = '';
        $mode_byte = 1;
        while($i <= $n)
        {
            if($b[$i] == 0x1B)
            {    // ESC
                $prev_mode = $mode;
                if ($b[$i+1] == 0x24 && $b[$i+2] == 0x40)
                {
                    // $@ JIS C 6226-1978
                    $esc_chars = chr(0x1B).chr(0x24).chr(0x40);
                    $mode = 1;
                    $mode_byte = 2;
                }
                elseif($b[$i+1] == 0x24 && $b[$i+2] == 0x42)
                {
                    // $B JIS X 0208-1983
                    $esc_chars = chr(0x1B).chr(0x24).chr(0x40);
                    $mode = 1;
                    $mode_byte = 2;
                }
                elseif ($b[$i+1] == 0x28 && $b[$i+2] == 0x49)
                {
                    // (I JIS X 0208 Katakana
                    $esc_chars = chr(0x1B).chr(0x28).chr(0x49);
                    $mode = 1;
                    $mode_byte = 1;
                }
                elseif ($b[$i+1] == 0x28 && $b[$i+2] == 0x42)
                {
                    // (B ASCII
                    $esc_chars = chr(0x1B).chr(0x28).chr(0x42);
                    $mode = 0;
                    $mode_byte = 1;
                }
                elseif ($b[$i+1] == 0x28 && $b[$i+2] == 0x4A)
                {
                    // (J JIS X 0208 Romaji
                    $esc_chars = chr(0x1B).chr(0x28).chr(0x4A);
                    $mode = 0;
                    $mode_byte = 1;
                }
                else
                {
                    // this is not ISO-2022-JP
                    trigger_error(
                        'textSpliter_iso2022jp::split()::not ISO-2022-JP',
                        E_USER_NOTICE
                    );
                    return array($string);
                }
                if($line_byte + 3 + $prev_mode*3 + $mode_byte > $max_length)
                {
                    if($prev_mode != 0)
                        $ret[$lines] .= chr(0x1B).chr(0x28).chr(0x42); // esc to ascii
                    $max_length = $length;
                    $lines++;
                    $ret[$lines] = $esc_chars;
                    $line_byte = 3;
                }
                else
                {
                    $ret[$lines] .= $esc_chars;
                    $line_byte += 3;
                }
                $i += 3;
            }
            else
            {
                if($line_byte + $mode*3 + $mode_byte > $max_length)
                {
                    if($mode != 0)
                        $ret[$lines] .= chr(0x1B).chr(0x28).chr(0x42); // esc to ascii
                    $max_length = $length;
                    $lines++;
                    $ret[$lines] = '';
                    if($mode > 0) $ret[$lines] .= $esc_chars;
                    $line_byte = strlen($ret[$lines]);
                }

                if($mode_byte == 1)
                {
                    $ret[$lines] .= chr($b[$i]);
                    $line_byte++;
                    $i++;
                }
                else
                {
                    $ret[$lines] .= chr($b[$i]).chr($b[$i+1]);
                    $line_byte+=2;
                    $i+=2;
                }
            }
        }
        return $ret;
    }
}
?>