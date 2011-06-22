<?php

class ETI_Util_Date
{

    /**
     * Tells you how many hours, minutes and seconds a given time interval
     * (expressend in seconds) took. By default an array containing the hours,
     * minutes and seconds (in that order) is returned. But you can specify
     * the result to be returned as a string like "01:12:33"
     * 
     * @param int $seconds
     * @params string $asString
     */
    public static function hoursMinutesSeconds ($seconds, $asString=false)
    {
        $hours = (int) floor($seconds / 3600);
        $rest = $hours === 0 ? $seconds : ($seconds % $hours);
        $minutes = (int) floor($rest / 60);
        $seconds = $seconds - ($hours * 3600) - ($minutes * 60);
        if($asString) {
            $hours = ETI_Util_String::zeroPad($hours);
            $minutes = ETI_Util_String::zeroPad($minutes);
            $seconds = ETI_Util_String::zeroPad($seconds);
            return "$hours:$minutes:$seconds";
        }
        return array($hours,$minutes,$seconds);
    }

}