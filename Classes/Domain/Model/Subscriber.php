<?php
namespace IDEAL\Domaintemp\Domain\Model;


/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2015
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
 * Subscriber
 */
class Subscriber extends \TYPO3\CMS\Extbase\DomainObject\AbstractValueObject {

	/**
	 * OpenEMM customer ID
	 *
	 * @var integer
	 */
	protected $customerId = 0;

	/**
	 * Customer has confirmed per Mail
	 *
	 * @var boolean
	 */
	protected $confirmed = FALSE;

	/**
	 * Name
	 *
	 * @var string
	 */
	protected $name = '';

	/**
	 * Email
	 *
	 * @var string
	 */
	protected $email = '';

	/**
	 * List of Mailinglists
	 *
	 * @var string
	 */
	protected $mailingLists = '';

	/**
	 * If unsubscript request
	 *
	 * @var boolean
	 */
	protected $unsubscribt = false;

	/**
	 * Serialised Parameter Data
	 *
	 * @var string
	 */
	protected $parameters = '';

	/**
	 * Returns the customerId
	 *
	 * @return integer $customerId
	 */
	public function getCustomerId() {
		return $this->customerId;
	}

	/**
	 * Sets the customerId
	 *
	 * @param integer $customerId
	 * @return void
	 */
	public function setCustomerId($customerId) {
		$this->customerId = $customerId;
	}

	/**
	 * Returns the confirmed
	 *
	 * @return boolean $confirmed
	 */
	public function getConfirmed() {
		return $this->confirmed;
	}

	/**
	 * Sets the confirmed
	 *
	 * @param boolean $confirmed
	 * @return void
	 */
	public function setConfirmed($confirmed) {
		$this->confirmed = $confirmed;
	}

	/**
	 * Returns the boolean state of confirmed
	 *
	 * @return boolean
	 */
	public function isConfirmed() {
		return $this->confirmed;
	}

	/**
	 * Returns the name
	 *
	 * @return string $name
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Sets the name
	 *
	 * @param string $name
	 * @return void
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * Returns the email
	 *
	 * @return string $email
	 */
	public function getEmail() {
		return $this->email;
	}

	/**
	 * Sets the email
	 *
	 * @param string $email
	 * @return void
	 */
	public function setEmail($email) {
		$this->email = $email;
	}

	/**
	 * Returns the mailingLists
	 *
	 * @return string $mailingLists
	 */
	public function getMailingLists() {
		return $this->mailingLists;
	}

	/**
	 * Sets the mailingLists
	 *
	 * @param string $mailingLists
	 * @return void
	 */
	public function setMailingLists($mailingLists) {
		$this->mailingLists = $mailingLists;
	}

	/**
	 * Returns the unsubscribt
	 *
	 * @return boolean $unsubscribt
	 */
	public function getUnsubscribt() {
		return $this->unsubscribt;
	}
        
        /**
	 * Is unsubscribt
	 *
	 * @return boolean $unsubscribt
	 */
	public function isUnsubscribt() {
		return $this->unsubscribt;
	}

	/**
	 * Sets the unsubscribt
	 *
	 * @param boolean $unsubscribt
	 * @return void
	 */
	public function setUnsubscribt($unsubscribt) {
		$this->unsubscribt = $unsubscribt;
	}

	/**
	 * Returns the parameters
	 *
	 * @return string $parameters
	 */
	public function getParameters() {
		return $this->parameters;
	}

	/**
	 * Sets the parameters
	 *
	 * @param string $parameters
	 * @return void
	 */
	public function setParameters($parameters) {
		$this->parameters = $parameters;
	}

}