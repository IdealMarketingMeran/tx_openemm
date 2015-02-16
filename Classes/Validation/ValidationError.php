<?php

namespace Ideal\Openemm\Validation;

/* * *************************************************************
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
 * ************************************************************* */


abstract class ValidationError {
    const ISEMPTY = 1;
    const EMAIL = 2;
    const NUMBERIC = 3;
    const CHECKED = 4;
    const TOOMANY = 5;
    const FORMAT = 6;
    const FILESIZE = 7;
}
