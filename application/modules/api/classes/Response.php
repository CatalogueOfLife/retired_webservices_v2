<?php
/**
 * 
 * @author ayco
 *
 */
class api_classes_Response
{
    
    const ERR_LEVEL_OK = 0;
    const ERR_LEVEL_WARNING = 1;
    const ERR_LEVEL_ERROR = 2;
    
    public $errLevel = self::ERR_LEVEL_OK;
    public $errMessage = null;
    public $returnValue = null;
    
    public $method = null;
    public $params = null;
    public $stackTrace = null;

}