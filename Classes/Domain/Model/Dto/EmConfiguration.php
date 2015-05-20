<?php

namespace Ideal\Openemm\Domain\Model\Dto;

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
 ************************************************************** */

/**
 * Class EmConfiguration
 * @package Ideal\Openemm\Domain\Model\Dto
 */
class EmConfiguration
{
    /**
     * Fill the properties properly
     *
     * @param array $configuration em configuration
     */
    public function __construct(array $configuration) {
        foreach ($configuration as $key => $value) {
            if (property_exists(__CLASS__, $key)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * @var string
     */
    protected $webserviceWsdl = "";

    /**
     * @var string
     */
    protected $webserviceUsername = "";

    /**
     * @var string
     */
    protected $webservicePassword = "";

    /**
     * @var string
     */
    protected $webserviceNonce = "";

    /**
     * @return string
     */
    public function getWebserviceWsdl()
    {
        return $this->webserviceWsdl;
    }

    /**
     * @param string $webserviceWsdl
     */
    public function setWebserviceWsdl($webserviceWsdl)
    {
        $this->webserviceWsdl = $webserviceWsdl;
    }

    /**
     * @return string
     */
    public function getWebserviceUsername()
    {
        return $this->webserviceUsername;
    }

    /**
     * @param string $webserviceUsername
     */
    public function setWebserviceUsername($webserviceUsername)
    {
        $this->webserviceUsername = $webserviceUsername;
    }

    /**
     * @return string
     */
    public function getWebservicePassword()
    {
        return $this->webservicePassword;
    }

    /**
     * @param string $webservicePassword
     */
    public function setWebservicePassword($webservicePassword)
    {
        $this->webservicePassword = $webservicePassword;
    }

    /**
     * @return string
     */
    public function getWebserviceNonce()
    {
        return $this->webserviceNonce;
    }

    /**
     * @param string $webserviceNonce
     */
    public function setWebserviceNonce($webserviceNonce)
    {
        $this->webserviceNonce = $webserviceNonce;
    }


}