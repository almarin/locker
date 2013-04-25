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

Horde_Registry::appInit('locker');

/* Example of how to use Horde_View. If getting a Horde_View instance via
 * createInstance() from the injector, the template path already defaults to
 * the application's templates/ folder. */

Locker::loadCoreJavaScript();

$storage = $injector->getInstance('Locker_Factory_Driver')->create();

$view = $injector->createInstance('Horde_View');

$active_groups_view = 	$injector->createInstance('Horde_View');
$active_groups_view->groups = $storage->listGroups(array('status' => 'active'));
$view->active_groups_html = $active_groups_view->render('group-list');

$inactive_groups_view =	$injector->createInstance('Horde_View');
$inactive_groups_view->disabled = true;
$inactive_groups_view->groups = $storage->listGroups(array('status' => 'inactive', 'page' => 0));
$view->morepages = (count($storage->listGroups(array('status' => 'inactive', 'page' => 1)))>0);
$view->inactive_groups_html = $inactive_groups_view->render('group-list');


/* Here starts the actual page output. First we output the complete HTML
 * header, CSS files, the topbar menu, and the sidebar menu. */
$page_output->header(array(
    'title' => _("List"),
));
/* Next we output any notification messages. This is not done automatically
 * because on some pages you might not want to have notifications. */
$notification->notify(array('listeners' => 'status'));




/* Here goes the actual content of your application's page. This could be
 * Horde_View output, a rendered Horde_Form, or any other arbitrary HTML
 * output. */
echo $view->render('list');


echo $view->render('js-templates');
/* Finally the HTML content is closed and JavaScript files are loaded. */
$page_output->footer();