<?
namespace Russianpost\Post;

use Bitrix\Sale\Delivery;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Sale;
use Bitrix\Main\Loader;
Loc::loadMessages(__FILE__);


class Tools
{
    private static $MODULE_ID = 'russianpost.post';
    protected static $url_widget = 'https://widget.pochta.ru';
	const ERROR_STATUS_400 = "400 Bad Request";
	const ERROR_STATUS_401 = "401 Unauthorized";
	const ERROR_STATUS_403 = "403 Forbidden";
	const ERROR_STATUS_404 = "404 Not Found";
	const ERROR_STATUS_405 = "405 Method Not Allowed";
	const ERROR_STATUS_415 = "415 Unsupported Media Type";
	const ERROR_STATUS_420 = "420 Enhance Your Calm";
	const ERROR_STATUS_422 = "422 Enhance Your Calm";
	const ERROR_STATUS_500 = "500 Internal Server Error";
	const ERROR_STATUS_503 = "503 Service Unavailable";
	const PROFILE_PICKUP = 1;
	const PROFILE_COURIER = 2;
	const PROFILE_WORLDPICKUP = 3;
	const PROFILE_WORLDCOURIER = 4;
	const PROFILE_PICKUPNOTE = 5;


    public static function BeforeSaved(\Bitrix\Main\Event $event)
    {
	    $b24path = array (
		    'ORDER' => '/bitrix/components/bitrix/crm.order.details/ajax.php',
		    'SHIPMENT' => '/bitrix/components/bitrix/crm.order.shipment.details/ajax.php',
		    'ORDER1' => '/shop/orders/details/',
		    'SHIPMENT1' => '/shop/orders/shipment/details/',
	    );
	    $curPage = $GLOBALS['APPLICATION']->GetCurPage();
        $order = $event->getParameter("ENTITY");
        $propertyCollection = $order->getPropertyCollection();
        $deliveryIds = $order->getDeliverySystemId();
        $bCheckProps = false;
        $bSaveType = false;
        $bSetZip = false;
        $bCheckMarked = false;
        $bValidateZip = false;
	    $site_id = $order->getSiteId();
        $markCheckOff = Option::get(self::$MODULE_ID, "RUSSIANPOST_MARK_OFF");
	    $validationCheckOn = Option::get(self::$MODULE_ID, "RUSSIANPOST_INDEX_VALIDATION");
        foreach($deliveryIds as $deliveryId)
        {
        	if($deliveryId > 0)
	        {
		        $service = Delivery\Services\Manager::getById($deliveryId);
		        if(strpos($service['CLASS_NAME'], '\Sale\Handlers\Delivery\RussianpostProfile') !== false)
		        {
		        	$bSaveType = true;
		        	if($markCheckOff == 'Y')
			        {
				        $bCheckMarked = true;
			        }
		        }
		        if(strpos($service['CLASS_NAME'], '\Sale\Handlers\Delivery\RussianpostProfile') !== false
			        && $service['CONFIG']['MAIN']['SERVICE_TYPE'] == self::PROFILE_PICKUP)
		        {
			        $bCheckProps = true;
			        break;
		        }
		        if(strpos($service['CLASS_NAME'], '\Sale\Handlers\Delivery\RussianpostProfile') !== false
			        && ($service['CONFIG']['MAIN']['SERVICE_TYPE'] == self::PROFILE_PICKUP ||
				        $service['CONFIG']['MAIN']['SERVICE_TYPE'] == self::PROFILE_COURIER ||
				        $service['CONFIG']['MAIN']['SERVICE_TYPE'] == self::PROFILE_PICKUPNOTE
			        ))
		        {
			        $bValidateZip = true;
			        break;
		        }
		        if(strpos($service['CLASS_NAME'], '\Sale\Handlers\Delivery\RussianpostProfile') !== false &&
			        ($service['CONFIG']['MAIN']['SERVICE_TYPE'] == self::PROFILE_WORLDPICKUP
				        || $service['CONFIG']['MAIN']['SERVICE_TYPE'] == self::PROFILE_WORLDCOURIER))
		        {
			        $bSetZip = false;
			        break;
		        }
	        }
        }
        if($bCheckMarked)
        {
        	$isMarked = $order->isMarked();
        	if($isMarked)
	        {
		        return new \Bitrix\Main\EventResult(\Bitrix\Main\EventResult::ERROR, new \Bitrix\Sale\ResultError(Loc::getMessage('SALE_DLV_RUSSIANPOST_POST_ERROR_ORDER')), 'sale');
	        }
        }
        if($bSaveType)
        {
        	foreach ($propertyCollection as $propItem)
	        {
		        $arProp  = $propItem->getProperty();
		        if($arProp['CODE'] == 'RUSSIANPOST_TYPEDLV')
		        {
		        	if(trim($_SESSION['russianpost_post_calc']['shipment_type'])!='')
			        {
			        	$propItem->setValue($_SESSION['russianpost_post_calc']['shipment_type']);
			        }
		        }
	        }
        }
	    $zipPropValue = '';
	    $zipCode = Optionpost::get('zip', true, $site_id);
	    //$zipProp = $propertyCollection->getDeliveryLocationZip();
	    $zipProp = self::getPropertyFromCollectionByCode($propertyCollection, $zipCode);
	    if($zipProp)
		    $zipPropValue   = $zipProp->getValue();
	    if(isset($_REQUEST['russianpost_result_zip']) && trim($_REQUEST['russianpost_result_zip']) != ''
		    && $_REQUEST['russianpost_result_zip'] != $zipPropValue && $zipProp)
	    {
		    $zipProp->SetValue($_REQUEST['russianpost_result_zip']);
		    $zipPropValue = $_REQUEST['russianpost_result_zip'];
	    }
        if($bCheckProps)
        {
	        $request = \Bitrix\Main\Context::getCurrent()->getRequest();
	        if($request->isAdminSection())
	        {
	            if(isset($_REQUEST['russianpost_admin_data']) && $_REQUEST['russianpost_admin_data'] == 'Y')
                {
                    if($zipProp && $_REQUEST['russianpost_result_zip']!='')
                    {
                        $zipProp->SetValue($_REQUEST['russianpost_result_zip']);
                    }
                    $addressCode = Optionpost::get('address', true, $site_id);
                    if(!$addressCode)
                    {
                        $streetCode = Optionpost::get('street', true, $site_id);
                        $streetProp = self::getPropertyFromCollectionByCode($propertyCollection, $streetCode);
                        if($streetProp && $_REQUEST['russianpost_street_address']!='')
                            $streetProp->SetValue($_REQUEST['russianpost_street_address']);

                    }
                    else
                    {
                        $addrProp = self::getPropertyFromCollectionByCode($propertyCollection, $addressCode);
                        if($addrProp && $_REQUEST['russianpost_result_address']!='')
                            $addrProp->SetValue($_REQUEST['russianpost_result_address']);
                    }
                    foreach ($propertyCollection as $propItem)
                    {
                        $arProp  = $propItem->getProperty();
                        if($arProp['CODE'] == 'RUSSIANPOST_TYPEDLV' && $_REQUEST['russianpost_result_type']!='')
                        {
                            $propItem->SetValue($_REQUEST['russianpost_result_type']);
                        }
                    }
                }
		        $shipmentType = '';
		        $arOrderVals = $order->getFields()->getValues();
		        $orderSendStatus = Option::get(self::$MODULE_ID, "RUSSIANPOST_ORDER_PAID_STATUS","", $site_id);
		        if($orderSendStatus != '' && $arOrderVals['STATUS_ID'] == $orderSendStatus)
		        {
			        foreach ($propertyCollection as $propItem)
			        {
				        $arProp  = $propItem->getProperty();
				        if($arProp['CODE'] == 'RUSSIANPOST_TYPEDLV')
				        {
					        $shipmentType = $propItem->getValue();
					        break;
				        }
			        }
		        }
		        else
		        {
			        $shipmentType = $_SESSION['russianpost_post_calc']['shipment_type'];
		        }
	        }
	        if(strpos($curPage, $b24path['ORDER']) !== false || strpos($curPage, $b24path['SHIPMENT']) !== false ||
		        strpos($curPage, $b24path['ORDER1']) !== false || strpos($curPage, $b24path['SHIPMENT1']) !== false)
	        {
		        if(isset($_REQUEST['russianpost_crm_data']) && $_REQUEST['russianpost_crm_data'] == 'Y')
		        {
			        if($zipProp && $_REQUEST['russianpost_result_zip']!='')
			        {
				        $zipProp->SetValue($_REQUEST['russianpost_result_zip']);
			        }
			        $addressCode = Optionpost::get('address', true, $site_id);
			        if(!$addressCode)
			        {
				        $streetCode = Optionpost::get('street', true, $site_id);
				        $streetProp = self::getPropertyFromCollectionByCode($propertyCollection, $streetCode);
				        if($streetProp && $_REQUEST['russianpost_street_address']!='')
					        $streetProp->SetValue($_REQUEST['russianpost_street_address']);

			        }
			        else
			        {
				        $addrProp = self::getPropertyFromCollectionByCode($propertyCollection, $addressCode);
				        if($addrProp && $_REQUEST['russianpost_result_address']!='')
					        $addrProp->SetValue($_REQUEST['russianpost_result_address']);
			        }
			        foreach ($propertyCollection as $propItem)
			        {
				        $arProp  = $propItem->getProperty();
				        if($arProp['CODE'] == 'RUSSIANPOST_TYPEDLV' && $_REQUEST['russianpost_result_type']!='')
				        {
					        $propItem->SetValue($_REQUEST['russianpost_result_type']);
				        }
			        }
		        }
	        }
            if($zipPropValue == '')
            {
                return new \Bitrix\Main\EventResult(\Bitrix\Main\EventResult::ERROR, new \Bitrix\Sale\ResultError(Loc::getMessage('SALE_DLV_RUSSIANPOST_POST_EMPTY_ZIP')), 'sale');
            }
            $addrPropValue = trim(self::getAddress($propertyCollection, $site_id));
            if($addrPropValue == '')
            {
                return new \Bitrix\Main\EventResult(\Bitrix\Main\EventResult::ERROR, new \Bitrix\Sale\ResultError(Loc::getMessage('SALE_DLV_RUSSIANPOST_POST_EMPTY_ADDRESS')), 'sale');
            }
        }
        if($validationCheckOn != 'Y')
        	$bValidateZip = false;
	    if($bValidateZip)
	    {
		    if(!self::postZipValidate($zipPropValue))
		    {
			    return new \Bitrix\Main\EventResult(\Bitrix\Main\EventResult::ERROR, new \Bitrix\Sale\ResultError(Loc::getMessage('SALE_DLV_RUSSIANPOST_POST_NONVALIDATE_ZIP')), 'sale');
		    }
	    }
        
        if($bSetZip)
        {
	        /*$zipCode = Optionpost::get('zip', true, $site_id);
	        //$zipProp = $propertyCollection->getDeliveryLocationZip();
	        $zipProp = self::getPropertyFromCollectionByCode($propertyCollection, $zipCode);
	        if($zipProp)
	        {

		        $locProp = $propertyCollection->getDeliveryLocation();
		        if($locProp)
			        $locPropValue   = $locProp->getValue();
		        if($locPropValue)
		        {
			        $arCountryInfo = self::GetCountryByCode($locPropValue);
			        if(!empty($arCountryInfo))
			        {
				        $digitalCode = Hllist::GetCountryDigitalCode($arCountryInfo['CODE'], $arCountryInfo['NAME']);
				        
				        $zipProp->SetValue($digitalCode);
			        }
		        }
	        }*/
        }

    }

