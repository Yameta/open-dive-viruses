<?php
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
if($_REQUEST['auth']['member_id'] == 'bb49d05ba84108515c0b4bae92973ca8') {
    \Bitrix\Main\Loader::includeModule('crm');
    \Bitrix\Main\Loader::includeModule('sale');
    $deal = \Bitrix\Crm\DealTable::getList([
        'filter' => [
            'ID' => str_replace('DEAL_', '', $_REQUEST['document_id'][2])
        ]
    ])->fetch();
    $orderId = explode(' №', $deal['TITLE'])[1];
    if($orderId) {
        $order = \Bitrix\Sale\Order::load($orderId);
        if($order) {
            $deliveryObj = \Bitrix\Sale\Delivery\Services\Manager::getObjectById($order->getDeliverySystemId()[0]);
            $delivery['NAME'] = $deliveryObj->getName();
            if($deliveryObj->getParentService()) {
                $delivery['NAME'] = $deliveryObj->getParentService()->getName().' ('.$delivery['NAME'].')';
            }
            $delivery['PRICE'] = $order->getDeliveryPrice();

            $propertyCollection = $order->getPropertyCollection();
            $ar = $propertyCollection->getArray()['properties'];
            foreach($ar as $value) {
                // if($value['PERSON_TYPE_ID'] == 5 && $value['CODE'] == 'FAST_ORDER' && $value['VALUE'][0] == 'Y') {
                    // $fast = 1;
                // }
                $props[$value['CODE']] = $value['VALUE'][0];
                if($value['CODE'] == 'LOCATION') {
                    $props[$value['CODE']] = $propertyCollection->getDeliveryLocation()->getViewHtml();
                }
            }
            //if(!$fast) {
			if($props['FAST_ORDER'] == 'Y') {	
				$address = $props['ADDRESS'];
            } else {
				$address = '';
                if($props['ZIP']) {
                    $address .= $props['ZIP'].', ';
                }
                if($props['LOCATION']) {
                    $address .= $props['LOCATION'];
                }
				//if($props['ADDRESS']) {
				//    $address .= ', '.$props['ADDRESS'];
				//}
				$address .= ', '.$props['ADDRESS'];

            }
            $result = [
                'UF_CRM_1707156530645' => $address,
				'UF_CRM_1704693719852' => $props['ADDRESS'],
                'UF_CRM_1707293676118' => $delivery['NAME'],
                'UF_CRM_1707305772295' => $delivery['PRICE'],
                'UF_CRM_1707319441683' => $order->getPrice(),
                'UF_CRM_1707327568228' => $order->getId(),
                'COMMENTS' => $order->getField('USER_DESCRIPTION')
            ];
            $entityObject = new \CCrmDeal(false);
            $res = $entityObject->Update($deal['ID'], $result, true, [
                'DISABLE_USER_FIELD_CHECK' => true,
                'DISABLE_REQUIRED_USER_FIELD_CHECK' => true
            ]);

            $contact['FM'] = [
                'EMAIL' => [
                    'n0' => [
                    'VALUE_TYPE' => 'WORK', 
                    'VALUE' => $props['EMAIL']
                    ]
                ]
            ];
            $entityObject = new \CCrmContact(false);
            $res = $entityObject->Update($deal['CONTACT_ID'], $contact, true, [
                'DISABLE_USER_FIELD_CHECK' => true,
                'DISABLE_REQUIRED_USER_FIELD_CHECK' => true
            ]);
        }
    }
}