<?php
/**
 * Tine 2.0
 *
 * @package     ActiveSync
 * @subpackage  Controller
 * @license     http://www.gnu.org/licenses/agpl.html AGPL Version 3
 * @copyright   Copyright (c) 2008-2012 Metaways Infosystems GmbH (http://www.metaways.de)
 * @author      Lars Kneschke <l.kneschke@metaways.de>
 */

/**
 * class documentation
 *
 * @package     ActiveSync
 * @subpackage  Controller
 */
abstract class ActiveSync_Controller_Abstract implements Syncroton_Data_IData
{
    const LONGID_DELIMITER = "\xe2\x87\x94"; # ⇔
    
    /**
     * information about the current device
     *
     * @var Syncroton_Model_IDevice
     */
    protected $_device;
    
    /**
     * timestamp to use for all sync requests
     *
     * @var Tinebase_DateTime
     */
    protected $_syncTimeStamp;
    
    /**
     * class to use for search entries
     *
     * @var string
     */
    protected $_contentFilterClass;
    
    /**
     * instance of the content specific controller
     *
     * @var Tinebase_Controller_Record_Abstract
     */
    protected $_contentController;
    
    /**
     * name of Tine 2.0 backend application
     * 
     * gets set by the instance of this abstract class
     *
     * @var string
     */
    protected $_applicationName;
    
    /**
     * name of Tine 2.0 model to use
     * 
     * strip of the applicationnamel and "model"
     * for example "Addressbook_Model_Contacts" becomes "Contacts"
     *
     * @var string
     */
    protected $_modelName;
    
    /**
     * type of the default folder
     *
     * @var int
     */
    protected $_defaultFolderType;
    
    /**
     * default container for new entries
     * 
     * @var string
     */
    protected $_defaultFolder;
    
    /**
     * type of user created folders
     *
     * @var int
     */
    protected $_folderType;
    
    /**
     * name of special folder
     * 
     * get used when the client does not support more that one folder
     *
     * @var string
     */
    protected $_specialFolderName;
    
    /**
     * name of property which defines the filterid for different content classes
     * 
     * @var string
     */
    protected $_filterProperty;
    
    /**
     * field to sort search results by
     * 
     * @var string
     */
    protected $_sortField;
    
    /**
     * name of the contentcontoller class
     * Defaults to $this->_applicationName . '_Controller_' . $this->_modelName
     * 
     * @var string
     */
    protected $_contentControllerName;
    
    /**
     * the constructor
     *
     * @param Tinebase_DateTime $_syncTimeStamp
     */
    public function __construct(Syncroton_Model_IDevice $_device, DateTime $_syncTimeStamp)
    {
        if(empty($this->_applicationName)) {
            throw new Tinebase_Exception_UnexpectedValue('$this->_applicationName can not be empty');
        }
        
        if(empty($this->_modelName)) {
            throw new Tinebase_Exception_UnexpectedValue('$this->_modelName can not be empty');
        }
        
        if(empty($this->_defaultFolderType)) {
            throw new Tinebase_Exception_UnexpectedValue('$this->_defaultFolderType can not be empty');
        }
        
        if(empty($this->_folderType)) {
            throw new Tinebase_Exception_UnexpectedValue('$this->_folderType can not be empty');
        }
                
        if(empty($this->_specialFolderName)) {
            $this->_specialFolderName = strtolower($this->_applicationName) . '-root';
        }
        
        $this->_device              = $_device;
        $this->_syncTimeStamp       = $_syncTimeStamp;
        
        $this->_contentFilterClass  = $this->_applicationName . '_Model_' . $this->_modelName . 'Filter';
        if (empty($this->_contentControllerName)) {
            $this->_contentControllerName = $this->_applicationName . '_Controller_' . $this->_modelName;
        }
        $this->_contentController   = call_user_func(array($this->_contentControllerName, 'getInstance'));
    }
    