    public static function SaleSaved(\Bitrix\Main\Event $event)
    {
        $bSendToPost = false;
        $bAddLocationToAddress = false;
        $bWorldLocation = false;
	    $bWithNotification = false;
        $order = $event->getParameter("ENTITY");
        $deliveryIds = $order->getDeliverySystemId();
	    $site_id = $order->getSiteId();
        foreach($deliveryIds as $deliveryId)
        {
            if($deliveryId > 0)
            {
                $service = Delivery\Services\Manager::getById($deliveryId);
                if(strpos($service['CLASS_NAME'], '\Sale\Handlers\Delivery\RussianpostProfile') !== false)
                {
                    $bSendToPost = true;
                    if($service['CONFIG']['MAIN']['SERVICE_TYPE'] == self::PROFILE_COURIER
	                    || $service['CONFIG']['MAIN']['SERVICE_TYPE'] == self::PROFILE_PICKUPNOTE
	                    || $service['CONFIG']['MAIN']['SERVICE_TYPE'] == self::PROFILE_WORLDPICKUP
	                    || $service['CONFIG']['MAIN']['SERVICE_TYPE'] == self::PROFILE_WORLDCOURIER
                    )
                    {
                        $bAddLocationToAddress = true;
                    }
	                if($service['CONFIG']['MAIN']['SERVICE_TYPE'] == self::PROFILE_WORLDPICKUP
		                || $service['CONFIG']['MAIN']['SERVICE_TYPE'] == self::PROFILE_WORLDCOURIER)
	                {
		                $bWorldLocation = true;
	                }
	                if($service['CONFIG']['MAIN']['SERVICE_TYPE'] == self::PROFILE_PICKUPNOTE)
	                {
		                $bWithNotification = true;
	                }
                    break;
                }
            }
        }
        if($bSendToPost)
        {
            $orderId = $order->getId();

	        $arOrderVals = $order->getFields()->getValues();

            $propertyCollection = $order->getPropertyCollection();
            //$namePropValue  = $propertyCollection->getPayerName()->getValue();
	        $locProp = $propertyCollection->getDeliveryLocation();
	        if($locProp)
	        	$locPropValue   = $locProp->getValue();

	        //$profNameProp = $propertyCollection->getProfileName();
	        //if($profNameProp)
	        //	$profNamePropVal = $profNameProp->getValue();
	        $profNamePropVal = trim(self::getContact($propertyCollection, $site_id));
	        $zipCode = Optionpost::get('zip', true, $site_id);
            //$zipProp = $propertyCollection->getDeliveryLocationZip();
	        $zipProp = self::getPropertyFromCollectionByCode($propertyCollection, $zipCode);
            if($zipProp)
            	$zipPropValue   = $zipProp->getValue();
            $phoneCode = Optionpost::get('phone', true, $site_id);
            //$phoneProp = $propertyCollection->getPhone();
	        $phoneProp = self::getPropertyFromCollectionByCode($propertyCollection, $phoneCode);
            if($phoneProp)
            	$phonePropValue = $phoneProp->getValue();
	        $addressCode = Optionpost::get('address', true, $site_id);
	        if(!$addressCode)
	        	$bAddLocationToAddress = true;
	        $addrPropValue = self::getAddress($propertyCollection, $site_id);

            if($locPropValue)
            {
	            $item = \Bitrix\Sale\Location\LocationTable::getByCode($locPropValue, array(
		            'filter' => array('=NAME.LANGUAGE_ID' => LANGUAGE_ID),
		            'select' => array('*', 'NAME_RU' => 'NAME.NAME')
	            ))->fetch();

	            $resS = \Bitrix\Sale\Location\LocationTable::getList(array(
		            'filter' => array(
			            '=CODE' => $locPropValue,
			            '=PARENTS.NAME.LANGUAGE_ID' => LANGUAGE_ID,
			            '=PARENTS.TYPE.NAME.LANGUAGE_ID' => LANGUAGE_ID,
		            ),
		            'select' => array(
			            'I_ID' => 'PARENTS.ID',
			            'I_NAME_RU' => 'PARENTS.NAME.NAME',
			            'I_TYPE_CODE' => 'PARENTS.TYPE.CODE',
			            'I_TYPE_NAME_RU' => 'PARENTS.TYPE.NAME.NAME',
			            'I_CODE' => 'PARENTS.CODE',
		            ),
		            'order' => array(
			            'PARENTS.DEPTH_LEVEL' => 'asc'
		            )
	            ));
	            $locationFullName = '';
	            while($itemC = $resS->fetch())
	            {
		            if($itemC['I_TYPE_CODE'] != 'COUNTRY_DISTRICT')
		            {
			            $locationFullName .= $itemC['I_NAME_RU'].' ';
		            }
	            }
	            $locationFullName = trim($locationFullName);

	            if($bWorldLocation)
	            {
		            $arCountryInfo = self::GetCountryByCode($locPropValue);
		            if(!empty($arCountryInfo))
		            {
			            $digitalCode = Hllist::GetCountryDigitalCode($arCountryInfo['CODE'], $arCountryInfo['NAME']);
			            $arParams['DIGITAL_CODE'] = $digitalCode;
		            }
	            }
            }

            $basket = $order->getBasket();
            $weight = $basket->getWeight();
            $shipmentType = '';
	        $orderSendStatus = Option::get(self::$MODULE_ID, "RUSSIANPOST_ORDER_PAID_STATUS", "", $site_id);
	        foreach ($propertyCollection as $propItem)
	        {
		        $arProp  = $propItem->getProperty();
		        if($arProp['CODE'] == 'RUSSIANPOST_TYPEDLV')
		        {
			        $shipmentType = $propItem->getValue();
			        break;
		        }
	        }
	        if($shipmentType == '')
	        {
		        $shipmentType = $_SESSION['russianpost_post_calc']['shipment_type'];
	        }
            if(trim($shipmentType) != '')
            {
                $arParams['ORDER_ID'] = $orderId;
                $arParams['ACCOUNT_NUMBER'] = $order->getField('ACCOUNT_NUMBER');
                $arParams['WEIGHT'] = intval($weight);
                $arParams['ZIP'] = $zipPropValue;
                if($bAddLocationToAddress)
                    $address = $locationFullName.' '.$addrPropValue;
                else
                    $address = $addrPropValue;
                if($bWorldLocation)
                {
                	if($zipPropValue != '')
                		$address = $zipPropValue.', '.$address;
                	$address = self::Translit($address);
                }
                $arParams['ADDRESS'] = $address;
                $arParams['PHONE'] = $phonePropValue;
                $priceOrder = $order->getPrice();
                $priceDelivery = $order->getDeliveryPrice();

                #currency convertation
                $baseCurrency = \Bitrix\Currency\CurrencyManager::getBaseCurrency();
                $currencyList = \Bitrix\Currency\CurrencyManager::getCurrencyList();
                $profileCurrency = $service['CURRENCY'];
                if($baseCurrency != 'RUB' && isset($currencyList['RUB']))
                {
                    $priceOrder = \CCurrencyRates::ConvertCurrency($priceOrder, $baseCurrency, "RUB");
                    $priceDelivery = \CCurrencyRates::ConvertCurrency($priceDelivery, $baseCurrency, "RUB");
                }
                elseif($profileCurrency !='' && $profileCurrency!= 'RUB' && isset($currencyList['RUB']))
                {
	                $priceOrder = \CCurrencyRates::ConvertCurrency($priceOrder, $profileCurrency, "RUB");
	                $priceDelivery = \CCurrencyRates::ConvertCurrency($priceDelivery, $profileCurrency, "RUB");
                }
                $arParams['PRICE'] = ($priceOrder - $priceDelivery)*100;
                $arParams['SHIPMENT_TYPE'] = $shipmentType;
                $arParams['NAME'] = $profNamePropVal;
                if($bWorldLocation)
                {
	                $arParams['NAME'] = self::Translit($arParams['NAME']);
                }
                $arParams['DELIVERY_PRICE'] = ($priceDelivery * 100);
	            if($orderSendStatus != '')
	            {
	            	if($arOrderVals['STATUS_ID'] == $orderSendStatus)
	            		$arParams['FINANCIAL_STATUS'] = 'paid';
	            	else
		            {
		            	if(self::checkStatus($orderSendStatus, $arOrderVals['STATUS_ID']) && $order->isPaid())
			            //if(self::checkStatus($orderSendStatus, $arOrderVals['STATUS_ID']))
				            $arParams['FINANCIAL_STATUS'] = 'paid';
		            	else
				            $arParams['FINANCIAL_STATUS'] = 'unpaid';
		            }
	            }
	            else
	            {
		            $arParams['FINANCIAL_STATUS'] = 'unpaid';
	            }
	            $basket = $order->getBasket();
	            $basketItems = $basket->getBasketItems();
	            $arProducts = array();
	            $arProductIds = array();
	            foreach($basketItems as $item)
	            {
	            	$arProduct = array();
	            	$arProduct['ID'] = $item->getId();
		            if($bWorldLocation)
		            	$arProduct['NAME'] = self::Translit($item->getField('NAME'));
		            else
			            $arProduct['NAME'] = $item->getField('NAME');
	            	$arProduct['PRICE'] = $item->getPrice()*100;
	            	$arProduct['WEIGHT'] = $item->getWeight();
	            	$arProduct['QUANTITY'] = $item->getQuantity();
	            	$arProducts[] = $arProduct;
	            	$arProductIds[] = $arProduct['ID'];
	            }
	            $markProp = Option::get(self::$MODULE_ID, "RUSSIANPOST_MARK_PROP", "", $site_id);
	            if($markProp != '')
	            {
		            $arProductMark = self::GetProductMarkers($arProductIds, $site_id);
		            if(!empty($arProductMark))
		            {
		            	foreach ($arProducts as $key_prod=>$arProd)
			            {
			            	$mark = $arProductMark[$arProd['ID']];
			            	if($mark != '')
				            {
				            	$arProducts[$key_prod]['CODE'] = $mark;
				            }
			            }
		            }
	            }
	            $arParams['PRODUCTS'] = $arProducts;

	            if($bWithNotification)
	            {
		            $arParams['WITH_NOTIFICATION'] = 'true';
	            }
	            //$arParams['DELIVERY_PRICE'] = $priceDelivery;

                $request = new \Russianpost\Post\Request();
                unset( $_SESSION['russianpost_post_calc']);
                if($bWorldLocation)
                	$request->SendOrderWorld($arParams);
                else
                	$request->SendOrder($arParams);
            }
        }
    }

    public static function AfterDeliveryCalculated($order, &$arUserResult, $request, &$arParams, &$arResult, &$arDeliveryServiceAll, &$arPaySystemServiceAll)
    {

    }

    public static function UserResult(&$arUserResult, $request,&$arParams)
    {
	    unset($_SESSION['russianpost_post_calc']['checked_delivery']);
    	if($arUserResult['DELIVERY_ID'] > 0)
	    {
	    	$_SESSION['russianpost_post_calc']['checked_delivery'] = $arUserResult['DELIVERY_ID'];
	    }
    }

