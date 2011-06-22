<?php
class BaseController extends Zend_Controller_Action
{
    
    /**
     * @var ETI_Zend_Controller_Plugin_Messenger
     */
    protected $_messenger = null;
    
    /**
     * @var Zend_Controller_Action_Helper_Redirector
     */
    protected $_redirector = null;
    
    /**
     * @var ETI_Zend_Navigation_Container
     */
    protected $_menu = null;

    public function init ()
    {
        $this->_messenger = Bootstrap::instance()->getMessenger();
        $this->_menu = Bootstrap::instance()->getMenu();
        $this->_redirector = $this->_helper->getHelper('Redirector');
        $projectId = $this->_request->getParam('projectId');
        if (!empty($projectId)) {
            $this->_setGlobalProjectId($projectId);
        }
        if ($this->_request()->getActionName() == 'show') {
            $this->_activateMenuItem('Save');
            $this->_activateMenuItem('Save As ...');
        }
    }

    /**
     * Get the project ID that is stored in the $_SESSION array
     */
    protected function _getGlobalProjectId ()
    {
        $value = Bootstrap::instance()->getSession()->projectId;
        return empty($value) ? null : $value;
    }

    /**
     * Store a project ID in the $_SESSION array
     * 
     * @param mixed $value
     */
    protected function _setGlobalProjectId ($value)
    {
        Bootstrap::instance()->getSession()->projectId = $value;
    }

    /**
     * This method provides a more explicit contract for getting
     * request variables than $this->_request->getParam($key).
     * 
     * [1] If the key is present, but its value is null OR an empty string, this method returns null
     * [2] If the key is not present at all, it return false (or the value you specify for $default)
     * [3] Otherwise it returns the value of the parameter
     * 
     * @param $key string The name of the request parameter
     * @param $default mixed The value to return when there is no such key
     */
    protected function _param ($key, $default = false)
    {
        $source = $this->_request->getParams();
        if (array_key_exists($key, $source)) {
            $value = $source[$key];
            if ((is_string($value) && strlen($value) === 0) || ($value === null)) {
                return null;
            }
            return $value;
        }
        return $default;
    }

    /**
     * This method is mainly there to cast the request object down
     * to its actual type (Zend_Controller_Request_Http) so as to
     * ease in autocompletion.
     * 
     * @return Zend_Controller_Request_Http
     */
    protected function _request ()
    {
        return $this->_request;
    }

    /**
     * Redirect to the show action in the current controller.
     * Called at the end of a save action, so that a page refresh does
     * not result in another form submit.
     * @param ETI_Zend_Db_DAO $dao
     */
    protected function _redirectToShowAction (ETI_Zend_Db_DAO $dao)
    {
        $this->_redirector->gotoSimple('show', $this->_request()->getControllerName(), $this->_request()->getModuleName(), array(
            'id' => $dao->getPrimaryKey()
        ));
    }

    /**
     * Redirect to the index action in the current controller.
     * Called at the end of a delete action, and in catch blocks.
     */
    protected function _redirectToIndexAction (array $params = array())
    {
        $this->_redirector->gotoSimple('index', $this->_request()->getControllerName(), $this->_request()->getModuleName(), $params);
    }

    protected function _activateMenuItem ($label, $uri = null)
    {
        $this->_menu->activatePage($label, 'activated', $uri);
    }

    protected function _deactivateMenuItem ($label)
    {
        $this->_menu->deactivatePage($label, 'deactivated');
    }

    protected function _handleException (Exception $e, $gotoAction = null, $dump = null)
    {
        if (APPLICATION_ENV == 'development') {
            echo '<pre>';
            echo $this->_strip($e->getFile() . '(' . $e->getLine() . '): ' . $e->getMessage() . "\n\n");
            $trace = explode("\n", $e->getTraceAsString());
            foreach ($trace as $line) {
                echo $this->_strip($line) . "\n";
            }
            if ($dump !== null) {
                print_r($dump);
            }
            echo '</pre>';
            exit();
        }
        else {
            if ($gotoAction === null) {
                $gotoAction = 'index';
            }
            $this->_messenger->error($e->getMessage());
            $req = $this->_request();
            $this->_redirector->gotoSimple($gotoAction, $req->getControllerName(), $req->getModuleName());
        }
    }

    /**
     * Convenience method - shortcut to Zend_Debug::dump
     * 
     * @param mixed $value
     * @param bool $exit
     */
    protected function _dump ($value, $exit = false)
    {
        Zend_Debug::dump($value);
        if ($exit === true) {
            exit();
        }
    }

    private function _strip ($line)
    {
        return str_replace(dirname(APPLICATION_PATH), '', $line);
    }

}

