<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
if(!empty($component->__parent))
{
	$component->__parent->arResult['CUSTOM_OFFERS'] = [];
	foreach($templateData['ITEMS'] as $arItem)
	{
		if(!empty($arItem['ID']))
			$component->__parent->arResult['CUSTOM_OFFERS'][$arItem['ID']] = $arItem;
	}
}/*
print('<pre>');
print_r($component);
print('</pre>');*/