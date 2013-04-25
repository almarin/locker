<?php
/**
 * Locker_Driver factory.
 *
 * @author   Alfonso Marin <almarin@um.es>
 * @category Horde
 * @license  http://www.horde.org/licenses/gpl GPL
 * @package  Locker
 */
class Locker_Factory_Driver extends Horde_Core_Factory_Injector
{
    /**
     * @var array
     */
    private $_instances = array();

    /**
     * Return an Locker_Driver instance.
     *
     * @return Locker_Driver
     */
    public function create(Horde_Injector $injector)
    {
        $driver = Horde_String::ucfirst($GLOBALS['conf']['storage']['driver']);
        $signature = serialize(array($driver, $GLOBALS['conf']['storage']['params']['driverconfig']));
        if (empty($this->_instances[$signature])) {
            switch ($driver) {
            case 'Sql':
                try {
                    if ($GLOBALS['conf']['storage']['params']['driverconfig'] == 'horde') {
                        $db = $GLOBALS['injector']->getInstance('Horde_Db_Adapter');
                    } else {
                        $db = $GLOBALS['injector']->getInstance('Horde_Core_Factory_Db')
                            ->create('locker', 'storage');
                    }
                } catch (Horde_Exception $e) {
                    throw new Locker_Exception($e);
                }
                $params = array('db' => $db);
                break;
            case 'Ldap':
                try {
                    $params = array('ldap' => $injector->getIntance('Horde_Core_Factory_Ldap')->create('locker', 'storage'));
                } catch (Horde_Exception $e) {
                    throw new Locker_Exception($e);
                }
                break;
            }
            $class = 'Locker_Driver_' . $driver;
            $this->_instances[$signature] = new $class($params);
        }

        return $this->_instances[$signature];
    }
}