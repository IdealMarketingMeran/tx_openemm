<?php

namespace Ideal\Openemm\Validation;

use \TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
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

class SubscriberValidator
{

    /**
     * Validate Subscriber Data
     * 
     * @param array $arguments
     * @param array $settings
     * @return array|bool (has error array with fields has a error)
     */
    public static function validateSubscriber(array $arguments, array $settings)
    {
        $fields = array();
        $ret = array();
        if (is_string($settings['new']['subscriber']['required'])) {
            $fields = explode(',', $settings['new']['subscriber']['required']);
        }
        //required Fields
        foreach ($fields as $field) {
            if (!isset($arguments[$field])) {
                $ret[$field] = ValidationError::ISEMPTY;
                continue;
            } elseif (empty($arguments[$field])) {
                $ret[$field] = ValidationError::ISEMPTY;
                continue;
            }

            if ($field == "email" && filter_var($arguments[$field], FILTER_VALIDATE_EMAIL) === false) {
                $ret[$field] = ValidationError::EMAIL;
                continue;
            }
        }
        if (count($ret) > 0) {
            return $ret;
        }
        return true;
    }

    /**
     * Validate Form Security
     *
     * @param \int $control
     * @return string has error, errormessage, else null
     */
    public static function validateFormSecurity($control)
    {
        $errorMsg = null;
        $controllArray = explode('.', $control);
        if (count($controllArray) !== 3) {
            $errorMsg = LocalizationUtility::translate('fatalFormSecurityError', 'openemm') != NULL ? LocalizationUtility::translate('fatalFormSecurityError', 'openemm') : 'fatalFormSecurityError:count:' . $control;
        } else {
            //Timeout
            if (intval($controllArray[1]) + 1200 < time()) {
                $errorMsg = LocalizationUtility::translate('formTimeout', 'openemm') != NULL ? LocalizationUtility::translate('formTimeout', 'openemm') : 'formTimeout';
            }
            //hash
            $controlHash = md5($controllArray[1] . $controllArray[2] . $GLOBALS["TYPO3_CONF_VARS"]['SYS']['encryptionKey']);
            if ($controlHash !== $controllArray[0]) {
                $errorMsg = LocalizationUtility::translate('fatalFormSecurityError', 'openemm') != NULL ? LocalizationUtility::translate('fatalFormSecurityError', 'openemm') : 'fatalFormSecurityError:hash:' . $control;
            }
        }
        return $errorMsg;
    }

    /**
     * Generate User Authstring
     * @param int $userId
     * @return string
     */
    public static function getUserAuthcodeString($userId)
    {
        return '$' . $userId . '$' . md5($userId . $GLOBALS["TYPO3_CONF_VARS"]['SYS']['encryptionKey']);
    }

    /**
     * Validate Authstring
     * @param string $authcode
     * @return boolean
     */
    public static function validateAuthcodeString($authcode)
    {
        $array = explode('$', $authcode);
        $controllHash = md5($array[0] . $GLOBALS["TYPO3_CONF_VARS"]['SYS']['encryptionKey']);
        if ($array[0] === $controllHash) {
            return true;
        }
        return false;
    }

}
