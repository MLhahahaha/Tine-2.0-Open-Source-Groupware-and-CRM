<?php
/**
 * Tine 2.0
 *
 * @package     Felamimail
 * @subpackage  Controller
 * @license     http://www.gnu.org/licenses/agpl.html AGPL Version 3
 * @author      Philipp Schuele <p.schuele@metaways.de>
 * @copyright   Copyright (c) 2009 Metaways Infosystems GmbH (http://www.metaways.de)
 * @version     $Id$
 * 
 */

/**
 * Account controller for Felamimail
 *
 * @package     Felamimail
 * @subpackage  Controller
 */
class Felamimail_Controller_Account extends Tinebase_Controller_Record_Abstract
{
    /**
     * application name (is needed in checkRight())
     *
     * @var string
     */
    protected $_applicationName = 'Felamimail';
    
    /**
     * holdes the instance of the singleton
     *
     * @var Felamimail_Controller_Account
     */
    private static $_instance = NULL;
    
    /**
     * the constructor
     *
     * don't use the constructor. use the singleton
     */
    private function __construct() {
        $this->_modelName = 'Felamimail_Model_Account';
        $this->_doContainerACLChecks = FALSE;
        $this->_backend = new Felamimail_Backend_Account();
        
        $this->_currentAccount = Tinebase_Core::getUser();
    }
    
    /**
     * don't clone. Use the singleton.
     *
     */
    private function __clone() 
    {        
    }
    
    /**
     * the singleton pattern
     *
     * @return Felamimail_Controller_Account
     */
    public static function getInstance() 
    {
        if (self::$_instance === NULL) {            
            self::$_instance = new Felamimail_Controller_Account();
        }
        
        return self::$_instance;
    }

    /******************************** overwritten funcs *********************************/
    
    /**
     * get list of records
     *
     * @param Tinebase_Model_Filter_FilterGroup|optional $_filter
     * @param Tinebase_Model_Pagination|optional $_pagination
     * @param bool $_getRelations
     * @param boolean $_onlyIds
     * @return Tinebase_Record_RecordSet|array
     */
    public function search(Tinebase_Model_Filter_FilterGroup $_filter = NULL, Tinebase_Record_Interface $_pagination = NULL, $_getRelations = FALSE, $_onlyIds = FALSE)
    {
        if ($_filter === NULL) {
            $_filter = new Felamimail_Model_AccountFilter(array());
        }
        
        $result = parent::search($_filter, $_pagination, $_getRelations, $_onlyIds);
        
        // check preference / config if we should add default account with tine user credentials or from config.inc.php 
        $this->_addDefaultAccount($result);
        
        return $result;
    }

    /**
     * Gets total count of search with $_filter
     * 
     * @param Tinebase_Model_Filter_FilterGroup $_filter
     * @return int
     */
    public function searchCount(Tinebase_Model_Filter_FilterGroup $_filter) 
    {
        $count = parent::searchCount($_filter);        
        if (isset(Tinebase_Core::getConfig()->imap)) {
            $count++;
        }
        return $count;
    }
    
    /**
     * get by id
     *
     * @param string $_id
     * @param int $_containerId
     * @return Tinebase_Record_Interface
     */
    public function get($_id, $_containerId = NULL)
    {
        if ($_id === Felamimail_Model_Account::DEFAULT_ACCOUNT_ID) {
            if (! isset(Tinebase_Core::getConfig()->imap)) {
                throw new Felamimail_Exception('No default imap account defined in config.inc.php!');
            }
            
            // get account data from config file    
            $record = new Felamimail_Model_Account(Tinebase_Core::getConfig()->imap->toArray());
        } else {
            $record = parent::get($_id, $_containerId);
        }
        
        return $record;    
    }
    
    /**
     * Removes accounts where current user has no access to
     * 
     * @param Tinebase_Model_Filter_FilterGroup $_filter
     * @param string $_action get|update
     */
    protected function _checkFilterACL(/*Tinebase_Model_Filter_FilterGroup */$_filter, $_action = 'get')
    {        
        foreach ($_filter->getFilterObjects() as $filter) {
            if ($filter->getField() === 'user_id') {
                $userFilter = $filter;
                $userFilter->setValue($this->_currentAccount->getId());
            }
        }
        
        if (! isset($userFilter)) {
            // force a $userFilter filter (ACL)
            $userFilter = $_filter->createFilter('user_id', 'equals', $this->_currentAccount->getId());
            $_filter->addFilter($userFilter);
            
            Tinebase_Core::getLogger()->debug(__METHOD__ . '::' . __LINE__ . ' Adding user_id filter.');
        }
    }

