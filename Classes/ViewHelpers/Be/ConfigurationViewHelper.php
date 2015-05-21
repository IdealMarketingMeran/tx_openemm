<?php
namespace Ideal\Openemm\ViewHelpers\Be;

class ConfigurationViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Be\AbstractBackendViewHelper {
    /**
     * @var \TYPO3\CMS\Core\Page\PageRenderer
     */
    private $pageRenderer = NULL;

    public function render() {

        $this->pageRenderer = $this->getDocInstance()->getPageRenderer();

        $baseUrl = '../' . \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath('openemm');
        $this->pageRenderer->disableCompressJavascript();

        $this->pageRenderer->addInlineSettingArray(
            'openemm',
            array('baseUrl' => $baseUrl)
        );
        if(!\TYPO3\CMS\Core\Utility\GeneralUtility::compat_version('7.0')) {
            $this->pageRenderer->addJsFile($baseUrl . 'Resources/Public/Js/bootstrap.min.js');
            $this->pageRenderer->addCssFile($baseUrl . 'Resources/Public/Css/bootstrap.min.css');
        }



        // This call is only done for backwards compatibility for TYPO3 versions below 6.2
        // It can be removed once compatibility for these versions is dropped as since 6.2 this is populated automatically
        //$this->pageRenderer->addInlineSettingArray(
        //    'ajaxUrls',
        ////);

        // Custom CSS
        //$this->pageRenderer->addCssFile($baseUrl . 'Resources/Public/jsDomainModeling/extbaseModeling.css');
    }
}