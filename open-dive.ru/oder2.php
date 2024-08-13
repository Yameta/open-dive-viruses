<?php
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
if($_REQUEST['auth']['member_id'] == 'bb49d05ba84108515c0b4bae92973ca8') {
    \Bitrix\Main\Loader::includeModule('crm');
    \Bitrix\Main\Loader::includeModule('sale');
	

$order = \Bitrix\Sale\Order::load('4538');
$shipmentCollection = $order->getShipmentCollection();
foreach ($shipmentCollection as $shipment)
{    
	$delid[]= $shipment->getField('DELIVERY_ID'); 
}

print_r($order);
} 

/* if($_REQUEST['auth']['member_id'] == 'bb49d05ba84108515c0b4bae92973ca8') {
    \Bitrix\Main\Loader::includeModule('crm');
    \Bitrix\Main\Loader::includeModule('sale');
    $deal = \Bitrix\Crm\DealTable::getList([
        'filter' => [
            'ID' => str_replace('DEAL_', '', $_REQUEST['document_id'][2])
        ]
    ])->fetch();
    $orderId = explode(' №', $deal['TITLE'])[1];
    $order = \Bitrix\Sale\Order::load($orderId);
	
	print_r($orderId); 
} */