<?php
/**
 * FlashMessenger helper
 *
 * @uses viewHelper Zend_View_Helper
 */
class Zend_View_Helper_Messages
{
    
    /**
     * @var Zend_View_Interface 
     */
    public $view;

    public function messages ()
    {
        $pattern = '<div class="%s message">%s</div>';
        $msgs = Bootstrap::instance()->getMessenger()->getAll();
        $html = array();
        foreach ($msgs as $msg) {
            $level = $msg[0];
            switch ($level) {
                case ETI_Zend_Controller_Plugin_Messenger::ERROR:
                    $html[] = sprintf($pattern, 'error', $msg[1]);
                    break;
                case ETI_Zend_Controller_Plugin_Messenger::WARNING:
                    $html[] = sprintf($pattern, 'warning', $msg[1]);
                    break;
                default:
                    $html[] = sprintf($pattern, 'info', $msg[1]);
            }
        }
        return implode('',$html);
    }

    /**
     * Sets the view field 
     * @param $view Zend_View_Interface
     */
    public function setView (Zend_View_Interface $view)
    {
        $this->view = $view;
    }
}

