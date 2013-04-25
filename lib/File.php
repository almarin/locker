<?php

class Locker_File{

	public function __construct($data = null, $group = null){
		$this->data = $data;
		
		if (empty($this->data['file_creation_date'])){
			$this->data['file_creation_date'] = new Horde_Date(date('Y-m-d'));
		}
		if (is_string($this->data['file_creation_date'])){
			$this->data['file_creation_date'] = new Horde_Date($this->data['file_creation_date']);	
		}
		if (empty($this->data['file_owner'])) {
			$this->data['file_owner'] = $GLOBALS['registry']->getAuth();
		}
		$this->group = $group;
	}

	public function __get($attr){
		return $this->data[$attr];
	}
	public function id($new_id = null){
		if ($new_id){
			$this->data['file_id'] = $new_id;
		} else {
			return $this->data['file_id'];	
		}
	}
	public function unlink(){
		unlink(Locker::getLockerPath().$this->id());
	}
	public function status($new_status = null){
		if ($new_status){
			$this->data['file_status'] = $new_status;
		} else {
			return $this->data['file_status'];	
		}
	}
	public function getHumanSize(){
		$size = $this->file_size;
            if ($size >= 1000000000) {
            	$size = sprintf('%0.2f GB', $size / 1000000000);
            } else if ($size >= 1000000) {
            	$size = sprintf('%0.2f MB', $size / (1024*1024));
            } else {
				$size = sprintf('%0.2f MB', $size / 1024);
            }
            return $size;	
	}
	public function fetch($id = null){
		//TODO
		if (empty($id)){
			return false;
		}
		$storage = $GLOBALS['injector']->getInstance('Locker_Factory_Driver')->create();
		$rows = $storage->_fetch('locker_files', array('file_id' => $id));
		if (count($rows)){
			$this->data = $rows[0];
			return true;
		}
		return false;
	}
	public function create(){
		$storage = $GLOBALS['injector']->getInstance('Locker_Factory_Driver')->create();		
		$storage->_saveEntity('locker_files', $this->data);
	}

	public function save(){

		$storage = $GLOBALS['injector']->getInstance('Locker_Factory_Driver')->create();

		if (empty($this->data['file_id'])){
			$this->data['file_id'] = new Horde_Support_Randomid();
			$storage->_saveEntity('locker_files', $this->data);
		} else {
			$storage->_saveEntity('locker_files', $this->data, array('file_id' => $this->data['file_id']));
		}
		//echo "<pre>";print_r($this->data);exit;
		
	}
}