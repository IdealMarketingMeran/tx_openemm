<?php

namespace Ideal\Openemm\Persistence\Mapper;

    /***************************************************************
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
     ***************************************************************/

/**
 * Description of SubscriberModel
 *
 * @author Markus Pircher
 */
abstract class SubscriberMapper
{

    /**
     * Map Soap Node to Model
     * @param object $subscriber
     * @return \Ideal\Openemm\Domain\Model\Subscriber
     */
    public static function MapFromSoap($subscriber)
    {
        /** @var \Ideal\Openemm\Domain\Model\Emm\Subscriber $subscriberModel */
        $subscriberModel = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Ideal\\Openemm\\Domain\\Model\\Emm\\Subscriber');
        $subscriberModel->setCustomerID($subscriber->customerID);

        $parameters = array();
        foreach ($subscriber->parameters->item as $parameter) {
            $parameters[$parameter->key] = $parameter->value;
        }
        $subscriberModel->setParameters($parameters);
        return $subscriberModel;
    }

    /**
     * Map Model to SOAP Request Array
     * @param \Ideal\Openemm\Domain\Model\Emm\Subscriber $subscriber
     * @return array
     */
    public static function MapToSoap(\Ideal\Openemm\Domain\Model\Emm\Subscriber $subscriber)
    {
        $subscriberArray = array();
        $subscriberArray['customerID'] = $subscriber->getCustomerID();

        $item = array();
        foreach ($subscriber->getParameters() as $key => $value) {
            $item[] = array(
                "key" => new \SoapVar($key, XSD_STRING, "string", "http://www.w3.org/2001/XMLSchema"),
                "value" => new \SoapVar($value, XSD_STRING, "string", "http://www.w3.org/2001/XMLSchema")
            );
        }
        $subscriberArray['parameters'] = $item;
        return $subscriberArray;
    }

}
