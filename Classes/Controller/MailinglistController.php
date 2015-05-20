<?php

namespace Ideal\Openemm\Controller;

use \TYPO3\CMS\Core\Utility\GeneralUtility;

/***************************************************************
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
 ***************************************************************/

/**
 * Class MailinglistController
 * @package Ideal\Openemm\Controller
 */
class MailinglistController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    /**
     * OpenEMM Service
     * @var \Ideal\Openemm\Services\OpenEMMService
     */
    private $openEmmService = null;

    /**
     * @throws \Exception
     */
    public function initializeAction()
    {
        $this->openEmmService = $this->objectManager->get("Ideal\\Openemm\\Services\\OpenEMMService");
        try {
            $this->openEmmService->Init($this->settings);
        } catch (\Exception $ex) {
            throw new \Exception("OpenEMMService->Init: " . $ex->getMessage());
        }

        if ($this->arguments->hasArgument('mailinglist')) {
            var_dump("hasmailinglist");
            $mailinglistConfiguration = $this->arguments->getArgument('mailinglist')->getPropertyMappingConfiguration();
            $mailinglistConfiguration->allowAllProperties()->setTypeConverterOption('TYPO3\CMS\Extbase\Property\TypeConverter\PersistentObjectConverter', \TYPO3\CMS\Extbase\Property\TypeConverter\PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED, true);

            //->allowAllProperties()->forProperty('relation')->allowAllProperties()->setTypeConverterOption(  'TYPO3\\CMS\\Extbase\\Property\\TypeConverter\\PersistentObjectConverter',  PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED,  TRUE); 

        }
    }

    public function listAction()
    {
        if ($this->openEmmService == null) {
            $this->Init();
        }
        try {
            $lists = $this->openEmmService->ListMailinglists();
        } catch (\SoapFault $ex) {
            /** @var $logger \TYPO3\CMS\Core\Log\Logger */
            $logger = GeneralUtility::makeInstance('TYPO3\CMS\Core\Log\LogManager')->getLogger(__CLASS__);
            $logger->error("SOAP Fault", array('Message' => $ex->getMessage(), 'Line' => $ex->getLine()));
            throw $ex;
        }
        $assign = array();
        $list = array();
        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $listStorage */
        $listStorage = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Persistence\ObjectStorage');

        foreach ($lists->item as $item) {
            /** @var \Ideal\Openemm\Domain\Model\Emm\Mailinglist $mailinglist */
            $mailinglist = GeneralUtility::makeInstance('Ideal\Openemm\Domain\Model\Emm\Mailinglist');
            $mailinglist->setId($item->id);
            $mailinglist->setShortname($item->shortname);
            $mailinglist->setDescription($item->description);
            $listStorage->attach($mailinglist);
            $list[] = array(
                'id' => $item->id,
                'shortname' => $item->shortname,
                'description' => $item->description,
            );
        }
        $assign['itemsO'] = $listStorage;
        $assign['items'] = $list;
        $this->view->assignMultiple($assign);
    }

    /**
     * @param \Ideal\Openemm\Domain\Model\Emm\Mailinglist $mailinglist
     */
    public function newAction(\Ideal\Openemm\Domain\Model\Emm\Mailinglist $mailinglist = null)
    {

        $this->view->assign('mailinglist', $mailinglist);
    }

    /**
     * @param \Ideal\Openemm\Domain\Model\Emm\Mailinglist $mailinglist
     * @return mixed
     */
    public function createAction(\Ideal\Openemm\Domain\Model\Emm\Mailinglist $mailinglist)
    {
        return json_encode($mailinglist);
    }
}