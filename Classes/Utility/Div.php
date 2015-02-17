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
     * @param \Ideal\Contest\Domain\Model\Participants $participant
     */
    public function mappingParticipantsFields(array $fields, \Ideal\Contest\Domain\Model\Participants $participant) {
        foreach ($fields as $name => $field) {
            $getM = "get" . ucfirst($name);
            if (method_exists($participant, $getM)) {
                $fields[$name]['value'] = $participant->{$getM}();
            } else {
                $fields[$name]['value'] = NULL;
            }
        }
        $fields['debug'] = $fields;
        return $fields;
    }

    /**
     * Store Images Temporal
     * 
     * @param array $files $_FILES array
     * @param string $transId Registrations transactions ID
     */
    public function storeTempData(array $files, $transId) {
        $return = array();
        /** @var \TYPO3\CMS\Core\Resource\ResourceStorage $storage */
        $storage = $this->storageRepository->findByUid('1');
        $this->deleteTempFilesByTransid($transId);
        for ($i = 0; $i < count($files['name']['images']); $i++) {
            if ($files['name']['images'][$i] == "") {
                continue;
            }
            $filePathInfo = PathUtility::pathinfo($files['name']['images'][$i]);
            $files['name']['images'][$i] = '_tmp_participants.' . time() . "." . $transId . "." . $i . "." . strtolower($filePathInfo['extension']);
            $debug["files['tmp_name']['images'][$i]"] = $files['_temp_']['images'][$i];
            $newFileObject = $storage->addFile(
                    $files['tmp_name']['images'][$i], $storage->getFolder("_temp_/"), $files['name']['images'][$i]
            );
            $return[$i] = $newFileObject->getProperty('uid');
        }
        return $return;
    }

    /**
     * Store Images finaly
     * 
     * @param string $transId Registrations transactions ID
     * @param string $subFolder Name of Subfolder (openemmname or Id)
     * @param string $filePrefix File Prefix;
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Ideal\Contest\Domain\Model\FileReference>
     */
    public function storeImages($transId, $subFolder, $filePrefix = "") {
        $return = array();
        /** @var \TYPO3\CMS\Core\Resource\ResourceStorage $storage */
        $storage = $this->storageRepository->findByUid('1');
        //$finalStorage = $this->storageRepository->findByUid('1');

        $folder = "openemm/" . $subFolder . "/";
        if (!$storage->hasFolder($folder)) {
            $storage->createFolder($folder);
        }
        $files = $storage->getFilesInFolder($storage->getFolder("_temp_/"));
        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Ideal\Contest\Domain\Model\FileReference> $objectStorage */
        foreach ($files as $file) {
            $namear = explode('.', $file->getName());
            if ($namear[2] == $transId && $namear[0] == '_tmp_participants') {
                $storage->moveFile($file, $storage->getFolder($folder), $targetFileName = $filePrefix . $namear[2] . "." . $namear[3] . "." . $namear[4]);
                $fileReference = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Ideal\\Contest\\Domain\\Model\\FileReference');
                $fileReference->setOriginalResource($file);
                $return[] = $fileReference;
            }
        }
        return $return;
    }

    /**
     * Delete temporay files by TransId
     * 
     * @param string $transId
     */
    public function deleteTempFilesByTransid($transId) {
        /** @var \TYPO3\CMS\Core\Resource\ResourceStorage $storage */
        $storage = $this->storageRepository->findByUid('1');
        $oldFiles = $storage->getFilesInFolder($storage->getFolder("_temp_/"));
        foreach ($oldFiles as $oldFile) {
            $namear = explode('.', $oldFile->getName());
            if ($namear[2] == $transId && $namear[0] == '_tmp_participants') {
                $storage->deleteFile($oldFile);
            }
        }
    }

    /**
     * Delete temporay files was older as 1 hour
     * 
     * @param string $transId
     */
    public function deleteTempFilesByTimeOut() {
        /** @var \TYPO3\CMS\Core\Resource\ResourceStorage $storage */
        $storage = $this->storageRepository->findByUid('1');
        $oldFiles = $storage->getFilesInFolder($storage->getFolder("_temp_/"));
        foreach ($oldFiles as $oldFile) {
            $namear = explode('.', $oldFile->getName());
            if ($namear[1] + 3600 < time() && $namear[0] == '_tmp_participants') {
                $storage->deleteFile($oldFile);
            }
        }
    }

    /**
     * 
     * @param \Ideal\Contest\Domain\Model\Contests $openemms
     * @param \TYPO3\CMS\Extbase\Domain\Model\FrontendUser $user
     * @param type $votingGroup
     * @return boolean
     */
    public function canVote(\Ideal\Contest\Domain\Model\Contests $openemms, \TYPO3\CMS\Extbase\Domain\Model\FrontendUser $user = null, $votingGroup = "") {
        $currentVotings = $this->votingsRepository->findVotingsFromContest($openemms);
        if (count($currentVotings) == 0) {
            return true;
        } else {
            $refererUrl = parse_url($_SERVER['HTTP_REFERER']);
            if ($_SERVER['HTTP_HOST'] !== $refererUrl["host"]) {
                return false;
            }
            /** @var \Ideal\Contest\Domain\Model\Votings $vote */
            foreach ($currentVotings as $vote) {
                if ($user == null) {
                    if ($this->getVotingSecurity()['hash'] == $vote->getHash() && $vote->getCrdate()->getTimestamp() > (time() - 86400)) {
                        return false;
                    }
                } else {
                    if ($vote->getFeUser() == $user->getUid() && $vote->getVotingGroup() == $votingGroup) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    public function getVotingSecurity() {
        return array(
            'remoteAddr' => $_SERVER['REMOTE_ADDR'],
            'userAgent' => $_SERVER['HTTP_USER_AGENT'],
            'hash' => md5($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'] . gethostname()),
            'hostname' => gethostbyaddr($_SERVER['REMOTE_ADDR'])
        );
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
