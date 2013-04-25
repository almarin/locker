<?php
/**
 * Example list script.
 *
 * Copyright 2007-2013 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.horde.org/licenses/gpl.
 *
 * @author   Alfonso Marin <almarin@um.es>
 */

require_once __DIR__ . '/lib/Application.php';

Horde_Registry::appInit('locker', array('authentication' => 'none', 'session_control' => 'none'));

$fileid = Horde_Util::getFormData('file');
if (empty($fileid)){
	exit;
}
$storage = $injector->getInstance('Locker_Factory_Driver')->create();

$file = $storage->getFile($fileid);

$today = new Horde_Date(date('Y-m-d H:i:s'));

// Es un fichero valido?
if (empty($file) ||
	$file->status() != 'online' ||
	$file->group->group_expiration_date->compareDate(date('Y-m-d H:i:s')) <= -1){
	echo "Acceso denegado";
	exit;
}
$browser->downloadHeaders($file->file_name,$file->file_type,false,$file->file_size);
Locker::readFileChunged(Locker::getLockerPath().$fileid);