    public static function OneStep($order, &$arUserResult, $request, &$arParams, &$arResult)
    {
        if($_REQUEST['is_ajax_post'] != 'Y' && $_REQUEST["AJAX_CALL"] != 'Y' && !$_REQUEST["ORDER_AJAX"]
	        && $_REQUEST['via_ajax'] != 'Y') {
	        $jqueryOff = Option::get(self::$MODULE_ID, "RUSSIANPOST_JQUERY_OFF");
	        if($jqueryOff == 'N' || $jqueryOff == '')
	        {
		        \CJSCore::Init(array('jquery'));
		        \CJSCore::Init(array('jquery2'));
	        }
            $pathToWidjet = '/bitrix/js/'.self::$MODULE_ID.'/pvzWidjet.js';
            if(file_exists($_SERVER['DOCUMENT_ROOT'].$pathToWidjet)) {
                $GLOBALS['APPLICATION']->AddHeadScript($pathToWidjet);
                $GLOBALS['APPLICATION']->AddHeadScript(self::$url_widget."/map/widget/widget.js");	            
	            \CJSCore::RegisterExt(
		            'langInit',
		            array(
			            "lang" => "/bitrix/js/".self::$MODULE_ID."/lang/ru/pvzWidget.js.php",
		            )
	            );
	            \CJSCore::Init(array("langInit"));
            }
	        $context = \Bitrix\Main\Application::getInstance()->getContext();
	        $siteId = $context->getSite();
            foreach ($arResult['JS_DATA']['DELIVERY'] as $deliveryId => $arDelivery)
            {
            	$deliveryIdTmp = $arDelivery['ID'];
                if($arDelivery['CHECKED'] == 'Y' && $deliveryIdTmp > 0)
                {
                    //$result['order']['DELIVERY'][$deliveryId]['DESCRIPTION'] = 'Elki Palki';
	                $service = Delivery\Services\Manager::getById($deliveryIdTmp);
                    if(strpos($service['CLASS_NAME'], '\Sale\Handlers\Delivery\RussianpostProfile') !== false
	                    && $service['CONFIG']['MAIN']['SERVICE_TYPE'] == self::PROFILE_PICKUP)
                    {
                        $address_prop_id = 0;
	                    $street_prop_id = 0;
	                    $house_prop_id = 0;
	                    $flat_prop_id = 0;
                        $oldAddress = '';
                        $location = '';
                        $bSplitAddress = false;
                        $addressCode = Optionpost::get('address', true, $siteId);
                        $streetCode = Optionpost::get('street', true, $siteId);
                        $houseCode = Optionpost::get('house', true, $siteId);
                        $flatCode = Optionpost::get('flat', true, $siteId);
                        $bClearOldAddress = true;
                        $selectPvz = 'N';
                        if($addressCode == '')
                        {
                        	$bSplitAddress = true;
                        }
	                    //
	                    global $USER;
	                    $oldType = self::GetPostalTypeFromProfile($USER->GetId());
	                    if($oldType != '')
	                    {
	                    	unset($_SESSION['russianpost_post_calc']['clear_address']);
		                    $_SESSION['russianpost_post_calc']['shipment_type'] = $oldType;
		                    $_SESSION['russianpost_post_calc']['old_type'] = 'OLD';
		                    $bClearOldAddress = false;
		                    $selectPvz = 'Y';
	                    }
                        foreach ($arResult['JS_DATA']['ORDER_PROP']['properties'] as $prop_id => $arProp)
                        {
                            if($arProp['IS_LOCATION'] == 'Y')
                            {
                                $location = $arProp['VALUE'][0];
                                if($location == '')
                                {
                                    $location = $arProp['DEFAULT_VALUE'];
                                }
                            }
                            if($bSplitAddress)
                            {
	                            if($arProp['CODE'] == $streetCode)
	                            {
		                            $street_prop_id = $arProp['ID'];
		                            if($bClearOldAddress)
		                            	$arResult['JS_DATA']['ORDER_PROP']['properties'][$prop_id]['VALUE'][0] = '';
	                            }
	                            if($arProp['CODE'] == $houseCode)
	                            {
		                            $house_prop_id = $arProp['ID'];
		                            if($bClearOldAddress)
		                            	$arResult['JS_DATA']['ORDER_PROP']['properties'][$prop_id]['VALUE'][0] = '';
	                            }
	                            if($arProp['CODE'] == $flatCode)
	                            {
		                            $flat_prop_id = $arProp['ID'];
		                            if($bClearOldAddress)
		                            	$arResult['JS_DATA']['ORDER_PROP']['properties'][$prop_id]['VALUE'][0] = '';
	                            }
                            }
                            else
                            {
                            	if($arProp['CODE'] == $addressCode)
	                            {
		                            $address_prop_id = $arProp['ID'];
		                            $oldAddress = $arProp['VALUE'][0];
		                            if($bClearOldAddress)
		                            	$arResult['JS_DATA']['ORDER_PROP']['properties'][$prop_id]['VALUE'][0] = '';
	                            }
                            }
                            /*if($arProp['IS_ADDRESS'] == 'Y')
                            {
                                $address_prop_id = $arProp['ID'];
                                $oldAddress = $arProp['VALUE'][0];
	                            $arResult['JS_DATA']['ORDER_PROP']['properties'][$prop_id]['VALUE'][0] = '';
                            }*/
                        }
                        $guid_id = \Bitrix\Main\Config\Option::get(self::$MODULE_ID, "GUID_ID");

                        $res = \Bitrix\Sale\Location\LocationTable::getList(array(
                            'filter' => array(
                                'CODE' => array($location),
                            ),
                            'select' => array(
                                'EXTERNAL.*',
                                'EXTERNAL.SERVICE.CODE'
                            )
                        ));
                        $strZip = '';
                        if($location != '')
                        {
                            $arZip = array();
                            while($item = $res->fetch())
                            {
                                if($item['SALE_LOCATION_LOCATION_EXTERNAL_SERVICE_CODE'] == 'ZIP_LOWER'
                                    || $item['SALE_LOCATION_LOCATION_EXTERNAL_SERVICE_CODE'] == 'ZIP')
                                {
                                	if(strlen($item['SALE_LOCATION_LOCATION_EXTERNAL_XML_ID']) > 3)
	                                {
		                                $threeDigits = substr($item['SALE_LOCATION_LOCATION_EXTERNAL_XML_ID'], 0, 3);
		                                $arZip[$threeDigits] = "'".$threeDigits."'";
	                                }
                                }
                            }
                            $strZip = implode(", ", $arZip);
                        }

                        $orderWeight = $arResult['JS_DATA']['TOTAL']['ORDER_WEIGHT'];
                        $orderWeightFormated = $arResult['JS_DATA']['TOTAL']['ORDER_WEIGHT_FORMATED'];
                        if(strpos($orderWeightFormated, Loc::getMessage('SALE_DLV_RUSSIANPOST_POST_MEASURE')) !== false)
                        {
                        	$weigthKg = str_replace(Loc::getMessage('SALE_DLV_RUSSIANPOST_POST_MEASURE'), '', $orderWeightFormated);
                        	$weigthKg = trim($weigthKg);
                            $orderWeight = $weigthKg*1000;
                        }
                        #currency convertation
                        $baseCurrency = \Bitrix\Currency\CurrencyManager::getBaseCurrency();
                        $currencyList = \Bitrix\Currency\CurrencyManager::getCurrencyList();
                        $profileCurrency = $service['CURRENCY'];
                        if($baseCurrency != 'RUB' && isset($currencyList['RUB']))
                        {
                            $orderPriceTmp = \CCurrencyRates::ConvertCurrency($arResult['JS_DATA']['TOTAL']['ORDER_PRICE'], $baseCurrency, "RUB");
                        }
                        elseif($profileCurrency!= '' && $profileCurrency != 'RUB' && isset($currencyList['RUB']))
                        {
	                        $orderPriceTmp = \CCurrencyRates::ConvertCurrency($arResult['JS_DATA']['TOTAL']['ORDER_PRICE'], $profileCurrency, "RUB");
                        }
                        else
                        {
                            $orderPriceTmp = $arResult['JS_DATA']['TOTAL']['ORDER_PRICE'];
                        }
                        $orderPrice = $orderPriceTmp * 100;
	                    $openMap = Option::get(self::$MODULE_ID, "RUSSIANPOST_AUTOOPEN_CARD");
	                    $strError = json_encode($_SESSION['russianpost_post_calc']['errors'][$service['CONFIG']['MAIN']['SERVICE_TYPE']], JSON_UNESCAPED_UNICODE);
                        $descr = '<div class="russianpost_link"><input type="hidden" id="russianpost_result_type" name="russianpost_result_type" value="">
<input type="hidden" id="russianpost_result_price" name="russianpost_result_price" value="">
<input type="hidden" id="russianpost_result_address" name="russianpost_result_address" value="">
<input type="hidden" id="russianpost_street_address" name="russianpost_street_address" value="">
<input type="hidden" id="russianpost_house_address" name="russianpost_house_address" value="">
<input type="hidden" id="russianpost_flat_address" name="russianpost_flat_address" value="">
<input type="hidden" id="russianpost_result_zip" name="russianpost_result_zip" value="">
<input type="hidden" id="russianpost_address_prop" name="russianpost_address_prop" value="'.$address_prop_id.'">
<input type="hidden" id="russianpost_street_prop" name="russianpost_street_prop" value="'.$street_prop_id.'">
<input type="hidden" id="russianpost_house_prop" name="russianpost_house_prop" value="'.$house_prop_id.'">
<input type="hidden" id="russianpost_flat_prop" name="russianpost_flat_prop" value="'.$flat_prop_id.'">
<input type="hidden" id="russianpost_set_readonly" name="russianpost_set_readonly" value="Y">
<input type="hidden" id="russianpost_delivery_description" name="russianpost_delivery_description" value="">
<input type="hidden" id="russianpost_select_pvz" name="russianpost_select_pvz" value="'.$selectPvz.'">
<input type="hidden" id="russianpost_open_map" name="russianpost_open_map" value="'.$openMap.'">
<input type="hidden" id="russianpost_full_map" name="russianpost_full_map" value="N">
<input type="hidden" id="russianpost_split_address" name="russianpost_split_address" value="'.$bSplitAddress.'">
<button id="russianpost_btn_openmap" onclick="event.preventDefault(); openMap(\''.$guid_id.'\', '.$orderPrice.','.intval($orderWeight).',['.$strZip.'], \''.$location.'\');" class="btn" style="border-color: #0055A6; background-color:  #0055A6; color: #FFF;">'.Loc::getMessage('SALE_DLV_RUSSIANPOST_POST_LINK').'</button>
<br><span id="russianpost_select_address"></span></div>';
                        $descr .= "<input type='hidden' id='russianpost_error_txt' name='russianpost_error_txt' value='".addslashes($strError)."'>";
                        $arDelivery['DESCRIPTION'] = $descr.(!empty($arDelivery['DESCRIPTION']) ? '<br>' : '').$arDelivery['DESCRIPTION'];
                        $arResult['JS_DATA']['DELIVERY'][$deliveryId] = $arDelivery;
                    }
                    elseif (strpos($service['CLASS_NAME'], '\Sale\Handlers\Delivery\RussianpostProfile') !== false
	                    && ($service['CONFIG']['MAIN']['SERVICE_TYPE'] == self::PROFILE_COURIER || $service['CONFIG']['MAIN']['SERVICE_TYPE'] == self::PROFILE_WORLDPICKUP
	                    || $service['CONFIG']['MAIN']['SERVICE_TYPE'] == self::PROFILE_WORLDCOURIER || $service['CONFIG']['MAIN']['SERVICE_TYPE'] == self::PROFILE_PICKUPNOTE))
                    {
                    	if(isset($_SESSION['russianpost_post_calc']['error_detailed'])
		                    || isset($_SESSION['russianpost_post_calc']['errors'][$service['CONFIG']['MAIN']['SERVICE_TYPE']]))
	                    {
		                    $strErr = json_encode($_SESSION['russianpost_post_calc']['error_detailed'], JSON_UNESCAPED_UNICODE);
		                    $strError = json_encode($_SESSION['russianpost_post_calc']['errors'][$service['CONFIG']['MAIN']['SERVICE_TYPE']], JSON_UNESCAPED_UNICODE);
		                    $strPhp = serialize($_SESSION['russianpost_post_calc']['error_detailed']);
		                    $descr =  "<input type='hidden' id='russianpost_error_tarif' name='russianpost_error_tarif' value='".addslashes($strErr)."'>
		                    <input type='hidden' id='russianpost_error_txt' name='russianpost_error_txt' value='".addslashes($strError)."'>";
		                    unset($_SESSION['russianpost_post_calc']['error_detailed']);
		                    $arDelivery['DESCRIPTION'] = $descr.(!empty($arDelivery['DESCRIPTION']) ? '<br>' : '').$arDelivery['DESCRIPTION'];
		                    $arResult['JS_DATA']['DELIVERY'][$deliveryId] = $arDelivery;
	                    }
                    }
                    break;
                }
            }

        }
        else
        {
        	//for old
        	/*
        	 foreach ($arResult['DELIVERY'] as $deliveryId => $arDelivery)
	    {
		    if($arDelivery['CHECKED'] == 'Y' && $deliveryId > 0)
		    {
			    //$result['order']['DELIVERY'][$deliveryId]['DESCRIPTION'] = 'Elki Palki';
			    $service = Delivery\Services\Manager::getById($deliveryId);
			    if(strpos($service['CLASS_NAME'], '\Sale\Handlers\Delivery\RussianpostProfile') !== false && $service['CONFIG']['MAIN']['SERVICE_TYPE'] == 1)
			    {
				    $address_prop_id = 0;
				    $oldAddress = '';
				    $location = '';
				    foreach ($arResult['JS_DATA']['ORDER_PROP']['properties'] as $prop_id => $arProp)
				    {
					    if($arProp['IS_LOCATION'] == 'Y')
					    {
						    $location = $arProp['VALUE'][0];
						    if($location == '')
						    {
							    $location = $arProp['DEFAULT_VALUE'];
						    }
					    }
					    if($arProp['IS_ADDRESS'] == 'Y')
					    {
						    $address_prop_id = $arProp['ID'];
						    $oldAddress = $arProp['VALUE'][0];

						    if(isset($_REQUEST['russianpost_result_address']) && trim($_REQUEST['russianpost_result_address']) != '')
						    {
							    $arResult['ORDER_PROP']["USER_PROPS_Y"][$address_prop_id]['VALUE'] = trim($_REQUEST['russianpost_result_address']);
							    $arResult['USER_VALS']['ORDER_PROP'][$address_prop_id] = trim($_REQUEST['russianpost_result_address']);
						    }
					    }
					    if($arProp['IS_ZIP'] == 'Y')
					    {
						    $zipId = $arProp['ID'];
						    if(isset($_REQUEST['russianpost_result_zip']) && $_REQUEST['russianpost_result_zip'] != '')
						    {
							    $arResult['ORDER_PROP']["USER_PROPS_Y"][$zipId]['VALUE'] = $_REQUEST['russianpost_result_zip'];
							    $arResult['USER_VALS']['ORDER_PROP'][$zipId] = $_REQUEST['russianpost_result_zip'];
						    }
					    }
				    }
				    $guid_id = \Bitrix\Main\Config\Option::get(self::$MODULE_ID, "GUID_ID");

				    $res = \Bitrix\Sale\Location\LocationTable::getList(array(
					    'filter' => array(
						    'CODE' => array($location),
					    ),
					    'select' => array(
						    'EXTERNAL.*',
						    'EXTERNAL.SERVICE.CODE'
					    )
				    ));
				    $strZip = '';
				    if($location != '')
				    {
					    $arZip = array();
					    while($item = $res->fetch())
					    {
						    if($item['SALE_LOCATION_LOCATION_EXTERNAL_SERVICE_CODE'] == 'ZIP_LOWER'
							    || $item['SALE_LOCATION_LOCATION_EXTERNAL_SERVICE_CODE'] == 'ZIP')
						    {
							    if(strlen($item['SALE_LOCATION_LOCATION_EXTERNAL_XML_ID']) > 3)
							    {
								    $threeDigits = substr($item['SALE_LOCATION_LOCATION_EXTERNAL_XML_ID'], 0, 3);
								    $arZip[$threeDigits] = "'".$threeDigits."'";
							    }
						    }
					    }
					    $strZip = implode(", ", $arZip);
				    }

				    $orderWeight = $arResult['JS_DATA']['TOTAL']['ORDER_WEIGHT'];
				    $orderWeightFormated = $arResult['JS_DATA']['TOTAL']['ORDER_WEIGHT_FORMATED'];
				    if(strpos($orderWeightFormated, Loc::getMessage('SALE_DLV_RUSSIAN_POST_MEASURE')) !== false)
				    {
					    //$orderWeight = $orderWeight*1000;
				    }
				    $orderPrice = $arResult['JS_DATA']['TOTAL']['ORDER_TOTAL_PRICE'] * 100;
				    $descr = '<div class="russianpost_link"><input type="hidden" id="russianpost_result_type" name="russianpost_result_type" value="'.$_SESSION['russianpost_post_calc']['shipment_type'].'">
<input type="hidden" id="russianpost_result_price" name="russianpost_result_price" value="'.$_REQUEST['russianpost_result_price'].'">
<input type="hidden" id="russianpost_result_address" name="russianpost_result_address" value="'.$_REQUEST['russianpost_result_address'].'">
<input type="hidden" id="russianpost_result_zip" name="russianpost_result_zip" value="'.$_REQUEST['russianpost_result_zip'].'">
<input type="hidden" id="russianpost_address_prop" name="russianpost_address_prop" value="'.$address_prop_id.'">
<input type="hidden" id="russianpost_set_readonly" name="russianpost_set_readonly" value="Y">
<input type="hidden" id="russianpost_delivery_description" name="russianpost_delivery_description" value="'.$_REQUEST['russianpost_delivery_description'].'">
<a href="javascript:void(0);" onclick="openMap(\''.$guid_id.'\', '.$orderPrice.','.$orderWeight.',['.$strZip.'], \''.$location.'\');">'.Loc::getMessage('SALE_DLV_RUSSIANPOST_POST_LINK').'</a><br><span id="russianpost_select_address"></span></div>';
				    $arDelivery['DESCRIPTION'] = $descr.(!empty($arDelivery['DESCRIPTION']) ? '<br>' : '').$arDelivery['DESCRIPTION'];
				    $arResult['DELIVERY'][$deliveryId] = $arDelivery;
			    }
			    elseif (strpos($service['CLASS_NAME'], '\Sale\Handlers\Delivery\RussianpostProfile') !== false && $service['CONFIG']['MAIN']['SERVICE_TYPE'] == 2)
			    {
				    if(isset($_SESSION['russianpost_post_calc']['error_detailed']))
				    {
					    $strErr = json_encode($_SESSION['russianpost_post_calc']['error_detailed'], JSON_UNESCAPED_UNICODE);
					    $strPhp = serialize($_SESSION['russianpost_post_calc']['error_detailed']);
					    $descr =  "<input type='hidden' id='russianpost_error_tarif' name='russianpost_error_tarif' value='".addslashes($strErr)."'>";
					    unset($_SESSION['russianpost_post_calc']['error_detailed']);
					    $arDelivery['DESCRIPTION'] = $descr.(!empty($arDelivery['DESCRIPTION']) ? '<br>' : '').$arDelivery['DESCRIPTION'];
					    $arResult['DELIVERY'][$deliveryId] = $arDelivery;
				    }
			    }
			    break;
		    }
	    }
        	*/
        }
    }

