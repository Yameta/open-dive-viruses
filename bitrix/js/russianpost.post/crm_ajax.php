<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
use \Bitrix\Main\Loader;
$module_id = "russianpost.post";
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
Loader::includeModule('sale');
Loader::includeModule($module_id);
$deliveryId = $_REQUEST['delivery_id'];
Loc::loadMessages(__FILE__);
$context = \Bitrix\Main\Application::getInstance()->getContext();
$siteId = $context->getSite();
if($deliveryId > 0)
{
	$service = \Bitrix\Sale\Delivery\Services\Manager::getById($deliveryId);
	if (strpos($service['CLASS_NAME'], '\Sale\Handlers\Delivery\RussianpostProfile') !== false && $service['CONFIG']['MAIN']['SERVICE_TYPE'] == 1)
	{

		$ajaxData['status'] = 'success';
		$guid_id = Option::get($module_id, "GUID_ID");

		$location_code = \Russianpost\Post\Optionpost::get("location", true, $siteId);
		$addressCode = \Russianpost\Post\Optionpost::get('address', true, $siteId);
		$streetCode = \Russianpost\Post\Optionpost::get('street', true, $siteId);
		$houseCode = \Russianpost\Post\Optionpost::get('house', true, $siteId);
		$flatCode = \Russianpost\Post\Optionpost::get('flat', true, $siteId);
		$zipCode = \Russianpost\Post\Optionpost::get('zip', true, $siteId);
		if ($addressCode == '')
		{
			$bSplitAddress = true;
		}
		$location = '';
		if ($locProp = \CSaleOrderProps::GetList(array(), array('PERSON_TYPE_ID' => $_REQUEST['person_type_id'], 'CODE' => $location_code))->Fetch())
		{
			$location = $_REQUEST['location'];
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
		if ($location != '')
		{
			$res = \Bitrix\Sale\Location\LocationTable::getList(array(
				'filter' => array(
					'CODE' => array($location),
					//'CODE' => array('0000073738'),
				),
				'select' => array(
					'EXTERNAL.*',
					'EXTERNAL.SERVICE.CODE'
				)
			));
			$arZip = array();
			while ($item = $res->fetch())
			{
				if ($item['SALE_LOCATION_LOCATION_EXTERNAL_SERVICE_CODE'] == 'ZIP_LOWER'
					|| $item['SALE_LOCATION_LOCATION_EXTERNAL_SERVICE_CODE'] == 'ZIP'
				)
				{
					$threeDigits = substr($item['SALE_LOCATION_LOCATION_EXTERNAL_XML_ID'], 0, 3);
					$arZip[$threeDigits] = "'" . $threeDigits . "'";
				}
			}
			$strZip = implode(", ", $arZip);
		}
		$ajaxData['message'] = '<a href="javascript:void(0)" onclick="event.preventDefault(); openMap(\'' . $guid_id . '\', ' . ($_REQUEST['price_basket']*100) . ',' . intval($_REQUEST['weight_basket']) . ',[' . $strZip . '], \'' . $location . '\');">'.Loc::getMessage('SALE_DLV_RUSSIANPOST_POST_LINK').'</a>';
		$ajaxData['message'] .= '<input type="hidden" name="link_set" id="link_set" value="Y">';
		$ajaxData['message'] .= '<input type="hidden" id="russianpost_address_prop" name="russianpost_address_prop" value="' . $address_prop_id . '">';
		$ajaxData['message'] .= '<input type="hidden" id="russianpost_street_prop" name="russianpost_street_prop" value="' . $street_prop_id . '">';
		$ajaxData['message'] .= '<input type="hidden" id="russianpost_house_prop" name="russianpost_house_prop" value="' . $house_prop_id . '">';
		$ajaxData['message'] .= '<input type="hidden" id="russianpost_flat_prop" name="russianpost_flat_prop" value="' . $flat_prop_id . '">';
		$ajaxData['message'] .= '<input type="hidden" id="russianpost_zip_prop" name="russianpost_zip_prop" value="' . $zip_prop_id . '">';
		$ajaxData['message'] .= '<input type="hidden" id="russianpost_delivery_type_prop" name="russianpost_delivery_type_prop" value="' . $delivery_type_prop_id . '">';
		$ajaxData['message'] .= '<input type="hidden" id="russianpost_split_address" name="russianpost_split_address" value="'.$bSplitAddress.'">';
		$ajaxData['message'] .= '<input type="hidden" id="russianpost_index_delivery" name="russianpost_index_delivery" value="'.htmlspecialcharsbx($_REQUEST['index_delivery']).'">';

		$ajaxData['message'] .= '<input type="hidden" id="russianpost_address_prop_type" name="russianpost_address_prop_type" value="' . $address_prop_type . '">';
		$ajaxData['message'] .= '<input type="hidden" id="russianpost_street_prop_type" name="russianpost_street_prop_type" value="' . $street_prop_type . '">';
		$ajaxData['message'] .= '<input type="hidden" id="russianpost_house_prop_type" name="russianpost_house_prop_type" value="' . $house_prop_type . '">';
		$ajaxData['message'] .= '<input type="hidden" id="russianpost_flat_prop_type" name="russianpost_flat_prop_type" value="' . $flat_prop_type . '">';
		$ajaxData['message'] .= '<input type="hidden" id="russianpost_crm_data" name="russianpost_crm_data" value="Y">';
		$ajaxData['message'] .= '<input type="hidden" id="russianpost_result_price" name="russianpost_result_price" value="">';
		$ajaxData['message'] .= '<input type="hidden" id="russianpost_delivery_description" name="russianpost_delivery_description" value="">';
	}
	else
	{
		$ajaxData['status'] = 'success';
		$ajaxData['message'] = '';
	}
}
else
{
	$ajaxData['status'] = 'error';
	$ajaxData['message'] = '';
}



$APPLICATION->RestartBuffer();
header('Content-Type: application/json');
die(\Bitrix\Main\Web\Json::encode($ajaxData));
?>