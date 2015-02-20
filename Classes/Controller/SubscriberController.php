<?php

namespace Ideal\Openemm\Controller;

/* * *************************************************************
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
 * ************************************************************* */

/**
 * Description of SubscriberController
 *
 * @author Markus Pircher
 */
class SubscriberController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

    /**
     * Utilitys
     *
     * @var \Ideal\Openemm\Utility\Div
     * @inject
     */
    protected $div = NULL;

    /**
     * Get zones from Isocode
     * 
     * @param string $countryIsoA3
     * @ignorevalidation $countryIsoA3
     * @return string JSON Zone
     */
    public function getZoneAjaxAction($countryIsoA3) {
        $zoneRepository = $this->objectManager->get('Ideal\\Openemm\\Domain\\Repository\\CountryZoneRepository');
        $zones = $zoneRepository->findByIsoCodeA3($countryIsoA3);
        $options = array();
        foreach ($zones as $zone) {
            //$options[$zone->getUid()] = $zone->getLocalName();
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
    public function newAction($step = 1) {
        $error = array();
        $debug = array();
        $this->request->getArguments();

        //Step 2
        if ($step == 2) {
            $errorMsg = null;
            if (!$this->request->hasArgument("control") || $participant === NULL) {
                $errorMsg = LocalizationUtility::translate('fatalFormError', 'contest') != NULL ? LocalizationUtility::translate('fatalFormError', 'contest') : 'fatalFormError:newAction' . $this->request->count();
            } else {
                $errorMsg = \Ideal\Contest\Validation\Validator::validateFormSecurity($contests->getUid(), $this->request->getArgument("control"));
            }
            if ($errorMsg !== null) {
                $this->controllerContext->getFlashMessageQueue()->enqueue(
                        $this->objectManager->get(
                                'Ideal\\Contest\\Messaging\\FlashMessage', $errorMsg, LocalizationUtility::translate('fatalFormErrorTitle', 'contest'), \Ideal\Contest\Messaging\FlashMessage::ERROR
                        )
                );
            } else {
                /** @var \TYPO3\CMS\Core\Resource\ResourceStorage $storage */
                $storage = $this->storageRepository->findByUid(1);
                $debug[] = $_FILES['tx_contest_pi1'];
                $valideFields = \Ideal\Contest\Validation\Validator::validateParticipant($participant, $this->settings);
                $uploadFiedls = \Ideal\Contest\Validation\Validator::validateImageUpload($_FILES['tx_contest_pi1'], $this->settings);
                if ($uploadFiedls > 0) {
                    $valideFields['images'] = $uploadFiedls;
                } else {
                    //temp storage
                    unset($valideFields['images']);
                    $debug[] = $this->div->storeTempData($_FILES['tx_contest_pi1'], $this->request->getArgument("transId"));
                }
                if (is_array($valideFields) && count($valideFields) > 0) {
                    $flashMessageTitle = LocalizationUtility::translate('formErrorDefaultTitle', 'contest') != NULL ? LocalizationUtility::translate('formErrorDefaultTitle', 'contest') : 'formErrorDefaultTitle';
                    $flashMessage = "<ul>";
                    foreach ($valideFields as $field => $errorEnum) {
                        switch ($errorEnum) {
                            case \Ideal\Contest\Validation\ValidationError::ISEMPTY:
                                $error[$field]["msg"] = LocalizationUtility::translate('formErrorEmpty', 'contest') != NULL ? LocalizationUtility::translate('formErrorEmpty', 'contest') : 'formErrorEmpty';
                                break;
                            case \Ideal\Contest\Validation\ValidationError::EMAIL:
                                $error[$field]["msg"] = LocalizationUtility::translate('formErrorEmail', 'contest') != NULL ? LocalizationUtility::translate('formErrorEmail', 'contest') : 'formErrorEmail';
                                break;
                            case \Ideal\Contest\Validation\ValidationError::CHECKED:
                                $error[$field]["msg"] = LocalizationUtility::translate('formErrorChecked', 'contest') != NULL ? LocalizationUtility::translate('formErrorChecked', 'contest') : 'formErrorChecked';
                                break;
                            case \Ideal\Contest\Validation\ValidationError::NUMBERIC:
                                $error[$field]["msg"] = LocalizationUtility::translate('formErrorNumberic', 'contest') != NULL ? LocalizationUtility::translate('formErrorNumberic', 'contest') : 'formErrorNumberic';
                                break;
                            case \Ideal\Contest\Validation\ValidationError::TOOMANY:
                                $error[$field]["msg"] = LocalizationUtility::translate('formErrorTooMany', 'contest') != NULL ? LocalizationUtility::translate('formErrorTooMany', 'contest') : 'formErrorTooMany';
                                break;
                            case \Ideal\Contest\Validation\ValidationError::FORMAT:
                                $error[$field]["msg"] = LocalizationUtility::translate('formErrorFormat', 'contest') != NULL ? LocalizationUtility::translate('formErrorFormat', 'contest') : 'formErrorFormat';
                                break;
                            case \Ideal\Contest\Validation\ValidationError::FILESIZE:
                                $error[$field]["msg"] = LocalizationUtility::translate('formErrorFilesize', 'contest') != NULL ? LocalizationUtility::translate('formErrorFilesize', 'contest') : 'formErrorFilesize';
                                break;
                            default:
                                $error[$field]["msg"] = "[$error[$field].$errorEnum]: " . LocalizationUtility::translate('formErrorDefault', 'contest') != NULL ? LocalizationUtility::translate('formErrorDefault', 'contest') : 'formErrorDefault';
                        }
                        $error[$field]["hasError"] = true;
                        #$error[$field]["class"] = $this->settings['booking']['new']['errorClass'];
                        $fielLC = \TYPO3\CMS\Core\Utility\GeneralUtility::camelCaseToLowerCaseUnderscored($field);
                        $flashMessage .= "<li>" . LocalizationUtility::translate('tx_contest_domain_model_participants.' . $fielLC, 'contest') . " ($fielLC): " . $error[$field]["msg"] . "</li>";
                    }
                    $flashMessage .= "</ul>";
                    $this->controllerContext->getFlashMessageQueue()->enqueue(
                            $this->objectManager->get(
                                    'Ideal\\Contest\\Messaging\\FlashMessage', $flashMessage, $flashMessageTitle, \Ideal\Contest\Messaging\FlashMessage::ERROR
                            )
                    );
                    $step = 1;
                } else {
                    //fields
                    if (isset($this->settings['participants']['fields']) && !empty($this->settings['participants']['fields'])) {
                        $fields = $this->div->prepareFields($this->settings, $error, 'participants');
                        $fields = $this->div->mappingFields($fields['fields'], $participant);
                        unset($fields['images']);
                        $this->view->assign('fields', $fields);
                    }
                }
            }
        }

        //Step 1
        if ($step == 1) {
            //fields
            if (isset($this->settings['new']['subscriber']['fields']) && !empty($this->settings['new']['subscriber']['fields'])) {
                $fields = $this->div->prepareFields($this->settings, $error, 'new');
                $this->view->assign('fields', $fields['fields']);
                $this->view->assign('required', $fields['required']);
                $debug[] = $fields;
            }
        }
        if ($step == 1 || $step == 2) {
            $controlPart1 = time();
            $controlPart2 = rand(1000, 1000000);
            $controlString = $controlPart1 . '.' . $controlPart2;
            $controlHash = md5($controlPart1 . $controlPart2 . $GLOBALS["TYPO3_CONF_VARS"]['SYS']['encryptionKey']);

            if ($this->request->hasArgument("transId")) {
                $transId = $this->request->getArgument("transId");
            } else {
                $transId = $controlHash;
            }
            $this->view->assign('transId', $transId);
            $this->view->assign('controlString', $controlString);
            $this->view->assign('controlHash', $controlHash);
            $this->view->assign('settings', $this->settings);
            $this->view->assign('step', intval($step));
            $this->view->assign('nextStep', intval($step + 1));
        }

        $this->view->assign('debug', $debug);
        $this->view->assign('participant', $participant);
        $this->view->assign('contest', $contests);
    }
    
    /**
     * Send User to OpenEMM
     */
    public function createAction() {
        
    }
    
    /**
     * Confimation and Update Subscriber
     * 
     * @param string $authCode
     */
    public function confirmAction($authCode) {
        
    }

}
