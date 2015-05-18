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
                $fields = $this->div->prepareFields($this->settings, $this->errorFieldsArray, 'new');
                $fields['fields'] = $this->div->mappingFields($fields['fields'], $this->request->getArguments());
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

}
