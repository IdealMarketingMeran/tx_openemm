<?php

namespace Ideal\Openemm\Services\SoapClient;

    /***************************************************************
     *
     *  Copyright notice
     *
     *  (c) 2015 Markus Pircher <technik@idealit.com>, IDEAL
     *
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
     ******************************************************************************* */

/*
  SOAP client with WSSE support for use of EMM/OpenEMM Webservice API 2.0
 */
class WsseSoapClient extends \SoapClient
{

    /**
     * Namespace for SOAP
     * @var string
     */
    const SOAP_NAMESPACE = 'http://schemas.xmlsoap.org/soap/envelope/';

    /**
     * Namespace for WSSE
     * @var string
     */
    const WSSE_NAMESPACE = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd';

    /**
     * Namespace for WSU
     * @var string
     */
    const WSU_NAMESPACE = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd';

    /**
     * Encoding of "nonce
     * @var string
     */
    const NONCE_ENCODING_TYPE = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary';

    /**
     * Type of password
     * @var string
     */
    const PASSWORD_TYPE = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordDigest';

    /**
     * Username for authentication
     * @var string
     */
    private $username;

    /**
     * Password for authentication
     * @var string
     */
    private $password;

    /**
     * Prefix used for nonce generation
     * @var string
     */
    private $prefix;

    /**
     * Creates a new SOAP client with WSSE support.
     *
     * The prefix is used, when client runs on different machines.
     * To prevent generation of the same nonce when run at the same time,
     * a different prefix should be used for different instances of the client.
     *
     * @param string $wsdlUrl location of WSDL document
     * @param string $username username for authentication
     * @param string $password password
     * @param string $prefix optional configuration information (see SoapClient in PHP manual)
     * @param array $options optional prefix used for nonce-generation.
     */
    function __construct($wsdlUrl, $username, $password, $prefix = null, $options = array())
    {
        parent::__construct($wsdlUrl, $options);

        $this->username = $username;
        $this->password = $password;

        $this->prefix = $prefix == null ? php_uname('n') : $prefix;
    }

    /**
     * Implementation of "SoapClient::__doRequest" to add the WSSE security informations automatically.
     *
     * @param type $request
     * @param type $location
     * @param type $action
     * @param type $version
     * @return type
     */
    function __doRequest($request, $location, $action, $version, $one_way = 0)
    {
        $nonce = $this->generateNonce($this->prefix);
        $timestamp = $this->getUTCTimestamp();
        //$passwordDigest = $this->generatePasswordDigest($this->password, $timestamp, $nonce);

        $xml = new \DOMDocument();
        $xml->loadXML($request);
        $this->appendWsseSecurityElements($xml);
        $request = $xml->saveXML();

        //echo "REQUEST" . $request;
        return parent::__doRequest($request, $location, $action, $version, $one_way);
    }

    /**
     * Appends the WSSE security information to the SOAP header.
     *
     * @param \DOMDocument $xml the parsed SOAP request
     */
    private function appendWsseSecurityElements(\DOMDocument $xml)
    {
        $nonce = $this->generateNonce($this->prefix);
        $timestamp = $this->getUTCTimestamp();
        $passwordDigest = $this->generatePasswordDigest($this->password, $timestamp, $nonce);

        $headerElement = $this->getSoapHeaderElement($xml);
        $headerElement->appendChild($this->createSecurityElement($xml, $passwordDigest, $nonce, $timestamp));
    }

    /**
     * Locates and returns the SOAP Header element.
     * If Header element is not present, a new Header element is added to the SOAP request.
     *
     * @param \DOMDocument $xml the parsed SOAP request
     * @return \DOMNode XML SOAP "Header" element
     */
    private function getSoapHeaderElement(\DOMDocument $xml)
    {

        $xpath = new \DOMXPath($xml);
        $xpath->registerNamespace('SOAP-ENV', self::SOAP_NAMESPACE);

        $headerElement = $xpath->query('/SOAP-ENV:Envelope/SOAP-ENV:Header')->item(0);

        if (!$headerElement) {
            $headerElement = $xml->createElementNS(self::SOAP_NAMESPACE, 'SOAP-ENV:Header');
            $envelopeElement = $xpath->query('/SOAP-ENV:Envelope')->item(0);
            $bodyElement = $xpath->query('/SOAP-ENV:Envelope/SOAP-ENV:Body')->item(0);
            $envelopeElement->insertBefore($headerElement, $bodyElement);
        }

        return $headerElement;
    }

