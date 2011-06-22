<?php

class BaseschemaTable extends Zend_Db_Table_Abstract
{
    public function __construct($config = array())
    {
        $resource = Bootstrap::instance()->getPluginResource('multidb');
        $this->_db = $resource->getDb('baseschema');
        parent::__construct($config);
    }
}