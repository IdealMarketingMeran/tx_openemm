<?php

namespace Ideal\Openemm\Messaging;

/* * *************************************************************
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
 * ************************************************************* */

/**
 * FlashMassages new styles
 *
 * @author Markus Pircher
 */
class FlashMessage extends \TYPO3\CMS\Core\Messaging\FlashMessage {

    protected $classes = array(
        self::NOTICE => 'notice alert alert-info',
        self::INFO => 'info alert alert-info',
        self::OK => 'ok alert alert-success',
        self::WARNING => 'warning alert alert-warning',
        self::ERROR => 'error alert alert-danger'
    );

    /**
     * Renders the flash message.
     *
     * @return string The flash message as HTML.
     */
    public function render() {
        $title = '';
        if (!empty($this->title)) {
            $title = '<div class="header">' . $this->title . '</div>';
        }
        $message = '<div class="' . $this->getClass() . '">' . $title . '' . $this->message . '</div>';
        return $message;
    }

}
