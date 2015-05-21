<?php
/**
 * Created by PhpStorm.
 * User: Petra
 * Date: 21.05.15
 * Time: 11:29
 */

namespace Ideal\Openemm\ViewHelpers\Be;

class IconViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Be\AbstractBackendViewHelper
{

    /**
     * @param string $type
     * @return string
     */
    public function render($type) {
        if(\TYPO3\CMS\Core\Utility\GeneralUtility::compat_version('7.0')) {
            return $this->getIconsT7($type);
        }
        return $this->getIcons($type);
    }

    /**
     * @param string $type
     * @return string
     */
    private function getIcons($type) {
        switch(strtolower($type)) {
            case "trash":
                return '<span class="t3-icon t3-icon-actions t3-icon-actions-edit t3-icon-edit-delete">&nbsp;</span>';
            case "edit":
                return '<span class="t3-icon t3-icon-actions t3-icon-actions-document t3-icon-document-open">&nbsp;</span>';
        }
    }

    /**
     * @param string $type
     * @return string
     */
    private function getIconsT7($type) {
        switch(strtolower($type)) {
            case "trash":
                return '<span class="t3-icon fa fa-trash"> </span>';
            case "edit":
                return '<span class="t3-icon fa fa-pencil"> </span>';
        }
    }
}