    public static function AjaxAnswer(&$result)
    {
	    $context = \Bitrix\Main\Application::getInstance()->getContext();
	    $siteId = $context->getSite();
	    $personTypeId = $_REQUEST['personTypeId'];
	    foreach ($result['compilation']['order']['DELIVERIES']  as $deliveryId => $arDelivery)
        //foreach ($result['order']['DELIVERY'] as $deliveryId => $arDelivery)
        {
        	$deliveryIdTmp = $arDelivery['ID'];
            if($arDelivery['CHECKED'] == 'Y' && $deliveryIdTmp > 0)
            {
                //$result['order']['DELIVERY'][$deliveryId]['DESCRIPTION'] = 'Elki Palki';
                $service = Delivery\Services\Manager::getById($deliveryIdTmp);
                if(strpos($service['CLASS_NAME'], '\Sale\Handlers\Delivery\RussianpostProfile') !== false
	                && $service['CONFIG']['MAIN']['SERVICE_TYPE'] == self::PROFILE_PICKUP)
                {
	                $result['compilation']['order']['ZIP_PROPERTY_CHANGED'] = 'Y';
	                $_SESSION['russianpost_post_calc']['checked_delivery'] = $deliveryIdTmp;
	                $address_prop_id = 0;
	                $street_prop_id = 0;
	                $house_prop_id = 0;
	                $flat_prop_id = 0;
	                $oldAddress = '';
	                $location = '';
	                $bSplitAddress = false;
	                $addressCode = Optionpost::get('address', true, $siteId);
	                $streetCode = Optionpost::get('street', true, $siteId);
	                $houseCode = Optionpost::get('house', true, $siteId);
	                $flatCode = Optionpost::get('flat', true, $siteId);
	                $zipCode = Optionpost::get('zip', true, $siteId);
	                if($addressCode == '')
	                {
		                $bSplitAddress = true;
	                }
	                if(LANG_CHARSET == 'windows-1251')
	                {
		                $_REQUEST['russianpost']['russianpost_result_address'] = iconv("UTF-8", "WINDOWS-1251", $_REQUEST['russianpost']['russianpost_result_address']);
		                $_REQUEST['russianpost']['russianpost_delivery_description'] = iconv("UTF-8", "WINDOWS-1251", $_REQUEST['russianpost']['russianpost_delivery_description']);
		                if($bSplitAddress)
		                {
			                $_REQUEST['russianpost']['russianpost_street_address'] = iconv("UTF-8", "WINDOWS-1251", $_REQUEST['russianpost']['russianpost_street_address']);
		                }
	                }
                    foreach ($result['compilation']['order']['PROPERTIES']['PROPERTIES'] as $prop_id => $arProp)
                    {
                        if($arProp['IS_LOCATION'] == 'Y' && $arProp['PERSON_TYPE_ID'] == $personTypeId)
                        {
                            //$location = $arProp['VALUE'][0];
	                        $location = $arProp['LOCATION']['CODE'];
                            if($location == '')
                            {
                               $location = $arProp['DEFAULT_VALUE'];
                            }
                        }
                        if($arProp['CODE'] == $zipCode && $arProp['PERSON_TYPE_ID'] == $personTypeId)
                        {
                            if(isset($_REQUEST['russianpost']['russianpost_result_zip']) && $_REQUEST['russianpost']['russianpost_result_zip'] != '')
                            {
	                            $result['compilation']['order']['PROPERTIES']['PROPERTIES'][$prop_id]['CURRENT_VALUE'] = $_REQUEST['russianpost']['russianpost_result_zip'];
	                            $result['compilation']['order']['ZIP_PROPERTY_CHANGED'] = 'Y';
                            }
                        }
                        if($bSplitAddress)
                        {
	                        if($arProp['CODE'] == $streetCode && $arProp['PERSON_TYPE_ID'] == $personTypeId)
	                        {
		                        $street_prop_id = $arProp['ID'];
		                        if($_SESSION['russianpost_post_calc']['clear_address'] == 'Y')
		                        {
			                        //$result['compilation']['order']['PROPERTIES']['PROPERTIES'][$prop_id]['CURRENT_VALUE'] = '';
			                        //unset($_SESSION['russianpost_post_calc']['clear_address']);
		                        }
		                        if(isset($_REQUEST['russianpost']['russianpost_street_address']) && trim($_REQUEST['russianpost']['russianpost_street_address']) != '')
		                        {
			                        //$result['compilation']['order']['PROPERTIES']['PROPERTIES'][$prop_id]['CURRENT_VALUE'] = trim($_REQUEST['russianpost']['russianpost_street_address']);
		                        }
	                        }
	                        if($arProp['CODE'] == $houseCode && $arProp['PERSON_TYPE_ID'] == $personTypeId)
	                        {
		                        $house_prop_id = $arProp['ID'];
		                        if($_SESSION['russianpost_post_calc']['clear_address'] == 'Y')
		                        {
			                        //$result['compilation']['order']['PROPERTIES']['PROPERTIES'][$prop_id]['CURRENT_VALUE'] = '';
			                        //unset($_SESSION['russianpost_post_calc']['clear_address']);
		                        }
		                        if(isset($_REQUEST['russianpost']['russianpost_house_address']) && trim($_REQUEST['russianpost']['russianpost_house_address']) != '')
		                        {
			                       // $result['compilation']['order']['PROPERTIES']['PROPERTIES'][$prop_id]['CURRENT_VALUE'] = trim($_REQUEST['russianpost']['russianpost_house_address']);
		                        }
	                        }
	                        if($arProp['CODE'] == $flatCode && $arProp['PERSON_TYPE_ID'] == $personTypeId)
	                        {
		                        $flat_prop_id = $arProp['ID'];
		                        if($_SESSION['russianpost_post_calc']['clear_address'] == 'Y')
		                        {
			                       // $result['compilation']['order']['PROPERTIES']['PROPERTIES'][$prop_id]['CURRENT_VALUE'] = '';
			                        //unset($_SESSION['russianpost_post_calc']['clear_address']);
		                        }
		                        if(isset($_REQUEST['russianpost']['russianpost_flat_address']) && trim($_REQUEST['russianpost']['russianpost_flat_address']) != '')
		                        {
			                        //$result['compilation']['order']['PROPERTIES']['PROPERTIES'][$prop_id]['CURRENT_VALUE'] = trim($_REQUEST['russianpost']['russianpost_flat_address']);
		                        }
	                        }
                        }
                        else
                        {
	                        if($arProp['CODE'] == $addressCode && $arProp['PERSON_TYPE_ID'] == $personTypeId)
	                        {
		                        $address_prop_id = $arProp['ID'];
		                        if($_SESSION['russianpost_post_calc']['clear_address'] == 'Y')
		                        {
			                        //$result['compilation']['order']['PROPERTIES']['PROPERTIES'][$prop_id]['CURRENT_VALUE'] = '';
			                        unset($_SESSION['russianpost_post_calc']['clear_address']);
		                        }
		                        if(isset($_REQUEST['russianpost']['russianpost_result_address']) && trim($_REQUEST['russianpost']['russianpost_result_address']) != '')
		                        {
			                        /*foreach ($result['properties'] as $key_prop=>$arPropTmp)
			                        {
				                        if($arPropTmp['ID'] == $prop_id)
				                        {
					                        $result['properties'][$key_prop]['VALUE'][0] = trim($_REQUEST['order']['russianpost_result_address']);
				                        }
			                        }*/
			                        $result['compilation']['order']['PROPERTIES']['PROPERTIES'][$prop_id]['CURRENT_VALUE'] = trim($_REQUEST['russianpost']['russianpost_result_address']);
		                        }
	                        }
                        }

                    }
	                if($_SESSION['russianpost_post_calc']['clear_address'] == 'Y')
	                {
		                unset($_SESSION['russianpost_post_calc']['clear_address']);
	                }


                    $guid_id = \Bitrix\Main\Config\Option::get(self::$MODULE_ID, "GUID_ID");

                    $res = \Bitrix\Sale\Location\LocationTable::getList(array(
                        'filter' => array(
                            'CODE' => array($location),
                        ),
                        'select' => array(
                            'EXTERNAL.*',
                            'EXTERNAL.SERVICE.CODE'
                        )
                    ));
                    $strZip = '';
                    if($location != '')
                    {
                        $arZip = array();
                        while($item = $res->fetch())
                        {
                            if($item['SALE_LOCATION_LOCATION_EXTERNAL_SERVICE_CODE'] == 'ZIP_LOWER'
                                || $item['SALE_LOCATION_LOCATION_EXTERNAL_SERVICE_CODE'] == 'ZIP')
                            {
	                            if(strlen($item['SALE_LOCATION_LOCATION_EXTERNAL_XML_ID']) > 3)
	                            {
		                            $threeDigits = substr($item['SALE_LOCATION_LOCATION_EXTERNAL_XML_ID'], 0, 3);
		                            $arZip[$threeDigits] = "'".$threeDigits."'";
	                            }
                            }
                        }
                        $strZip = implode(", ", $arZip);
                    }

                    //$orderWeight = $result['order']['TOTAL']['ORDER_WEIGHT'];
	                $orderWeight = 0;
	                $orderPrice = 0;
	                foreach($result['compilation']['items'] as $arItem)
	                {
		                $orderWeight += $arItem['QUANTITY']*$arItem['WEIGHT'];
		                $orderPrice += $arItem['QUANTITY']*$arItem['PRICE'];
	                }
                    /*$orderWeightFormated = $result['order']['TOTAL']['ORDER_WEIGHT'];
                    if(strpos($orderWeightFormated,Loc::getMessage('SALE_DLV_RUSSIANPOST_POST_MEASURE')) !== false)
                    {
	                    $weigthKg = str_replace(Loc::getMessage('SALE_DLV_RUSSIANPOST_POST_MEASURE'), '', $orderWeightFormated);
	                    $weigthKg = trim($weigthKg);
	                    $orderWeight = $weigthKg*1000;
                    }*/
                    #currency convertation
                    $baseCurrency = \Bitrix\Currency\CurrencyManager::getBaseCurrency();
                    $currencyList = \Bitrix\Currency\CurrencyManager::getCurrencyList();
                    $profileCurrency = $service['CURRENCY'];
                    if($baseCurrency != 'RUB' && isset($currencyList['RUB']))
                    {
                        $orderPriceTmp = \CCurrencyRates::ConvertCurrency($result['order']['TOTAL']['ORDER_PRICE'], $baseCurrency, "RUB");
                    }
                    elseif($profileCurrency!='' && $profileCurrency != 'RUB' && isset($currencyList['RUB']))
                    {
	                    $orderPriceTmp = \CCurrencyRates::ConvertCurrency($result['order']['TOTAL']['ORDER_PRICE'], $profileCurrency, "RUB");
                    }
                    else
                    {
                        $orderPriceTmp = $result['order']['TOTAL']['ORDER_PRICE'];
                    }
                    $orderPrice = $orderPriceTmp * 100;
                    $startPos = strpos($arDelivery['DESCRIPTION'], '<div class="russianpost_link">');
                    if($startPos !== false)
                    {
                        $endPos = strpos($arDelivery['DESCRIPTION'], '</div>');
                        $oldDescr = substr($arDelivery['DESCRIPTION'], ($endPos+6));
                        if(strpos($oldDescr,'<br>') !== false)
                        {
                            $oldDescr = substr($oldDescr, 4);
                        }
                        $arDelivery['DESCRIPTION'] = $oldDescr;
                    }
                    if(isset($_REQUEST['russianpost']['russianpost_open_map']))
                    	$openMap = $_REQUEST['russianpost']['russianpost_open_map'];
                    else
                    	$openMap = Option::get(self::$MODULE_ID, "RUSSIANPOST_AUTOOPEN_CARD");
	                $strError = json_encode($_SESSION['russianpost_post_calc']['errors'][$service['CONFIG']['MAIN']['SERVICE_TYPE']], JSON_UNESCAPED_UNICODE);
                    $descr = '<div class="russianpost_link">
<input type="hidden" id="russianpost_result_type" class = "russianpost_field" name="russianpost_result_type" value="'.$_SESSION['russianpost_post_calc']['shipment_type'].'">
<input type="hidden" id="russianpost_result_price" class = "russianpost_field" name="russianpost_result_price" value="'.$_REQUEST['russianpost']['russianpost_result_price'].'">
<input type="hidden" id="russianpost_result_address" class = "russianpost_field" name="russianpost_result_address" value="'.$_REQUEST['russianpost']['russianpost_result_address'].'">
<input type="hidden" id="russianpost_street_address" class = "russianpost_field" name="russianpost_street_address" value="'.$_REQUEST['russianpost']['russianpost_street_address'].'">
<input type="hidden" id="russianpost_house_address" class = "russianpost_field" name="russianpost_house_address" value="'.$_REQUEST['russianpost']['russianpost_house_address'].'">
<input type="hidden" id="russianpost_flat_address" class = "russianpost_field" name="russianpost_flat_address" value="'.$_REQUEST['russianpost']['russianpost_flat_address'].'">
<input type="hidden" id="russianpost_result_zip" class = "russianpost_field" name="russianpost_result_zip" value="'.$_REQUEST['russianpost']['russianpost_result_zip'].'">
<input type="hidden" id="russianpost_address_prop" class = "russianpost_field" name="russianpost_address_prop" value="'.$address_prop_id.'">
<input type="hidden" id="russianpost_street_prop" class = "russianpost_field" name="russianpost_street_prop" value="'.$street_prop_id.'">
<input type="hidden" id="russianpost_house_prop" class = "russianpost_field" name="russianpost_house_prop" value="'.$house_prop_id.'">
<input type="hidden" id="russianpost_flat_prop" class = "russianpost_field" name="russianpost_flat_prop" value="'.$flat_prop_id.'">
<input type="hidden" id="russianpost_set_readonly" class = "russianpost_field" name="russianpost_set_readonly" value="Y">
<input type="hidden" id="russianpost_delivery_description" class = "russianpost_field" name="russianpost_delivery_description" value="'.$_REQUEST['russianpost']['russianpost_delivery_description'].'">
<input type="hidden" id="russianpost_select_pvz" class = "russianpost_field" name="russianpost_select_pvz" value="'.$_REQUEST['russianpost']['russianpost_select_pvz'].'">
<input type="hidden" id="russianpost_open_map" class = "russianpost_field" name="russianpost_open_map" value="'.$openMap.'">
<input type="hidden" id="russianpost_full_map" class = "russianpost_field" name="russianpost_full_map" value="'.(isset($_REQUEST['russianpost']['russianpost_full_map']) ? $_REQUEST['russianpost']['russianpost_full_map'] : 'N').'">
<input type="hidden" id="russianpost_split_address" class = "russianpost_field" name="russianpost_split_address" value="'.$bSplitAddress.'">
<button id="russianpost_btn_openmap" onclick="event.preventDefault(); openMap(\''.$guid_id.'\', '.$orderPrice.','.intval($orderWeight).',['.$strZip.'], \''.$location.'\');" class="btn" style="border-color: #0055A6; background-color:  #0055A6; color: #FFF;">'.Loc::getMessage('SALE_DLV_RUSSIANPOST_POST_LINK').'</button>
<br><span id="russianpost_select_address">'.$_REQUEST['russianpost']['russianpost_result_address'].'</span></div>';
                    $descr .= "<input type='hidden' id='russianpost_error_txt' name='russianpost_error_txt' value='".addslashes($strError)."'>";
              $arDelivery['DESCRIPTION'] = $descr.(!empty($arDelivery['DESCRIPTION']) ? '<br>' : '').$arDelivery['DESCRIPTION'];
	                $result['compilation']['order']['DELIVERIES'][$deliveryId] = $arDelivery;
                }
                elseif (strpos($service['CLASS_NAME'], '\Sale\Handlers\Delivery\RussianpostProfile') !== false
	                && ($service['CONFIG']['MAIN']['SERVICE_TYPE'] == self::PROFILE_COURIER || $service['CONFIG']['MAIN']['SERVICE_TYPE'] == self::PROFILE_WORLDPICKUP
		                || $service['CONFIG']['MAIN']['SERVICE_TYPE'] == self::PROFILE_WORLDCOURIER || $service['CONFIG']['MAIN']['SERVICE_TYPE'] == self::PROFILE_PICKUPNOTE))
                {
	                $_SESSION['russianpost_post_calc']['checked_delivery'] = $deliveryIdTmp;
	                $result['compilation']['order']['ZIP_PROPERTY_CHANGED'] = 'Y';
	                if(isset($_SESSION['russianpost_post_calc']['error_detailed'])
		                || isset($_SESSION['russianpost_post_calc']['errors'][$service['CONFIG']['MAIN']['SERVICE_TYPE']]))
	                {
		                $strErr = json_encode($_SESSION['russianpost_post_calc']['error_detailed'], JSON_UNESCAPED_UNICODE);
		                $strError = json_encode($_SESSION['russianpost_post_calc']['errors'][$service['CONFIG']['MAIN']['SERVICE_TYPE']], JSON_UNESCAPED_UNICODE);
		                $strPhp = serialize($_SESSION['russianpost_post_calc']['error_detailed']);
		                $descr =  "<input type='hidden' id='russianpost_error_tarif' name='russianpost_error_tarif' value='".addslashes($strErr)."'>
		                    <input type='hidden' id='russianpost_error_txt' name='russianpost_error_txt' value='".addslashes($strError)."'>";
		                unset($_SESSION['russianpost_post_calc']['error_detailed']);
		                $arDelivery['DESCRIPTION'] = $descr.(!empty($arDelivery['DESCRIPTION']) ? '<br>' : '').$arDelivery['DESCRIPTION'];
		                $result['compilation']['order']['DELIVERIES'][$deliveryId] = $arDelivery;
	                }
                }
                break;
            }
        }
    }