    /**
     * Creates the Security element for WSSE.
     *
     * @param \DOMDocument $xml the parsed SOAP request
     * @param string $passwordDigest the computed password digest
     * @param string $nonce nonce used for password digest
     * @param string $timestamp timestamp of digest generation
     * @return \DOMNode XML "Security" element
     */
    private function createSecurityElement(\DOMDocument $xml, $passwordDigest, $nonce, $timestamp)
    {
        $securityElement = $xml->createElementNS(self::WSSE_NAMESPACE, 'wsse:Security');
        $securityElement->appendChild($this->createUsernameTokenElement($xml, $passwordDigest, $nonce, $timestamp));

        return $securityElement;
    }

    /**
     * Creates the UsernameToken element.
     *
     * @param \DOMDocument $xml the parsed SOAP request
     * @param string $passwordDigest Digest the computed password digest
     * @param string $nonce nonce used for password digest
     * @param string $timestamp timestamp of digest generation
     * @return \DOMNode XML "UsernameToken" element
     */
    private function createUsernameTokenElement(\DOMDocument $xml, $passwordDigest, $nonce, $timestamp)
    {
        $usernameTokenElement = $xml->createElementNS(self::WSSE_NAMESPACE, 'wsse:UsernameToken');

        $usernameTokenElement->appendChild($this->createUsernameElement($xml));
        $usernameTokenElement->appendChild($this->createPasswordElement($xml, $passwordDigest));
        $usernameTokenElement->appendChild($this->createNonceElement($xml, $nonce));
        $usernameTokenElement->appendChild($this->createCreatedElement($xml, $timestamp));

        return $usernameTokenElement;
    }

    /**
     * Creates the Username element.
     *
     * @param \DOMDocument $xml parsed SOAP request
     * @return \DOMNode XML "Username" element
     */
    private function createUsernameElement(\DOMDocument $xml)
    {
        return $xml->createElementNS(self::WSSE_NAMESPACE, 'wsse:Username', $this->username);
    }

    /**
     *
     * @param \DOMDocument $xml parsed SOAP request
     * @param string $passwordDigest password digest
     * @return \DOMNode XML "Password" element
     */
    private function createPasswordElement(\DOMDocument $xml, $passwordDigest)
    {
        $passwordElement = $xml->createElementNS(self::WSSE_NAMESPACE, 'wsse:Password', $passwordDigest);
        $passwordElement->setAttribute('Type', self::PASSWORD_TYPE);

        return $passwordElement;
    }

    /**
     * Creates the Created element.
     *
     * @param \DOMDocument $xml parsed SOAP request
     * @param string $timestamp timestamp of digest generation
     * @return \DOMNode XML "Created" element
     */
    private function createCreatedElement(\DOMDocument $xml, $timestamp)
    {
        return $xml->createElementNS(self::WSU_NAMESPACE, 'wsu:Created', $timestamp);
    }

    /**
     * Creates the Nonce element.
     *
     * @param \DOMDocument $xml parsed SOAP request
     * @param type $nonce nonce used for digest generation
     * @return type XML "Nonce" element
     */
    private function createNonceElement(\DOMDocument $xml, $nonce)
    {
        $nonceElement = $xml->createElementNS(self::WSSE_NAMESPACE, 'wsse:Nonce', $nonce);
        $nonceElement->setAttribute('EncodingType', self::NONCE_ENCODING_TYPE);

        return $nonceElement;
    }

    /**
     *
     * @param string $password plain-text password
     * @param string $timestamp timestamp of generation
     * @param string $nonce nonce value
     * @return string password digest
     */
    private function generatePasswordDigest($password, $timestamp, $nonce)
    {
        $nonceBin = base64_decode($nonce);
        $rawDigest = $nonceBin . $timestamp . $password;
        $sha1 = sha1($rawDigest, true);

        return base64_encode($sha1);
    }

    /**
     * Generates a unique nonce value.
     *
     * @param string $prefix prefix used in nonce generation to create a unique nonce
     * @return string nonce value
     */
    private function generateNonce($prefix)
    {
        return base64_encode(substr(md5(uniqid($prefix . '_', true)), 0, 20));
    }

    /**
     * Returns the current time as UTC timestamp.
     *
     * @return string UTC timestamp
     */
    private function getUTCTimestamp()
    {
        $dateTime = new \DateTime("now", new \DateTimeZone('UTC'));
        $str = $dateTime->format('Y-m-d H:i:s');

        $i = strpos($str, ' ');
        $date = substr($str, 0, $i);
        $time = substr($str, $i + 1);

        return $date . "T" . $time . "Z";
    }
}