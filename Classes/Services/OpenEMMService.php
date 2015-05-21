<?php

namespace Ideal\Openemm\Services;

use \TYPO3\CMS\Core\Utility\GeneralUtility;

    /***************************************************************
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
     ***************************************************************/

/**
 * OpenEMM Service, aviable in other Extensions
 */
class OpenEMMService
{

    /**
     * @var \Ideal\Openemm\Services\SoapClient\WsseSoapClient $wsseSoapClient
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
     * @return array
     * @throws \Exception
     */
    public function Init(array $settings = null)
    {
        $this->objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
        if ($settings == null) {
            $configurationManager = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManager');
            $settings = $configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, 'openemm');
        }
        $this->settings = $settings;
        if (is_array($this->settings) && count($this->settings) > 0) {
            $this->wsseSoapClient = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Ideal\Openemm\Services\SoapClient\WsseSoapClient',
                $this->settings['webservice']['wsdl'],
                $this->settings['webservice']['username'],
                $this->settings['webservice']['password'],
                null,
                $this->settings['webservice']['soapOption']);
        } else {
            throw new \Exception(/*print_r($settings, true)*/);
        }
        return $this->settings;
    }

    /**
     * Get OpenEMM Soap Client
     * @return \Ideal\Openemm\Services\SoapClient\WsseSoapClient $wsseSoapClient
     */
    public function GetSoapClient()
    {
        return $this->wsseSoapClient;
    }

    /**
     *
     * @param int $subscriberId
     * @return \Ideal\Openemm\Domain\Model\Emm\Subscriber
     */
    public function GetSubscriber($subscriberId)
    {
        $subscriber = $this->wsseSoapClient->GetSubscriber(array("customerID" => intval($subscriberId)));
        return \Ideal\Openemm\Persistence\Mapper\SubscriberMapper::MapFromSoap($subscriber);
    }

    /**
     * Add Subscriber
     * @param \Ideal\Openemm\Domain\Model\Emm\Subscriber $subscriber
     * @param array $mailinglists
     * @return \Ideal\Openemm\Domain\Model\Emm\Subscriber
     */
    public function AddSubscriber(\Ideal\Openemm\Domain\Model\Emm\Subscriber $subscriber, array $mailinglists = null)
    {
        $subscriberArray = \Ideal\Openemm\Persistence\Mapper\SubscriberMapper::MapToSoap($subscriber);
        unset($subscriber['customerID']);
        $subscriberArray['doubleCheck'] = true;
        $subscriberArray['keyColumn'] = "email";
        $subscriberArray['overwrite'] = false;
        $response = $this->wsseSoapClient->AddSubscriber($subscriber);
        if ($response->customerID != 0) {
            $subscriber->setCustomerID($response->customerID);
        }

        if ($mailinglists != null && count($mailinglists) > 0) {
            foreach ($mailinglists as $listid) {
                $this->SetSubscriberBinding($subscriber->getCustomerID(), $listid);
            }
        }

        return $subscriber;
    }

    /**
     * Set or update Binding
     * @param int $customerID
     * @param int $mailinglistID
     * @param int $mediatype
     * @param int $status
     * @param string $userType
     * @param string $remark
     * @param int $exitMailingID
     */
    public function SetSubscriberBinding($customerID, $mailinglistID, $mediatype = 0, $status = 5, $userType = "W", $remark = "tx_openemm", $exitMailingID = 0)
    {
        $request = array(
            'customerID' => $customerID,
            'mailinglistID' => $mailinglistID,
            'mediatype' => $mediatype,
            'status' => $status,
            'userType' => $userType,
            'remark' => $remark,
            'exitMailingID' => $exitMailingID = 0
        );
        $this->wsseSoapClient->SetSubscriberBinding($request);
    }

    /**
     * List all Mailinglists
     */
    public function ListMailinglists()
    {
        return $this->wsseSoapClient->ListMailinglists();
    }

    /**
     * @param \Ideal\Openemm\Domain\Model\Emm\Mailinglist $mailinglist
     */
    public function AddMailinglist(\Ideal\Openemm\Domain\Model\Emm\Mailinglist &$mailinglist)
    {
        $request = array(
            'shortname' => $mailinglist->getShortname(),
            'description' => $mailinglist->getDescription()
        );
        $newMailinglist = $this->wsseSoapClient->AddMailinglist($request);
        $mailinglist->setId($newMailinglist->mailinglistID);
    }

    /**
     * @param int $mailinglistID
     * @return \Ideal\Openemm\Domain\Model\Emm\Mailinglist
     */
    public function GetMailinglist($mailinglistID)
    {
        $request = array(
            'mailinglistID' => $mailinglistID,
        );
        $getMailinglist = $this->wsseSoapClient->GetMailinglist($request);

        /** @var \Ideal\Openemm\Domain\Model\Emm\Mailinglist $mailinglist */
        $mailinglist = GeneralUtility::makeInstance('Ideal\Openemm\Domain\Model\Emm\Mailinglist');
        $mailinglist->setShortname($getMailinglist->shortname);
        $mailinglist->setDescription($getMailinglist->description);
        $mailinglist->setId($getMailinglist->id);
        return $mailinglist;
    }

    /**
     * @param \Ideal\Openemm\Domain\Model\Emm\Mailinglist $mailinglist
     */
    public function UpdateMailinglist(\Ideal\Openemm\Domain\Model\Emm\Mailinglist &$mailinglist)
    {
        $request = array(
            'mailingListId' => $mailinglist->getId(),
            'shortname' => $mailinglist->getShortname(),
            'description' => $mailinglist->getDescription()
        );
        $this->wsseSoapClient->UpdateMailinglist($request);
    }
    /**
     * @param int $mailinglist
     * @return bool
     */
    public function DeleteMailinglist($mailinglist)
    {
        $request = array(
            'mailinglistID' => $mailinglist,
        );
        return $this->wsseSoapClient->DeleteMailinglist($request)->value;
    }


}
