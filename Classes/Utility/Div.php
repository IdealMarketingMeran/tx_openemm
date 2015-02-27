<?php

namespace Ideal\Openemm\Utility;

use \TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

/* * *************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2015 Markus Pircher <technik@idealit.com>, IDEAL GmBh
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
 * Functions
 */
class Div {
    
    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager 
     * @inject
     */
    private $objectManager = null;

    /**
     * Prepare Participants Fields
     * 
     * @param array $settings
     * @param array $fieldErrors
     * @param string $settingspart
     * @return array
     */
    public function prepareFields(array $settings, array $fieldErrors, $settingspart = 'new', $request = array()) {
        $fieldsReturn = array('fields' => array(), 'required' => array());

        $ofields = explode(',', $settings[$settingspart]['subscriber']['fields']);
        $fields = array();
        $requireds = array();
        if (is_array($settings[$settingspart]['subscriber']) && array_key_exists('required', $settings[$settingspart]['subscriber']) && !empty($settings[$settingspart]['subscriber']['required'])) {
            $requireds = explode(',', $settings[$settingspart]['subscriber']['required']);
        }
        $fieldsReturn['required'] = $requireds;
        foreach ($ofields as $field) {
            $fieldInfo = explode(':', $field);
            $fields[$fieldInfo[0]]['property'] = $fieldInfo[0];
            $fieldLabel = LocalizationUtility::translate('fields.' . GeneralUtility::camelCaseToLowerCaseUnderscored($fieldInfo[0]), 'openemm');
            $fields[$fieldInfo[0]]['name'] = !empty($fieldLabel) ? $fieldLabel : $fieldInfo[0] ;

            //Fieldtype
            if (count($fieldInfo) > 1) {
                $fields[$fieldInfo[0]]['type'] = $fieldInfo[1];
            } else {
                $fields[$fieldInfo[0]]['type'] = 'textfield';
            }
            if ($fieldInfo[1] == 'select' || $fieldInfo[1] == 'radio') {
                $options = array();
                //Gender
                if ($fieldInfo[0] == 'gender') {
                    //$options[0] = LocalizationUtility::translate('pleaceSelect', 'openemm');
                    $options[0] = LocalizationUtility::translate('male', 'openemm');
                    $options[1] = LocalizationUtility::translate('female', 'openemm');
                }
                //Title
                if ($fieldInfo[0] == 'title') {
                    $options[0] = LocalizationUtility::translate('pleaceSelect', 'openemm');
                    for ($i = 1; $i < 50; $i++) {
                        $selectName = LocalizationUtility::translate('fields.' . GeneralUtility::camelCaseToLowerCaseUnderscored($fieldInfo[0]) . '.' . $i, 'openemm');
                        if ($selectName != NULL) {
                            $options[$i] = $selectName;
                        } else {
                            break;
                        }
                    }
                }
                if($fieldInfo[0] == 'country' || $fieldInfo[0] == 'region' || $fieldInfo[0] == 'zone')
                    $countryRepository = $this->objectManager->get('SJBR\\StaticInfoTables\\Domain\\Repository\\CountryRepository');
                
                //Country
                if ($fieldInfo[0] == 'country') {
                    $options[0] = LocalizationUtility::translate('pleaceSelect', 'openemm');
                    $countrys = $countryRepository->findAll();

                    foreach($countrys as $country) {
                        //$options[$country->getUid()] = $country->getShortNameLocal();
                        $options[$country->getIsoCodeA3()] = $country->getShortNameLocal();
                    }
                    $fields[$fieldInfo[0]]['options'] = $options;
                }
                //Region-Zone
                //$request['country'] = "ITA";
                if ($fieldInfo[0] == 'region' || $fieldInfo[0] == 'zone') {
                    $options[0] = LocalizationUtility::translate('pleaceSelect', 'openemm'); 
                    if(count($request) > 0 && isset($request['country'])) {                  

                        //$country = $countryRepository->findAllOrderedBy("isoCodeA3");

                        $zoneRepository = $this->objectManager->get('Ideal\\Openemm\\Domain\\Repository\\CountryZoneRepository');
                        $zones = $zoneRepository->findByIsoCodeA3($request['country']);
                        foreach($zones as $zone) {
                            //$options[$zone->getUid()] = $zone->getLocalName();
                            $options[$zone->getIsoCode()] = $zone->getLocalName();
                        }
                    } else {
                        //$options[0] = LocalizationUtility::translate('pleaceSelectContry', 'openemm');  
                    }
                }
                $fields[$fieldInfo[0]]['options'] = $options;
            }

            //required
            if (in_array($fieldInfo[0], $requireds, true)) {
                $fields[$fieldInfo[0]]['required'] = true;
            } else {
                $fields[$fieldInfo[0]]['required'] = false;
            }

            //errors
            if (array_key_exists($fieldInfo[0], $fieldErrors)) {
                $fields[$fieldInfo[0]]["hasError"] = $fieldErrors[$fieldInfo[0]]["hasError"];
                if (array_key_exists('errorClassWrap', $settings[$settingspart]['new'])) {
                    $fields[$fieldInfo[0]]["errorClassWrap"] = ' ' . $settings[$settingspart]['new']['errorClassWrap'];
                }
                if (array_key_exists('errorClassInput', $settings[$settingspart]['new'])) {
                    $fields[$fieldInfo[0]]["errorClassInput"] = ' ' . $settings[$settingspart]['new']['errorClassInput'];
                }
            } else {
                $fields[$fieldInfo[0]]["hasError"] = false;
            }
        }
        $fieldsReturn['fields'] = $fields;
        return $fieldsReturn;
    }

