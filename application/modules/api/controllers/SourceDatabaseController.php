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
        $sdb = new api_models_dao_SourceDatabase();
        $this->_response->setHeader('Content-Type', 'application/json');
        $this->_response->setBody(rawurlencode((Zend_Json::encode($sdb->loadMultiple1()))));
        $this->_response->sendResponse();
        exit();
    }

    public function flushAction ()
    {
        $flushTime = time();
        $response = new api_classes_Response();
        $arrs = Zend_Json::decode(str_replace('\\','',$this->_param('flush')));
        try {
            foreach ($arrs as $arr) {
                $sdb = new api_models_dao_SourceDatabase();
                $sdb->initialize($arr);
                $sdb->setFLushTime($flushTime);
                $sdb->save();
            }
        }
        catch (Exception $e) {
            $sdb = new api_models_dao_SourceDatabase();
            $sdb->setFLushTime($flushTime);
            $sdb->deleteByKey('flushTime');
            $response->errLevel = api_classes_Response::ERR_LEVEL_ERROR;
            $response->errMessage = get_class($e) . '. ' . $e->getMessage();
            if ($this->_param('_debug') === 'true') {
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

    /**
     * Validates the taxonomic coverage as entered manually in the Metadatabase.
     * 
     * Example:
     * 
     * http://localhost/webservices_v2/sourcedatabase/validate-taxonomic-coverage/? ... (to be specified)
     * 
     */
    public function validateTaxonomicCoverageAction ()
    {
        $response = new api_classes_Response();
        $response->returnValue = 1;
        $this->_response->setHeader('Content-Type', 'application/json');
        $this->_response->setBody(Zend_Json::encode($response));
        $this->_response->sendResponse();
        exit();
    }

 }

