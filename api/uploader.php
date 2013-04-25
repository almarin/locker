<?php
require_once __DIR__ . '/../lib/Application.php';
require_once LOCKER_BASE . '/lib/UploadHandler.php';

Horde_Registry::appInit('locker', array('authentication' => 'none'));



$UP = new UploadHandler(array('upload_dir' => Locker::getLockerPath()), false);

$user = $registry->getAuth();
if (empty($user)) {
	// De momento, si no está autenticado, error	
	$UP->generate_response(array('success' => false, 'error' => 'Auth failed'));
	exit;
}

$UP->initialize();