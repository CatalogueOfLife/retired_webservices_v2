<?php
/**
 * 
 * @author Dennis Seijts
 * 
 */
class SpeciesEstimateController extends RestController
{

	private $_flush;
	private $_daoSpeciesEstimate;
	
    public function init ()
    {
    
    }

    public function indexAction ()
    {
    
    }

    public function getAction ()
    {
    
    }
    
    public function flushAction () {
        $flushTime = time();
        $response = new api_classes_Response();
        $arrs = Zend_Json::decode($this->_param('flush'));
        try {
            $sdb = new api_models_dao_SpeciesEstimate();
            foreach ($arrs as $arr) {
	            $sdb->initialize($arr);
	            $sdb->setFLushTime($flushTime);
	            $sdb->save();
            }
        }
        catch (Exception $e) {
            $sdb = new api_models_dao_SpeciesEstimate();
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
    
}