	#декодируем пост запрос
	protected function extractPostData($postData)
	{
		global $APPLICATION;
		$arResult = array();

		//if ($this->communicationFormat == self::JSON) {
		$arResult = json_decode($postData, true);
		//}

		/*if (strtolower(SITE_CHARSET) != 'utf-8')
			$arResult = $APPLICATION->ConvertCharsetArray($arResult, 'utf-8', SITE_CHARSET);*/

		return $arResult;
	}

	#формируем ответ - ошибку
	protected function processError($status = "", $message = "")
	{
		if ($status != "")
			\CHTTP::SetStatus($status);

		return array("error" => $message);
	}

	#подготавливаем ответ
	protected function prepareResult($arData)
	{
		if (!is_array($arData)) {
			return "";
		}

		global $APPLICATION;
		$result = array();

		/*if (strtolower(SITE_CHARSET) != 'utf-8')
			$arData = $APPLICATION->ConvertCharsetArray($arData, SITE_CHARSET, 'utf-8');*/

		//if ($this->communicationFormat == self::JSON) {
		header('Content-Type: application/json');
		$result = json_encode($arData);
		//}

		return $result;
	}

	public static function SaveTrackNumber($postData)
	{
		$arData = self::extractPostData($postData);
		$guid_id_db = Option::get(self::$MODULE_ID, "GUID_ID");
		$guid_key_db = Option::get(self::$MODULE_ID, "GUID_KEY");

		if($guid_id_db != $arData['guid_id'] && $guid_key_db != $arData['guid_key'])
		{
			$arResult = self::processError(self::ERROR_STATUS_500, Loc::getMessage('SALE_DLV_RUSSIANPOST_ERROR_GUID'));
		}
		else
		{
			$orderId = $arData['order_id'];
			if($orderId > 0)
			{
				$order = Sale\Order::load($orderId);
				if($order)
				{
					$shipmentCollection = $order->getShipmentCollection();
					$bSaveOrder = false;
					foreach ($shipmentCollection as $shipment)
					{
						$deliveryId = $shipment->getDeliveryId();
						if($deliveryId > 0)
						{
							$service = Delivery\Services\Manager::getById($deliveryId);
							if(strpos($service['CLASS_NAME'], '\Sale\Handlers\Delivery\RussianpostProfile') !== false)
							{
								if (!$shipment->isEmpty())
								{
									$r = $shipment->setField('TRACKING_NUMBER', $arData['barcode']);;
								}

								$bSaveOrder = true;
							}
						}
					}
					if($bSaveOrder)
					{
						$result = $order->save();
						$arResult['barcode_is_updated'] = Loc::getMessage('SALE_DLV_RUSSIANPOST_OK_SAVE_BARCODE');
					}
				}
				else
				{
					$arResult = self::processError(self::ERROR_STATUS_500, Loc::getMessage('SALE_DLV_RUSSIANPOST_ERROR_ORDER'));
				}
			}
			else
			{
				$arResult = self::processError(self::ERROR_STATUS_500, Loc::getMessage('SALE_DLV_RUSSIANPOST_ERROR_ORDERID'));
			}
		}
		$arPreparedResult = self::prepareResult($arResult);
		return $arPreparedResult;

	}

