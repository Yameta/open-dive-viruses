<?
include_once('include/models.php');
include_once('only_product_v5.php');
include_once('redirects_v5.php');
include_once('custom_cmaxitem.php');

AddEventHandler("sale", "OnOrderNewSendEmail", "ModifyOrderSaleMails");
function ModifyOrderSaleMails($orderID, &$eventName, &$arFields)
{
   if(CModule::IncludeModule("sale") && CModule::IncludeModule("iblock"))
   {
	  $strOrderList = "";
	  $dbBasketItems = CSaleBasket::GetList(
				 array("NAME" => "ASC"),
				 array("ORDER_ID" => $orderID),
				 false,
				 false,
				 array("PRODUCT_ID", "ID", "NAME", "QUANTITY", "PRICE", "CURRENCY")
			   );
 while ($arProps = $dbBasketItems->Fetch())
  {
    $quantity = round($arProps['QUANTITY']);
    $price =  round($arProps['PRICE']);
    $summ = $quantity * $price;
    $strCustomOrderList .= "<tr><td>".$arProps['NAME']."</td><td>".$quantity."</td><td>".$price." руб.</td><td>".$summ." руб.</td><tr>";
  }
	   $arFields["ORDER_TABLE_ITEMS"] = "<table cellpadding='5' cellspacing='0' border='1'><tr><td>Наименование</td><td>Количество</td><td>Цена</td><td>Сумма</td>".$strCustomOrderList."</table>"; 

  $arOrder = CSaleOrder::GetByID($orderID);
 

 $order_props = CSaleOrderPropsValue::GetOrderProps($orderID);
 $phone="";
 $index = "";
 $country_name = "";
 $city_name = "";  
 $address = "";
 while ($arProps = $order_props->Fetch())
 {
   if ($arProps["CODE"] == "PHONE")
   {
      $phone = htmlspecialchars($arProps["VALUE"]);
   }
   if ($arProps["CODE"] == "LOCATION")
   {
       $arLocs = CSaleLocation::GetByID($arProps["VALUE"]);
       $country_name =  $arLocs["COUNTRY_NAME_ORIG"];
       $city_name = $arLocs["CITY_NAME_ORIG"];
   }

   if ($arProps["CODE"] == "INDEX")
   {
     $index = $arProps["VALUE"];  
   }

   if ($arProps["CODE"] == "ADDRESS")
   {
     $address = $arProps["VALUE"];
   }
 }

 $full_address = $index.", ".$country_name."-".$city_name.", ".$address;

 $arDeliv = CSaleDelivery::GetByID($arOrder["DELIVERY_ID"]);
 $delivery_name = "";
 if ($arDeliv)
 {
   $delivery_name = $arDeliv["NAME"];
 }

 $arPaySystem = CSalePaySystem::GetByID($arOrder["PAY_SYSTEM_ID"]);
 $pay_system_name = "";
 if ($arPaySystem)
 {
   $pay_system_name = $arPaySystem["NAME"];
 }

 $arFields["ORDER_DESCRIPTION"] = $arOrder["USER_DESCRIPTION"];
 $arFields["PHONE"] =  $phone;
 $arFields["DELIVERY_NAME"] =  $delivery_name;
 $arFields["PAY_SYSTEM_NAME"] =  $pay_system_name;
 $arFields["ADDRESS"] = $address;  

	} 

}

/* код Виктора */
class MyCustomAgent
{

    public static function checkActivity()
    {
        $_SERVER["DOCUMENT_ROOT"] = "/home/bitrix/ext_www/open-dive.ru";

        $fp = @fopen($_SERVER['DOCUMENT_ROOT']."/log.txt", "ab");
        fwrite($fp, print_r(date("H:i:s").'/', TRUE));
        fclose($fp);

        CModule::IncludeModule("catalog");
        CModule::IncludeModule("iblock");

        $el = new \CIBlockElement;
        $rsElement = \CIBlockElement::GetList([], [
            "IBLOCK_ID" => 41,
            "ACTIVE" => "N",
            "!SECTION_ID" => 3968
        ], false, false, ["ID"]);
        while ($arElement = $rsElement->fetch()) {
            $stores = \Bitrix\Catalog\StoreProductTable::getList([
                'filter' => ['=PRODUCT_ID' => $arElement['ID']],
                'select' => ['AMOUNT'],
            ])->fetchAll();
            $total = 0;

            foreach ($stores as $store) {
                $total += $store['AMOUNT'];
            }

            if ($total > 0) {
                $el->Update($arElement['ID'], ['ACTIVE' => 'Y']);
            }
        }

        $rsElement = \CIBlockElement::GetList([], [
            "IBLOCK_ID" => 41,
            "ACTIVE" => "Y",
            "!PROPERTY_DEACT_ON_ZERO_VALUE" => false,
            "!SECTION_ID" => 3968
        ], false, false, ["ID"]);
        while ($arElement = $rsElement->fetch()) {
            $stores = \Bitrix\Catalog\StoreProductTable::getList([
                'filter' => ['=PRODUCT_ID' => $arElement['ID']],
                'select' => ['AMOUNT'],
            ])->fetchAll();
            $total = 0;
            foreach ($stores as $store) {
                $total += $store['AMOUNT'];
            }

            if ($total == 0) {
                $el->Update($arElement['ID'], ['ACTIVE' => 'N']);
            }
        }

        $products = \Bitrix\Catalog\ProductTable::getList([
            'filter' => [
                'VAT_INCLUDED' => 'Y'
            ]
        ])->fetchAll();
        foreach ($products as $product) {
            CCatalogProduct::Update($product['ID'], ['VAT_INCLUDED' => 'N']);
        }

        return "MyCustomAgent::checkActivity();";
    }


