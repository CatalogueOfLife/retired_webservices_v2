<?php

class SynonymsController extends RestController
{
    public function queryAction ()
    {        
        $genus = $this->_getParam('genus');
        $species = $this->_getParam('species');
        $infraspecies = $this->_getParam('infraspecies');
        $version = $this->_getParam('version');
        $format = $this->_getParam('format');        
        
        $search = new api_helpers_Search();
        $result = $search->selectSynonyms($genus, $species, $infraspecies, $version, $format);
        
        $this->view->layout()->disableLayout();        
        $this->view->response = $result;        
    }
    
    public function init()
    {
        
    }
    
    public function indexAction ()
    {
        
    }

    public function getAction ()
    {
    
    }
}
