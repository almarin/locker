<?php
/**
 * Copyright 2010-2013 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.horde.org/licenses/gpl.
 *
  * @author   Alfonso Marin <almarin@um.es>
 * @category Horde
 * @license  http://www.horde.org/licenses/gpl GPL
 * @package  Locker
 */

/* Determine the base directories. */
if (!defined('LOCKER_BASE')) {
    define('LOCKER_BASE', __DIR__ . '/..');
}

if (!defined('HORDE_BASE')) {
    /* If Horde does not live directly under the app directory, the HORDE_BASE
     * constant should be defined in config/horde.local.php. */
    if (file_exists(LOCKER_BASE . '/config/horde.local.php')) {
        include LOCKER_BASE . '/config/horde.local.php';
    } else {
        define('HORDE_BASE', LOCKER_BASE . '/..');
    }
}

/* Load the Horde Framework core (needed to autoload
 * Horde_Registry_Application::). */
require_once HORDE_BASE . '/lib/core.php';

/**
 * Locker application API.
 *
 * This class defines Horde's core API interface. Other core Horde libraries
 * can interact with Locker through this API.
 *
 * @author   Alfonso Marin <almarin@um.es>
 * @category Horde
 * @license  http://www.horde.org/licenses/gpl GPL
 * @package  Locker
 */
class Locker_Application extends Horde_Registry_Application
{
    /**
     */
    public $version = 'H5 (0.2-git)';

    /**
     */
    protected function _bootstrap()
    {
        $GLOBALS['injector']->bindFactory('Locker_Driver', 'Locker_Factory_Driver', 'create');
    }

    /**
     * Adds items to the sidebar menu.
     *
     * Simple sidebar menu entries go here. More complex entries are added in
     * the sidebar() method.
     *
     * @param $menu Horde_Menu  The sidebar menu.
     */
    public function menu($menu)
    {
        /* If index.php == lists.php, jump some extra loops to highlight the
         * menu entry. */
        $menu->add(
            Horde::url('list.php'),
            _("My Sharings"),
            'skeleton-list',
            null,
            null,
            null,
            basename($_SERVER['PHP_SELF']) == 'index.php' ? 'current' : null);

        /* A regular entry. */
        $menu->add(Horde::url('files.php'), _("My Files"), 'horde-data');
    }

    /**
     * Adds additional items to the sidebar.
     *
     * @param Horde_View_Sidebar $sidebar  The sidebar object.
     */
    public function sidebar($sidebar)
    {

        $blank = new Horde_Url();
        $sidebar->addNewButton(
            _("Subir ficheros"),
            $blank,
            array('id' => 'lockerUploadFiles')
        );



    }
}