    /**
     * Метод для регистрации агента
     */
    public static function registerAgent()
    {
        global $DB;

        $DB->Query("DELETE FROM b_agent WHERE MODULE_ID IS NULL AND NAME='MyCustomAgent::checkActivity();'");

        CAgent::AddAgent(
            "MyCustomAgent::checkActivity();", // имя функции агента
            "", // идентификатор модуля
            "N", // агент не критичен
            84600, // интервал запуска в секундах (раз в сутки)
            "", // дата первой проверки
            "Y", // агент активен
            "", // дата первого запуска
            30 // сортировка
        );
    }

}


\Bitrix\Main\EventManager::getInstance()->addEventHandler(
  'main',
  'OnAfterUserRegister',
  'SendUserInfo'
);

\Bitrix\Main\EventManager::getInstance()->addEventHandler(
  'main',
  'OnAfterUserAdd',
  'SendUserInfo'
);

function SendUserInfo(&$arFields) {
  if($_POST['ONE_CLICK_BUY']['CONTACT_EMAIL'] && !$arFields['USER_ID']) {
    return;
  }
  $userId = $arFields['USER_ID'];
  if(!$userId) {
    $userId = $arFields['ID'];
  }
  if($userId) {
    if(!$_POST['REGISTER']['PASSWORD']) {
      $password = randString(10);
      $email = $_POST['ONE_CLICK_BUY']['CONTACT_EMAIL'];
      if(!$email) {
        $email = $_POST['ORDER_PROP_40'];
      }
      if(!$email) {
        $email = $_POST['ORDER_PROP_74'];
      }
      $user = new CUser;
      $user->Update($userId, [
        'LOGIN' => $email,
        'EMAIL' => $email,
        'PASSWORD' => $password,
        'CONFIRM_PASSWORD' => $password
      ]);
      CEvent::SendImmediate('PERSONAL_ACCESS', 's2', [
        'EMAIL' => $email,
        'PASSWORD' => $password
      ]);
      $user->Authorize($userId);
    }
  }
}

function checkPriceFrom($id) {
  Bitrix\Main\Loader::includeModule("iblock");
  Bitrix\Main\Loader::includeModule("catalog");
  $group = \CIBlockElement::GetProperty(41, $id, [], ["CODE" => "GROUP_OFFER_ID"])->Fetch()["VALUE"];
  if(!$group) {
    return false;
  }
  $rs = CIBlockElement::GetList(
    [],
    ["PROPERTY_GROUP_OFFER_ID" => $group],
    false,
    false,
    ['ID', 'IBLOCK_ID']
  );
  
  $current = false;
  while($item = $rs->getNext()) {
    $price = CPrice::GetBasePrice($item['ID'])['PRICE'];
    if(!$current) {
      $current = $price;
    }
    if($price != $current) {
      return true;
    }
  }
  return false;
}
/* /код Виктора */


//Bitrix\Main\EventManager::getInstance()->addEventHandler('sale', 'OnSaleComponentOrderJsData', 'OnSaleComponentOrderJsDataHandler');
function OnSaleComponentOrderJsDataHandler(&$arResult, &$arParams)
{
foreach($arResult['JS_DATA']['DELIVERY'] as $delivery)
{
	if($delivery['ID'] == 4 && $delivery['CHECKED'] == 'Y')
	{
		$arResult['JS_DATA']['PAY_SYSTEM'][0]['CHECKED'] = 'N';
		$arResult['JS_DATA']['PAY_SYSTEM'][1]['CHECKED'] = 'Y';
	}

	if($delivery['ID'] == 17 && $delivery['CHECKED'] == 'Y')
	{
		$arResult['JS_DATA']['PAY_SYSTEM'][0]['CHECKED'] = 'N';
		$arResult['JS_DATA']['PAY_SYSTEM'][1]['CHECKED'] = 'Y';
	}
	
	if($delivery['ID'] == 5 && $delivery['CHECKED'] == 'Y')
	{
		$arResult['JS_DATA']['PAY_SYSTEM'][0]['CHECKED'] = 'N';
		$arResult['JS_DATA']['PAY_SYSTEM'][1]['CHECKED'] = 'Y';
	}


}
}


AddEventHandler("iblock", "OnBeforeIBlockElementUpdate", array("UpdateExceptions", "OnBeforeIBlockElementUpdateHandler"));

class UpdateExceptions{

	static function OnBeforeIBlockElementUpdateHandler(&$arFields){
		if($arFields['IBLOCK_ID'] == '41' && $arFields['MODIFIED_BY'] == '3') {
			$cantEditActivity = false;
			$res = CIBlockElement::GetProperty(41, $arFields["ID"], "sort", "asc", array("CODE" => "DONT_CHANGE_ACTIVITY"));
			if ($ob = $res->GetNext()){
				$cantEditActivity = ($ob['VALUE'] == 5969);
			}
			if($cantEditActivity){
				$res = CIBlockElement::GetByID($arFields["ID"]);
				if ($ar_res = $res->GetNext()){
					$active = $ar_res['ACTIVE'];
				}
				$arFields['ACTIVE'] = $active;
			}
		}
	}

}

