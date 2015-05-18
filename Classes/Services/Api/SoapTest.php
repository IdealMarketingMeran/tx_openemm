<?php

use Ideal\Api\OpenEMM;
use Ideal\Api\OpenEMM\Model;

/* * *******************************************************************************
 * The contents of this file are subject to the Common Public Attribution
 * License Version 1.0 (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.openemm.org/cpal1.html. The License is based on the Mozilla
 * Public License Version 1.1 but Sections 14 and 15 have been added to cover
 * use of software over a computer network and provide for limited attribution
 * for the Original Developer. In addition, Exhibit A has been modified to be
 * consistent with Exhibit B.
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for
 * the specific language governing rights and limitations under the License.
 * 
 * The Original Code is OpenEMM.
 * The Original Developer is the Initial Developer.
 * The Initial Developer of the Original Code is AGNITAS AG. All portions of
 * the code written by AGNITAS AG are Copyright (c) 2007 AGNITAS AG. All Rights
 * Reserved.
 * 
 * Contributor(s): AGNITAS AG. 
 * ****************************************************************************** */
require("WsseSoapClient.php");
require("Model/SubscriberModel.php");

try {
// URL of the WSDL document. Modify that for your environment
    $wsdlURL = "";

// Your authentication information
    $username = "";
    $password = "";

// Create new SOAP client
    $soapOptions = array(
        'classmap' => array(
            'GetSubscriber' => "\\Ideal\\Api\OpenEMM\\Model\\SubscriberModel"
        )
    );
    
    $client = new \Ideal\Api\OpenEMM\WsseSoapClient($wsdlURL, $username, $password, null, $soapOptions);
    $client->__setLocation("");

// Example: Retrieve list of all available webservices
    var_dump($client->__getFunctions());

var_dump($client->GetSubscriber(array("customerID" => 1)));
// Example: Retrieve list of all mailings

    //var_dump($client->__soapCall("ListMailings", array()));
    //var_dump($client->ListMailings());
} catch (SoapFault $exception) {
    echo $exception->getMessage();
    echo $exception->getTraceAsString();
}