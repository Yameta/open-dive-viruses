<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * @var array $arParams
 * @var array $arResult
 * @var SaleOrderAjax $component
 */

$component = $this->__component;
$component::scaleImages($arResult['JS_DATA'], $arParams['SERVICES_IMAGES_SCALING']);
//echo '<pre>'; print_r($arResult['JS_DATA']);
//$arResult['JS_DATA']['LAST_ORDER_DATA']['PAY_SYSTEM']= 1; 