<?define("STATISTIC_SKIP_ACTIVITY_CHECK", "true");?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");?>
	<?$APPLICATION->IncludeComponent("aspro:oneclickbuy.max", "shop", array(
		"BUY_ALL_BASKET" => "Y",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "3600000",
		"CACHE_GROUPS" => "N",
		"SHOW_LICENCE" => CMax::GetFrontParametrValue('SHOW_LICENCE'),
		"SHOW_OFFER" => CMax::GetFrontParametrValue('SHOW_OFFER'),
		"SHOW_DELIVERY_NOTE" => COption::GetOptionString('aspro.max', 'ONECLICKBUY_SHOW_DELIVERY_NOTE', 'Y', SITE_ID),
		//"PROPERTIES" => (strlen($tmp = COption::GetOptionString('aspro.max', 'ONECLICKBUY_PROPERTIES', 'FIO,PHONE,EMAIL,COMMENT', SITE_ID)) ? explode(',', $tmp) : array()),
		"PROPERTIES" => ['CONTACT_FULL_NAME','CONTACT_EMAIL','CONTACT_PHONE', 'ADDRESS','COMMENT', 'FAST_ORDER'],
		"REQUIRED" => ['CONTACT_PHONE'],

		
		//"REQUIRED" => (strlen($tmp = COption::GetOptionString('aspro.max', 'ONECLICKBUY_REQUIRED_PROPERTIES', 'FIO,PHONE', SITE_ID)) ? explode(',', $tmp) : array()),

		"DEFAULT_PERSON_TYPE" => COption::GetOptionString('aspro.max', 'ONECLICKBUY_PERSON_TYPE', '1', SITE_ID),
		"DEFAULT_DELIVERY" => COption::GetOptionString('aspro.max', 'ONECLICKBUY_DELIVERY', '2', SITE_ID),
		"DEFAULT_PAYMENT" => COption::GetOptionString('aspro.max', 'ONECLICKBUY_PAYMENT', '1', SITE_ID),
		"DEFAULT_CURRENCY" => COption::GetOptionString('aspro.max', 'ONECLICKBUY_CURRENCY', 'RUB', SITE_ID),
		),
		false
	);?>