    /**
     * (non-PHPdoc)
     * @see Syncroton_Data_IData::getAllFolders()
     */
    public function getAllFolders()
    {
        $syncrotonFolders = array();
        
        // device supports multiple folders ?
        if(in_array(strtolower($this->_device->devicetype), array('iphone', 'ipad', 'thundertine', 'windowsphone', 'playbook'))) {
        
            // get the folders the user has access to
            $allowedFolders = $this->_getSyncableFolders();
            
            $wantedFolders = null;
            
            // @todo review wantedFolders block
            
            // check if contentfilter has a container limitation
            $filter = $this->_getContentFilter(0);
            $containerFilter = $filter->getFilter('container_id', FALSE, TRUE);
            if ($containerFilter && $containerFilter instanceof Tinebase_Model_Filter_Container) {
                $wantedFolders = array_flip($containerFilter->getContainerIds());
            }
            
            #$folders = $wantedFolders === null ? $allowedFolders : array_intersect_key($allowedFolders, $wantedFolders);
            $folders = $allowedFolders;
            
            foreach ($folders as $container) {
                $syncrotonFolders[$container->id] = new Syncroton_Model_Folder(array(
                    'serverId'      => $container->id,
                    'parentId'      => 0,
                    'displayName'   => $container->name,
                    'type'          => (count($syncrotonFolders) == 0) ? $this->_defaultFolderType : $this->_folderType
                ));
            }
            
        } else {
            $syncrotonFolders[$this->_specialFolderName] = new Syncroton_Model_Folder(array(
                'serverId'      => $this->_specialFolderName,
                'parentId'      => 0,
                'displayName'   => $this->_applicationName,
                'type'          => $this->_defaultFolderType
            ));
        }
        
        return $syncrotonFolders;
    }
    
    /**
     * (non-PHPdoc)
     * @see Syncroton_Data_IData::moveItem()
     */
    public function moveItem($srcFolderId, $serverId, $dstFolderId)
    {
        $item = $this->_contentController->get($serverId);
        
        $item->container_id = $dstFolderId;
        
        $item = $this->_contentController->update($item);
        
        return $item->getId();
    }
    
    /**
     * (non-PHPdoc)
     * @see Syncroton_Data_IData::createEntry()
     */
    public function createEntry($folderId, Syncroton_Model_IEntry $entry)
    {
        if (Tinebase_Core::isLogLevel(Zend_Log::DEBUG)) 
            Tinebase_Core::getLogger()->debug(__METHOD__ . '::' . __LINE__ . " create entry");
        
        $entry = $this->toTineModel($entry);
        $entry->creation_time = new Tinebase_DateTime($this->_syncTimeStamp);
        $entry->created_by = Tinebase_Core::getUser()->getId();
        
        // container_id gets set to personal folder in application specific controller if missing
        if($folderId != $this->_specialFolderName) {
            $entry->container_id = $folderId;
        } else {
            $containerId = Tinebase_Core::getPreference('ActiveSync')->{$this->_defaultFolder};
            
            if (Tinebase_Core::getUser()->hasGrant($containerId, Tinebase_Model_Grants::GRANT_ADD) === true) {
                $entry->container_id = $containerId;
            }
        }
        
        try {
            $entry = $this->_contentController->create($entry);
        } catch (Tinebase_Exception_AccessDenied $tead) {
            throw new Syncroton_Exception_AccessDenied();
        }
        
        if (Tinebase_Core::isLogLevel(Zend_Log::DEBUG)) Tinebase_Core::getLogger()->debug(
                __METHOD__ . '::' . __LINE__ . " added entry id " . $entry->getId());

        return $entry->getId();
    }
    
    /**
     * (non-PHPdoc)
     * @see Syncroton_Data_IData::createFolder()
     */
    public function createFolder(Syncroton_Model_IFolder $folder)
    {
        $container = Tinebase_Container::getInstance()->addContainer(new Tinebase_Model_Container(array(
            'name'              => $folder->displayName,
            'type'              => Tinebase_Model_Container::TYPE_PERSONAL,
            'owner_id'          => Tinebase_Core::getUser(),
            'backend'           => 'Sql',
            'application_id'    => Tinebase_Application::getInstance()->getApplicationByName($this->_applicationName)->getId()
        )));
        
        $folder->serverId = $container->getId();
        
        return $folder;
    }
    
    /**
     * (non-PHPdoc)
     * @see Syncroton_Data_IData::deleteFolder()
     */
    public function deleteFolder($_folderId)
    {
        
    }
    
    /**
     * (non-PHPdoc)
     * @see Syncroton_Data_IData::getEntry()
     */
    public function getEntry(Syncroton_Model_SyncCollection $collection, $serverId)
    {
        // is $serverId a LongId?
        if (strpos($serverId, ActiveSync_Controller_Abstract::LONGID_DELIMITER) !== false) {
            list($collection->collectionId, $serverId) = explode(ActiveSync_Controller_Abstract::LONGID_DELIMITER, $serverId, 2);
        }
        
        try {
            $entry = $this->_contentController->get($serverId);
        } catch (Tinebase_Exception_NotFound $tenf) {
            throw new Syncroton_Exception_NotFound();
        }
        
        return $this->toSyncrotonModel($entry, $collection->options);
    }
    
