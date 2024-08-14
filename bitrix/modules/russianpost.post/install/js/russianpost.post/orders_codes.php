<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
use \Bitrix\Main\Loader;
$module_id = "russianpost.post";
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
Loader::includeModule('sale');
Loader::includeModule($module_id);
$context = \Bitrix\Main\Application::getInstance()->getContext();
$siteId = $context->getSite();
$location_code = \Russianpost\Post\Optionpost::get("location", true, $siteId);
$addressCode = \Russianpost\Post\Optionpost::get('address', true, $siteId);
$streetCode = \Russianpost\Post\Optionpost::get('street', true, $siteId);
$houseCode = \Russianpost\Post\Optionpost::get('house', true, $siteId);
$flatCode = \Russianpost\Post\Optionpost::get('flat', true, $siteId);
$zipCode = \Russianpost\Post\Optionpost::get('zip', true, $siteId);

if ($locProp = \CSaleOrderProps::GetList(array(), array('PERSON_TYPE_ID' => $_REQUEST['person_type_id'], 'CODE' => $location_code))->Fetch())
{
	$loc_prop_id = $locProp['ID'];
}
if ($zipProp = \CSaleOrderProps::GetList(array(), array('PERSON_TYPE_ID' => $_REQUEST['person_type_id'], 'CODE' => $zipCode))->Fetch())
{
	$zip_prop_id = $zipProp['ID'];
}
if ($deliveryTypeProp = \CSaleOrderProps::GetList(array(), array('PERSON_TYPE_ID' => $_REQUEST['person_type_id'], 'CODE' => 'RUSSIANPOST_TYPEDLV'))->Fetch())
{
	$delivery_type_prop_id = $deliveryTypeProp['ID'];
}
if (!$bSplitAddress)
{
	if ($addressProp = \CSaleOrderProps::GetList(array(), array('PERSON_TYPE_ID' => $_REQUEST['person_type_id'], 'CODE' => $addressCode))->Fetch())
	{
		$address_prop_id = $addressProp['ID'];
		$address_prop_type = $addressProp['TYPE'];
	}

}
else
{
	if ($streetProp = \CSaleOrderProps::GetList(array(), array('PERSON_TYPE_ID' => $_REQUEST['person_type_id'], 'CODE' => $streetCode))->Fetch())
	{
		$street_prop_id = $streetProp['ID'];
		$street_prop_type = $streetProp['TYPE'];
	}
	if ($houseProp = \CSaleOrderProps::GetList(array(), array('PERSON_TYPE_ID' => $_REQUEST['person_type_id'], 'CODE' => $houseCode))->Fetch())
	{
		$house_prop_id = $houseProp['ID'];
		$house_prop_type = $houseProp['TYPE'];
	}
	if ($flatProp = \CSaleOrderProps::GetList(array(), array('PERSON_TYPE_ID' => $_REQUEST['person_type_id'], 'CODE' => $flatCode))->Fetch())
	{
		$flat_prop_id = $flatProp['ID'];
		$flat_prop_type = $flatProp['TYPE'];
	}
}

$ajaxData['zip'] = $zip_prop_id;
$ajaxData['address'] = $address_prop_id;
$ajaxData['street'] = $street_prop_id;
$ajaxData['house'] = $house_prop_id;
$ajaxData['flat'] = $flat_prop_id;
$ajaxData['location'] = $loc_prop_id;

$ajaxData['delivery_type'] = $delivery_type_prop_id;
$ajaxData['split'] = $bSplitAddress;
$ajaxData['status'] = 'success';

$APPLICATION->RestartBuffer();
header('Content-Type: application/json');
die(\Bitrix\Main\Web\Json::encode($ajaxData));
?>