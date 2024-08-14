<?
// ################################################
// Company: NicLab
// Site: https://www.psdtobitrix.ru
// Copyright (c) 2013-2018 NicLab
// ################################################
namespace Ptb\Canonical;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Context;
use Bitrix\Main\Page;
use Bitrix\Main\Web\Uri;
use Bitrix\Main\Localization\Loc;

/**
 * Class Handlers
 *
 * @author Nic-lab n.revin
 */
class Handlers
{
    
    private static $moduleName = 'ptb.canonical';
    
    public static function setCanonical()
    {
        $moduleName = self::$moduleName;
        
        if (! (defined('ADMIN_SECTION') && ADMIN_SECTION == true) && Loader::includeModule($moduleName)) {
            
            global $APPLICATION, $CACHE_MANAGER;
            
            $isManySite = Option::get($moduleName, 'ptb_canonical_for_sites', 'N') == 'Y';
            $bDisableIndex = Option::get($moduleName, 'ptb_index_disable', 'N') == 'Y';
            $isActive = Option::get($moduleName, 'ptb_canonical_active', 'Y', $isManySite ? SITE_ID : '-') == 'Y';
            $b404 = Option::get($moduleName, 'ptb_canonical_404', 'N', $isManySite ? SITE_ID : '-') == 'Y';
            
            
            if ($b404 && defined("ERROR_404") && ERROR_404 == true) {
                $isActive = false;
            }
            
            if ($isActive) {
                $isSetDefault = Option::get($moduleName, 'ptb_canonical_default', 'Y', $isManySite ? SITE_ID : '-') == 'Y';
                $isSetQuery = Option::get($moduleName, 'ptb_canonical_query', 'N', $isManySite ? SITE_ID : '-') == 'N';
                $cacheTime = Option::get($moduleName, 'ptb_canonical_cache_time', '86400', $isManySite ? SITE_ID : '-');
                $isUseServerUri = Option::get($moduleName, 'ptb_server_request_uri', 'N', $isManySite ? SITE_ID : '-') == 'Y';
                $isUseGetParams = Option::get($moduleName, 'ptb_get_params', 'N', $isManySite ? SITE_ID : '-') == 'Y';
                
                $curPage = $isUseGetParams ? $APPLICATION->GetCurPageParam("", array(
                    'clear_cache',
                    'clear_cache_session',
                    'back_url_admin',
                    'back_url',
                    'backurl',
                    'login',
                    'logout',
                    'compress'
                )) : $APPLICATION->GetCurPage($bDisableIndex ? false : null);
                
                if ($isUseServerUri) {
                    $curPage = $isUseServerUri ? $_SERVER['REQUEST_URI'] : str_replace("?" . $_SERVER['QUERY_STRING'], "", $_SERVER['REQUEST_URI']);
                }
                
                $obCache = Cache::createInstance();
                $cacheDir = '/' . SITE_ID . '/' . $moduleName . '/list/';
                
                $arFilter = array(
                    'ACTIVE' => 'Y',
                    'USE_REGEXP' => array(
                        false,
                        'N'
                    ),
                    '=SITE_ID' => SITE_ID
                );
                
                if ($isSetQuery) {
                    $arFilter['=PAGE'] = $curPage;
                }
                
                if ($obCache->initCache($cacheTime, md5($moduleName . SITE_ID . serialize($arFilter)), $cacheDir)) {
                    $arItems = $obCache->getVars();
                } elseif ($obCache->startDataCache()) {
                    
                    $CACHE_MANAGER->StartTagCache($cacheDir);
                    
                    $rs = ListTable::getList(array(
                        'filter' => $arFilter,
                        'select' => array(
                            'CANONICAL',
                            'PAGE'
                        )
                    ));
                    
                    $arItems = array();
                    
                    while ($arItem = $rs->fetch()) {
                        $arItems[$arItem['PAGE']] = $arItem['CANONICAL'];
                    }
                    
                    $CACHE_MANAGER->RegisterTag("ptb_canonical");
                    $CACHE_MANAGER->EndTagCache();
                    $obCache->endDataCache($arItems);
                }
                
                $curCanonical = trim($arItems[$curPage]);
                
                if (! $curCanonical) {
                    $curCanonical = self::setCanonicalByRegExp($curPage, $isManySite, $cacheTime);
                }
                
                if ((! $curCanonical || is_null($curCanonical)) && $isSetDefault) {
                    $curCanonical = $curPage;
                }
                
                if ($curCanonical) {
                    self::addHeadString($curCanonical);
                }
            }
        }
    }
    
