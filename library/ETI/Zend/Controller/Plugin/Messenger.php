<?php

/**
 * A simplified version of Zend's own FlashMessenger action helper.
 * 
 * Its main advantage is that it treats messages that you add in the
 * current request just like messages from previous requests. Therefore
 * you don't have to think about merging them.
 * 
 * @author ayco
 *
 */
class ETI_Zend_Controller_Plugin_Messenger extends Zend_Controller_Plugin_Abstract
{
    
    const INFO = 0;
    const WARNING = 1;
    const ERROR = 2;
    
    private $_session = null;

    function dispatchLoopStartup (Zend_Controller_Request_Abstract $request)
    {
        $this->_session = new Zend_Session_Namespace('messenger');
        if(!isset($this->_session->messages)) {
            $this->_session->messages = array();
        }
    }

    /**
     * Add a message to the message queue
     * 
     */
    public function message ($message, $level = self::INFO)
    {
        $this->_session->messages[] = array(
            $level, 
            $message
        );
    }

    public function warning ($message)
    {
        $this->message($message, self::WARNING);
    }

    public function error ($message)
    {
        $this->message($message, self::ERROR);
    }

    /**
     * Consume a message from the message queue.
     */
    public function get ()
    {
        if (count($this->_session->messages) === 0) {
            return null;
        }
        return array_shift($this->_session->messages);
    }

    /**
     * Consume all messages.
     */
    public function getAll ()
    {
        $all = $this->_session->messages;
        $this->_session->messages = array();
        return $all;
    }

    /**
     * Get the first (oldest) message in the message queue without consuming it.
     * Mainly for debug purposes.
     */
    public function peek ()
    {
        if (count($this->_session->messages) === 0) {
            return null;
        }
        return $this->_session->messages[0];
    }

    /**
     * Get all messages in the message queue wihtout consuming them.
     * Mainly for debug purposes.
     */
    public function peekAll ()
    {
        return $this->_session->messages;
    }

}
