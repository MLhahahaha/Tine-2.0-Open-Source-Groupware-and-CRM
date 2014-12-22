<?php
/**
 * Tine 2.0 - http://www.tine20.org
 * 
 * @package     ActiveSync
 * @license     http://www.gnu.org/licenses/agpl.html
 * @copyright   Copyright (c) 2010-2015 Metaways Infosystems GmbH (http://www.metaways.de)
 * @author      Jonas Fischer <j.fischer@metaways.de>
 */

class ActiveSync_AllTests
{
    public static function main ()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }
    
    public static function suite ()
    {
        $suite = new PHPUnit_Framework_TestSuite('Tine 2.0 ActiveSync All Tests');
        
        $suite->addTestSuite('ActiveSync_TimezoneConverterTest');
        $suite->addTestSuite('ActiveSync_Command_AllTests');
        $suite->addTestSuite('ActiveSync_Controller_AllTests');
        $suite->addTestSuite('ActiveSync_Backend_AllTests');
        
        $suite->addTestSuite('ActiveSync_Frontend_JsonTests');
        
        return $suite;
    }
}