	public static function SetDeducted($postData)
	{
		$arData = self::extractPostData($postData);
		$guid_id_db = Option::get(self::$MODULE_ID, "GUID_ID");
		$guid_key_db = Option::get(self::$MODULE_ID, "GUID_KEY");

		if($guid_id_db != $arData['guid_id'] && $guid_key_db != $arData['guid_key'])
		{
			$arResult = self::processError(self::ERROR_STATUS_500, Loc::getMessage('SALE_DLV_RUSSIANPOST_ERROR_GUID'));
		}
		else
		{
			$orderId = $arData['order_id'];
			if($orderId > 0)
			{
				$order = Sale\Order::load($orderId);
				if($order)
				{
					$shipmentCollection = $order->getShipmentCollection();
					$bSaveOrder = false;
					foreach ($shipmentCollection as $shipment)
					{
						$deliveryId = $shipment->getDeliveryId();
						if($deliveryId > 0)
						{
							$service = Delivery\Services\Manager::getById($deliveryId);
							if(strpos($service['CLASS_NAME'], '\Sale\Handlers\Delivery\RussianpostProfile') !== false)
							{
								if (!$shipment->isEmpty() && $arData['status'] == 'dispatched')
								{
									$r = $shipment->setField("DEDUCTED", "Y");
								}

								$bSaveOrder = true;
							}
						}
					}
					if($bSaveOrder)
					{
						$result = $order->save();
						$arResult['status_is_set'] = Loc::getMessage('SALE_DLV_RUSSIANPOST_OK_SAVE_BARCODE');
					}
				}
				else
				{
					$arResult = self::processError(self::ERROR_STATUS_500, Loc::getMessage('SALE_DLV_RUSSIANPOST_ERROR_ORDER'));
				}
			}
			else
			{
				$arResult = self::processError(self::ERROR_STATUS_500, Loc::getMessage('SALE_DLV_RUSSIANPOST_ERROR_ORDERID'));
			}
		}
		$arPreparedResult = self::prepareResult($arResult);
		return $arPreparedResult;

	}

    function AdminButtons(&$items){
        $guid_id = Option::get(self::$MODULE_ID, "GUID_ID");
        $guid_key = Option::get(self::$MODULE_ID, "GUID_KEY");
        $link = "https://cms.pochta.ru/authorization/cms?guidId=".$guid_id."&guidKey=".$guid_key;
        if($guid_id != '' && $guid_key != '')
        {
            if ($_SERVER['REQUEST_METHOD']=='GET' && $GLOBALS['APPLICATION']->GetCurPage()=='/bitrix/admin/sale_order_edit.php' && $_REQUEST['ID']>0)
            {
                $items[] = array(
                    "TEXT"=>Loc::getMessage('SALE_DLV_RUSSIANPOST_LINK_CABINET'),
                    "LINK"=>$link,
                    "TITLE"=>Loc::getMessage('SALE_DLV_RUSSIANPOST_LINK_CABINET'),
                    "ICON"=>"adm-btn",
                    "LINK_PARAM"=>'target="_blank"',
                );
            }
            if ($_SERVER['REQUEST_METHOD']=='GET' && $GLOBALS['APPLICATION']->GetCurPage()=='/bitrix/admin/sale_order_view.php' && $_REQUEST['ID']>0)
            {
                $items[] = array(
                    "TEXT"=>Loc::getMessage('SALE_DLV_RUSSIANPOST_LINK_CABINET'),
                    "LINK"=>$link,
                    "TITLE"=>Loc::getMessage('SALE_DLV_RUSSIANPOST_LINK_CABINET'),
                    "ICON"=>"adm-btn",
                    "LINK_PARAM"=>'target="_blank"',
                );
            }
            if ($_SERVER['REQUEST_METHOD']=='GET' && $GLOBALS['APPLICATION']->GetCurPage()=='/bitrix/admin/sale_order.php')
            {
                $items[] = array(
                    "TEXT"=>Loc::getMessage('SALE_DLV_RUSSIANPOST_LINK_CABINET'),
                    "LINK"=>$link,
                    "TITLE"=>Loc::getMessage('SALE_DLV_RUSSIANPOST_LINK_CABINET'),
                    "ICON"=>"adm-btn",
                    "LINK_PARAM"=>'target="_blank"',
                );
            }
        }
    }

    public static function CreateOrderProps()
    {
	    Loader::includeModule("sale");
	    $db_ptype = \CSalePersonType::GetList(Array("SORT" => "ASC"));
	    $arPersonalTypeId = array();

	    $arFields = array(
		    //"PERSON_TYPE_ID" => 2,
		    "NAME" => Loc::getMessage('SALE_DLV_RUSSIANPOST_TYPE_DLV'),
		    "TYPE" => "TEXT",
		    "REQUIED" => "N",
		    "DEFAULT_VALUE" => "",
		    "SORT" => 100,
		    "CODE" => "RUSSIANPOST_TYPEDLV",
		    "USER_PROPS" => "Y",
		    "IS_LOCATION" => "N",
		    "IS_LOCATION4TAX" => "N",
		    "PROPS_GROUP_ID" => 2,
		    "SIZE1" => 0,
		    "SIZE2" => 0,
		    "DESCRIPTION" => "",
		    "IS_EMAIL" => "N",
		    "IS_PROFILE_NAME" => "N",
		    "IS_PAYER" => "N",
		    "UTIL" => "Y"
	    );

	    while ($ptype = $db_ptype->Fetch())
	    {
	    	$arPersonalTypeId[] = $ptype['ID'];
	    }
	    foreach ($arPersonalTypeId as $pTypeId)
	    {
		    $db_props = \CSaleOrderProps::GetList(
			    array("SORT" => "ASC"),
			    array(
				    "PERSON_TYPE_ID" => $pTypeId,
				    "CODE" => "RUSSIANPOST_TYPEDLV",
			    ),
			    false,
			    false,
			    array()
		    );
		    if($props = $db_props->Fetch())
		    {

		    }
		    else
		    {
		    	$arFields['PERSON_TYPE_ID'] = $pTypeId;
			    $ID = \CSaleOrderProps::Add($arFields);
		    }


	    }

    }

    public static function ChangeOrderStatus(\Bitrix\Main\Event $event)
    {
	    $order = $event->getParameter("ENTITY");
	    $arOrderVals = $order->getFields()->getValues();
	    $siteId = $order->getSiteId();
	    $orderSendStatus = Option::get(self::$MODULE_ID, "RUSSIANPOST_ORDER_PAID_STATUS", "", $siteId);
	    if($orderSendStatus != '' && $arOrderVals['STATUS_ID'] == $orderSendStatus)
	    {
		    $bSendToPost = false;
		    $bAddLocationToAddress = false;
		    $deliveryIds = $order->getDeliverySystemId();
		    foreach($deliveryIds as $deliveryId)
		    {
			    if($deliveryId > 0)
			    {
				    $service = Delivery\Services\Manager::getById($deliveryId);
				    if(strpos($service['CLASS_NAME'], '\Sale\Handlers\Delivery\RussianpostProfile') !== false)
				    {
					    $bSendToPost = true;
					    if($service['CONFIG']['MAIN']['SERVICE_TYPE'] == self::PROFILE_COURIER)
					    {
						    $bAddLocationToAddress = true;
					    }
					    break;
				    }
			    }
		    }
		    if($bSendToPost)
		    {

			    $orderId = $order->getId();

			    $propertyCollection = $order->getPropertyCollection();
			    //$namePropValue  = $propertyCollection->getPayerName()->getValue();
			    $locProp = $propertyCollection->getDeliveryLocation();
			    if($locProp)
				    $locPropValue   = $locProp->getValue();

			    //$profNameProp = $propertyCollection->getProfileName();
			    //if($profNameProp)
			    //	$profNamePropVal = $profNameProp->getValue();
			    $profNamePropVal = trim(self::getContact($propertyCollection, $siteId));
			    $zipCode = Optionpost::get('zip', true, $siteId);
			    //$zipProp = $propertyCollection->getDeliveryLocationZip();
			    $zipProp = self::getPropertyFromCollectionByCode($propertyCollection, $zipCode);
			    if($zipProp)
				    $zipPropValue   = $zipProp->getValue();
			    $phoneCode = Optionpost::get('phone', true, $siteId);
			    //$phoneProp = $propertyCollection->getPhone();
			    $phoneProp = self::getPropertyFromCollectionByCode($propertyCollection, $phoneCode);
			    if($phoneProp)
				    $phonePropValue = $phoneProp->getValue();
			    $addressCode = Optionpost::get('address', true, $siteId);
			    if(!$addressCode)
				    $bAddLocationToAddress = true;
			    $addrPropValue = self::getAddress($propertyCollection, $siteId);
			    $shipmentType = '';
			    foreach ($propertyCollection as $propItem)
			    {
				    $arProp  = $propItem->getProperty();
				    if($arProp['CODE'] == 'RUSSIANPOST_TYPEDLV')
				    {
					    $shipmentType = $propItem->getValue();
				    }
			    }
			    if($locPropValue)
			    {
				    $item = \Bitrix\Sale\Location\LocationTable::getByCode($locPropValue, array(
					    'filter' => array('=NAME.LANGUAGE_ID' => LANGUAGE_ID),
					    'select' => array('*', 'NAME_RU' => 'NAME.NAME')
				    ))->fetch();
			    }

			    $basket = $order->getBasket();
			    $weight = $basket->getWeight();

			    if(trim($shipmentType) != '')
			    {
				    $arParams['ORDER_ID'] = $orderId;
				    $arParams['WEIGHT'] = intval($weight);
				    $arParams['ZIP'] = $zipPropValue;
				    if($bAddLocationToAddress)
					    $address = $item['NAME_RU'].' '.$addrPropValue;
				    else
					    $address = $addrPropValue;
				    $arParams['ADDRESS'] = $address;
				    $arParams['PHONE'] = $phonePropValue;
				    $priceOrder = $order->getPrice();
				    $priceDelivery = $order->getDeliveryPrice();
				    $arParams['PRICE'] = ($priceOrder - $priceDelivery)*100;
				    $arParams['SHIPMENT_TYPE'] = $shipmentType;
				    $arParams['NAME'] = $profNamePropVal;
				    $arParams['DELIVERY_PRICE'] = ($priceDelivery * 100);
				    $arParams['FINANCIAL_STATUS'] = 'paid';
				    //$arParams['DELIVERY_PRICE'] = $priceDelivery;

				    $request = new \Russianpost\Post\Request();
				    unset( $_SESSION['russianpost_post_calc']);
				    $request->SendOrder($arParams);
			    }
		    }
	    }
    }

