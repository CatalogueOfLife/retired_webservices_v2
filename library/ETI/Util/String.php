<?php

class ETI_Util_String
{

    /**
     * Zero pads a string up to the given length
     * 
     * @param string $string
     * @param int $length
     */
    public static function zeroPad ($string, $length = 2)
    {
        return str_pad($string, $length, '0', STR_PAD_LEFT);
    }

    public static function ellipse ($string, $maxLength)
    {
        if (strlen($string) < ($maxLength - 4)) {
            return $string;
        }
        return (substr($string, 0, ($maxLength - 4)) . ' ...');
    }

}
