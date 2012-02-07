<?php

abstract class RestController extends Zend_Rest_Controller
{
    protected $_format;

    public function preDispatch ()
    {
        
        $key = $this->_getParam('key', null);
        if ($key === null) {
            $this->_response->setHttpResponseCode(403);
            $this->_response->setBody('You must provide a "key" parameter in your request to gain access to this service. Go to /index.php to request a key.');
            $this->_response->sendResponse();
            exit();
        }
        
        $domain = $_SERVER['REMOTE_ADDR'];
        $keyStore = new api_models_dao_KeyStore();
        
        if (!$keyStore->load($domain)) {
            $this->_response->setHttpResponseCode(403);
            $this->_response->setBody('The domain (' . $domain . ') from which the web service is called has not been registered yet. Go to /index.php to request a key for your domain.');
            $this->_response->sendResponse();
            exit();
        }
        
        if ($keyStore->getServiceKey() != $key) {
            $this->_response->setHttpResponseCode(403);
            $this->_response->setBody('Invalid "key" parameter in request (' . $key . '). Go to /index.php to request a (new) key for your domain.');
            $this->_response->sendResponse();
            exit();
        }
        
        $this->_format = strtoupper($this->_param('format', 'XML', 'XML'));
        switch ($this->_format) {
            case 'JSON':
                $this->_response->setHeader('Content-Type', 'text/json');
                break;
            case 'XML':
                $this->_response->setHeader('Content-Type', 'text/xml');
                break;
            case 'DWCA': /* TODO: figure out content type */;
                break;
            default:
                $this->_response->setHttpResponseCode(403);
                $this->_response->sendResponse();
                exit();
        }
    }

    public function init ()
    {
        $this->_helper->viewRenderer->setNoRender(true);
    }

    public function deleteAction ()
    {
        $this->_response->setHttpResponseCode(403);
    }

    public function postAction ()
    {
        $this->_response->setHttpResponseCode(403);
    }

    public function putAction ()
    {
        $this->_response->setHttpResponseCode(403);
    }

    /**
     * Get the value of a request parameter.
     * 
     * This method is more explicit in what it returns than
     * $this->_request->getParam($key). Most notably, it returns null
     * when the parameter evaluates to an empty string. This is because,
     * if a user has left a text input or textarea blank, you will
     * probably want to set the corresponding database column to null,
     * rather than to an empty string.
     * 
     * [1] If the parameter evaluates to null or an empty string, this
     * method returns null
     * [2] If the parameter is not present at all, it return false
     * [3] Otherwise it returns the value of the parameter
     * 
     * @param $name string The name of the request parameter
     * @param $whenAbsent mixed The value to return when there is no such parameter
     * @param $whenNull mixed The value to return when the paramter evaluates to null or ''
     */
    protected function _param ($name, $whenAbsent = false, $whenNull = null)
    {
        $source = $this->_request->getParams();
        if (array_key_exists($name, $source)) {
            $value = $source[$name];
            if ($value === '' || $value === null) {
                return $whenNull;
            }
            return $value;
        }
        return $whenAbsent;
    }
}
