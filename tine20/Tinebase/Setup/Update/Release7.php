<?php
/**
 * Tine 2.0
 *
 * @package     Tinebase
 * @subpackage  Setup
 * @license     http://www.gnu.org/licenses/agpl.html AGPL3
 * @copyright   Copyright (c) 2012-2013 Metaways Infosystems GmbH (http://www.metaways.de)
 * @author      Philipp Schüle <p.schuele@metaways.de>
 */
class Tinebase_Setup_Update_Release7 extends Setup_Update_Abstract
{
    /**
     * update to 7.1
     * - added seq to modlog
     * 
     * @see 0000554: modlog: records can't be updated in less than 1 second intervals
     */
    public function update_0()
    {
        $declaration = new Setup_Backend_Schema_Field_Xml('
            <field>
                <name>seq</name>
                <type>integer</type>
                <length>64</length>
                <default>0</default>
            </field>
        ');
        
        try {
            $this->_backend->addCol('timemachine_modlog', $declaration);
        } catch (Zend_Db_Statement_Exception $zdse) {
            // already added
            $this->_backend->alterCol('timemachine_modlog', $declaration);
        }
        
        // try to drop "timemachine_modlog::application_id--applications::id" if it still exists
        try {
            $this->_backend->dropForeignKey('timemachine_modlog', 'timemachine_modlog::application_id--applications::id');
        } catch (Zend_Db_Statement_Exception $zdse) {
            // already dropped
        }
        
        $declaration = new Setup_Backend_Schema_Index_Xml('
           <index>
                <name>unique-fields</name>
                <unique>true</unique>
                <field>
                    <name>application_id</name>
                </field>
                <field>
                    <name>record_id</name>
                </field>
                <field>
                    <name>record_type</name>
                </field>
                <field>
                    <name>modification_account</name>
                </field>
                <field>
                    <name>modified_attribute</name>
                </field>
                <field>
                    <name>modification_time</name>
                </field>
                <field>
                    <name>seq</name>
                </field>
            </index>
        ');
        try {
            $this->_backend->dropIndex('timemachine_modlog', 'unique-fields');
        } catch (Zend_Db_Statement_Exception $zdse) {
            if (Tinebase_Core::isLogLevel(Zend_Log::NOTICE)) Tinebase_Core::getLogger()->notice(__METHOD__ . '::' . __LINE__
                . ' ' . $zdse->getMessage());
        }
        $this->_backend->addIndex('timemachine_modlog', $declaration);
        
        // add index to seq column
        $declaration = new Setup_Backend_Schema_Index_Xml('
            <index>
                <name>seq</name>
                <field>
                    <name>seq</name>
                </field>
            </index>
        ');
        try {
            $this->_backend->addIndex('timemachine_modlog', $declaration);
        } catch (Zend_Db_Statement_Exception $zdse) {
            // already added
        }
        
        $seqModels = array(
            'Tinebase_Model_Tree_FileObject'             => array('name' => 'tree_fileobjects',        'version' => 2),
            'Tinebase_Model_Container'                   => array('name' => 'container',               'version' => 7),
            'Tinebase_Model_Department'                  => array('name' => 'departments',             'version' => 2),
            'Tinebase_Model_ImportExportDefinition'      => array('name' => 'importexport_definition', 'version' => 6),
            'Tinebase_Model_Note'                        => array('name' => 'notes',                   'version' => 2),
            'Tinebase_Model_PersistentFilter'            => array('name' => 'filter',                  'version' => 4),
            'Tinebase_Model_PersistentObserver'          => array('name' => 'record_observer',         'version' => 3),
            'Tinebase_Model_Relation'                    => array('name' => 'relations',               'version' => 7),
            'Tinebase_Model_Tag'                         => array('name' => 'tags',                    'version' => 6),
        );
        
        $declaration = Tinebase_Setup_Update_Release7::getRecordSeqDeclaration();
        foreach ($seqModels as $model => $tableInfo) {
            try {
                $this->_backend->addCol($tableInfo['name'], $declaration);
            } catch (Zend_Db_Statement_Exception $zdse) {
                // ignore
            }
            $this->setTableVersion($tableInfo['name'], $tableInfo['version']);
            Tinebase_Setup_Update_Release7::updateModlogSeq($model, $tableInfo['name']);
        }
        
        $this->setTableVersion('timemachine_modlog', 3);
        $this->setApplicationVersion('Tinebase', '7.1');
    }
    
    /**
     * update to 7.2
     * 
     * - set length for field (persistent)filter-model from 40 to 64
     */
    public function update_1()
    {
        $update6 = new Tinebase_Setup_Update_Release6($this->_backend);
        $update6->update_8();
        $this->setTableVersion('importexport_definition', 7);
        $this->setTableVersion('filter', 5);
        $this->setApplicationVersion('Tinebase', '7.2');
    }
    
    /**
     * update to 7.3
     * 
     * @see 0006990: user with right "manage_shared_*_favorites" should be able to delete/edit default shared favorites
     * - remove manage_shared_favorites and update manage_shared_<model>_favorites accordingly
     */
    public function update_2()
    {
        $mapping = array(
            'Timetracker' => array('Timeaccount', 'Timesheet'),
            'Calendar'    => array('Event'),
            'Crm'         => array('Lead'),
            'Tasks'       => array('Task'),
            'Addressbook' => array('Contact')
        );

        $paging = new Tinebase_Model_Pagination();
        $filter = new Tinebase_Model_RoleFilter();
        
        foreach ($mapping as $appName => $modelNames) {
            try {
                $app = Tinebase_Application::getInstance()->getApplicationByName($appName);
            } catch (Tinebase_Exception_NotFound $e) {
                continue;
            }
            if ($app) {
                $roles = Tinebase_Acl_Roles::getInstance()->searchRoles($filter, $paging);
                if ($roles) {
                    foreach ($roles as $role) {
                        $rights = Admin_Controller_Role::getInstance()->getRoleRights($role->getId());
                        $hasRight = false;
                        $newRights = array();
                        foreach ($rights as $right) {
                            if ($right['application_id'] == $app->getId() && $right['right'] == 'manage_shared_favorites') {
                                $hasRight = true;
                            } else {
                                $newRights[] = $right;
                            }
                        }
                        if ($hasRight) {
                            foreach ($modelNames as $modelName) {
                                $newRights[] = array('role_id' => $role->getId(), 'application_id' => $app->getId(), 'right' => 'manage_shared_' . strtolower($modelName) . '_favorites');
                                Tinebase_Acl_Roles::getInstance()->setRoleRights($role->getId(), $newRights);
                            }
                        }
                    }
                }
            }
        }
        $this->setApplicationVersion('Tinebase', '7.3');
    }
    
    /**
     * get record seq xml schema
     * 
     * @return Setup_Backend_Schema_Field_Xml
     */
    public static function getRecordSeqDeclaration()
    {
        return new Setup_Backend_Schema_Field_Xml('
            <field>
                <name>seq</name>
                <type>integer</type>
                <notnull>true</notnull>
                <default>0</default>
            </field>'
        );
    }
    
    /**
     * update modlog seq
     * 
     * @param string $model
     * @param string $recordTable
     */
    public static function updateModlogSeq($model, $recordTable)
    {
        if (! class_exists($model)) {
            throw new Setup_Exception('Could not find model class');
        }
        
        if (Tinebase_Core::isLogLevel(Zend_Log::INFO)) Tinebase_Core::getLogger()->info(__METHOD__ . '::' . __LINE__ . 
            ' Fetching modlog records for ' . $model);
        
        // check if modlog table already has seq col
        $db = Tinebase_Core::getDb();
        $modlogTable = SQL_TABLE_PREFIX . 'timemachine_modlog';
        $modlogTableColumns = Tinebase_Db_Table::getTableDescriptionFromCache($modlogTable, $db);
        if (!  (isset($modlogTableColumns['seq']) || array_key_exists('seq', $modlogTableColumns))) {
            throw new Tinebase_Exception_SystemGeneric('You need to update Tinebase before updating any other application');
        }
        
        // fetch modlog records for model
        $sql = "SELECT DISTINCT record_id,modification_time,seq "
            . "FROM $modlogTable WHERE record_type ='{$model}' "
            . "ORDER BY modification_time ASC ";
        if (Tinebase_Core::isLogLevel(Zend_Log::TRACE)) Tinebase_Core::getLogger()->trace(__METHOD__ . '::' . __LINE__ . 
            ' SQL for fetching modlogs: ' . $sql);
        
        $result = $db->fetchAll($sql);
        
        if (empty($result)) {
            if (Tinebase_Core::isLogLevel(Zend_Log::INFO)) Tinebase_Core::getLogger()->info(__METHOD__ . '::' . __LINE__ . 
                ' No modlog records found for ' . $model);
            return;
        }
        
        $recordSeqs = array();
        $updateSeqs = array();
        
        // collect modlog data
        foreach ($result as $modification) {
            if ($modification['seq'] != 0) {
                $recordSeqs[$modification['record_id']] = $modification['seq'];
                continue;
            }
            
            if (! isset($recordSeqs[$modification['record_id']])) {
                $seq = $recordSeqs[$modification['record_id']] = 1;
            } else {
                $seq = ++$recordSeqs[$modification['record_id']];
            }
            
            if (! isset($updateSeqs[$seq])) {
                $updateSeqs[$seq] = array();
            }
            $updateSeqs[$seq][] = array(
                'record_id'         => $modification['record_id'],
                'modification_time' => $modification['modification_time']
            );
        }

        if (Tinebase_Core::isLogLevel(Zend_Log::INFO)) Tinebase_Core::getLogger()->info(__METHOD__ . '::' . __LINE__ . 
            ' Found ' . count($recordSeqs) . ' (different seqs: ' . count($updateSeqs) . ') records for modlog sequence update.');
        
        // update modlog
        foreach ($updateSeqs as $seq => $modsBySeq) {
            if (Tinebase_Core::isLogLevel(Zend_Log::DEBUG)) Tinebase_Core::getLogger()->debug(__METHOD__ . '::' . __LINE__ . 
                ' Updating ' . count($modsBySeq) . ' modification(s) to seq ' . $seq);
            
            $updateData = array(
                'seq' => $seq
            );
            $i = 0;
            while ($i < count($modsBySeq)) {
                $whereArray = array();
                // step by 1000 
                for ($j = 0; $j < 1000 && ($i + $j) < count($modsBySeq); $j++) {
                    $whereArray[] = '(' . $db->quoteInto('record_id = ?', $modsBySeq[$i+$j]['record_id']) . ' AND '
                        . $db->quoteInto('modification_time = ?', $modsBySeq[$i+$j]['modification_time']) . ')';
                }
                if (Tinebase_Core::isLogLevel(Zend_Log::TRACE)) Tinebase_Core::getLogger()->trace(__METHOD__ . '::' . __LINE__ . 
                    ' Stepping from ' . $i . ' to ' . ($i + $j) . '(' . count($whereArray) . ' mods)');
                
                if (count($whereArray) > 0) {
                    $where = implode(' OR ', $whereArray);
                    $db->update($modlogTable, $updateData, $where);
                }
                $i += $j;
            }
        }
        
        // update records
        $maxSeqs = array();
        foreach ($recordSeqs as $recordId => $maxSeq) {
            $maxSeqs[$maxSeq][] = (string) $recordId;
        }
        
        foreach ($maxSeqs as $maxSeq => $recordIds) {
            if (Tinebase_Core::isLogLevel(Zend_Log::DEBUG)) Tinebase_Core::getLogger()->debug(__METHOD__ . '::' . __LINE__ . 
                ' Setting max seq to ' . $maxSeq . ' for ' . count($recordIds) . ' record(s).');
            
            $updateData = array(
                'seq' => $maxSeq
            );
            $where = $db->quoteInto($db->quoteIdentifier('id') . ' IN (?)', (array) $recordIds);
            try {
                $db->update(SQL_TABLE_PREFIX . $recordTable, $updateData, $where);
            } catch (Zend_Db_Statement_Exception $zdse) {
                if (Tinebase_Core::isLogLevel(Zend_Log::WARN)) Tinebase_Core::getLogger()->warn(__METHOD__ . '::' . __LINE__ . 
                    ' Could not update record seq: ' . $zdse->getMessage());
                if (Tinebase_Core::isLogLevel(Zend_Log::DEBUG)) Tinebase_Core::getLogger()->debug(__METHOD__ . '::' . __LINE__ . 
                    ' Data: ' . print_r($updateData, TRUE) . ' Where: ' . substr($where, 0, 256));
            }
        }
        
        if (Tinebase_Core::isLogLevel(Zend_Log::INFO)) Tinebase_Core::getLogger()->info(__METHOD__ . '::' . __LINE__ . 
            ' Finished modlog sequence update for ' . $model);
    }
    
    /**
     * update to 7.4
     * 
     * - save each state_id in an own field
     */
    public function update_3()
    {
        // add a default value of "false", as PGSQL does allow notnull columns with default value null
        $declaration = new Setup_Backend_Schema_Field_Xml('
            <field>
                <name>state_id</name>
                <type>text</type>
                <length>128</length>
                <notnull>true</notnull>
                <default>false</default>
            </field>
        ');
        
        $this->_backend->addCol('state', $declaration);
        
        $this->_backend->dropIndex('state', 'user_id');
        
        $declaration = new Setup_Backend_Schema_Index_Xml('
            <index>
                <name>user_id--state_id</name>
                <unique>true</unique>
                <field>
                    <name>user_id</name>
                </field>
                <field>
                    <name>state_id</name>
                </field>
            </index>
        ');
        
        $this->_backend->addIndex('state', $declaration);
        
        
        $be = new Tinebase_Backend_Sql(array(
            'modelName' => 'Tinebase_Model_State', 
            'tableName' => 'state',
        ));
        
        $allStates = $be->getAll();
        foreach ($allStates as $oldState) {
            $oldData = Zend_Json::decode($oldState->data);
            foreach ($oldData as $stateId => $data) {
                
                $filter = new Tinebase_Model_StateFilter(array(
                    array('field' => 'state_id', 'operator' => 'equals', 'value' => $stateId),
                    array('field' => 'user_id', 'operator' => 'equals', 'value' => $oldState->user_id)
                ));
                $result = $be->search($filter);
                
                try {
                    if ($result->count()) {
                        $record = $result->getFirstRecord();
                        $record->data = $data;
                        $be->update($record);
                    } else {
                        $record = new Tinebase_Model_State(array(
                            'user_id'   => $oldState->user_id,
                            'state_id'  => $stateId,
                            'data'      => $data
                        ));
                        $be->create($record);
                    }
                } catch (Exception $e) {
                    if (Tinebase_Core::isLogLevel(Zend_Log::INFO)) {
                        Tinebase_Core::getLogger()->debug(__METHOD__ . '::' . __LINE__ . 'Could not transfer old state: ' . $stateId . ': ' . print_r($data, 1));
                        Tinebase_Core::getLogger()->debug(__METHOD__ . '::' . __LINE__ . 'Exception Message: ' . $e->getMessage()) ;
                    }
                }
            }
            $be->delete($oldState->getId());
        }
        
        // remove the default value "false" again
        $declaration = new Setup_Backend_Schema_Field_Xml('
            <field>
                <name>state_id</name>
                <type>text</type>
                <length>128</length>
                <notnull>true</notnull>
            </field>
        ');
        
        $this->_backend->alterCol('state', $declaration);
        
        $this->setApplicationVersion('Tinebase', '7.4');
        $this->setTableVersion('state', 2);
    }
    
    /**
     * update to 7.5
     * 
     * - rename scheduler task (just add again for param / function name change)
     */
    public function update_4()
    {
        $scheduler = Tinebase_Core::getScheduler();
        Tinebase_Scheduler_Task::addTempFileCleanupTask($scheduler);
        $this->setApplicationVersion('Tinebase', '7.5');
    }

    /**
     * update to 7.6
     * 
     * @see 0008318: add clear accesslog to scheduler
     */
    public function update_5()
    {
        $scheduler = Tinebase_Core::getScheduler();
        Tinebase_Scheduler_Task::addAccessLogCleanupTask($scheduler);
        $this->setApplicationVersion('Tinebase', '7.6');
    }
    
    /**
     * update to 7.7
     *
     * @see 8700: state save/restore no longer works in attendee filter grid
     */
    public function update_6()
    {
        $declaration = new Setup_Backend_Schema_Field_Xml('
            <field>
                <name>data</name>
                <type>clob</type>
            </field>
        ');
        
        $this->_backend->alterCol('state', $declaration);
        $this->setTableVersion('state', 3);
        
        $this->setApplicationVersion('Tinebase', '7.7');
    }
    
    /**
     * update to 7.8
     *
     * add uuid column to support folder creation via webdav
     */
    public function update_7()
    {
        $this->validateTableVersion('container', 7);
        
        $declaration = new Setup_Backend_Schema_Field_Xml('
            <field>
                <name>uuid</name>
                <type>text</type>
                <length>64</length>
                <default>NULL</default>
            </field>
        ');
        
        $this->_backend->addCol('container', $declaration);
        $this->setTableVersion('container', 8);
        
        $this->setApplicationVersion('Tinebase', '7.8');
    }

    /**
     * update to 8.0
     *
     * @return void
     */
    public function update_8()
    {
        $this->setApplicationVersion('Tinebase', '8.0');
    }
}
