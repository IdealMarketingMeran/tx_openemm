<?php

namespace Ideal\Openemm\Utility;

/* **************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2015 Markus Pircher <technik@idealit.com>, IDEAL
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


class FlexFormHelper {
    
    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager 
     */
    private $objectManager = null;
    
    /**
     * webservice settings
     * @var array
     */
    private $settings;
    
    /**
     * OpenEMM Service
     * @var \Ideal\Openemm\Services\OpenEMMService 
     */
    private $openEmmService = null;
    
    /**
     * Field Cache
     * @var array 
     */
    private $cache;
    
    private function Init()
    {
        $pluginConfiguration = array(
            'extensionName' => 'openemm',
            'pluginName' => 'Pi1'
        );
        $bootstrap = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Core\\Bootstrap');
        $bootstrap->initialize($pluginConfiguration);
        $this->objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        
        $configurationManager = $this->objectManager->get('TYPO3\\CMS\Extbase\\Configuration\\ConfigurationManager');
        $configuration = $configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManager::CONFIGURATION_TYPE_FULL_TYPOSCRIPT, 'openemm');
        
        $newConf['webservice'] = $configuration['plugin.']['tx_openemm.']['settings.']['webservice.'];
        $newConf['webservice']['soapOption'] = $configuration['plugin.']['tx_openemm.']['settings.']['webservice.']['soapOption.'];
        $newConf['fieldTypes'] = $configuration['plugin.']['tx_openemm.']['settings.']['fieldTypes.'];        
        $this->settings = $newConf;
        $this->openEmmService = $this->objectManager->get("Ideal\\Openemm\\Services\\OpenEMMService");
        
        try {
            $this->openEmmService->Init($newConf);            
        } catch (\Exception $ex) {
            throw new \Exception("OpenEMMService->Init: " . $ex->getMessage());
        }
    }
    
    /**
     * get all Malinglists for Flexform
     * @param array $fConfig
     * @param type $fObj
     * @return type
     * @throws \Exception
     */
    public function getMailinglists(&$fConfig, $fObj) {
        if($this->openEmmService == null ) {
            $this->Init();
        }
        if(!isset($this->cache['Mailinglists'])) {
            try {
                $lists = $this->openEmmService->ListMailinglists();
            } catch (\SoapFault $ex) {
                throw $ex;
            }
            $options = array();
            foreach($lists->item as $item) {
                $options[] = array(
                    $item->shortname, 
                    $item->id
                );
            }
            $this->cache['Mailinglists'] = $options;
        } elseif(is_array($this->cache['Mailinglists'])) {
            $options = $this->cache['Mailinglists'];
        } else {
            throw new \Exception('getMailinglists failed');
        }  
        $fConfig['items'] = array_merge($fConfig['items'], $options);
    }
    
    /**
     * get Example User
     * @param array $fConfig
     * @param type $fObj
     * @return type
     * @throws \Exception
     */
    public function getSubscriberFields(&$fConfig, $fObj) {        
        if($this->openEmmService == null ) {
            $this->Init();
        }
        $exclude = array(
            'creation_date_second_date',
            'change_date',
            'creation_date_minute_date',
            'change_date_hour_date',
            'change_date_month_date',
            'change_date_minute_date',
            'change_date_year_date',
            'campaign_source',
            'change_date_second_date',
            'datasource_id',
            'creation_date_month_date',
            'change_date_day_date',
            'creation_date',
            'creation_date_day_date',
            'creation_date_year_date',
            'creation_date_hour_date',
            'customer_id'
        );
        $defaultType = ":textfield";
        if(!isset($this->cache['SubscriberFields'])) {
            try {
                $subscriber = $this->openEmmService->GetSubscriber(1);
            } catch (\SoapFault $ex) {
                throw $ex;
            }
            $options = array();
            foreach($subscriber->parameters as $key => $value) {
                if(in_array($key, $exclude)) {
                    continue;
                }
                $options[] = array(
                    $key,
                    $key . (is_array($this->settings['fieldTypes']) && in_array($key, $this->settings['fieldTypes']) ? ' : ' . $this->settings['fieldTypes'][$key] : $defaultType)
                );
            }
            $this->cache['SubscriberFields'] = $options;
        } elseif(is_array($this->cache['SubscriberFields'])) {
            $options = $this->cache['SubscriberFields'];
        } else {
            throw new \Exception('getSubscriberFields failed');
        }
        $fConfig['items'] = array_merge($fConfig['items'], $options);        
    }
}
