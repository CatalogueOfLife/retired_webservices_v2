<?php

class api_models_dao_KeyStore extends BaseDAO
{
    
    private $_domain;
    private $_email;
    private $_serviceKey;

    public function getDomain ()
    {
        return $this->_domain;
    }

    public function setDomain ($_domain)
    {
        $this->_domain = $_domain;
    }

    public function getEmail ()
    {
        return $this->_email;
    }

    public function setEmail ($_email)
    {
        $this->_email = $_email;
    }

    public function getServiceKey ()
    {
        return $this->_serviceKey;
    }

    public function setServiceKey ($_serviceKey)
    {
        $this->_serviceKey = $_serviceKey;
    }

}