    /**
     * Paping Values to Field Array
     * 
     * @param array $fields
     * @param array $arguments
     */
    public function mappingFields(array $fields, array $arguments) {
        if(array_key_exists('subscriber', $arguments)) {
            foreach ($fields as $name => $field) {
                if(is_array($arguments['subscriber']) && array_key_exists($name, $arguments['subscriber']))
                {
                    if($name == 'country' || $name == 'region' || $name == 'zone')
                        $countryRepository = $this->objectManager->get('Ideal\\Openemm\\Domain\\Repository\\CountryRepository');
                    if($name == 'country') {
                        $country = $countryRepository->findByIsoCodeA3($arguments['subscriber'][$name])->getFirst();
                        $fields[$name]['value'] = $country->getShortNameLocal();
                    } elseif($name == 'region' || $name == 'zone') {
                        $zoneRepository = $this->objectManager->get('Ideal\\Openemm\\Domain\\Repository\\CountryZoneRepository');
                        $zone = $zoneRepository->findByIsoCodeA3($arguments['subscriber']['country'])->getFirst();
                        $fields[$name]['value'] = $zone->getLocalName();
                    } elseif($name == 'title') {
                        $fields[$name]['value'] = LocalizationUtility::translate('fields.title.' . $arguments['subscriber'][$name], 'openemm');
                    } elseif($name == 'gender') {
                        $gender = $arguments['subscriber'][$name] == 0 ? LocalizationUtility::translate('male', 'openemm') : LocalizationUtility::translate('female', 'openemm');
                        $fields[$name]['value'] = $gender;
                    } else {
                        $fields[$name]['value'] = $arguments['subscriber'][$name];
                    }
                }
            }
        } else {
            $fields['ERROR']['argument'] = $arguments;
        }
        return $fields;
    }

    /**
     * Return current logged in fe_user
     *
     * @return object
     */
    public function getCurrentUser() {
        if (!is_array($GLOBALS['TSFE']->fe_user->user)) {
            return NULL;
        }
        return $this->feusersRepository->findByUid($GLOBALS['TSFE']->fe_user->user['uid']);
    }

}
