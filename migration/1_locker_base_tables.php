<?php
/**
 * Create Skeleton base tables.
 *
 * Copyright 2012 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.horde.org/licenses/gpl.
 *
 * @author  Your Name <you@example.com>
 * @category Horde
 * @package  Skeleton
 */
class LockerBaseTables extends Horde_Db_Migration_Base
{
    /**
     * Upgrade
     */
    public function up()
    {
        $tableList = $this->tables();
        if (!in_array('locker_files', $tableList)) {
            $t = $this->createTable('locker_files', array('autoincrementKey' => false));
            $t->column('file_id', 'string', array('limit' => 32, 'null' => false));
            $t->column('file_name', 'string', array('limit' => 500, 'null' => false));
            $t->column('file_size', 'integer', array('default' => 0, 'null' => false));
            $t->column('file_owner', 'string', array('limit' => 255, 'null' => false));
            $t->column('file_expiration_date', 'date');
            $t->column('file_creation_date', 'date');
            $t->primaryKey(array('file_id'));
            $t->end();

            $this->addIndex('locker_files', array('file_owner'));
        }
        
        if (!in_array('locker_groups', $tableList)) {
            $t = $this->createTable('locker_groups', array('autoincrementKey' => false));
            $t->column('group_id', 'string', array('limit' => 32, 'null' => false));
            $t->column('group_owner', 'string', array('limit' => 255, 'null' => false));
            $t->column('group_hash', 'string', array('limit' => 55, 'null' => false));
            $t->column('group_type', 'string', array('limit' => 32, 'null' => false));
            $t->column('group_sent_date', 'date');
            $t->primaryKey(array('group_id'));
            $t->end();

            $this->addIndex('locker_groups', array('group_owner'));
        }
        if (!in_array('locker_groups_metadata', $tableList)) {
            $t = $this->createTable('locker_groups_metadata', array('autoincrementKey' => false));
            $t->column('metadata_id', 'string', array('limit' => 32, 'null' => false));
            $t->column('metadata_group_id', 'string', array('limit' => 32, 'null' => false));
            $t->column('metadata_name', 'string', array('limit' => 100, 'null' => false));
            $t->column('group_value', 'text');
            $t->primaryKey(array('metadata_id'));
            $t->end();

            $this->addIndex('locker_groups_metadata', array('metadata_group_id'));
        }    
    }

    /**
     * Downgrade
     */
    public function down()
    {
        $this->dropTable('locker_files');
        $this->dropTable('locker_groups');
        $this->dropTable('locker_groups_metadata');
    }
}
