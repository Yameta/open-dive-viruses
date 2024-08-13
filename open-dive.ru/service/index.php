<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("description", "Сервисное обслуживание и ремонт подводного снаряжения для дайвинга и подводной охоты в Опендайв - техническое обслуживание, диагностика, замена деталей");
$APPLICATION->SetPageProperty("HIDE_LEFT_BLOCK", "Y");
$APPLICATION->SetPageProperty("title", "Сервисный центр и обслуживание - Опендайв");
$APPLICATION->SetTitle("Сервисный центр");
?><div>
	 Магазин Опендайв является сертифицированным центром по обслуживанию подводных приборов SUUNTO
</div>
<p>
 <span> <br>
 <a href="https://open-dive.ru/service/servisnyi_tsentr_suunto/"><img width="265" src="/upload/images/ssc_200_1.jpg" height="95"></a><br>
 </span>
</p>
<p>
</p>
<div>
	 Также мы производим техническое обслуживание и ремонт подводных компьютеров<br>
	 SCUBAPRO, UWATEC, SUBGEAR, MARES, DACOR, OCEANIC, AERIS, TUSA, AQUALUNG, SHEARWATER, SALVIMAR,&nbsp;&nbsp;и др.<br>
 <br>
	 Техническое обслуживание и ремонт регуляторов&nbsp;<br>
	 APEKS,&nbsp;AQUALUNG,&nbsp;MARES,&nbsp;SCUBAPRO, POSEIDON, OCEANIC, HOLLIS, ATOMIC&nbsp;<br>
 <br>
	 Техническое обслуживание и ремонт любых жилетов компенсаторов и&nbsp;подвесных систем<br>
 <br>
	 Техническое обслуживание и ремонт любых&nbsp;сухих гидрокостюмов&nbsp;(в том числе и переклека молний и обтюраторов).&nbsp;<br>
 <br>
	 Ремонт подводных ружей, диагностика, снарядка ружей для подводной охоты. Замена деталей, поршней, уплотнителей, любых механизмов, сервисное обслуживание, установка тяжей, зацепов, фонарей, компенсаторов и т.д. Профессиональные рекомендации.&nbsp;<br>
 <br>
	 Внимание: срок выполнения сервисного обслуживания или ремонта от 1 часа (замена элементов питания, мелкий ремонт) до 3 дней (сервисное обслуживание регуляторов, ремонт жилетов или гидрокостюмов).
</div>
 <span style="color: #888888; font-style: italic; font-size: medium;"><em><br>
 <a href="https://open-dive.ru/service/prais_list_na_raboty_po_servisnomu_obsluzhivaniyu/"><img width="250" src="/upload/images/i_price.jpg" height="124"></a><a href="https://crm.open-dive.ru/service/prais_list_na_raboty_po_servisnomu_obsluzhivaniyu/"><br>
 </a> </em><br>
 </span>Сервисный центр расположен по адресу: <br>
 <img width="40" src="/upload/images/i_metro.png" height="40" style="color: #7f7f7f; vertical-align: middle;" alt="">Савеловская, 127220, ул. 2-ая Квесисская д.23,&nbsp;10 минут пешком от метро
<p>
</p>
<div>
	 Время работы:<br>
 <br>
	 Пн-Пт - 10.00 до 20.00,&nbsp;Сб -Вс и праздничные дни с 11.00 до 18.00<br>
 <br>
	 +7 (495) 215-56-52 (доб.5)
</div>
<p>
</p>
<p>
 <a style="outline: 0px; color: #438ae6; font-size: medium;" href="https://wa.me/79067681230"><img width="30" src="/upload/images/i_whatsapp.jpg" height="28" style="vertical-align: middle;" alt=""></a><span>&nbsp; Whatsapp&nbsp;&nbsp;<a style="outline: 0px; color: #438ae6;" href="https://t.me/+79067681230"><img width="30" src="/upload/images/i_telegram.jpg" height="30" style="vertical-align: middle;" alt=""></a>&nbsp; Telegram</span>
</p>
 Мы принимаем снаряжение в ремонт из любого города нашей страны.
<p>
 <span><br>
 <img width="200" src="/upload/images/i_sdek.jpg" height="165"></span>
</p>
 <br>
 <?
$bUseFeedback = CMax::GetFrontParametrValue('CONTACTS_USE_DILER', SITE_ID) != 'N';
?> <?if($bUseFeedback):?>
<div class="form_design">
	<div class="block">
		 <?Bitrix\Main\Page\Frame::getInstance()->startDynamicWithID("contacts-form-block");?> <?global $arTheme;?> <?$APPLICATION->IncludeComponent(
	"bitrix:form.result.new",
	"site",
	Array(
		"ADD_BUTTON_CLASS" => "appeals",
		"ADD_FORM_CLASS" => "appeals",
		"CACHE_TIME" => "3600000",
		"CACHE_TYPE" => "A",
		"CHAIN_ITEM_LINK" => "",
		"CHAIN_ITEM_TEXT" => "",
		"COMPONENT_TEMPLATE" => "popup",
		"COMPOSITE_FRAME_MODE" => "A",
		"COMPOSITE_FRAME_TYPE" => "AUTO",
		"EDIT_URL" => "",
		"HIDDEN_CAPTCHA" => CMax::GetFrontParametrValue("HIDDEN_CAPTCHA"),
		"IGNORE_CUSTOM_TEMPLATE" => "N",
		"LIST_URL" => "",
		"SEF_MODE" => "N",
		"SHOW_LICENCE" => $arTheme["SHOW_LICENCE"]["VALUE"],
		"SUCCESS_URL" => "?send=ok",
		"USE_EXTENDED_ERRORS" => "Y",
		"VARIABLE_ALIASES" => array("WEB_FORM_ID"=>"WEB_FORM_ID","RESULT_ID"=>"RESULT_ID",),
		"WEB_FORM_ID" => "12"
	)
);?> <?Bitrix\Main\Page\Frame::getInstance()->finishDynamicWithID("contacts-form-block", "");?>
	</div>
</div>
<?endif;?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>