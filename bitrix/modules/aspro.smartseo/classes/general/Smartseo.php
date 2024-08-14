<?php

namespace Aspro\Smartseo\General;

use \Bitrix\Main\Application;

class Smartseo
{
    const MODULE_ID = 'aspro.smartseo';

    private static $currentDataScope = [];
    private static $currentSeoProperty = [];
    /** @var \Aspro\Smartseo\Admin\Settings\SettingSmartseo */
    private static $setting = null;

    private static $isParentModule = false;
    private static $enableNoindexRule = true;

    static public function init()
    {
         self::$setting = \Aspro\Smartseo\Admin\Settings\SettingSmartseo::getInstance();
    }

    public static function getModulePath($needDocumentRoot = true)
    {
        $dir = '';

        if ($needDocumentRoot) {
            $dir = dirname(__DIR__);
        } else {
            $dir = str_ireplace(Application::getDocumentRoot(), '', dirname(__DIR__));
        }

        $dir = str_ireplace(['classes'], '', $dir);

        return preg_replace('|([/]+)|s', '/', $dir);
    }

    static public function setCurrentData($dataScope)
    {
        self::$currentDataScope = $dataScope;
    }

    static public function getCurrentData($code = '')
    {
        if(self::$currentDataScope && self::$currentDataScope[$code]) {
            return self::$currentDataScope[$code];
        }

        return self::$currentDataScope;
    }

    static public function setCurrentSeoProperty($seoProperty)
    {
        self::$currentSeoProperty = $seoProperty;
    }

    static public function getCurrentSeoProperty($code = '')
    {
        if(self::$currentSeoProperty && self::$currentSeoProperty[$code]) {
            return self::$currentSeoProperty[$code];
        }

        return self::$currentSeoProperty;
    }

    static public function getSettingObject()
    {
        return self::$setting;
    }

    static public function fixBitrixCoreAjaxAuth()
    {
        global $APPLICATION;

        if (\CSite::InDir('/bitrix/') && preg_match('|' . self::MODULE_ID . '_smartseo.php|', $APPLICATION->GetCurPage(false)) && $_REQUEST['AUTH_FORM'] == 'Y') {
            $_SESSION['SMARTSEO_NEED_RELOAD'] = true;
        }
    }

    static public function disallowNoindexRule(bool $value)
    {
        self::$enableNoindexRule = !$value;
    }

    static public function allowedNoindexRule()
    {
        return self::$enableNoindexRule;
    }

    static public function validateModules()
    {
        if(self::$isParentModule) {
            return self::$isParentModule;
        }

        if (!\Bitrix\Main\Loader::includeModule(self::MODULE_ID)) {
            self::$isParentModule = false;

            return false;
        }

        self::$isParentModule = true;

        return self::$isParentModule;
    }

    static public function getUrlByReal($urlReal = '', $siteId = SITE_ID) {
        if(!$urlReal) {
            return '';
        }

	    $url = urldecode($urlReal);

	    if (mb_strtolower(LANG_CHARSET) === 'windows-1251') {
		    $url = \Bitrix\Main\Text\Encoding::convertEncoding($url, 'UTF-8', LANG_CHARSET);
	    }

        // delete bitrix params from url
        $arRealUrlParametrsValues = $arSystemParametersValues = [];
        $arSystemParameters = array_merge(\Bitrix\Main\HttpRequest::getSystemParameters(), ['bxajaxid']);
        $uri = new \Bitrix\Main\Web\Uri($url);
        parse_str($uri->getQuery(), $arRealUrlParametrsValues);
        $uri->deleteParams($arSystemParameters);        
        $url = $uri->getUri();

        $dataScope = \Aspro\Smartseo\Models\SmartseoFilterConditionUrlTable::getDataUrlByUrl($url, SITE_ID);

        if($dataScope && $dataScope['URL_REAL']) {
            $url = $dataScope['URL_NEW'];
        }

        if (is_array($arRealUrlParametrsValues)) {
            foreach ($arRealUrlParametrsValues as $key => $val) {
                if (in_array($key, $arSystemParameters)) {
                    $arSystemParametersValues[$key] = $val;
                }
            }

            if ($arSystemParametersValues) {
                $uri = new \Bitrix\Main\Web\Uri($url);
                $uri = $uri->addParams($arSystemParametersValues);
                $url = $uri->getUri();
            }
        }

        return $url;
    }

    static public function replaceRealUrlByNew($urlReal = '') {
        if(!self::getSettingObject()->isReplaceUrlPage()) {
            return $urlReal;
        }

       return self::getUrlByReal($urlReal);
    }

    public static function unserialize($data, array $arOptions = []) {
		if (!is_string($data)) return false;
		
		$arDefaultConfig = [
			'allowed_classes' => false,
		];
		$arConfig = array_merge($arDefaultConfig, $arOptions);

		return \unserialize($data, $arConfig);
	}
}
