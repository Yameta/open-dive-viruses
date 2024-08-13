<?

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
global $USER;
$USER->Authorize(1);
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
//require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
//use Bitrix\Main,
//   Bitrix\Main\Loader,
//   Bitrix\Main\Service\GeoIp,
//   Bitrix\Main\Context,
//   Bitrix\Currency\CurrencyManager,
//   Bitrix\Sale,
//   Bitrix\Sale\Order,
//   Bitrix\Sale\Basket,
//   Bitrix\Sale\Delivery,
//   Bitrix\Sale\PaySystem;
//if(\Bitrix\Main\Loader::includeModule('sale'))
//{
//
//
//$order = \Bitrix\Sale\Order::load('{{Заказы}}');
//$paymentCollection = $order->getPaymentCollection();
//print_r($paymentCollection);
//foreach ($paymentCollection as $payment)
//{
//	$link= $payment->getField('PS_INVOICE_ID');
//
//}
////$rootActivity = $this->GetRootActivity();
////$rootActivity->SetVariable("yandex", $link);
//
//
//
//// $order = \Bitrix\Sale\Order::load(265);
//// $shipmentCollection = $order->getShipmentCollection();
//// print_r($shipmentCollection);
//// foreach ($shipmentCollection as $shipment)
//// {
//        // $price= $shipment->getField('BASE_PRICE_DELIVERY');
//        // $name = $shipment->getField('DELIVERY_NAME');
//
//// }
//
//}