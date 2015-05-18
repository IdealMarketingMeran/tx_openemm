<?php

namespace Ideal\Openemm\Domain\Model;

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

class FrontendUser extends \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
{
    /**
     * @var int
     */
    protected $emmId;

    /**
     * @var \Datetime
     */
    protected $emmLastSynchronisation;

    /**
     * @return int
     */
    public function getEmmId()
    {
        return $this->emmId;
    }

    /**
     * @param int $emmId
     */
    public function setEmmId($emmId)
    {
        $this->emmId = $emmId;
    }

    /**
     * @return \Datetime
     */
    public function getEmmLastSynchronisation()
    {
        return $this->emmLastSynchronisation;
    }

    /**
     * @param \Datetime $emmLastSynchronisation
     */
    public function setEmmLastSynchronisation($emmLastSynchronisation)
    {
        $this->emmLastSynchronisation = $emmLastSynchronisation;
    }

}