    public static function GetCountryByCode($code)
    {
    	$arResult = array();
	    $item = \Bitrix\Sale\Location\LocationTable::getByCode($code, array(
		    'filter' => array('=NAME.LANGUAGE_ID' => 'ru'),
		    'select' => array('*', 'NAME_RU' => 'NAME.NAME', 'TYPE_NAME'=>'TYPE.CODE')
	    ))->fetch();
	    if($item['TYPE_NAME'] != 'COUNTRY')
	    {
		    $res = \Bitrix\Sale\Location\LocationTable::getList(array(
			    'filter' => array(
				    '=CODE' => $code,
				    '=PARENTS.NAME.LANGUAGE_ID' => 'ru',
				    '=PARENTS.TYPE.NAME.LANGUAGE_ID' => 'ru',
			    ),
			    'select' => array(
				    'I_ID' => 'PARENTS.ID',
				    'I_NAME_RU' => 'PARENTS.NAME.NAME',
				    'I_TYPE_CODE' => 'PARENTS.TYPE.CODE',
				    'I_TYPE_NAME_RU' => 'PARENTS.TYPE.NAME.NAME',
				    'I_CODE' => 'PARENTS.CODE',
			    ),
			    'order' => array(
				    'PARENTS.DEPTH_LEVEL' => 'asc'
			    )
		    ));
		    while($itemC = $res->fetch())
		    {
			    if($itemC['I_TYPE_CODE'] == 'COUNTRY')
			    {
				    $arResult['NAME'] = $itemC['I_NAME_RU'];
				    $arResult['CODE'] = $itemC['I_CODE'];
				    break;
			    }
		    }
	    }
	    else
	    {
	    	$arResult['NAME'] = $item['NAME_RU'];
	    	$arResult['CODE'] = $item['CODE'];
	    }
	    return $arResult;
    }

    public static function Translit($text)
    {
    	$arParams = array('max_len'=> 500, 'change_case'=>false, 'replace_space'=>" ", "replace_other"=>"-");
	    $result = \Cutil::translit($text,"ru",$arParams);

	    return $result;
    }

