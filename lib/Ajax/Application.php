<?php

/**
 * Locker AJAX application API.
 *
 * This file defines the AJAX actions provided by this module. The primary
 * AJAX endpoint is represented by horde/services/ajax.php but that handler
 * will call the module specific actions defined in this class.
 *
 * @author   Alfonso Marin <almarin@um.es>
 * @category Horde
 * @license  http://www.horde.org/licenses/gpl GPL
 * @package  Locker
 */
class Locker_Ajax_Application extends Horde_Core_Ajax_Application
{
    /**
     * Application specific initialization tasks should be done in here.
     */
    protected function _init()
    {
        // This adds the 'noop' action to the current application.
        $this->addHandler('Locker_Ajax_Application_Handler');
    }

    public function doAction(){
    	parent::doAction();
        $stack = $GLOBALS['notification']->notify(array(
            'listeners' => 'status',
            'raw' => true
        ));
        if (!empty($stack)) {
            $this->data->msgs = $stack;
        }        
        
    	$this->data = new Horde_Core_Ajax_Response($this->data);

    }
    
}