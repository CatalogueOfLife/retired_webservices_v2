<?php

class CommonController extends RestController
{
    public function queryAction ()
    {        
        $genus = $this->_getParam('genus');
        $species = $this->_getParam('species');
        $infraspecies = $this->_getParam('infraspecies');
        $version = $this->_getParam('version');
        $format = $this->_getParam('format');
        $key = $this->_getParam('key');        
        
        $search = new api_helpers_Search();
        $result = $search->selectCommonNames($genus, $species, $infraspecies, $version, $format, $key);
        
        $this->view->layout()->disableLayout();        
        $this->view->response = $result;        
    }
    
    public function init()
    {
        
    }
    
    public function indexAction ()
    {
        $this->_response->setHeader('Content-Type', 'text/html');
    }

    public function getAction ()
    {
    
    }
}
