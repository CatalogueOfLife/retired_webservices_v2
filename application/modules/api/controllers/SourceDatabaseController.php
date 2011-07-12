<?php
/**
 * 
 * @author ayco
 * 
 * A set of web services dedicated to CRUD source databases in the base schema
 * database. These web services ony work against base schema 1.7 or higher. Specifically,
 * you need the __import_source_database table to be present in the base schema
 *
 */
class SourceDatabaseController extends RestController
{

    public function init ()
    {
    
    }

    public function indexAction ()
    {
    
    }

    public function getAction ()
    {
    
    }

    public function saveSourceDatabaseAction ()
    {
        $sdb = new api_models_dao_SourceDatabase();
        $sdb->initialize($this->_request->getParams(), true, 'strip_tags');
        $response = new api_classes_Response();
        try {
            if ($sdb->exists('name')) {
                throw new Exception("A source database with name \"{$sdb->getName()}\" already exists");
            }
            if ($sdb->exists('abbreviatedName')) {
                throw new Exception("A source database with abbreviated name \"{$sdb->getName()}\" already exists");
            }
            $sdb->save();
            $response->returnValue = $sdb->getId();
        }
        catch (Exception $e) {
            $response->errLevel = api_classes_Response::ERR_LEVEL_ERROR;
            $response->errMessage = get_class($e) . '. ' . $e->getMessage();
            if ($this->_param('_debug') === true) {
                $response->method = __METHOD__;
                $response->params = $this->_request->getParams();
                $response->stackTrace = $e->getTrace();
            }
        }
        $this->_response->setHeader('Content-Type', 'application/json');
        $this->_response->setBody(Zend_Json::encode($response));
        $this->_response->sendResponse();
        exit();
    }

    public function updateSourceDatabaseAction ()
    {
        $response = new api_classes_Response();
        $sdb = new api_models_dao_SourceDatabase();
        try {
            $sdb->setName($this->_param('name', null, null));
            if (($id = $sdb->exists('name')) === false) {
                throw new Exception("No source database with name \"{$sdb->getName()}\" exists");
            }
            $sdb->load($id);
            $sdb->initialize($this->_request->getParams());
            $sdb->update();
        }
        catch (Exception $e) {
            $response->errLevel = api_classes_Response::ERR_LEVEL_ERROR;
            $response->errMessage = get_class($e) . '. ' . $e->getMessage();
            if ($this->_param('_debug') === true) {
                $response->method = __METHOD__;
                $response->params = $this->_request->getParams();
                $response->stackTrace = $e->getTrace();
            }
        }
        $this->_response->setHeader('Content-Type', 'application/json');
        $this->_response->setBody(Zend_Json::encode($response));
        $this->_response->sendResponse();
        exit();
    }

    public function deleteSourceDatabaseAction ()
    {
        $sdb = new api_models_dao_SourceDatabase();
        $sdb->initialize($this->_request->getParams(), true, 'strip_tags');
        $response = new api_classes_Response();
        try {
            if (($id = $sdb->exists('name')) === false) {
                $response->errLevel = api_classes_Response::ERR_LEVEL_WARNING;
                $response->errMessage = "No source database with name \"{$sdb->getName()}\" exists";
                $this->_response->setHeader('Content-Type', 'application/json');
                $this->_response->setBody(Zend_Json::encode($response));
                $this->_response->sendResponse();
                exit();
            }
            $sdb->delete($id);
        }
        catch (Exception $e) {
            $response->errLevel = api_classes_Response::ERR_LEVEL_ERROR;
            $response->errMessage = get_class($e) . '. ' . $e->getMessage();
            if ($this->_param('_debug') === true) {
                $response->method = __METHOD__;
                $response->params = $this->_request->getParams();
                $response->stackTrace = $e->getTrace();
            }
        }
        $this->_response->setHeader('Content-Type', 'application/json');
        $this->_response->setBody(Zend_Json::encode($response));
        $this->_response->sendResponse();
        exit();
    }

}

