<?php
/**
 * Tine 2.0 - http://www.tine20.org
 * 
 * @package     Felamimail
 * @license     http://www.gnu.org/licenses/agpl.html
 * @copyright   Copyright (c) 2010 Metaways Infosystems GmbH (http://www.metaways.de)
 * @author      Philipp Schuele <p.schuele@metaways.de>
 * 
 */

/**
 * Test helper
 */
require_once dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'TestHelper.php';

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Expressomail_Model_AccountTest::main');
}

/**
 * Test class for Felamimail_Model_AccountTest
 */
class Expressomail_Model_AccountTest extends PHPUnit_Framework_TestCase
{
    /**
     * Runs the test methods of this class.
     *
     * @access public
     * @static
     */
    public static function main()
    {
        $suite  = new PHPUnit_Framework_TestSuite('Tine 2.0 Expressomail Account Model Tests');
        PHPUnit_TextUI_TestRunner::run($suite);
    }

    /**
     * Sets up the fixture.
     * This method is called before a test is executed.
     *
     * @access protected
     */
    protected function setUp()
    {
    }

    /**
     * Tears down the fixture
     * This method is called after a test is executed.
     *
     * @access protected
     */
    protected function tearDown()
    {
    }

    /********************************* test funcs *************************************/
    
    /**
     * test get smtp config
     */
    // TODO: Fix this test. (task13912)
    /*public function testGetSmtpConfig()
    {
        $smtpConfig = Tinebase_Config::getInstance()->get(Tinebase_Config::SMTP, new Tinebase_Config_Struct())->toArray();
        
        $account = new Expressomail_Model_Account(array(
            'type'      => Expressomail_Model_Account::TYPE_SYSTEM,
        ));
        $accountSmtpConfig = $account->getSmtpConfig();
        
        if (array_key_exists('primarydomain', $smtpConfig)) {
            $this->assertContains($smtpConfig['primarydomain'], $accountSmtpConfig['username']);
        }
        
        if (TestServer::getInstance()->getConfig()->mailserver) {
            $this->assertEquals(TestServer::getInstance()->getConfig()->mailserver, $accountSmtpConfig['hostname']);
        }
    }*/

    /**
     * test get username email as login name
     */
    public function testGetUsernameEmailAsLoginName()
    {
        $imapConfig = Tinebase_Config::getInstance()->get(Tinebase_Config::IMAP, new Tinebase_Config_Struct())->toArray();

        $account = new Expressomail_Model_Account(array(
            'type'  => Expressomail_Model_Account::TYPE_SYSTEM,
        ));

        $useUsernameAsLoginName = isset($imapConfig['useEmailAsLoginName']) ? !$imapConfig['useEmailAsLoginName'] : TRUE;
        if($useUsernameAsLoginName) {
            $validator = new Zend_Validate_EmailAddress();
            $this->assertTrue($validator->isValid($account->getUsername()));
        }
    }

    /**
     * test get username not email as login name
     */
    public function testGetUsernameNotEmailAsLoginName()
    {
        $imapConfig = Tinebase_Config::getInstance()->get(Tinebase_Config::IMAP, new Tinebase_Config_Struct())->toArray();

        $account = new Expressomail_Model_Account(array(
            'type'  => Expressomail_Model_Account::TYPE_SYSTEM,
        ));

        $useUsernameAsLoginName = isset($imapConfig['useEmailAsLoginName']) ? !$imapConfig['useEmailAsLoginName'] : TRUE;
        if(!$useUsernameAsLoginName) {
            $validator = new Zend_Validate_EmailAddress();
            $this->assertFalse($validator->isValid($account->getUsername()));
        }
    }
}