    /**
     * (non-PHPdoc)
     * @see Syncroton_Data_IData::getFileReference()
     */
    public function getFileReference($fileReference)
    {
        
    }
    
    /**
     * (non-PHPdoc)
     * @see Syncroton_Data_IData::updateEntry()
     */
    public function updateEntry($folderId, $serverId, Syncroton_Model_IEntry $entry)
    {
        if (Tinebase_Core::isLogLevel(Zend_Log::DEBUG)) 
            Tinebase_Core::getLogger()->debug(__METHOD__ . '::' . __LINE__ . " update CollectionId: $folderId Id: $serverId");
        
        try {
            $oldEntry = $this->_contentController->get($serverId);
        } catch (Tinebase_Exception_NotFound $tenf) {
            if (Tinebase_Core::isLogLevel(Zend_Log::DEBUG)) Tinebase_Core::getLogger()->debug(__METHOD__ . '::' . __LINE__ 
                . ' ' . $tenf);
            throw new Syncroton_Exception_NotFound($tenf->getMessage());
        }
        
        $updatedEmtry = $this->toTineModel($entry, $oldEntry);
        $updatedEmtry->last_modified_time = new Tinebase_DateTime($this->_syncTimeStamp);
        
        try {
            $updatedEmtry = $this->_contentController->update($updatedEmtry);
        } catch (Tinebase_Exception_AccessDenied $tead) {
            throw new Syncroton_Exception_AccessDenied();
        }
        
        return $updatedEmtry->getId();
    }
    
    /**
     * (non-PHPdoc)
     * @see Syncroton_Data_IData::updateFolder()
     */
    public function updateFolder(Syncroton_Model_IFolder $folder)
    {
        
    }
    
    /**
     * (non-PHPdoc)
     * @see Syncroton_Data_IData::deleteEntry()
     */
    public function deleteEntry($folderId, $serverId, $collectionData)
    {
        if (Tinebase_Core::isLogLevel(Zend_Log::DEBUG)) 
            Tinebase_Core::getLogger()->debug(__METHOD__ . '::' . __LINE__ . " delete ColectionId: $folderId Id: $serverId");
        
        try {
            $this->_contentController->delete($serverId);
        } catch (Tinebase_Exception_AccessDenied $tead) {
            throw new Syncroton_Exception_AccessDenied();
        } catch (Tinebase_Exception_NotFound $tenf) {
            throw new Syncroton_Exception_NotFound();
        }
    }
    
    /**
     * search for existing entry in all syncable folders
     *
     * @param string            $_forlderId
     * @param SimpleXMLElement  $_data
     * @return Tinebase_Record_Abstract
     */
    #public function search($_folderId, SimpleXMLElement $_data)
    #{
    #    if (Tinebase_Core::isLogLevel(Zend_Log::DEBUG)) Tinebase_Core::getLogger()->debug(__METHOD__ . '::' . __LINE__ . " CollectionId: $_folderId");
    #    
    #    $filterArray  = $this->_toTineFilterArray($_data);
    #    $filter       = new $this->_contentFilterClass($filterArray);
    #    
    #    $this->_addContainerFilter($filter, $_folderId);
    #    
    #    $foundEmtries = $this->_contentController->search($filter);
    #
    #    if (Tinebase_Core::isLogLevel(Zend_Log::DEBUG)) Tinebase_Core::getLogger()->debug(__METHOD__ . '::' . __LINE__ . " found " . count($foundEmtries));
    #        
    #    return $foundEmtries;
    #}
    
    /**
     * used by the mail backend only. Used to update the folder cache
     * 
     * @param  string  $_folderId
     */
    public function updateCache($_folderId)
    {
        // does nothing by default
    }
    
    /**
     * add container acl filter to filter group
     * 
     * @param Tinebase_Model_Filter_FilterGroup $_filter
     * @param string                            $_containerId
     */
    protected function _addContainerFilter(Tinebase_Model_Filter_FilterGroup $_filter, $_containerId)
    {
        $syncableContainers = $this->_getSyncableFolders();
        
        $containerIds = array();
        
        if($_containerId == $this->_specialFolderName) {
            $containerIds = $syncableContainers->getArrayOfIds();
        } elseif(in_array($_containerId, $syncableContainers->id)) {
            $containerIds = array($_containerId);
        }

        $_filter->addFilter($_filter->createFilter('container_id', 'in', $containerIds));
    }
    
