<?php
/**
* @version $Id: unicode.php,v 1.2 2006/02/26 13:20:44 harryf Exp $
* Tools for conversion between UTF-8 and unicode
* The Original Code is Mozilla Communicator client code.
* The Initial Developer of the Original Code is
* Netscape Communications Corporation.
* Portions created by the Initial Developer are Copyright (C) 1998
* the Initial Developer. All Rights Reserved.
* Ported to PHP by Henri Sivonen (http://hsivonen.iki.fi)
* Slight modifications to fit with phputf8 library by Harry Fuecks (hfuecks gmail com)
* @see http://lxr.mozilla.org/seamonkey/source/intl/uconv/src/nsUTF8ToUnicode.cpp
* @see http://lxr.mozilla.org/seamonkey/source/intl/uconv/src/nsUnicodeToUTF8.cpp
* @see http://hsivonen.iki.fi/php-utf8/
* @package utf8
* @subpackage unicode
*/

//--------------------------------------------------------------------
/**
* Takes an UTF-8 string and returns an array of ints representing the 
* Unicode characters. Astral planes are supported ie. the ints in the
* output can be > 0xFFFF. Occurrances of the BOM are ignored. Surrogates
* are not allowed.
* Returns false if the input string isn't a valid UTF-8 octet sequence
* and raises a PHP error at level E_USER_WARNING
* Note: this function has been modified slightly in this library to
* trigger errors on encountering bad bytes
* @author <hsivonen@iki.fi>
* @param string UTF-8 encoded string
* @return mixed array of unicode code points or FALSE if UTF-8 invalid
* @see utf8_from_unicode
* @see http://hsivonen.iki.fi/php-utf8/
* @package utf8
* @subpackage unicode
*/
function utf8_to_unicode($str) {
    $mState = 0;    
    $mUcs4  = 0;    
    $mBytes = 1;    
    
    $out = [];
    $len = strlen($str);
    
    for ($i = 0; $i < $len; $i++) {
        $in = ord($str[$i]);
        
        if ($mState == 0) {
            if (0 == (0x80 & $in)) {
                $out[] = $in;
                $mBytes = 1;
            } elseif (0xC0 == (0xE0 & $in)) {
                $mUcs4 = ($in & 0x1F) << 6;
                $mState = 1;
                $mBytes = 2;
            } elseif (0xE0 == (0xF0 & $in)) {
                $mUcs4 = ($in & 0x0F) << 12;
                $mState = 2;
                $mBytes = 3;
            } elseif (0xF0 == (0xF8 & $in)) {
                $mUcs4 = ($in & 0x07) << 18;
                $mState = 3;
                $mBytes = 4;
            } else {
                trigger_error('utf8_to_unicode: Illegal byte '.$i, E_USER_WARNING);
                return FALSE;
            }
        } else {
            if (0x80 == (0xC0 & $in)) {
                $shift = ($mState - 1) * 6;
                $mUcs4 |= ($in & 0x3F) << $shift;
                
                if (--$mState == 0) {
                    if (
                        ($mBytes == 2 && $mUcs4 < 0x80) ||
                        ($mBytes == 3 && $mUcs4 < 0x800) ||
                        ($mBytes == 4 && $mUcs4 < 0x10000) ||
                        ($mUcs4 > 0x10FFFF) ||
                        ($mUcs4 >= 0xD800 && $mUcs4 <= 0xDFFF)
                    ) {
                        trigger_error('utf8_to_unicode: Invalid sequence at byte '.$i, E_USER_WARNING);
                        return FALSE;
                    }
                    
                    if ($mUcs4 != 0xFEFF) {
                        $out[] = $mUcs4;
                    }
                    
                    $mState = 0;
                    $mUcs4  = 0;
                    $mBytes = 1;
                }
            } else {
                trigger_error('utf8_to_unicode: Incomplete sequence at byte '.$i, E_USER_WARNING);
                return FALSE;
            }
        }
    }
    return $out;
}

function utf8_from_unicode($arr) {
    $result = '';
    foreach ($arr as $k => $codepoint) {
        if ($codepoint >= 0 && $codepoint <= 0x7F) {
            $result .= chr($codepoint);
        } elseif ($codepoint <= 0x7FF) {
            $result .= chr(0xC0 | ($codepoint >> 6));
            $result .= chr(0x80 | ($codepoint & 0x3F));
        } elseif ($codepoint == 0xFEFF) {
            continue;
        } elseif ($codepoint >= 0xD800 && $codepoint <= 0xDFFF) {
            trigger_error('utf8_from_unicode: Illegal surrogate at index: '.$k, E_USER_WARNING);
            return FALSE;
        } elseif ($codepoint <= 0xFFFF) {
            $result .= chr(0xE0 | ($codepoint >> 12));
            $result .= chr(0x80 | (($codepoint >> 6) & 0x3F));
            $result .= chr(0x80 | ($codepoint & 0x3F));
        } elseif ($codepoint <= 0x10FFFF) {
            $result .= chr(0xF0 | ($codepoint >> 18));
            $result .= chr(0x80 | (($codepoint >> 12) & 0x3F));
            $result .= chr(0x80 | (($codepoint >> 6) & 0x3F));
            $result .= chr(0x80 | ($codepoint & 0x3F));
        } else {
            trigger_error('utf8_from_unicode: Codepoint out of range at index: '.$k, E_USER_WARNING);
            return FALSE;
        }
    }
    return $result;
}
