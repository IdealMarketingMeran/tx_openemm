<?php

namespace Ideal\Openemm\Controller;

use \TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \Ideal\Openemm\Validation\Validator;
use \Ideal\Openemm\Validation\SubscriberValidator;
use \Ideal\Openemm\Validation\ValidationError;
use TYPO3\CMS\Core\Utility\PathUtility;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2015 Markus Pircher <technik@idealit.it>, IDEAL
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
 ***************************************************************/

/**
 * Description of SubscriberController
 *
 * @author Markus Pircher
 */
class SubscriberController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

    /**
     * Utilitys
     *
     * @var \Ideal\Openemm\Utility\Div
     * @inject
     */
    protected $div = NULL;

    /**
     * FrontendUser Utility
     *
     * @var \Ideal\Openemm\Utility\FrontendUserUtility
     * @inject
     */
    protected $frontendUserUtility = NULL;

    /**
     * OpenEMM Service
     * @var \Ideal\Openemm\Services\OpenEMMService
     */
    private $openEmmService = null;

    /**
     * Errors
     *
     * @var array
     */
    private $errorArray;

    /**
     * Errors
     *
     * @var array
     */
    private $errorFieldsArray = array();

    /**
     * Get zones from Isocode
     *
     * @param string $countryIsoA3
     * @ignorevalidation $countryIsoA3
     * @return string JSON Zone
     */
    public function getZoneAjaxAction($countryIsoA3)
    {
        /** @var \Ideal\Openemm\Domain\Repository\CountryZoneRepository $zoneRepository */
        $zoneRepository = $this->objectManager->get('Ideal\\Openemm\\Domain\\Repository\\CountryZoneRepository');
        $zones = $zoneRepository->findByIsoCodeA3($countryIsoA3);
        $options = array();
        /** @var \Ideal\Openemm\Domain\Model\CountryZone $zone */
        foreach ($zones as $zone) {
            $options[] = array(
                'key' => $zone->getIsoCode(),
                'value' => $zone->getLocalName()
            );
        }
        return json_encode($options);
    }

    /**
     * Form
     *
     * @param int $step
     * @ignorevalidation $participant
     * @return void
     */
    public function newAction($step = 1)
    {
        $debug = array();

        //Step 2
        if ($step == 2 && $this->request->hasArgument("subscriber")) {
            $isValideSubsciber = $this->validateSubscriber($this->request->getArgument("subscriber"));
            if (!$isValideSubsciber) {
                $step = 1;
                //$debug[] = $this->request->getArgument("subscriber");
            } else {
                //Todo DataControl

            }
        } elseif($step == 2 && !$this->request->hasArgument("subscriber")) {
            $step = 1;
            $this->addAlertMessage(LocalizationUtility::translate('noDataText', 'openemm'), LocalizationUtility::translate('noDataTitle', 'openemm'), \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);
        }

        //Step 1
        if ($step == 1) {
            //fields
            if (isset($this->settings['new']['subscriber']['fields']) && !empty($this->settings['new']['subscriber']['fields'])) {
                $fields = $this->prepareFields($this->settings, $this->errorFieldsArray, 'new');
                $fields['fields'] = $this->mappingFields($fields['fields'], $this->request->getArguments());
                $this->view->assignMultiple(array(
                    'fields' => $fields['fields']
                ));
                $debug[] = $fields;
            }
        }
        $this->assignFormSecurity();
        $this->view->assignMultiple(array(
            'settings' => $this->settings,
            'step' => intval($step),
            'nextStep' => intval($step + 1),
            'alerts' => $this->errorArray
        ));
        $this->view->assign('debug', $debug);
    }

    /**
     * Send User to OpenEMM
     */
    public function createAction()
    {
        //Todo Validation!!
        $this->openEmmService->AddSubscriber(null);
    }

    /**
     * Confimation and Update Subscriber
     *
     * @param string $authCode
     */
    public function confirmAction($authCode)
    {
        if (\Ideal\Openemm\Validation\Validator::validateAuthcodeString($authCode)) {
            //todo activate User...
        } else {
            //todo error...
        }
    }

    /**
     * Make Security Info Fields for Form
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     */
    private function assignFormSecurity()
    {
        $controlPart1 = time();
        $controlPart2 = rand(1000, 1000000);
        $controlString = $controlPart1 . '.' . $controlPart2;
        $controlHash = md5($controlPart1 . $controlPart2 . $GLOBALS["TYPO3_CONF_VARS"]['SYS']['encryptionKey']);

        if ($this->request->hasArgument("transId")) {
            $transId = $this->request->getArgument("transId");
        } else {
            $transId = $controlHash;
        }
        $this->view->assignMultiple(array(
            'transId' => $transId,
            'controlString' => $controlString,
            'controlHash' => $controlHash,
        ));
    }

    /**
     * Add Error
     * @param string $message
     * @param string $title
     * @param int $type
     */
    private function addAlertMessage($message, $title, $type)
    {
        $this->errorArray[] = array(
            'title' => $title,
            'message' => $message,
            'type' => $type
        );
    }

    /**
     * Validate Subscriber
     * @param array $subscriber
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     * @return bool
     */
    private function validateSubscriber(array $subscriber)
    {
        $validateFormSecurity = Validator::validateFormSecurity($this->request->getArgument("control"));
        if ($validateFormSecurity === null) {
            $subscriberValidationData = SubscriberValidator::validateSubscriber($subscriber, $this->settings);
            if ($subscriberValidationData !== true) {
                $errorMessage = "";
                foreach ($subscriberValidationData as $field => $error) {
                    $fieldPropertyName = $field;
                    if(strpos($field, ":") > 0) {
                        $fieldPropertyName = GeneralUtility::trimExplode(":", $field)[0];
                    }
                    $fieldName = LocalizationUtility::translate('subscriber.property.' . GeneralUtility::camelCaseToLowerCaseUnderscored($fieldPropertyName), 'openemm');
                    switch ($error) {
                        case ValidationError::ISEMPTY:
                            $errorMsg = LocalizationUtility::translate('formErrorEmpty', 'openemm');
                            $this->errorFieldsArray[$field] = $errorMsg != NULL ? $errorMsg : 'formErrorEmpty';
                            break;
                        case ValidationError::EMAIL:
                            $errorMsg = LocalizationUtility::translate('formErrorEmail', 'openemm');
                            $this->errorFieldsArray[$field] = $errorMsg != NULL ? $errorMsg : 'formErrorEmail';
                            break;
                        case ValidationError::CHECKED:
                            $errorMsg = LocalizationUtility::translate('formErrorChecked', 'openemm');
                            $this->errorFieldsArray[$field] = $errorMsg != NULL ? $errorMsg : 'formErrorChecked';
                            break;
                        case ValidationError::LENGTH:
                            $errorMsg = LocalizationUtility::translate('formErrorLength', 'openemm');
                            $this->errorFieldsArray[$field] = $errorMsg != NULL ? $errorMsg : 'formErrorLength';
                            break;
                        default:
                            $errorMsg = LocalizationUtility::translate('formErrorDefault', 'openemm');
                            $this->errorFieldsArray[$field] = "[FORM FIELD ERROR " . $error . "]: " . $errorMsg != NULL ? $errorMsg : 'formErrorDefault';
                    }
                    $errorMessage .= "<li>" . ($fieldName != null ? $fieldName : 'subscriber.property.' . $field) . ": " . $this->errorFieldsArray[$field] . "</li>";
                }
                $this->addAlertMessage("<ul>" . $errorMessage . "</ul>", LocalizationUtility::translate('formFieldErrorTitle', 'openemm'), \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);
                return false;
            }
        } else {
            $this->addAlertMessage($validateFormSecurity, LocalizationUtility::translate('fatalFormErrorTitle', 'openemm'), \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);
            return false;
        }
        return true;
    }

    /**
     * Prepare Subscriber Fields
     *
     * @param array $settings
     * @param array $fieldErrors
     * @param string $settingsPart
     * @param array $request
     * @return array
     */
    public function prepareFields(array $settings, array $fieldErrors, $settingsPart = 'new', $request = array())
    {
        $fieldsReturn = array('fields' => array(), 'required' => array());

        $ofields = explode(',', $settings[$settingsPart]['subscriber']['fields']);
        $fields = array();
        $requireds = array();
        if (is_array($settings[$settingsPart]['subscriber']) && array_key_exists('required', $settings[$settingsPart]['subscriber']) && !empty($settings[$settingsPart]['subscriber']['required'])) {
            $requireds = explode(',', $settings[$settingsPart]['subscriber']['required']);
        }
        $fieldsReturn['required'] = $requireds;
        foreach ($ofields as $field) {
            $fieldInfo = explode(':', $field);
            $fields[$fieldInfo[0]]['property'] = $fieldInfo[0];
            $fieldLabel = LocalizationUtility::translate('subscriber.property.' . GeneralUtility::camelCaseToLowerCaseUnderscored($fieldInfo[0]), 'openemm');
            $fields[$fieldInfo[0]]['name'] = !empty($fieldLabel) ? $fieldLabel : $fieldInfo[0];

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
                        $selectName = LocalizationUtility::translate('subscriber.property.' . GeneralUtility::camelCaseToLowerCaseUnderscored($fieldInfo[0]) . '.' . $i, 'openemm');
                        if ($selectName != NULL) {
                            $options[$i] = $selectName;
                        } else {
                            break;
                        }
                    }
                }
                /** @var \SJBR\StaticInfoTables\Domain\Repository\CountryRepository $countryRepository */
                if ($fieldInfo[0] == 'country' || $fieldInfo[0] == 'region' || $fieldInfo[0] == 'zone')
                    $countryRepository = $this->objectManager->get('SJBR\\StaticInfoTables\\Domain\\Repository\\CountryRepository');

                //Country
                if ($fieldInfo[0] == 'country') {
                    $options[0] = LocalizationUtility::translate('pleaceSelect', 'openemm');
                    $countrys = $countryRepository->findAll();
                    /** @var \Ideal\Openemm\Domain\Model\Country  $country */
                    foreach ($countrys as $country) {
                        $options[$country->getIsoCodeA3()] = $country->getShortNameLocal();
                    }
                    $fields[$fieldInfo[0]]['options'] = $options;
                }
                //Region-Zone
                //$request['country'] = "ITA";
                if ($fieldInfo[0] == 'region' || $fieldInfo[0] == 'zone') {
                    $options[0] = LocalizationUtility::translate('pleaceSelect', 'openemm');
                    if (count($request) > 0 && isset($request['country'])) {

                        //$country = $countryRepository->findAllOrderedBy("isoCodeA3");

                        $zoneRepository = $this->objectManager->get('Ideal\\Openemm\\Domain\\Repository\\CountryZoneRepository');
                        $zones = $zoneRepository->findByIsoCodeA3($request['country']);
                        /** @var \Ideal\Openemm\Domain\Model\CountryZone $zone */
                        foreach ($zones as $zone) {
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
                if (array_key_exists('errorClassWrap', $settings[$settingsPart]['new'])) {
                    $fields[$fieldInfo[0]]["errorClassWrap"] = ' ' . $settings[$settingsPart]['errorClassWrap'];
                }
                if (array_key_exists('errorClassInput', $settings[$settingsPart]['new'])) {
                    $fields[$fieldInfo[0]]["errorClassInput"] = ' ' . $settings[$settingsPart]['errorClassInput'];
                }
            } else {
                $fields[$fieldInfo[0]]["hasError"] = false;
            }
        }

        //Selectable Lists
        if($this->settings[$settingsPart]['mailinglists.selectable'])

        $fieldsReturn['fields'] = $fields;
        return $fieldsReturn;
    }

    /**
     * Mapping Values to Field Array
     *
     * @param array $fields
     * @param array $arguments
     * @return array
     */
    public function mappingFields(array $fields, array $arguments)
    {
        //var_dump($fields);
        if (array_key_exists('subscriber', $arguments)) {
            /** @var \SJBR\StaticInfoTables\Domain\Repository\CountryRepository $countryRepository */
            foreach ($fields as $name => $field) {
                if(strpos($name, ":") > 0) {
                    $name = GeneralUtility::trimExplode(":", $name)[0];
                }
                var_dump($arguments['subscriber'][$name]);
                if (array_key_exists($name, $arguments['subscriber'])) {
                    if ($name == 'country' || $name == 'region' || $name == 'zone')
                        $countryRepository = $this->objectManager->get('Ideal\\Openemm\\Domain\\Repository\\CountryRepository');
                    if ($name == 'country') {
                        $country = $countryRepository->findByIsoCodeA3($arguments['subscriber'][$name])->getFirst();
                        $fields[$name]['value'] = $country->getShortNameLocal();
                    } elseif ($name == 'region' || $name == 'zone') {
                        $zoneRepository = $this->objectManager->get('Ideal\\Openemm\\Domain\\Repository\\CountryZoneRepository');
                        $zone = $zoneRepository->findByIsoCodeA3($arguments['subscriber']['country'])->getFirst();
                        $fields[$name]['value'] = $zone->getLocalName();
                    } elseif ($name == 'title') {
                        $fields[$name]['value'] = LocalizationUtility::translate('fields.title.' . $arguments['subscriber'][$name], 'openemm');
                    } elseif ($name == 'gender') {
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
}