    /**
     * get syncable folders
     * 
     * @return array
     */
    protected function _getSyncableFolders()
    {
        $folders = array();
        
        $containers = Tinebase_Container::getInstance()->getContainerByACL(Tinebase_Core::getUser(), $this->_applicationName, Tinebase_Model_Grants::GRANT_SYNC);
        
        return $containers;
    }
    
    /**
     * (non-PHPdoc)
     * @see Syncroton_Data_IData::getChangedEntries()
     */
    public function getChangedEntries($folderId, DateTime $_startTimeStamp, DateTime $_endTimeStamp = NULL)
    {
        $filter = $this->_getContentFilter(0);
        
        $this->_addContainerFilter($filter, $folderId);
        
        $startTimeStamp = ($_startTimeStamp instanceof DateTime) ? $_startTimeStamp->format(Tinebase_Record_Abstract::ISO8601LONG) : $_startTimeStamp;
        $endTimeStamp = ($_endTimeStamp instanceof DateTime) ? $_endTimeStamp->format(Tinebase_Record_Abstract::ISO8601LONG) : $_endTimeStamp;
        
        // @todo filter also for create_timestamo??
        $filter->addFilter(new Tinebase_Model_Filter_DateTime(
            'last_modified_time',
            'after',
            $startTimeStamp
        ));
        
        if($endTimeStamp !== NULL) {
            $filter->addFilter(new Tinebase_Model_Filter_DateTime(
                'last_modified_time',
                'before',
                $endTimeStamp
            ));
        }
        
        $result = $this->_contentController->search($filter, NULL, false, true, 'sync');
        
        return $result;
    }    
    
    /**
     * 
     * @param unknown_type $_folderId
     * @param unknown_type $_filterType
     * @return Ambigous <Tinebase_Record_RecordSet, multitype:>
     */
    public function getServerEntries($folderId, $filterType)
    {
        $filter = $this->_getContentFilter($filterType);
        $this->_addContainerFilter($filter, $folderId);
        
        if(!empty($this->_sortField)) {
            $pagination = new Tinebase_Model_Pagination(array(
                'sort' => $this->_sortField
            ));
        } else {
            $pagination = null;
        }
        
        //if (Tinebase_Core::isLogLevel(Zend_Log::DEBUG)) Tinebase_Core::getLogger()->debug(__METHOD__ . '::' . __LINE__ . " assembled {$this->_contentFilterClass}: " . print_r($filter->toArray(), TRUE));
        $result = $this->_contentController->search($filter, $pagination, false, true, 'sync');
        
        return $result;
    }
    
    /**
     * (non-PHPdoc)
     * @see Syncroton_Data_IData::getCountOfChanges()
     */
    public function getCountOfChanges(Syncroton_Backend_IContent $contentBackend, Syncroton_Model_IFolder $folder, Syncroton_Model_ISyncState $syncState)
    {
        $this->updateCache($folder->serverId);
        
        $allClientEntries = $contentBackend->getFolderState($this->_device, $folder);
        $allServerEntries = $this->getServerEntries($folder->serverId, $folder->lastfiltertype);
        
        $addedEntries       = array_diff($allServerEntries, $allClientEntries);
        $deletedEntries     = array_diff($allClientEntries, $allServerEntries);
        $changedEntries     = $this->getChangedEntries($folder->serverId, $syncState->lastsync);
        
        return count($addedEntries) + count($deletedEntries) + count($changedEntries);
    }
    
    /**
     * return (outer) contentfilter array
     * 
     * @param  int $_filterType
     * @return Tinebase_Model_Filter_FilterGroup
     */
    protected function _getContentFilter($_filterType)
    {
        $filter = new $this->_contentFilterClass();
        
        try {
            $persistentFilterId = $this->_device->{$this->_filterProperty};
            if ($persistentFilterId) {
                $filter = Tinebase_PersistentFilter::getFilterById($persistentFilterId);
            }
        } catch (Tinebase_Exception_NotFound $tenf) {
            // filter got deleted already
        }
        
        return $filter;
    }
    
    /**
     * convert contact from xml to Tinebase_Record_Interface
     *
     * @param SimpleXMLElement $_data
     * @return Tinebase_Record_Interface
     */
    abstract public function toTineModel(Syncroton_Model_IEntry $data, $entry = null);
    
    abstract public function toSyncrotonModel($entry, array $options = array());
}
