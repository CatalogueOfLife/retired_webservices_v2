<?php
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    
    /**
     * @var Bootstrap
     */
    private static $_instance = null;
    
    /**
     * @var Zend_Log
     */
    private $_logger;
    
    /**
     * @var Zend_Session_Namespace
     */
    private $_session;
    
    /**
     * @var ETI_Zend_Navigation_Container
     */
    private $_menu;
    
    /**
     * @var ETI_Zend_Controller_Plugin_Messenger
     */
    private $_messenger;

    /**
     * Convenience method to get a reference to The Bootstrap
     * singleton when not in an action.
     * 
     * @return Bootstrap
     */
    public static function instance ()
    {
        if (self::$_instance === null) {
            self::$_instance = Zend_Controller_Front::getInstance()->getParam('bootstrap');
        }
        return self::$_instance;
    }

    public function run ()
    {
        // ...
        return parent::run();
    }

    public function getOption ($key)
    {
        $chunks = explode('.', $key);
        if (count($chunks) === 1) {
            return parent::getOption($key);
        }
        $options = parent::getOptions();
        foreach ($chunks as $chunk) {
            if (array_key_exists($chunk, $options)) {
                $options = $options[$chunk];
            }
            else {
                $options = null;
                break;
            }
        }
        return $options;
    }

    /**
     * @return Zend_Log
     */
    public function getLogger ()
    {
        return $this->_logger;
    }

    /**
     * @return Zend_Session_Namespace
     */
    public function getSession ()
    {
        return $this->_session;
    }

    /**
     * @return ETI_Zend_Navigation_Container
     */
    public function getMenu ()
    {
        return $this->_menu;
    }

    /**
     * @return ETI_Zend_Controller_Plugin_Messenger
     */
    public function getMessenger ()
    {
        return $this->_messenger;
    }

    /**
     * @return Zend_Loader_Autoloader
     */
    protected function _initAutoloader ()
    {
        require_once 'Zend/Loader/Autoloader/Interface.php';
        require_once 'ETI/Zend/Loader/SimpleClassAutoLoader.php';
        $loader = new ETI_Zend_Loader_SimpleClassAutoLoader();
        $autoLoader = Zend_Loader_Autoloader::getInstance();
        $autoLoader->pushAutoloader($loader);
        $autoLoader->setAutoloaders(array($loader));
        $autoLoader->setDefaultAutoloader(array($loader,'autoload'));
        return $autoLoader;
    }

    /**
     * @return Zend_Session_Namespace
     */
    public function _initSession ()
    {
        $this->_session = new Zend_Session_Namespace('qaw');
        return $this->_session;
    }

    /**
     * @return Zend_View
     */
    protected function _initView ()
    {
        
        // need to bootstrap this one first b/c we need the baseUrl
        // to be set before using the BaseUrl view helper.
        $this->bootstrap('frontcontroller');
        
        $view = new Zend_View();
        $view->doctype('XHTML1_STRICT');
        $view->headTitle($this->getOption('appInfo.htmlTitle'))->setSeparator(' - ');
        
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');
        $viewRenderer->setView($view);
        
        $view->addHelperPath(APPLICATION_PATH . '/helpers/view');
        
        $this->bootstrap('menu');
        $menu = $this->getResource('menu');
        $view->getHelper('navigation')->setContainer($menu);
        $view->getHelper('navigation')->menu()->setUlClass('sf-menu');
        
        return $view;
    }

    /**
     * @return Zend_View_Helper_HeadScript
     */
    protected function _initJavascript ()
    {
        $helper = $this->_getView()->getHelper('HeadScript');
        $helper->appendFile($this->_baseUrl('js/jquery-1.4.3.min.js'));
        $helper->appendFile($this->_baseUrl('js/jquery-ui-1.8.6.custom.min.js'));
        $helper->appendFile($this->_baseUrl('js/superfish.js'));
        $helper->appendFile($this->_baseUrl('js/hoverIntent.js'));
        $helper->appendFile($this->_baseUrl('js/common.js'));
        $helper->appendFile($this->_baseUrl('js/SimpleDialog.js'));
        return $helper;
    }

    /**
     * @return Zend_View_Helper_HeadLink
     */
    protected function _initCss ()
    {
        $helper = $this->_getView()->getHelper('HeadLink');
        $helper->appendStylesheet('http://yui.yahooapis.com/3.1.1/build/cssreset/reset-min.css', 'all');
        $helper->appendStylesheet('http://yui.yahooapis.com/3.1.1/build/cssfonts/fonts-min.css', 'all');
        $helper->appendStylesheet('http://yui.yahooapis.com/3.1.1/build/cssbase/base-min.css', 'all');
        $helper->appendStylesheet($this->_baseUrl('css/superfish.css'), 'all');
        $helper->appendStylesheet($this->_baseUrl('css/smoothness/jquery-ui-1.8.6.custom.css'), 'all');
        $helper->appendStylesheet($this->_baseUrl('css/common.css'), 'all');
        return $helper;
    }

    /**
     * @return ETI_Zend_Navigation_Container
     */
    protected function _initMenu ()
    {
        $config = new Zend_Config_Xml(APPLICATION_PATH . '/configs/menu.xml', 'menu');
        $this->_menu = new ETI_Zend_Navigation_Container($config);
        return $this->_menu;
    }

    /**
     * @return Zend_Log
     */
    protected function _initLogging ()
    {
        $writer = new Zend_Log_Writer_Firebug();
        $this->_logger = new Zend_Log($writer);
        return $this->_logger;
    }

    /**
     * @return ETI_Zend_Controller_Plugin_Messenger
     */
    protected function _initMessenger ()
    {
        $this->bootstrap('frontcontroller');
        $this->_messenger = new ETI_Zend_Controller_Plugin_Messenger();
        Zend_Controller_Front::getInstance()->registerPlugin($this->_messenger);
        return $this->_messenger;
    }

    private function _baseUrl ($file = null)
    {
        return $this->_getView()->getHelper('BaseUrl')->baseUrl($file);
    }

    /**
     * @return Zend_View
     */
    private function _getView ()
    {
        $this->bootstrap('view');
        return $this->getResource('view');
    }
    
}

