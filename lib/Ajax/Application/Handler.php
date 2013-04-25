<?php
/**
 * Defines the AJAX actions used in Kronolith.
 *
 * Copyright 2012-2013 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.horde.org/licenses/gpl.
 *
 * @author   Michael Slusarz <slusarz@horde.org>
 * @author   Jan Schneider <jan@horde.org>
 * @author   Gonçalo Queirós <mail@goncaloqueiros.net>
 * @category Horde
 * @license  http://www.horde.org/licenses/gpl GPL
 * @package  Kronolith
 */
class Locker_Ajax_Application_Handler extends Horde_Core_Ajax_Application_Handler
{

    public function share()
    {

    	$result = new stdClass;
    	//1.- Comprobamos que hay ficheros que enviar
    	if (empty($this->vars->tickets)){
    		$GLOBALS['notification']->push(_("No se han especificado ficheros a compartir"), 'horde.error');
			$result->success = false;
    	} else {

    		$parser = new Horde_Mail_Rfc822();
    		$list = $parser->parseAddressList($this->vars->to);
    		$expireDays = intval($this->vars->expire);
    		if (empty($expireDays)){
    			$expireDays = 3; //TODO: cambiar a fecha por defecto
    		}
    		if (!count($list->addresses)){
	    		$GLOBALS['notification']->push(_("Debes especificar al menos un destinatario válido"), 'horde.error');
				$result->success = false;

    		} else {			
	    		if (empty($this->vars->subject)){
	    			$this->vars->subject = _('(Sin asunto)');
	    		}
	    		$metadata = array();
	    		if (Locker::isValidType($this->vars->type)){
	    			$metadata = new stdClass();
		    		switch ($this->vars->type){
		    			case 'mail':
		    				
		    				$metadata->to = $this->vars->to;
		    				$metadata->subject = $this->vars->subject;
		    				$metadata->msg = $this->vars->msg;		    					
		    				break;
		    			default:
		    				$metadata->msg = $this->vars->msg;
		    		}
		    		$data = array('group_type' => $this->vars->type,
		    			'group_metadata' => $metadata, 'group_status' => 'active');
		    		$group = new Locker_Group($data, $this->vars->tickets);
		    		$group->setExpirationDate(date('Y-m-d'), $expireDays);
		    		if (!$group->checkFiles()){
		    			$GLOBALS['notification']->push(_("Error al procesar los ficheros·"), 'horde.error');
						$result->success = false;
		    		} else {
				    	try{
				    		$group->save();
				    	} catch(Horde_Db_Exception $e) {
				    		$GLOBALS['notification']->push($e, 'horde.error');
				    		$result->success = false;
				    	}
						$result->success = true;		    			
		    		}
	    		} else {
	    			$GLOBALS['notification']->push(_("Tipo de mensaje desconocido"), 'horde.error');
					$result->success = false;
	    		}
    		}
    	}
    	if ($result->success){

    		$result->success = Locker::sendGroup($group);
    	}
    	return $result;

    	$result = new stdClass;
    	$result->hola="adios";

    	
    	return $result;
    }
    public function groupPage(){
    	global $injector;
    	$storage = $injector->getInstance('Locker_Factory_Driver')->create();
		$view =	$injector->createInstance('Horde_View');
		$view->disabled = true;

		$result = new stdClass;
		if ($this->vars->type == 'group'){
	    	$view->groups = $storage->listGroups(array('status' => 'inactive', 'page' => $this->vars->page));
			$result->html=$view->render('group-list');
			$result->morepages=(count( $storage->listGroups(array('status' => 'inactive', 'page' => $this->vars->page+1)))>0);
		} else {
	    	$view->files = $storage->listFiles(array('status' => 'inactive', 'page' => $this->vars->page));
			$result->html=$view->render('file-list');
			$result->morepages=(count( $storage->listFiles(array('status' => 'inactive', 'page' => $this->vars->page+1)))>0);			
		}

    	$result->success = true;
    	
    	return $result;
    }
    public function delete(){
    	return $this->_setStatus('deleted');
    }
    public function hide(){
    	return $this->_setStatus('hidden');
    }

    private function _setStatus($status){
    	global $injector;
		$storage = $injector->getInstance('Locker_Factory_Driver')->create();
    	if ($this->vars->type == 'file'){
    		$obj = $storage->getFile($this->vars->id);
    	} else {
    		$obj = $storage->getGroup($this->vars->id);
    	}
    	$obj->status($status);
    	
    	$obj->save();
    	if ($status == 'deleted'){
    		$obj->unlink();
    	}
    	$result = new stdClass;
    	$result->success = true;
    	return $result;    	
    }
}