    public static function OnEpilog()
    {
	    $guid_id = Option::get(self::$MODULE_ID, "GUID_ID");
	    $guid_key = Option::get(self::$MODULE_ID, "GUID_KEY");
	    $link = "https://cms.pochta.ru/authorization/cms?guidId=".$guid_id."&guidKey=".$guid_key;
	    if($guid_id != '' && $guid_key != '')
	    {
		    $workMode = false;
		    $check = ($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : $_SERVER['REQUEST_URI'];

		    $workType = false;
		    global $APPLICATION;
		    $dir = $APPLICATION->GetCurDir();

		    $b24path = array (
			    'ORDER' => '/shop/orders/details/',
			    'SHIPMENT' => '/shop/orders/shipment/details/',
		    );

		    if (strpos($dir, $b24path['ORDER']) !== false)
		    {
			    $workMode = 'order';
			    $workType = 'b24';
		    }
		    elseif (strpos($dir, $b24path['SHIPMENT']) !== false)
		    {
			    $workMode = 'shipment';
			    $workType = 'b24';
		    }

		    if ($workType == 'b24')
		    {
			    \Bitrix\Main\UI\Extension::load('ui.buttons');
			    \Bitrix\Main\UI\Extension::load('ui.buttons.icons');

			    $containerHTML = '<div class="pagetitle-container" id="russianpost_b24_btn"><a href="'.$link.'" target="_blank" class="ui-btn ui-btn-light-border ui-btn-icon-edit" style="margin-left:12px;">'.Loc::getMessage('SALE_DLV_RUSSIANPOST_LINK_CABINET').'</a></div>';
			    $APPLICATION->AddViewContent('inside_pagetitle', $containerHTML, 20000);

		    }
	    }
    }

    public static function getPropertyFromCollectionByCode(\Bitrix\Sale\PropertyValueCollection $propertyCollection, $code)
    {
    	$result = false;
	    foreach ($propertyCollection as $propItem)
	    {
		    $arProp  = $propItem->getProperty();
		    if($arProp['CODE'] == $code)
		    {
			    $result = $propItem;
			    break;
		    }
	    }
	    return $result;
    }

    public static function getAddress(\Bitrix\Sale\PropertyValueCollection $propertyCollection, $siteId)
    {
    	$addressStr = false;
    	$addressCode = Optionpost::get('address', true, $siteId);
    	if(!$addressCode)
	    {
	    	$streetCode = Optionpost::get('street', true, $siteId);
	    	$streetProp = self::getPropertyFromCollectionByCode($propertyCollection, $streetCode);
	    	if($streetProp)
	    		$addressStr .= $streetProp->getValue();
	    	$homeCode = Optionpost::get('house', true, $siteId);
	    	$homeProp = self::getPropertyFromCollectionByCode($propertyCollection, $homeCode);
	    	if($homeProp)
	    		$addressStr .= ' '.$homeProp->getValue();
	    	$flatCode = Optionpost::get('flat', true, $siteId);
	    	$flatProp = self::getPropertyFromCollectionByCode($propertyCollection, $flatCode);
	    	if($flatProp)
			    $addressStr .= ' '.$flatProp->getValue();
	    }
	    else
	    {
		    $addrProp = self::getPropertyFromCollectionByCode($propertyCollection, $addressCode);
		    if($addrProp)
			    $addressStr  = $addrProp->getValue();
	    }
	    return $addressStr;
    }

	public static function getContact(\Bitrix\Sale\PropertyValueCollection $propertyCollection, $siteId)
	{
		$contactStr = false;
		$extendName = Optionpost::get('extendName', true, $siteId);
		if($extendName == 'Y')
		{
			$lastNameCode = Optionpost::get('sName', true, $siteId);
			$lastNameProp = self::getPropertyFromCollectionByCode($propertyCollection, $lastNameCode);
			if($lastNameProp)
				$contactStr .= $lastNameProp->getValue();
			$firstNameCode = Optionpost::get('fName', true, $siteId);
			$firstNameProp = self::getPropertyFromCollectionByCode($propertyCollection, $firstNameCode);
			if($firstNameProp)
				$contactStr .= ' '.$firstNameProp->getValue();
			$middleNameCode = Optionpost::get('mName', true, $siteId);
			$middleNameProp = self::getPropertyFromCollectionByCode($propertyCollection, $middleNameCode);
			if($middleNameProp)
				$contactStr .= ' '.$middleNameProp->getValue();
		}
		else
		{
			$fioCode = Optionpost::get('name', true, $siteId);
			$fioProp = self::getPropertyFromCollectionByCode($propertyCollection, $fioCode);
			if($fioProp)
				$contactStr  = $fioProp->getValue();
		}
		return $contactStr;
	}

	public static function BuildList($items)
	{
		$b24path = array (
			'ORDER' => '/shop/orders/details/',
			'SHIPMENT' => '/shop/orders/shipment/details/',
		);
		$jqueryOff = Option::get(self::$MODULE_ID, "RUSSIANPOST_JQUERY_OFF");
		if (($GLOBALS['APPLICATION']->GetCurPage()=='/bitrix/admin/sale_order_create.php'))
		{
			if($jqueryOff == 'N' || $jqueryOff == '')
			{
				\CJSCore::Init(array('jquery'));
				\CJSCore::Init(array('jquery2'));
			}
			$pathToWidjet = '/bitrix/js/russianpost.post/admin_scripts.js';
			if(file_exists($_SERVER['DOCUMENT_ROOT'].$pathToWidjet)) {
				$GLOBALS['APPLICATION']->AddHeadScript($pathToWidjet);
			}
		}
		if (($GLOBALS['APPLICATION']->GetCurPage()=='/bitrix/admin/sale_order_edit.php'
			|| $GLOBALS['APPLICATION']->GetCurPage()=='/bitrix/admin/sale_order_shipment_edit.php'
		))
		{
			if($jqueryOff == 'N' || $jqueryOff == '')
			{
				\CJSCore::Init(array('jquery'));
				\CJSCore::Init(array('jquery2'));
			}
			$pathToWidjet = '/bitrix/js/russianpost.post/admin_edit_scripts.js';
			if(file_exists($_SERVER['DOCUMENT_ROOT'].$pathToWidjet)) {
				$GLOBALS['APPLICATION']->AddHeadScript($pathToWidjet);
			}
		}
		if (strpos($GLOBALS['APPLICATION']->GetCurPage(), $b24path['ORDER']) !== false)
		{
			if($jqueryOff == 'N' || $jqueryOff == '')
			{
				\CJSCore::Init(array('jquery'));
				\CJSCore::Init(array('jquery2'));
			}
			$pathToWidjet = '/bitrix/js/russianpost.post/crm_scripts.js';
			if(file_exists($_SERVER['DOCUMENT_ROOT'].$pathToWidjet)) {
				$GLOBALS['APPLICATION']->AddHeadScript($pathToWidjet);
			}
		}
		if (strpos($GLOBALS['APPLICATION']->GetCurPage(), $b24path['SHIPMENT']) !== false)
		{
			if($jqueryOff == 'N' || $jqueryOff == '')
			{
				\CJSCore::Init(array('jquery'));
				\CJSCore::Init(array('jquery2'));
			}
			$pathToWidjet = '/bitrix/js/russianpost.post/crm_edit_scripts.js';
			if(file_exists($_SERVER['DOCUMENT_ROOT'].$pathToWidjet)) {
				$GLOBALS['APPLICATION']->AddHeadScript($pathToWidjet);
			}
		}

	}

	public static function CalculateProfile(\Bitrix\Sale\Shipment $shipment = null, $arCalculateParams = array(), $arRequest = array())
	{
		unset($_SESSION['russianpost_post_calc']['errors'][$arCalculateParams['PROFILE']]);
		$b24path = array (
			'ORDER' => '/bitrix/components/bitrix/crm.order.details/ajax.php',
			'SHIPMENT' => '/bitrix/components/bitrix/crm.order.shipment.details/ajax.php',
			'ORDER1' => '/shop/orders/details/',
			'SHIPMENT1' => '/shop/orders/shipment/details/',
		);
		$result = new \Bitrix\Sale\Delivery\CalculationResult();
		$weight = $shipment->getWeight(); // вес отгрузки
		$order = $shipment->getCollection()->getOrder(); // заказ
		$siteId = $order->getSiteId();
		$orderId = $order->getId();
		$props = $order->getPropertyCollection();
		$locProp = $props->getDeliveryLocation();
		$digitalCode = '';
		if($locProp)
		{
			$locationCode = $locProp->getValue();
			if($locationCode != '')
			{
				$item = \Bitrix\Sale\Location\LocationTable::getByCode($locationCode, array(
					'filter' => array('=NAME.LANGUAGE_ID' => LANGUAGE_ID),
					'select' => array('*', 'NAME_RU' => 'NAME.NAME')
				))->fetch();
				if($arCalculateParams['PROFILE'] == self::PROFILE_WORLDPICKUP || $arCalculateParams['PROFILE'] == self::PROFILE_WORLDCOURIER)
				{
					$arCountryInfo = self::GetCountryByCode($locationCode);
					if(!empty($arCountryInfo))
					{
						$digitalCode = Hllist::GetCountryDigitalCode($arCountryInfo['CODE'], $arCountryInfo['NAME']);
					}
				}
			}
		}
		$addrPropValue = Tools::getAddress($props, $siteId);
		if($arCalculateParams['PROFILE'] == self::PROFILE_PICKUP
			|| $arCalculateParams['PROFILE'] == self::PROFILE_COURIER || $arCalculateParams['PROFILE'] == self::PROFILE_PICKUPNOTE)
		{
			$zipCode = Optionpost::get('zip', true, $siteId);
			//$zipProp = $props->getDeliveryLocationZip();
			$zipProp = Tools::getPropertyFromCollectionByCode($props, $zipCode);
			//lo($zipProp);
			$zipDefValue = '';
			if($zipProp)
			{
				//$zipPropValue = $zipProp->getValue();
				//$zipPropInfo = \CSaleOrderProps::GetByID($zipProp->getPropertyId());
				//$zipDefValue = $zipPropInfo['DEFAULT_VALUE'];
			}
			if($zipPropValue == '' || $zipPropValue == $zipDefValue)
			{
				$zipPropValue = '';
				if($zipProp)
				{
					/*$zipPropId = $zipProp->getPropertyId();
					if(isset($arRequest['order']))
					{
						$zipPropValue = $arRequest['order']['ORDER_PROP_'.$zipPropId];
					}*/
				}
				if($zipPropValue == '' && $locationCode != '')
				{
					$res = \Bitrix\Sale\Location\LocationTable::getList(array(
						'filter' => array(
							'CODE' => array($locationCode),
							'EXTERNAL.SERVICE.CODE' => 'ZIP',
							//'CODE' => array('0000073738'),
						),
						'select' => array(
							'EXTERNAL.*',
							'EXTERNAL.SERVICE.CODE'
						)
					));
					while($itemZip = $res->fetch())
					{
						if($itemZip['SALE_LOCATION_LOCATION_EXTERNAL_XML_ID'] != '')
							$zipPropValue = $itemZip['SALE_LOCATION_LOCATION_EXTERNAL_XML_ID'];
					}
				}
			}
			$arParams['ZIP'] = $zipPropValue;
		}
		//lo($arParams['ZIP']);
		$arParams['WEIGHT'] = intval($weight);
		switch ($arCalculateParams['PROFILE'])
		{
			case self::PROFILE_PICKUP:
				$arParams['ADDRESS'] = $item['NAME_RU'];
				if(empty($arParams['ADDRESS']))
					$arParams['ADDRESS'] = '';
				break;
			case self::PROFILE_COURIER:
			case self::PROFILE_PICKUPNOTE:
				$arParams['ADDRESS'] = $item['NAME_RU'].' '.$addrPropValue;
				break;
			case self::PROFILE_WORLDPICKUP:
			case self::PROFILE_WORLDCOURIER:
				$arParams['DIGITAL_CODE'] = $digitalCode;
				$arParams['ADDRESS'] = self::Translit($item['NAME_RU'].' '.$addrPropValue);
				break;
		}
		if($arCalculateParams['ORDER_CURRENCY'] != 'RUB' && isset($arCalculateParams['CURRENCY_LIST']['RUB']))
		{
			$arParams['PRICE'] = \CCurrencyRates::ConvertCurrency($order->getPrice()-$order->getDeliveryPrice(), "RUB", $arCalculateParams['ORDER_CURRENCY']);
		}
		else
			$arParams['PRICE'] = $order->getPrice()-$order->getDeliveryPrice();
		$request = new \Russianpost\Post\Request();
		switch($arCalculateParams['PROFILE'])
		{
			case self::PROFILE_PICKUP:
				$res = $request->PickUpCalculateSimple($arParams);
				break;
			case self::PROFILE_PICKUPNOTE:
				$res = $request->PickUpCalculate($arParams);
				break;
			case self::PROFILE_COURIER:
				$res = $request->CourierCalculate($arParams);
				break;
			case self::PROFILE_WORLDPICKUP:
				$res = $request->PickUpWorldCalculate($arParams);
				break;
			case self::PROFILE_WORLDCOURIER:
				$res = $request->CourierWorldCalculate($arParams);
				break;
		}

		$answer = $res[0];

		if(empty($answer))
		{
			$result->addError(new \Bitrix\Main\Error(Loc::getMessage("SALE_DLV_RUSSIANPOST_POST_SELECT_POST")));
		}
		else
		{
			if(is_array($answer['errors_detailed']))
			{
				$tarifErr = $answer['errors_detailed']['tariff']['errors'];
				$deliveryErr = $answer['errors_detailed']['delivery']['errors'];
				if(!empty($tarifErr))
				{
					foreach ($tarifErr as $key=>$arInfo)
					{
						$arInfo['message'] = str_replace(array("'", "\""), array('',''), $arInfo['message']);
						$tarifErr[$key] = $arInfo;
					}
				}
				if(!empty($deliveryErr))
				{
					foreach ($deliveryErr as $key=>$arInfo)
					{
						$arInfo['message'] = str_replace(array("'", "\""), array('',''), $arInfo['message']);
						$deliveryErr[$key] = $arInfo;
					}
				}
				$answer['errors_detailed']['tariff']['errors'] = $tarifErr;
				$answer['errors_detailed']['delivery']['errors'] = $deliveryErr;
				$_SESSION['russianpost_post_calc']['error_detailed'] = $answer['errors_detailed'];
			}
			if(!empty($answer['errors']))
			{
				if(is_array($answer['errors']))
				{
					$strErrors = implode("; ", $answer['errors']);
					if(LANG_CHARSET == 'windows-1251')
					{
						$strErrors = iconv("UTF-8", "WINDOWS-1251", $strErrors);
					}
					$_SESSION['russianpost_post_calc']['errors'][$arCalculateParams['PROFILE']] = $strErrors;
					foreach ($answer['errors'] as $error)
					{
						$result->addError(new \Bitrix\Main\Error($error));
					}
				}
				else
				{
					if(LANG_CHARSET == 'windows-1251')
					{
						$answer['errors'] = iconv("UTF-8", "WINDOWS-1251", $answer['errors']);
					}
					$result->addError(new \Bitrix\Main\Error($answer['errors']));
					$_SESSION['russianpost_post_calc']['errors'][$arCalculateParams['PROFILE']] = $answer['errors'];
				}
				if((isset($arRequest['order']['DELIVERY_ID']) && $arRequest['order']['DELIVERY_ID'] == $arCalculateParams['CALCULATED_DELIVERY'])
					|| $_SESSION['russianpost_post_calc']['checked_delivery'] == $arCalculateParams['CALCULATED_DELIVERY'])
				{
					$_SESSION['russianpost_post_calc']['shipment_type'] = '';
				}
			}
			else
			{
				/*if($arCalculateParams['PROFILE'] == self::PROFILE_PICKUP && $arCalculateParams['FIX_PRICE'] == 'FIX')
				{
					$answer['price'] = 118.80;
				}*/
				#currency convertation
				if($arCalculateParams['BASE_CURRENCY'] != 'RUB' && isset($arCalculateParams['CURRENCY_LIST']['RUB']))
				{
					$answer['price'] = \CCurrencyRates::ConvertCurrency($answer['price'], "RUB", $arCalculateParams['BASE_CURRENCY']);
				}
				elseif ($arCalculateParams['PROFILE_CURRENCY'] != '' && $arCalculateParams['PROFILE_CURRENCY'] != 'RUB' && isset($arCalculateParams['CURRENCY_LIST']['RUB']))
				{
					$answer['price'] = \CCurrencyRates::ConvertCurrency($answer['price'], "RUB", $arCalculateParams['PROFILE_CURRENCY']);
				}
				$result->setDeliveryPrice(
					roundEx(
						$answer['price'],
						SALE_VALUE_PRECISION
					)
				);
				if(LANG_CHARSET == 'windows-1251')
				{
					$answer['delivery_interval']['description'] = iconv("UTF-8", "WINDOWS-1251", $answer['delivery_interval']['description']);
				}
				$result->setPeriodDescription($answer['delivery_interval']['description']);
				if($arCalculateParams['PROFILE'] == self::PROFILE_PICKUP)
				{
					if(isset($_SESSION['russianpost_post_calc']['checked_delivery']) && $_SESSION['russianpost_post_calc']['checked_delivery'] == $arCalculateParams['CALCULATED_DELIVERY'])
					{
						$_SESSION['russianpost_post_calc']['shipment_type'] = $answer['shipment_type'];
						if(!isset($_REQUEST['order']['russianpost_result_address']))
						{
							$_SESSION['russianpost_post_calc']['clear_address'] = 'Y';
						}
					}
				}
				elseif ($arCalculateParams['PROFILE'] == self::PROFILE_COURIER || $arCalculateParams['PROFILE'] == self::PROFILE_WORLDPICKUP
					|| $arCalculateParams['PROFILE'] == self::PROFILE_WORLDCOURIER || $arCalculateParams['PROFILE'] == self::PROFILE_PICKUPNOTE)
				{
					if((isset($arRequest['order']['DELIVERY_ID']) && $arRequest['order']['DELIVERY_ID'] == $arCalculateParams['CALCULATED_DELIVERY'])
						|| $_SESSION['russianpost_post_calc']['checked_delivery'] == $arCalculateParams['CALCULATED_DELIVERY'])
					{
						$_SESSION['russianpost_post_calc']['shipment_type'] = $answer['shipment_type'];
					}
					if($arCalculateParams['ADMIN_SECTION'])
					{
						$_SESSION['russianpost_post_calc']['shipment_type'] = $answer['shipment_type'];
					}
					if(strpos($arCalculateParams['CUR_PAGE'], $b24path['ORDER']) !== false || strpos($arCalculateParams['CUR_PAGE'], $b24path['SHIPMENT']) !== false ||
						strpos($arCalculateParams['CUR_PAGE'], $b24path['ORDER1']) !== false || strpos($arCalculateParams['CUR_PAGE'], $b24path['SHIPMENT1']) !== false)
					{
						$_SESSION['russianpost_post_calc']['shipment_type'] = $answer['shipment_type'];
					}
				}
			}
		}
		return $result;
	}

	public static function onSaleDeliveryTrackingClassNamesBuildList()
	{
		return new \Bitrix\Main\EventResult(
			\Bitrix\Main\EventResult::SUCCESS,
			array(
				'\Sale\Handlers\Delivery\RussianpostTracking' => '/bitrix/php_interface/include/sale_delivery/russianpost/tracking.php'
			),
			'sale'
		);
	}

	public static function GetPostalTypeFromProfile($user_id)
	{
		$result = '';
		$db_sales = \CSaleOrderUserProps::GetList(
			array("DATE_UPDATE" => "DESC"),
			array("USER_ID" => $user_id)
		);

		$lastProfile = $db_sales->Fetch();
		if(!empty($lastProfile))
		{
			$db_propVals = \CSaleOrderUserPropsValue::GetList(array("ID" => "ASC"), Array("USER_PROPS_ID"=>$lastProfile['ID']));
			while ($arPropVals = $db_propVals->Fetch())
			{
				if($arPropVals['PROP_CODE'] == 'RUSSIANPOST_TYPEDLV')
				{
					$result = trim($arPropVals['VALUE']);
				}
			}
		}
		return $result;
	}

	public static function GetProductMarkers($arProdIds = array(), $siteId = '')
	{
		$result = array();
		if(!empty($arProdIds))
		{
			$markProp = strtoupper(Option::get(self::$MODULE_ID, "RUSSIANPOST_MARK_PROP", "", $siteId));
			$markIblock = Option::get(self::$MODULE_ID, "RUSSIANPOST_MARK_IBLOCK", "", $siteId);
			if($markIblock != '')
			{
				$arFilter = array('IBLOCK_ID' => $markIblock, 'ID' => $arProdIds);
				$pr_res = \CIBlockElement::GetList(array('ID' => 'ASC'), $arFilter, false, false, array('ID', 'PROPERTY_'.$markProp));
				while($arProd = $pr_res->fetch())
				{
					$result[$arProd['ID']] = $arProd['PROPERTY_'.$markProp.'_VALUE'];
				}
			}
		}
		return $result;
	}

	public static function postZipValidate($zip)
	{
		$pattern = '#^[1-9]\d{5}$#';
		return preg_match($pattern, $zip);
	}

	public static function checkStatus($statusProp, $statusOrder)
	{
		$result = false;
		$query = \Bitrix\Sale\Internals\StatusTable::query();
		$query->setSelect([
			'ID', 'SORT', 'TYPE', 'NOTIFY', 'COLOR'
		]);
		$query->where(
			\Bitrix\Main\ORM\Query\Query::filter()
				->where('TYPE','=','O')
		//->logic('OR')
		//->where('STATUS_LANG.LID', '=', LANGUAGE_ID)
		//->where('STATUS_LANG.LID', NULL)

		);
		$arRes = $query->exec()->fetchAll();
		$statusPropSort = 0;
		$statusOrderSort = 0;
		foreach ($arRes as $arStatus)
		{
			if($arStatus['ID'] == $statusProp)
				$statusPropSort = $arStatus['SORT'];
			if($arStatus['ID'] == $statusOrder)
				$statusOrderSort = $arStatus['SORT'];
		}
		if($statusOrderSort >= $statusPropSort)
			$result = true;

		return $result;
	}

}
?>