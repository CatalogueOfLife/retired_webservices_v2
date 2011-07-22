<?php

try {
    
    // Define path to application directory
    defined('APPLICATION_PATH')
        || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
    
    // Define application environment
    defined('APPLICATION_ENV')
        || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));
    
    set_include_path(implode(PATH_SEPARATOR, array(
        realpath(APPLICATION_PATH . '/../library'), 
        APPLICATION_PATH . '/modules', 
        APPLICATION_PATH . '/classes', 
    )));

    //Detect magic quotes and remove slashes from cookies and requests
    if(get_magic_quotes_gpc()) {
	    $process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
	    while (list($key, $val) = each($process)) {
	        foreach ($val as $k => $v) {
	            unset($process[$key][$k]);
	            if (is_array($v)) {
	                $process[$key][stripslashes($k)] = $v;
	                $process[] = &$process[$key][stripslashes($k)];
	            } else {
	                $process[$key][stripslashes($k)] = stripslashes($v);
	            }
	        }
	    }
	    unset($process);
    }
    
    require_once 'Zend/Application.php';
/*     
	require_once 'Zend/Config/Xml.php';
	require_once 'Zend/Config/Ini.php';    

	// Load configuration
	$config = new Zend_Config_Xml(
	    APPLICATION_PATH . '/configs/application.xml',
	    APPLICATION_ENV,
	    true
	);
	try {
	    $config->merge(
	        new Zend_Config_Ini(
	            APPLICATION_PATH . '/configs/config.ini',
	            APPLICATION_ENV
	        )
	    );
	}
	// There is no config file or the section APPLICATION_ENV doe not exist
	catch(Zend_Config_Exception $e) {
	    exit(
	        '<b>The configuration of the application is not valid</b><br/>' .
	        'Please make sure that the proper environment [' . APPLICATION_ENV . 
	        '] has been defined in the configuration file'
	    );
	}
*/    
    $application = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.xml');

    // Store config
    //Zend_Registry::set('config', $config);
    
    $application->bootstrap()->run();
    
}

catch (Exception $e) {
    printf("<pre>%s\n%s (%s): %s</pre>", $e->getTraceAsString(), $e->getFile(), $e->getLine(), $e->getMessage());
}

