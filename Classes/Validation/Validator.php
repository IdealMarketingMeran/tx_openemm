<?php

namespace Ideal\Openemm\Validation;

use \TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

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

class Validator {
    
    /*
     * Validate Subscriber Data
     * 
     * @param array $arguments
     * @param array $settings
     * @return array|bool (has error array with fields has a error)
     */
    public static function validateParticipant(array $arguments, array $settings) {
        $fields = array();
        $ret = array();
        if (is_string($settings['new']['subscriber']['required'])) {
            $fields = explode(',', $settings['new']['subscriber']['required']);
        }
        //required Fields
        foreach ($fields as $field) {
            if(!isset($arguments['$field'])) {
                $ret[$field] = ValidationError::ISEMPTY;
                continue;
            } elseif(empty($arguments['$field'])) {
                $ret[$field] = ValidationError::ISEMPTY;
                continue;
            }
            
            if($field == "email" && filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
                $ret[$field] = ValidationError::EMAIL;
                continue;
            }
        }
        if (count($ret) > 0) {
            return $ret;
        }
        return true;
    }

    /*
     * Validate Form Security
     * 
     * @param \int $uid
     * @param \int $control
     * @return string has error, errormessage, else null
     */

    public static function validateFormSecurity($uid, $control) {
        $errorMsg = null;
        $conrollArray = explode('.', $control);
        if (count($conrollArray) !== 3) {
            $errorMsg = LocalizationUtility::translate('fatalFormSecurityError', 'openemm') != NULL ? LocalizationUtility::translate('fatalFormSecurityError', 'openemm') : 'fatalFormSecurityError:count:' . $control;
        } else {
            //Timeout
            if (intval($conrollArray[1]) + 1200 < time()) {
                $errorMsg = LocalizationUtility::translate('formTimeout', 'openemm') != NULL ? LocalizationUtility::translate('formTimeout', 'openemm') : 'formTimeout';
            }
            //hash
            $controlHash = md5($conrollArray[1] . $conrollArray[2] . $uid . $GLOBALS["TYPO3_CONF_VARS"]['SYS']['encryptionKey']);
            if ($controlHash !== $conrollArray[0]) {
                $errorMsg = LocalizationUtility::translate('fatalFormSecurityError', 'openemm') != NULL ? LocalizationUtility::translate('fatalFormSecurityError', 'openemm') : 'fatalFormSecurityError:hash:' . $control;
            }
        }
        return $errorMsg;
    }

}