<?php

namespace Ideal\Openemm\Services;

/* * *************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2014 Markus Pircher <technik@idealit.com>, IDEAL
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 * ************************************************************* */

/**
 * OpenEMM Service, aviable in other Extensions
 */
class OpenEMMService {

    /**
     * @var \Ideal\Openemm\Services\Api\WsseSoapClient $wsseSoapClient
     */
    private $wsseSoapClient = null;

    /**
     * @var array $settings
     */
    private $settings = array();

    /**
     * Typo3 Object Manager
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager
     */
    public $objectManager = null;
    
     /**
     * @param array $settings
     */
    public function Init(array $settings = null) {
        $this->objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        if ($settings == null) {
            $configurationManager = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManager');
            $settings = $configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, 'openemm');
        }
        $this->settings = $settings;
        if (is_array($this->settings) && count($this->settings) > 0) {
            $this->wsseSoapClient = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance("\\Ideal\\Openemm\\Services\\Api\\WsseSoapClient", 
                    $this->settings['webservice']['wsdl'], 
                    $this->settings['webservice']['username'], 
                    $this->settings['webservice']['password'], 
                    null, 
                    $this->settings['webservice']['soapOption']);
        }
        return $this->settings;
    }

    /**
     * Get OpenEMM Soap Client
     * @return \Ideal\Openemm\Services\Api\WsseSoapClient $wsseSoapClient
     */
    public function GetSoapClient() {
        return $this->wsseSoapClient;
    }

}
