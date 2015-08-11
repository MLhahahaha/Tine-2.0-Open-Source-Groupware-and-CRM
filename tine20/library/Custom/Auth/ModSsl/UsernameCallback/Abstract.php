<?php

/**
 * Tine 2.0
 *
 * @package     Custom
 * @subpackage  Auth
 * @license     http://www.gnu.org/licenses/agpl.html AGPL Version 3
 * @author      Antonio Carlos da Silva <antonio-carlos.silva@serpro.gov.br>
 * @author      Mario Cesar Kolling <mario.kolling@serpro.gov.br>
 * @copyright   Copyright (c) 2009-2014 Serpro (http://www.serpro.gov.br)
 * @copyright   Copyright (c) 2013 Metaways Infosystems GmbH (http://www.metaways.de)
 * 
 */

abstract class Custom_Auth_ModSsl_UsernameCallback_Abstract implements Custom_Auth_ModSsl_UsernameCallback_Interface
{
    /**
     * @var Custom_Auth_ModSsl_Certificate_X509
     */
    protected $certificate;
            
    public function __construct(Custom_Auth_ModSsl_Certificate_X509 $certificate)
    {
        $this->certificate = $certificate;
    }
    
    public function getUsername()
    {
        return $this->certificate->getEmail();
    }
}