    /**
     * inspect creation of one record
     * - add credentials here
     * 
     * @param   Tinebase_Record_Interface $_record
     * @return  void
     */
    protected function _inspectCreate(Tinebase_Record_Interface $_record)
    {
        if (! $this->user || ! $this->password) {
            Tinebase_Core::getLogger()->debug(__METHOD__ . '::' . __LINE__ . ' No username or password given for new account.');
            return;    
        }
        
        $_record->credentials_id = $this->_createCredentials($_record->user, $_record->password);
    }

    /**
     * inspect update of one record
     * - update credentials here
     * 
     * @param   Tinebase_Record_Interface $_record      the update record
     * @param   Tinebase_Record_Interface $_oldRecord   the current persistent record
     * @return  void
     */
    protected function _inspectUpdate($_record, $_oldRecord)
    {
        // get old credentials
        $credentialsBackend = Tinebase_Auth_CredentialCache::getInstance();
        $userCredentialCache = Tinebase_Core::get(Tinebase_Core::USERCREDENTIALCACHE);
        $credentialsBackend->getCachedCredentials($userCredentialCache);
        
        $credentials = $credentialsBackend->get($_oldRecord->credentials_id);
        $credentials->key = substr($userCredentialCache->password, 0, 24);
        $credentialsBackend->getCachedCredentials($credentials);
        
        // check if something changed
        if (
                (! empty($_record->user) && $_record->user !== $credentials->username) 
            ||  (! empty($_record->password) && $_record->password !== $credentials->password)
        ) {
            $newPassword = ($_record->password) ? $_record->password : $credentials->password;
            $newUsername = ($_record->user) ? $_record->user : $credentials->username;

            $_record->credentials_id = $this->_createCredentials($newUsername, $newPassword);
        }
    }
    
    /******************************** protected funcs *********************************/

    /**
     * add default account with tine user credentials or from config.inc.php 
     *
     * @param Tinebase_Record_RecordSet $_accounts
     * 
     * @todo get default account data (host, port, ...) from preferences?
     */
    protected function _addDefaultAccount($_accounts)
    {
        // add account from config.inc.php if available
        if (isset(Tinebase_Core::getConfig()->imap) && Tinebase_Core::getConfig()->imap->useAsDefault) {
            try {
                $defaultAccount = new Felamimail_Model_Account(
                    Tinebase_Core::getConfig()->imap->toArray()
                );
                $defaultAccount->setId('default');
                $_accounts->addRecord($defaultAccount);
            } catch (Tinebase_Exception $e) {
                Tinebase_Core::getLogger()->warn(__METHOD__ . '::' . __LINE__ . $e->getMessage());
            }
        // create new account with user credentials (if preference is set)
        } else if (count($_accounts) == 0 && Tinebase_Core::getPreference('Felamimail')->userEmailAccount) {
            $defaultAccount = new Felamimail_Model_Account(Tinebase_Core::getConfig()->imap->toArray(), TRUE);
            
            $userId = $this->_currentAccount->getId();
            $defaultAccount->user_id = $userId;
            
            $fullUser = Tinebase_User::getInstance()->getFullUserById($userId);
            $defaultAccount->user   = $fullUser->accountLoginName;
            
            // only create account if email address is set
            if ($fullUser->accountEmailAddress) {
                $defaultAccount->email  = $fullUser->accountEmailAddress;
                $defaultAccount->name   = $fullUser->accountEmailAddress;
                $defaultAccount->from   = $fullUser->accountFullName;
                
                // get password from credentials cache and create account credentials
                $defaultAccount->credentials_id = $this->_createCredentials();

                // create new account
                $defaultAccount = $this->_backend->create($defaultAccount);
                $_accounts->addRecord($defaultAccount);
                
                // set as default account preference
                Tinebase_Core::getPreference('Felamimail')->{Felamimail_Preference::DEFAULTACCOUNT} = $defaultAccount->getId();
                
                Tinebase_Core::getLogger()->debug(__METHOD__ . '::' . __LINE__ . ' Created new default account ' . $defaultAccount->name);
            }
        }
    }
    
    /**
     * create account credentials and return new credentials id
     *
     * @param string $_username
     * @param string $_password
     * @return string
     */
    protected function _createCredentials($_username = NULL, $_password = NULL)
    {
        $userCredentialCache = Tinebase_Core::get(Tinebase_Core::USERCREDENTIALCACHE);
        Tinebase_Auth_CredentialCache::getInstance()->getCachedCredentials($userCredentialCache);

        $accountCredentials = Tinebase_Auth_CredentialCache::getInstance()->cacheCredentials(
            ($_username !== NULL) ? $_username : $userCredentialCache->username,
            ($_password !== NULL) ? $_password : $userCredentialCache->password,
            $userCredentialCache->password
        );
        return $accountCredentials->getId();
    }
}
