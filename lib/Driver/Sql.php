<?php

/**
 * Locker storage implementation for the Horde_Db database abstraction layer.
 *
 * @author   Alfonso Marin <almarin@um.es>
 * @category Horde
 * @license  http://www.horde.org/licenses/gpl GPL
 * @package  Locker
 */
class Locker_Driver_Sql extends Locker_Driver
{
    /**
     * Handle for the current database connection.
     *
     * @var Horde_Db_Adapter
     */
    protected $_db;

    /**
     * Storage variable.
     *
     * @var array
     */
    protected $_foo = array();

    /**
     * Constructs a new SQL storage object.
     *
     * @param array $params  Class parameters:
     *                       - db:    (Horde_Db_Adapater) A database handle.
     *                       - table: (string, optional) The name of the
     *                                database table.
     *
     * @throws InvalidArgumentException
     */
    public function __construct(array $params = array())
    {
        if (!isset($params['db'])) {
            throw new InvalidArgumentException('Missing db parameter.');
        }
        $this->_db = $params['db'];
        unset($params['db']);

        parent::__construct($params);
    }
    public function getGroup($id){

        $query=" SELECT * FROM locker_groups, locker_groups_files, locker_files WHERE (group_id = gf_group_id AND file_id = gf_file_id) AND group_id = ?";

        $rows = $this->_db->selectAll($query, array($id));
        if (empty($rows)){

            return false;
        }

        $groups = $this->rawToGroups($rows);
        return array_shift($groups);
    }
    public function getFile($id){
        if (empty($id)){
            return null;
        }
        $query = "SELECT * FROM locker_groups, locker_groups_files, locker_files WHERE (group_id = gf_group_id AND file_id = gf_file_id) AND file_id = ?";

        $rows = $this->_db->selectAll($query, array($id));
        if (empty($rows)){
            return null;
        }
        $row = $rows[0];
        $file = new Locker_File(array(
            'file_id' => $row['file_id'],
            'file_name' => $row['file_name'],
            'file_size' => $row['file_size'],
            'file_type' => $row['file_type'],
            'file_owner' => $row['file_owner'],
            'file_status' => $row['file_status'],
            'file_creation_date' => $row['file_creation_date']
        ),new Locker_Group(array(
                    'group_id' => $row['group_id'],
                    'group_owner' => $row['group_owner'],
                    'group_type' => $row['group_type'],
                    'group_metadata' => $row['group_metadata'],
                    'group_sent_date' => $row['group_sent_date'],
                    'group_expiration_date' => $row['group_expiration_date']
                )));
        return $file;
    }
    public function _listRawFiles($options){

        $defaults = array(
            'status' => 'active',
            'page' => '*'
        );
        $options = array_merge($defaults, $options);


        // Consulta base;
        $conds = array(' group_owner = ?');
        $values = array($GLOBALS['registry']->getAuth());  


        // Si queremos activos o expirados

        
        $values[] = date('Y-m-d');
        if ($options['status'] == 'active'){
            $conds[] = ' group_expiration_date >= ? AND group_status = ? ';
            $values[] = 'active';
        } else  {
            $conds[] = ' group_expiration_date < ? OR group_status = ? ';
            $values[] = 'deleted';
        }
        

        $query .= "SELECT group_id FROM locker_groups WHERE ".implode(' AND ', $conds);
        $query .= " ORDER BY group_sent_date ASC ";
        //$query .= " ORDER BY group_expiration_date, asc";
        if ($options['page'] !== '*'){

            $offset = $options['page'] * $GLOBALS['conf']['page_size'];
           
            $query .= " LIMIT " . $offset . ", " . $GLOBALS['conf']['page_size'];
            
        }

        $rows = $this->_db->selectAll($query, $values);

        if (count($rows) == 0){
            return array();
        }

        $group_ids = array();
        foreach ($rows as $row){
            $group_ids[] = $row['group_id'];
        }

        $query=" SELECT * FROM locker_groups, locker_groups_files, locker_files WHERE (group_id = gf_group_id AND file_id = gf_file_id) AND group_id IN ('" . implode("', '", $group_ids) . "') ORDER BY group_sent_date DESC";

        $rows = $this->_db->selectAll($query);
        return $rows;        
    }
    public function listGroups($options = array()){

        $rows = $this->_listRawFiles($options);
        return $this->rawToGroups($rows);
    }
    public function rawToGroups($rows){
        $groups = array();
        foreach ($rows as $row){
            $file = new Locker_File(array(
                'file_id' => $row['file_id'],
                'file_name' => $row['file_name'],
                'file_size' => $row['file_size'],
                'file_type' => $row['file_type'],
                'file_owner' => $row['file_owner'],
                'file_status' => $row['file_status'],
                'file_creation_date' => $row['file_creation_date']));

            if (isset($groups[$row['group_id']])){
                $groups[$row['group_id']]->addFile($file);
            } else {
                $groups[$row['group_id']] = new Locker_Group(array(
                    'group_id' => $row['group_id'],
                    'group_owner' => $row['group_owner'],
                    'group_type' => $row['group_type'],
                    'group_metadata' => $row['group_metadata'],
                    'group_sent_date' => $row['group_sent_date'],
                    'group_expiration_date' => $row['group_expiration_date']
                ), array($file));
            }
        }

        return $groups;
    }

