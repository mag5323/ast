<?php
// $Id: class.jcode.php,v 1.3 2003/06/23 16:54:11 haruki Exp $
/**
 * class.jcode.php - Encoding converter class for Japanese.
 *
 * This class converts the encodings of Japanese character,
 * and is able to handle SJIS, EUC-JP, JIS(ISO-2022-JP) and UTF-8.
 *
 * This class is based on jcode.phps v1.34 by TOMO
 * See http://www.spencernetwork.org/
 *
 * the license of class.jcode.php is the same as jcode.phps,
 * free but without any warranty.
 * use this script at your own risk.
 * (BSD like?)
 *
 * @author Haruki Setoyama <haruki@planewave.org>
 */
/**
* define
*/
define('JCODE_AUTO', 0);
define('JCODE_EUC', 1);
define('JCODE_SJIS', 2);
define('JCODE_JIS', 3);
define('JCODE_UTF8', 4);
define('JCODE_UNKNOWN', 5);
define('JCODE_ASCII', 6);

define('JCODE_UNKNOWN_CHAR','?');

/**
 * jcode - general handler of jcode
 * @access public
 */
class jcode
{
    var $internal_encoding = 'EUC-JP';

    function internal_encoding($encoding ='')
    {
        $encoding = strtoupper($encoding);
        if($encoding !='')
        {
            return $this->internal_encoding;
        }
        elseif($encoding == 'EUC-JP' || $encoding == 'UTF-8')
        {
            $this->internal_encoding = $encoding;
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * Converts the encoding of japanese string $str from $from to $to.
	 *
	 * @access 	public
     * @param 	&string	$str
     * @param 	mixed	$from
     * @param 	mixed	$to
     * @return 	string
     */
    function convert_encoding(&$str, $to, $from ='')
    {
        $name =    array(
                        JCODE_EUC => 'euc',
                        'EUC-JP' => 'euc',
                        'EUC' => 'euc',
                        JCODE_SJIS => 'sjis',
                        'SJIS' => 'sjis',
                        'SHIFT-JIS' => 'sjis',
                        JCODE_JIS => 'jis',
                        'ISO-2022-JP' => 'jis',
                        'JIS' => 'jis',
                        JCODE_UTF8 => 'utf8',
                        'UTF-8' => 'utf8'
                    );
        if($from == '')
        {
            $from = $this->internal_encoding;
        }
        elseif ($from === JCODE_AUTO || strtolower($from) == 'auto')
        {
            $from = $this->detect_encoding($str);
        }
        else
        {
            $from = strtoupper($from);
        }

        if($from == 'ASCII') return $str;

        $to = strtoupper($to);

        $class = 'jcode_'.$name[$from].'_'.$name[$to];
        if(! class_exists($class))
        {
            trigger_error('jcode::unsuported encoding. '.$from.' -> '.$to, E_USER_NOTICE);
            return $str;
        }
        $converter = new $class;
        return $converter->convert($str);
    }

    /**
     * Detects the encoding of Japanese strings.
     *
     * @access public
     * @param  string $str
     * @return string
     */
    function detect_encoding(&$str)
    {
    	if (!ereg("[\x80-\xFF]", $str)) {
    		// --- Check ISO-2022-JP ---
    		if (ereg("\x1B", $str)) return 'JIS'; // ISO-2022-JP(JIS)
    		return 'ASCII'; //US-ASCII
    	}

    	$b = unpack('C*', ereg_replace("^[^\x80-\xFF]+", "", $str));
    	$n = count($b);
    
    	// --- Check EUC-JP ---
    	$euc = true;
    	for ($i = 1; $i <= $n; ++$i){
    		if ($b[$i] < 0x80) {
    			continue;
    		}
    		if ($b[$i] < 0x8E) {
    			$euc = false; break;
    		}
    		if ($b[$i] == 0x8E) {
    			if (!isset($b[++$i])) {
    				$euc = false; break;
    			}
    			if (($b[$i] < 0xA1) || (0xDF < $b[$i])) {
    				$euc = false; break;
    			}
    		} elseif ((0xA1 <= $b[$i]) && ($b[$i] <= 0xFE)) {
    			if (!isset($b[++$i])) {
    				$euc = false; break;
    			}
    			if (($b[$i] < 0xA1) || (0xFE < $b[$i])) {
    				$euc = false; break;
    			}
    		} else {
    			$euc = false; break;
    		}
    	}
    	if ($euc) return 'EUC-JP'; // EUC-JP
    
    	// --- Check UTF-8 ---
    	$utf8 = true;
    	for ($i = 1; $i <= $n; ++$i) {
    		if (($b[$i] < 0x80)) {
    			continue;
    		}
    		if ((0xC0 <= $b[$i]) && ($b[$i] <=0xDF)) {
    			if (!isset($b[++$i])) {
    				$utf8 = false; break;
    			}
    			if (($b[$i] < 0x80) || (0xEF < $b[$i])) {
    				$utf8 = false; break;
    			}
    		} elseif ((0xE0 <= $b[$i]) && ($b[$i] <= 0xEF)) {
    			if (!isset($b[++$i])) {
    				$utf8 = false; break;
    			}
    			if (($b[$i] < 0x80) || (0xBF < $b[$i])) {
    				$utf8 = false; break;
    			}
    			if (!isset($b[++$i])) {
    				$utf8 = false; break;
    			}
    			if (($b[$i] < 0x80) || (0xBF < $b[$i])) {
    				$utf8 = false; break;
    			}
    		} else {
    			$utf8 = false; break;
    		}
    	}
    	if ($utf8) return 'UTF-8'; // UTF-8
    
    	// --- Check Shift_JIS ---
    	$sjis = true;
    	for ($i = 1; $i <= $n; ++$i) {
    		if (($b[$i] <= 0x80) || (0xA1 <= $b[$i] && $b[$i] <= 0xDF)) {
    			continue;
    		}
    		if (($b[$i] == 0xA0) || ($b[$i] > 0xEF)) {
    			$sjis = false; break;
    		}
    		if (!isset($b[++$i])) {
    			$sjis = false; break;
    		}
    		if (($b[$i] < 0x40) || ($b[$i] == 0x7F) || ($b[$i] > 0xFC)){
    			$sjis = false; break;
    		}
    	}
    	if ($sjis) return JCODE_SJIS; // Shift_JIS
    
    	return false; // Unknown
    }
}

/**
 * jcode_converter - concrete converters
 * @access interface
 */
class jcode_converter
{
    function convert($text, $subst ='?') {}
}

/**
 * jcode_converter_utf8
 * @access abstruct
 */
class jcode_converter_utf8 extends jcode_converter
{
    var $table_utf8_jis;
    var $table_jis_utf8;

    function _table_utf8_jis()
    {
        include('code_table.ucs2jis.php');
        $this->table_utf8_jis = $table_utf8_jis;
    }

    function _table_jis_utf8()
    {
        include('code_table.jis2ucs.php');
        $this->table_jis_utf8 = $table_jis_utf8;
    }

    function _get_one_char(&$b, &$i)
    {
        // BOM

        if($b[$i] == 0xEF && $b[$i+1] == 0xBB && $b[$i+2] == 0xBF)
        {
            $i += 3;
            return $this->_get_one_char($b, $i);
        }

        if(0x00 <= $b[$i] && $b[$i] <= 0x7F)
        {
            //char_byte = 1;
            $utf8 = $b[$i];
        }
        elseif(0xC0 <= $b[$i] && $b[$i] <= 0xDF)
        {
            //char_byte = 2;
            $utf8 = ($b[$i] << 8) + $b[++$i];
        }
        elseif(0xE0 <= $b[$i] && $b[$i] <= 0xEF)
        {
            //char_byte = 3;
            $utf8 = ($b[$i] << 16) + ($b[++$i] << 8) + $b[++$i];
        }
        elseif(0xF0 <= $b[$i] && $b[$i] <= 0xF7)
        {
            //char_byte = 4;
            $utf8 = ($b[$i] << 24) + ($b[++$i] << 16) + ($b[++$i] << 8) + $b[++$i];
        }
        elseif(0xF8 <= $b[$i] && $b[$i] <= 0xFB)
        {
            //char_byte = 5;
            $utf8 = ($b[$i] << 32) + ($b[++$i] << 24) + ($b[++$i] << 16)
                + ($b[++$i] << 8) + $b[++$i];
        }
        elseif(0xFC <= $b[$i] && $b[$i] <= 0xFD)
        {
            //char_byte = 6;
            $utf8 = ($b[$i] << 40) + ($b[++$i] << 32) + ($b[++$i] << 24)
                + ($b[++$i] << 16) + ($b[++$i] << 8) + $b[++$i];
        }
        else
        {
            trigger_error('jcode::invalid UTF-8 string', E_USER_WARNING);
            $utf8 = false;
        }
        return $utf8;
    }
}

/**
 * jcode_jis_euc
 * @access public
 */
class jcode_jis_euc extends jcode_converter
{
    function convert(&$str_JIS, $subst ='?')
    {
        $str_EUC = '';
        $mode = 0;
        $b = unpack('C*', $str_JIS);
        $n = count($b);

        for ($i = 1; $i <= $n; ++$i) {

            //Check escape sequence
            while ($b[$i] == 0x1B) {
                if (($b[$i+1] == 0x24 && $b[$i+2] == 0x42)
                    || ($b[$i+1] == 0x24 && $b[$i+2] == 0x40)) {
                    $mode = 1;
                } elseif (($b[$i+1] == 0x28 && $b[$i+2] == 0x49)) {
                    $mode = 2;
                } else {
                    $mode = 0;
                }
                $i += 3;
                if (!isset($b[$i])) break 2;
            }

            //Do convert
            if ($mode == 1) {
                $str_EUC .= chr($b[$i] + 0x80).chr($b[++$i] + 0x80);
            } elseif ($mode == 2) {
                $str_EUC .= chr(0x8E).chr($b[$i] + 0x80);
            } else {
                $str_EUC .= chr($b[$i]);
            }
        }

        return $str_EUC;
    }
}

/**
 * jcode_sjis_euc
 * @access public
 */
class jcode_sjis_euc extends jcode_converter
{
	function convert(&$str_SJIS, $subst ='?')
    {
		$b = unpack('C*', $str_SJIS);
    	$n = count($b);
    	$str_EUC = '';

    	for ($i = 1; $i <= $n; ++$i) {
    		$b1 = $b[$i];
    		if (0xA1 <= $b1 && $b1 <= 0xDF) {
    			$str_EUC .= chr(0x8E).chr($b1);
    		} elseif ($b1 >= 0x81) {
    			$b2 = $b[++$i];
    			$b1 <<= 1;
    			if ($b2 < 0x9F) {
    				if ($b1 < 0x13F) $b1 -= 0x61; else $b1 -= 0xE1;
    				if ($b2 > 0x7E)  $b2 += 0x60; else $b2 += 0x61;
    			} else {
    				if ($b1 < 0x13F) $b1 -= 0x60; else $b1 -= 0xE0;
    				$b2 += 0x02;
    			}
    			$str_EUC .= chr($b1).chr($b2);
    		} else {
    			$str_EUC .= chr($b1);
    		}
    	}

    	return $str_EUC;
    }
}

/**
 * jcode_utf8_euc
 * @access public
 */
class jcode_utf8_euc extends jcode_converter_utf8
{
    function jcode_utf8_euc()
    {
        $this->_table_utf8_jis();
    }

	function convert(&$str_UTF8, $subst ='?')
    {
    	$str_EUC = '';
    	$b = unpack('C*', $str_UTF8);
    	$n = count($b);

    	for ($i = 1; $i <= $n; $i++)
    	{
    	    $utf8 = $this->_get_one_char($b, $i);
    		if ($utf8 >= 0x80)
    		{ //Not ASCII
    			if (isset($this->table_utf8_jis[$utf8]))
    			{
    				$jis = $this->table_utf8_jis[$utf8];
    				if ($jis < 0xFF)
    				{ //Hankaku
    					$str_EUC .= chr(0x8E).chr($jis - 0x80);
    				}
    				else
    				{ //Zenkaku
    					$str_EUC .= chr(($jis >> 8) - 0x80).chr(($jis & 0xFF) - 0x80);
    				}
    			}
    			else
    			{ //Unknown
    				$str_EUC .= $subst;
    			}
    		}
    		else
    		{ //ASCII
    			//$str_EUC .= chr($b[$i]);
    			$str_EUC .= chr($utf8);
    		}
    	}

    	return $str_EUC;
    }
}

/**
 * jcode_euc_sjis
 * @access public
 */
class jcode_euc_sjis extends jcode_converter
{
    function convert(&$str_EUC, $subst ='?')
    {
        $str_SJIS = '';
        $b = unpack('C*', $str_EUC);
        $n = count($b);

        for ($i = 1; $i <= $n; ++$i) {
            $b1 = $b[$i];
            if ($b1 > 0x8E) {
                $b2 = $b[++$i];
                if ($b1 & 0x01) {
                    $b1 >>= 1;
                    if ($b1 < 0x6F) $b1 += 0x31; else $b1 += 0x71;
                    if ($b2 > 0xDF) $b2 -= 0x60; else $b2 -= 0x61;
                } else {
                    $b1 >>= 1;
                    if ($b1 <= 0x6F) $b1 += 0x30; else $b1 += 0x70;
                    $b2 -= 0x02;
                }
                $str_SJIS .= chr($b1).chr($b2);
            } elseif ($b1 == 0x8E) {
                $str_SJIS .= chr($b[++$i]);
            } else {
                $str_SJIS .= chr($b1);
            }
        }
    
        return $str_SJIS;
    }
}

/**
 * jcode_jis_sjis
 * @access public
 */
class jcode_jis_sjis extends jcode_converter
{
    function convert(&$str_JIS, $subst ='?')
    {
        $str_SJIS = '';
    	$mode = 0;
    	$b = unpack('C*', $str_JIS);
    	$n = count($b);
    
    	for ($i = 1; $i <= $n; ++$i) {
    
    		//Check escape sequence
    		while ($b[$i] == 0x1B) {
    			if (($b[$i+1] == 0x24 && $b[$i+2] == 0x42)
    				|| ($b[$i+1] == 0x24 && $b[$i+2] == 0x40)) {
    				$mode = 1;
    			} elseif (($b[$i+1] == 0x28 && $b[$i+2] == 0x49)) {
    				$mode = 2;
    			} else {
    				$mode = 0;
    			}
    			$i += 3;
    			if (!isset($b[$i])) break 2;
    		}
    
    		//Do convert
    		if ($mode == 1) {
    			$b1 = $b[$i];
    			$b2 = $b[++$i];
    			if ($b1 & 0x01) {
    				$b1 >>= 1;
    				if ($b1 < 0x2F) $b1 += 0x71; else $b1 -= 0x4F;
    				if ($b2 > 0x5F) $b2 += 0x20; else $b2 += 0x1F;
    			} else {
    				$b1 >>= 1;
    				if ($b1 <= 0x2F) $b1 += 0x70; else $b1 -= 0x50;
    				$b2 += 0x7E;
    			}
    			$str_SJIS .= chr($b1).chr($b2);
    		} elseif ($mode == 2) {
    			$str_SJIS .= chr($b[$i] + 0x80);
    		} else {
    			$str_SJIS .= chr($b[$i]);
    		}
    	}
    
    	return $str_SJIS;
    }
}

/**
 * jcode_utf8_sjis
 * @access public
 */
class jcode_utf8_sjis extends jcode_converter_utf8
{
    function jcode_utf8_sjis()
    {
        $this->_table_utf8_jis();
    }

    function convert(&$str_UTF8, $subst ='?')
    {
        $str_SJIS = '';
        $b = unpack('C*', $str_UTF8);
        $n = count($b);

        for ($i = 1; $i <= $n; ++$i)
        {
            $utf8 = $this->_get_one_char($b, $i);
            if ($utf8 >= 0x80)
            { //Not ASCII
                if (isset($this->table_utf8_jis[$utf8]))
                {
                    $jis = $this->table_utf8_jis[$utf8];
                    if ($jis < 0xFF)
                    { //Hankaku
                        $str_SJIS .= chr($jis + 0x80);
                    }
                    else
                    { //Zenkaku
                        $b1 = $jis >> 8;
                        $b2 = $jis & 0xFF;
                        if ($b1 & 0x01)
                        {
                            $b1 >>= 1;
                            if ($b1 < 0x2F) $b1 += 0x71; else $b1 -= 0x4F;
                            if ($b2 > 0x5F) $b2 += 0x20; else $b2 += 0x1F;
                        }
                        else
                        {
                            $b1 >>= 1;
                            if ($b1 <= 0x2F) $b1 += 0x70; else $b1 -= 0x50;
                            $b2 += 0x7E;
                        }
                        $str_SJIS .= chr($b1).chr($b2);
                    }
                }
                else
                { //Unknown
                    $str_SJIS .= $subst;
                }
            }
            else
            { //ASCII
                $str_SJIS .= chr($utf8);
            }
        }
    
        return $str_SJIS;
    }
}

/**
 * jcode_euc_jis
 * @access public
 */
class jcode_euc_jis extends jcode_converter
{
    function convert(&$str_EUC, $subst ='?')
    {
        $str_JIS = '';
        $mode = 0;
        $b = unpack('C*', $str_EUC);
        $n = count($b);

        //Escape sequence
        $ESC = array(chr(0x1B).chr(0x28).chr(0x42),
                 chr(0x1B).chr(0x24).chr(0x42),
                 chr(0x1B).chr(0x28).chr(0x49));

        for ($i = 1; $i <= $n; ++$i) {
            $b1 = $b[$i];
            if ($b1 == 0x8E) {
                if ($mode != 2) {
                    $mode = 2;
                    $str_JIS .= $ESC[$mode];
                }
                $str_JIS .= chr($b[++$i] - 0x80);
            } elseif ($b1 > 0x8E) {
                if ($mode != 1) {
                    $mode = 1;
                    $str_JIS .= $ESC[$mode];
                }
                $str_JIS .= chr($b1 - 0x80).chr($b[++$i] - 0x80);
            } else {
                if ($mode != 0) {
                    $mode = 0;
                    $str_JIS .= $ESC[$mode];
                }
                $str_JIS .= chr($b1);
            }
        }
        if ($mode != 0) $str_JIS .= $ESC[0];
    
        return $str_JIS;
    }
}

/**
 * jcode_sjis_jis
 * @access public
 */
class jcode_sjis_jis extends jcode_converter
{
    function convert(&$str_SJIS, $subst ='?')
    {
        $str_JIS = '';
    	$mode = 0;
    	$b = unpack('C*', $str_SJIS);
    	$n = count($b);
    
    	//Escape sequence
    	$ESC = array(chr(0x1B).chr(0x28).chr(0x42),
    		     chr(0x1B).chr(0x24).chr(0x42),
    		     chr(0x1B).chr(0x28).chr(0x49));
    
    	for ($i = 1; $i <= $n; ++$i) {
    		$b1 = $b[$i];
    		if (0xA1 <= $b1 && $b1 <= 0xDF) {
    			if ($mode != 2) {
    				$mode = 2;
    				$str_JIS .= $ESC[$mode];
    			}
    			$str_JIS .= chr($b1 - 0x80);
    		} elseif ($b1 >= 0x80) {
    			if ($mode != 1) {
    				$mode = 1;
    				$str_JIS .= $ESC[$mode];
    			}
    			$b2 = $b[++$i];
    			$b1 <<= 1;
    			if ($b2 < 0x9F) {
    				if ($b1 < 0x13F) $b1 -= 0xE1; else $b1 -= 0x61;
    				if ($b2 > 0x7E)  $b2 -= 0x20; else $b2 -= 0x1F;
    			} else {
    				if ($b1 < 0x13F) $b1 -= 0xE0; else $b1 -= 0x60;
    				$b2 -= 0x7E;
    			}
    			$str_JIS .= chr($b1).chr($b2);
    		} else {
    			if ($mode != 0) {
    				$mode = 0;
    				$str_JIS .= $ESC[$mode];
    			}
    			$str_JIS .= chr($b1);
    		}
    	}
    	if ($mode != 0) $str_JIS .= $ESC[0];
    
    	return $str_JIS;
    }
}

/**
 * jcode_utf8_jis
 * @access public
 */
class jcode_utf8_jis extends jcode_converter_utf8
{
    function jcode_utf8_jis()
    {
        $this->_table_utf8_jis();
    }

    function convert(&$str_UTF8, $subst ='?')
    {
        $str_JIS = '';
        $mode = 0;
        $b = unpack('C*', $str_UTF8);
        $n = count($b);

        //Escape sequence
        $ESC = array(chr(0x1B).chr(0x28).chr(0x42),
                 chr(0x1B).chr(0x24).chr(0x42),
                 chr(0x1B).chr(0x28).chr(0x49));

        $i = 1;
        while ($i <= $n)
        {
            $utf8 = $this->_get_one_char($b, $i);
            if ($utf8 >= 0x80)
            { //Not ASCII
                if (isset($this->table_utf8_jis[$utf8]))
                {
                    $jis = $this->table_utf8_jis[$utf8];
                    if ($jis < 0xFF)
                    { //Hankaku
                        if ($mode != 2)
                        {
                            $mode = 2;
                            $str_JIS .= $ESC[$mode];
                        }
                        $str_JIS .= chr($jis);
                    }
                    else
                    { //Zenkaku
                        if ($mode != 1)
                        {
                            $mode = 1;
                            $str_JIS .= $ESC[$mode];
                        }
                        $str_JIS .= chr($jis >> 8).chr($jis & 0xFF);
                    }
                }
                else
                { //Unknown
                    if ($mode != 0)
                    {
                        $mode = 0;
                        $str_JIS .= $ESC[$mode];
                    }
                    $str_JIS .= $subst;
                }
            }
            else
            { //ASCII
                if ($mode != 0)
                {
                    $mode = 0;
                    $str_JIS .= $ESC[$mode];
                }
                $str_JIS .= chr($utf8);
            }

            $i++;
        }
        if ($mode != 0) $str_JIS .= $ESC[0];
    
        return $str_JIS;
    }
}

/**
 * jcode_euc_utf8
 * @access public
 */
class jcode_euc_utf8 extends jcode_converter_utf8
{
    function jcode_euc_utf8()
    {
        $this->_table_jis_utf8();
    }

    function convert(&$str_EUC, $subst ='?')
    {
        $str_UTF8 = '';
        $b = unpack('C*', $str_EUC);
        $n = count($b);

        for ($i = 1; $i <= $n; ++$i) {
            if ($b[$i] == 0x8E) { //Hankaku
                $b2 = $b[++$i] - 0x40;
                $u2 = 0xBC | (($b2 >> 6) & 0x03);
                $u3 = 0x80 | ($b2 & 0x3F);
                $str_UTF8 .= chr(0xEF).chr($u2).chr($u3);
            } elseif ($b[$i] >= 0x80) { //Zenkaku
                $jis = (($b[$i] - 0x80) << 8) + ($b[++$i] - 0x80);
                if (isset($this->table_jis_utf8[$jis])) {
                    $utf8 = $this->table_jis_utf8[$jis];
                    if ($utf8 < 0xFFFF) {
                        $str_UTF8 .= chr($utf8 >> 8).chr($utf8);
                    } else {
                        $str_UTF8 .= chr($utf8 >> 16).chr($utf8 >> 8).chr($utf8);
                    }
                } else { //Unknown
                    $str_UTF8 .= $subst;
                }
            } else { //ASCII
                $str_UTF8 .= chr($b[$i]);
            }
        }
    
        return $str_UTF8;
    }
}

/**
 * jcode_sjis_utf8
 * @access public
 */
class jcode_sjis_utf8 extends jcode_converter_utf8
{
    function jcode_sjis_utf8()
    {
        $this->_table_jis_utf8();
    }

    function convert(&$str_SJIS, $subst ='?')
    {
    	$str_UTF8 = '';
    	$b = unpack('C*', $str_SJIS);
    	$n = count($b);

    	for ($i = 1; $i <= $n; ++$i) {
    		if (0xA1 <= $b[$i] && $b[$i] <= 0xDF) { //Hankaku
    			$b2 = $b[$i] - 0x40;
    			$u2 = 0xBC | (($b2 >> 6) & 0x03);
    			$u3 = 0x80 | ($b2 & 0x3F);
    			$str_UTF8 .= chr(0xEF).chr($u2).chr($u3);
    		} elseif ($b[$i] >= 0x80) { //Zenkaku
    			$b1 = $b[$i] << 1;
    			$b2 = $b[++$i];
    			if ($b2 < 0x9F) {
    				if ($b1 < 0x13F) $b1 -= 0xE1; else $b1 -= 0x61;
    				if ($b2 > 0x7E)  $b2 -= 0x20; else $b2 -= 0x1F;
    			} else {
    				if ($b1 < 0x13F) $b1 -= 0xE0; else $b1 -= 0x60;
    				$b2 -= 0x7E;
    			}
    			$b1 &= 0xFF;
    			$jis = ($b1 << 8) + $b2;
    			if (isset($this->table_jis_utf8[$jis])) {
    				$utf8 = $this->table_jis_utf8[$jis];
    				if ($utf8 < 0xFFFF) {
    					$str_UTF8 .= chr($utf8 >> 8).chr($utf8);
    				} else {
    					$str_UTF8 .= chr($utf8 >> 16).chr($utf8 >> 8).chr($utf8);
    				}
    			} else {
    				$str_UTF8 .= $subst; //Unknown
    			}
    		} else { //ASCII
    			$str_UTF8 .= chr($b[$i]);
    		}
    	}
    
    	return $str_UTF8;
    }
}

/**
 * jcode_jis_utf8
 * @access public
 */
class jcode_jis_utf8 extends jcode_converter_utf8
{
    function jcode_jis_utf8()
    {
        $this->_table_jis_utf8();
    }

    function convert(&$str_JIS, $subst ='?')
    {
    	$str_UTF8 = '';
    	$mode = 0;
    	$b = unpack('C*', $str_JIS);
    	$n = count($b);
    
    	for ($i = 1; $i <= $n; ++$i) {
    
    		//Check escape sequence
    		while ($b[$i] == 0x1B) {
    			if (($b[$i+1] == 0x24 && $b[$i+2] == 0x42)
    				|| ($b[$i+1] == 0x24 && $b[$i+2] == 0x40)) {
    				$mode = 1;
    			} elseif ($b[$i+1] == 0x28 && $b[$i+2] == 0x49) {
    				$mode = 2;
    			} else {
    				$mode = 0;
    			}
    			$i += 3;
    			if (!isset($b[$i])) break 2;
    		}
    
    		if ($mode == 1) { //Zenkaku
    			$jis = ($b[$i] << 8) + $b[++$i];
    			if (isset($this->table_jis_utf8[$jis])) {
    				$utf8 = $this->table_jis_utf8[$jis];
    				if ($utf8 < 0xFFFF) {
    					$str_UTF8 .= chr($utf8 >> 8).chr($utf8);
    				} else {
    					$str_UTF8 .= chr($utf8 >> 16).chr($utf8 >> 8).chr($utf8);
    				}
    			} else { //Unknown
    				$str_UTF8 .= $subst;
    			}
    		} elseif ($mode == 2) { //Hankaku
    			$b2 = $b[$i] + 0x40;
    			$u2 = 0xBC | (($b2 >> 6) & 0x03);
    			$u3 = 0x80 | ($b2 & 0x3F);
    			$str_UTF8 .= chr(0xEF).chr($u2).chr($u3);
    		} else { //ASCII
    			$str_UTF8 .= chr($b[$i]);
    		}
    	}
    
    	return $str_UTF8;
    }
}
?>