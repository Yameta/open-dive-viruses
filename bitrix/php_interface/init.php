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

/*function checkActivity() {
  CModule::IncludeModule("catalog");
  CModule::IncludeModule("iblock");
  $el = new \CIBlockElement;
  $rsElement = \CIBlockElement::GetList([], [
    "IBLOCK_ID" => 41,
    "ACTIVE" => "N",
    "!SECTION_ID" => 3968
  ], false, false, ["ID"]);
  while($arElement = $rsElement->fetch()) {
    $stores = \Bitrix\Catalog\StoreProductTable::getList([
        'filter' => ['=PRODUCT_ID' => $arElement['ID']],
        'select' => ['AMOUNT'],
      ])->fetchAll();
      $total = 0;
      foreach($stores as $store) {
          $total += $store['AMOUNT'];
      }

      if($total > 0) {
          $el->Update($arElement['ID'], ['ACTIVE' => 'Y']);
      }
  }

  $rsElement = \CIBlockElement::GetList([], [
    "IBLOCK_ID" => 41,
    "ACTIVE" => "Y",
    "!PROPERTY_DEACT_ON_ZERO_VALUE" => false,
    "!SECTION_ID" => 3968
  ], false, false, ["ID"]);
  while($arElement = $rsElement->fetch()) {
    $stores = \Bitrix\Catalog\StoreProductTable::getList([
      'filter' => ['=PRODUCT_ID' => $arElement['ID']],
      'select' => ['AMOUNT'],
    ])->fetchAll();
    $total = 0;
    foreach($stores as $store) {
        $total += $store['AMOUNT'];
    }

    if($total == 0) {
        $el->Update($arElement['ID'], ['ACTIVE' => 'N']);
    }
  }

  $products = \Bitrix\Catalog\ProductTable::getList([
    'filter' => [
      'VAT_INCLUDED' => 'Y'
    ]
  ])->fetchAll();
  foreach($products as $product) {
    CCatalogProduct::Update($product['ID'], ['VAT_INCLUDED' => 'N']);
  }

  return "checkActivity();";
}*/
/* /код Виктора */


