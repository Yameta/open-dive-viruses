<?php
	use Bitrix\Main\Localization\Loc;
	use Bitrix\Sale;
	Loc::loadMessages(__FILE__);
	
	$sum = round($params['SUM'], 2);

	if ( isset($_REQUEST['ORDER_ID'])  ):

	$ORDER_ID = $_REQUEST['ORDER_ID'];
	$basket = Sale\Order::load($ORDER_ID)->getBasket();
	$orderBasket = $basket->getOrderableItems()->getBasketItems(); // массив объектов Sale\BasketItem
	$not_avail_order = false;
	foreach ($orderBasket as $basketItem) {
		$arProduct = CCatalogProduct::GetByID($basketItem->getField('PRODUCT_ID'));
		if ($arProduct['QUANTITY'] == 0)
		{
			$not_avail_order = true;			
			break;
		}
	}
?>
<style>
	<?php
		require 'style.css';
	?>
</style>
<div class="mb-4" >
	<? if ($not_avail_order): ?>
	
	<div class="alert alert-info">Некоторых товаров нет в наличии, с Вами свяжется менеджер для уточнения заказа</div>
	
	<? else: ?>
	<div class="col-auto pl-0">
		<a class="btn btn-lg btn-success" style="border-radius: 32px;" href="<?=$params['URL'];?>"><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_YANDEX_CHECKOUT_BUTTON_PAID')?></a>
	</div>
	<div class="widget-payment-checkout-info"><?= Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_YANDEX_CHECKOUT_DESCRIPTION_MSGVER_1') ?></div>
	<div class="widget-payment-checkout-info"><?= Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_YANDEX_CHECKOUT_DESCRIPTION_SUM', ['#SUM#' => SaleFormatCurrency($sum, $params['CURRENCY'])]) ?></div>
	
	<div class="d-flex align-items-center mb-3">
		
		<div class="col pr-0 widget-payment-checkout-info"><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_YANDEX_CHECKOUT_REDIRECT_MESS');?></div>
	</div>
	
	
	<div class="alert alert-info"><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_YANDEX_CHECKOUT_WARNING_RETURN');?></div>
	
	<? endif; ?>

</div>
<? else: ?>
<style>
	<?php
		require 'style.css';
	?>
</style>

<div class="mb-4" >
	<div class="widget-payment-checkout-info"><?= Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_YANDEX_CHECKOUT_DESCRIPTION_MSGVER_1') ?></div>
	<div class="widget-payment-checkout-info"><?= Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_YANDEX_CHECKOUT_DESCRIPTION_SUM', ['#SUM#' => SaleFormatCurrency($sum, $params['CURRENCY'])]) ?></div>
	<div class="d-flex align-items-center mb-3">
		<div class="col-auto pl-0">
			<a class="btn btn-lg btn-success" style="border-radius: 32px;" href="<?=$params['URL'];?>"><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_YANDEX_CHECKOUT_BUTTON_PAID')?></a>
		</div>
		<div class="col pr-0 widget-payment-checkout-info"><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_YANDEX_CHECKOUT_REDIRECT_MESS');?></div>
	</div>
	<div class="alert alert-info"><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_YANDEX_CHECKOUT_WARNING_RETURN');?></div>
</div>

<? endif ?>