<?php

class Locker_Group{
	public $data;
	public function __construct($data = null, $files = array()){
		$this->data = $data;

		if (empty($this->data['group_expiration_date'])){
			$this->setExpirationDate(date('Y-m-d'));
		}
		if (!is_a('Horde_Date', $this->data['group_expiration_date'])){
			$this->data['group_expiration_date'] = new Horde_Date($data['group_expiration_date']);
		}
		if (isset($this->data['group_sent_date'])){
			$this->data['group_sent_date'] = new Horde_Date($this->data['group_sent_date']);
		}
		if (empty($this->data['group_owner'])) {
			$this->data['group_owner'] = $GLOBALS['registry']->getAuth();
		}

		
		if (is_string($this->data['group_metadata'])){
			$this->data['group_metadata'] = json_decode($this->data['group_metadata']);
		}
		$this->_files_map = array();
		if (!empty($files)){
			foreach ($files as $file){
				$this->addFile($file);
			}
		}
	}

	public function getDaysLeftToExpire(){
		$today = new Horde_Date(date('Y-m-d'));
		
		return $this->group_expiration_date->toDays() - $today->toDays();

	}
	public function getNumDaysToExpire(){
		$days = $this->getDaysLeftToExpire();
		if ($days < 0){
			$negativo = true;
			$days = -$days;
		}
		return $days;				
	}
	public function expireStr(){
		$days = $this->getNumDaysToExpire();
		if ($days == 0){
			return sprintf(_("Expire <a title='exacly next %s'>today</a>"), $this->group_expiration_date->format('d/m/Y'));
		} else if ($days < 14){
			$str = sprintf(_("<a title='exacly next %s'>%s days</a>"), $this->group_expiration_date->format('d/m/Y'), $days);
		} else if ($days < 30){
			$str = sprintf(_("<a title='exacly next %s'>%s weeks</a>"), $this->group_expiration_date->format('d/m/Y'), intval($days/7));
		} else if ($days < 365){
			$str = sprintf(_("<a title='exacly next %s'>%s months</a>"), $this->group_expiration_date->format('d/m/Y'), intval($days/30));
		} else {
			$str = sprintf(_("<a title='exacly next %s'>%s years</a>"), $this->group_expiration_date->format('d/m/Y'), intval($days/365));	
		}

		$str = sprintf( (empty($negativo)) ? _('Expire in %s') : _('Expired %s ago'), $str);
		return $str;
	}
	public function __get($attr){
		return $this->data[$attr];
	}
	public function getTitle(){
		switch($this->data['group_type']){
			case 'mail':
				return $this->data['group_metadata']->subject;
				break;
		}
	}
	public function addFile($file){
		$this->_files_map[(is_string($file) ? $file : $file->id())] = $file;
	}
	public function setExpirationDate($date, $incDays = 0){
		list($year, $month, $day) = explode('-', $date);
		if (!checkdate($month, $day, $year)){
			return false;
		}
		$time = mktime(0, 0, 0, $month, $day, $year);
		//echo $time.' - '.$incDays.'-'.($incDays * 86400);exit;
		$time += ($incDays * 86400);

		$this->data['group_expiration_date'] = new Horde_Date(date('Y-m-d', $time));
		$this->data['group_days'] = $incDays;
	}
	public function status($new_status = null){
		if ($new_status){
			$this->data['group_status'] = $new_status;
		} else {
			return $this->data['group_status'];	
		}
	}
	public function checkFiles(){

		foreach ($this->_files_map as $file_id => $file){
			if (!($f = $this->loadFile($file_id))){
				return false;
			}
			if ($f->status() == 'deleted'){
				return false;
			}
		}
		return true;
	}
	public function unlink(){
		foreach($this->getFiles() as $file){
			$file->unlink();
		}
	}
	public function getFiles(){
		return $this->_files_map;
	}
	public function loadFile($file_id){
		if (empty($this->_files_map[$file_id])){
			return false;
		}
		if (is_a($this->_files_map[$file_id], 'Locker_File')){
			return $this->_files_map[$file_id];
		}

		$file = new Locker_File();
		if (!$file->fetch($file_id)){
			return false;
		}
		$this->_files_map[$file_id] = $file;
		return $file;
	}
	public function save($set_sent_date = true){
		if (empty($this->data['group_id'])){
			$this->data['group_id'] = new Horde_Support_Randomid();
		} else {
			$conds = array('group_id' => $this->data['group_id']);
		}
		if ($set_sent_date && empty($this->data['group_sent_date'])){
			$this->data['group_sent_date'] = new Horde_Date(date('Y-m-d H:i:s'));
		}
		$storage = $GLOBALS['injector']->getInstance('Locker_Factory_Driver')->create();

		$data = $this->data;
		$data['group_metadata'] = json_encode($data['group_metadata']);
		$data['group_sent_date'] = $data['group_sent_date']->format('Y-m-d H:i:s');
		$data['group_expiration_date'] = $data['group_expiration_date']->format('Y-m-d');

		$storage->_saveEntity('locker_groups', $data, $conds);
		
		if (empty($conds)){
			foreach($this->_files_map as $file_id => $file){
				if (is_string($file)){
					$file = $this->loadFile($file_id);
				}

				$row = array(
					'gf_group_id' => $this->data['group_id'],
					'gf_file_id' => $file->id()

				);
				$storage->_saveEntity('locker_groups_files', $row);
				$file->status('online');
				$file->save();
				
			}			
		}
	}
}