    protected static function getDeleteParams()
    {
        $isManySite = Option::get(static::$moduleName, 'ptb_canonical_for_sites', 'N') == 'Y';
        $deleteGet = Option::get(static::$moduleName, 'ptb_get_delete_params', '', $isManySite ? SITE_ID : '-');
        $deleteGet = trim($deleteGet);
        $arDeleteGet = (array) explode("\n", str_replace("\r", "", $deleteGet));
        return $arDeleteGet;
    }
    
    protected static function addHeadString($curCanonical)
    {
        $moduleName = self::$moduleName;
        $host = Context::getCurrent()->getRequest()->getHttpHost();
        $hostWithProtocol = 'http' . (Context::getCurrent()->getRequest()->isHttps() ? 's' : '') . '://' . $host;
        
        $bDebug = Option::get($moduleName, 'ptb_canonical_debug', 'N') == 'Y';
        $bNotDomainSet = Option::get($moduleName, 'ptb_domain_disable', 'N') == 'Y';
        
        if (strpos($curCanonical, 'http://') === false && strpos($curCanonical, 'https://') === false && ! $bNotDomainSet) {
            $curCanonical = $hostWithProtocol . $curCanonical;
        }
        
        $uri = new Uri($curCanonical);
        if ($arDeleteGet = static::getDeleteParams()) {
            $uri->deleteParams($arDeleteGet);
        }
        
        
        Page\Asset::getInstance()->addString('<link rel="canonical" href="' . $uri->getUri() . '"'.($bDebug ? ' from="ptb.canonical"' : '').' />', true);
    }
    
    protected static function setCanonicalByRegExp($curPage, $isManySite, $cacheTime)
    {
        global $CACHE_MANAGER;
        $moduleName = self::$moduleName;
        
        $arFilter = array(
            'ACTIVE' => 'Y',
            'USE_REGEXP' => 'Y',
            '=SITE_ID' => SITE_ID
        );
        
        $obCache = Cache::createInstance();
        $cacheDir = '/' . SITE_ID . '/' . $moduleName . '/listregexp/';
        
        if ($obCache->initCache($cacheTime, md5($moduleName . SITE_ID, serialize($arFilter)), $cacheDir)) {
            $arItems = $obCache->getVars();
        } elseif ($obCache->startDataCache()) {
            $CACHE_MANAGER->StartTagCache($cacheDir);
            
            $rs = ListTable::getList(array(
                'filter' => $arFilter,
                'order' => array(
                    'SORT' => 'ASC',
                    'ID' => 'DESC'
                ),
                'select' => array(
                    'CANONICAL',
                    'PAGE'
                )
            ));
            
            $arItems = array();
            
            while ($arItem = $rs->fetch()) {
                $arItems[$arItem['PAGE']] = $arItem['CANONICAL'];
            }
            
            $CACHE_MANAGER->RegisterTag("ptb_canonical");
            $CACHE_MANAGER->EndTagCache();
            $obCache->endDataCache($arItems);
        }
        
        foreach ($arItems as $regexp => $canonical) {
            $pattern = "@" . $regexp . "@is";
            $match = [];
            if (preg_match($pattern, $curPage, $match)) {
                $replace = [];
                
                foreach ($match as $i => $val) {
                    if ($i == 0) {
                        continue;
                    }
                    
                    $replace['$' . $i] = $val;
                }
                
                $canonical = str_replace(array_keys($replace), $replace, $canonical);
                
                return $canonical;
            }
        }
        
        return null;
    }
    
    public static function OnBeforeProlog()
    {
        if (! (defined('ADMIN_SECTION') && ADMIN_SECTION == true) && $GLOBALS['USER']->IsAdmin()) {
            global $APPLICATION;
            
            $APPLICATION->AddPanelButton(array(
                'ID' => 'ptb_canonical_panel_button',
                'TEXT' => Loc::getMessage('PTB_CANONICAL_HANDLERS_PANEL_BUTTON_TEXT'),
                'MAIN_SORT' => 10000,
                'SORT' => 10000,
                'HREF' => '/bitrix/admin/ptb_canonical_edit.php?lang=' . LANG . '&URL=' . $APPLICATION->GetCurPage(false) . '&SITE_ID=' . SITE_ID . '&backurl=' . $APPLICATION->GetCurPageParam('', [], false),
                'ICON' => '',
                'SRC' => '/bitrix/images/ptb.canonical/panel.png',
                "ALT" => Loc::getMessage('PTB_CANONICAL_HANDLERS_PANEL_BUTTON_TEXT'),
                "HINT" => ''
            ), false);
        }
    }
}
?>