    /**
     * Devuelve listados de ficheros
     * @param array $options    Array asociativo de opciones para la consulta
     *                      'status' => 'active|expired',
     *                      'offset' => offset del LIMIT de la consulta
     *                      'size' => size del LIMIT de la consulta. 0 Significa sin limite
     *                      'order_by' => Campo de ordenacion
     *                      'order_dir' => Campo de dirección de ordenacion
     * @throws Locker_Exception
     */
    public function listFiles($options = array()){

        $defaults = array(
            'status' => 'active',
            'page' => '*'
        );
        $options = array_merge($defaults, $options);

        // Consulta base;
        $conds = array(' group_owner = ?');
        
        $values = array($GLOBALS['registry']->getAuth());

        if ($options['status'] == 'active'){
            $conds[] = ' group_expiration_date >= "'.date('Y-m-d').'" AND group_status = "active" AND file_status = "online"';
        } else  {
            $conds[] = ' ((group_expiration_date < "'.date('Y-m-d').'" OR group_status != "active") AND (file_status != "hidden")) OR file_status="deleted"  ';
        }

        $query=" SELECT * FROM locker_groups, locker_groups_files, locker_files WHERE (group_id = gf_group_id AND file_id = gf_file_id) AND (".implode(' AND ', $conds).") ORDER BY group_sent_date DESC";
        if ($options['page'] !== '*'){

            $offset = $options['page'] * $GLOBALS['conf']['page_size'];
           
            $query .= " LIMIT " . $offset . ", " . $GLOBALS['conf']['page_size'];
            
        }

        $rows = $this->_db->selectAll($query, $values);
        


        $files = array();
        foreach ($rows as $row){
            $files[] = new Locker_File(array(
                'file_id' => $row['file_id'],
                'file_name' => $row['file_name'],
                'file_size' => $row['file_size'],
                'file_type' => $row['file_type'],
                'file_owner' => $row['file_owner'],
                'file_status' => $row['file_status'],
                'file_creation_date' => $row['file_creation_date']
            ),new Locker_Group(array(
                    'group_id' => $row['group_id'],
                    'group_owner' => $row['group_owner'],
                    'group_type' => $row['group_type'],
                    'group_metadata' => $row['group_metadata'],
                    'group_sent_date' => $row['group_sent_date'],
                    'group_expiration_date' => $row['group_expiration_date']
                )));
        }

        return $files;

    }
    public function _fetch($table, $conds){
        $q_conds = array();
        foreach ($conds as $key_cond => $value_cond){
            $q_conds[] = $key_cond . ' = ? ';
        }

        $query = "SELECT * FROM $table WHERE ".implode(' AND ', $q_conds);

        $rows = $this->_db->selectAll($query, array_values($conds));
        return $rows;
    }
    public function _saveEntity($table, $data, $conds = null){

        if (empty($conds)){
            $query = "INSERT INTO $table " . 
                    '(' . implode(',', array_keys($data)) . ') ' .
                    ' VALUES (' . str_repeat('?,', count($data) - 1) . '?)';
            $this->_db->insert($query,   array_values($data));

        } else {

            $q_sets = array();
            foreach ($data as $key => $value){
                $q_sets[] = $key . ' = ?';
            }

            $q_conds = array();
            foreach ($conds as $key_cond => $value_cond){
                $q_conds[] = $key_cond . ' = ? ';
            }

            $query = "UPDATE $table SET ".implode(',',$q_sets)." WHERE ".implode(' AND ', $q_conds);
            
            $this->_db->update($query,   array_merge(array_values($data), array_values($conds)));
        }
    }
    /**
     * Stores a foo in the database.
     *
     * @throws Sms_Exception
     */
    /*
    public function storeFile($data)
    {
        $query = 'INSERT INTO locker_files' .
                 ' (file_id, file_owner, file_name, file_size, file_type, file_expiration_date, file_creation_date)' .
                    ' VALUES (?, ?, ?, ?, ?, ?, ?)';
        $values = array(
            $data->name,
            $GLOBALS['registry']->getAuth(),
            $data->filename,
            $data->size,
            $data->type,
            $data->expiration,
            date('Y-m-d'));

        try {
            $this->_db->insert($query, $values);
        } catch (Horde_Db_Exception $e) {

            throw new Locker_Exception($e->getMessage());
        }
    }

    public function storeGroup($tickets, $type, $metadata){

        $query = 'INSERT INTO locker_groups ' .
                '(group_id, group_owner, group_type, group_sent_date) ' .
                ' VALUES (?, ?, ?, ?)';
        $group_id = new Horde_Support_Randomid();

        $values = array(
            $group_id,
            $GLOBALS['registry']->getAuth(),
            $type,
            date('Y-m-d H:i:s')
        );

        try {
            $this->_db->insert($query, $values);

            $query = 'INSERT INTO locker_groups_metadata ' .
                     '(metadata_group_id, metadata_name, metadata_value) ' . 
                    ' VALUES (?, ?, ?)';

            foreach ($metadata as $key => $value){
                $values = array($group_id, $key, $value);
                $this->_db->insert($query, $values);
            }

            foreach ($tickets as $ticket){
                $values = array($group_id, 'ticket', $ticket);
                $this->_db->insert($query, $values);
            }
        } catch (Horde_Db_Exception $e) {
            //rollback
            $values = array($group_id);
            $this->_db->delete("DELETE FROM locker_groups_metadata WHERE metadata_group_id = ?", $values);
            $this->_db->delete("DELETE FROM locker_groups WHERE group_id = ?", $values);
            throw $e;
        }

    }
    */
}