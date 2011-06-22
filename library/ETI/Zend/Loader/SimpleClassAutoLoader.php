<?php

class ETI_Zend_Loader_SimpleClassAutoLoader implements Zend_Loader_Autoloader_Interface
{
    
    private $_path;

    public function __construct ($path = null)
    {
        if ($path === null) {
            $this->_path = explode(PATH_SEPARATOR, get_include_path());
        }
        else if (!is_array($path)) {
            $this->_path = explode(PATH_SEPARATOR, (string) $path);
        }
        else {
            $this->_path = $path;
        }
    }

    public function addPath ($directory, $prefix = false)
    {
        if ($prefix) {
            array_unshift($this->_path, $directory);
        }
        else {
            $this->_path[] = $directory;
        }
    }

    public function autoload ($class)
    {
        $file = str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';
        if (($path = $this->_find($file)) !== null) {
            include $path;
        }
    }

    private function _find ($file)
    {
        foreach ($this->_path as $dir) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_file($path)) {
                return $path;
            }
        }
        return null;
    }

}