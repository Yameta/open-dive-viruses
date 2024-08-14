<?global $USER;?>
<?Bitrix\Main\Page\Frame::getInstance()->startDynamicWithID("form-block".$arParams["WEB_FORM_ID"]);?>
<?if($USER->IsAuthorized()):?>
	<?
	$dbRes = CUser::GetList(($by = "id"), ($order = "asc"), array("ID" => $USER->GetID()), array("FIELDS" => array("ID", "PERSONAL_PHONE")));
	$arUser = $dbRes->Fetch();

	$fio = $USER->GetFullName();
	$phone = $arUser['PERSONAL_PHONE'];
	$email = $USER->GetEmail();
	?>
	<script type="text/javascript">
	$(document).ready(function() {
		try{
			$('.form.<?=$arResult["arForm"]["SID"]?> input[data-sid=CLIENT_NAME], .form.<?=$arResult["arForm"]["SID"]?> input[data-sid=FIO], .form.<?=$arResult["arForm"]["SID"]?> input[data-sid=NAME]').val(BX.Text.decode('<?=CMax::formatJsNameEx($fio)?>'));
			$('.form.<?=$arResult["arForm"]["SID"]?> input[data-sid=PHONE]').val(BX.Text.decode('<?=CMax::formatJsNameEx($phone)?>'));
			$('.form.<?=$arResult["arForm"]["SID"]?> input[data-sid=EMAIL]').val(BX.Text.decode('<?=CMax::formatJsNameEx($email)?>'));
		}
		catch(e){
		}
	});
	</script>
<?endif;?>
<?Bitrix\Main\Page\Frame::getInstance()->finishDynamicWithID("form-block".$arParams["WEB_FORM_ID"], "");?>

<?$arScripts = ['phone_input', 'phone_mask']?>
<?if (CMax::GetFrontParametrValue('USE_INTL_PHONE') === 'Y'):?>
	<?$arScripts[] = 'intl_phone_input'?>
<?endif;?>
<?\Aspro\Max\Functions\Extensions::initInPopup($arScripts);?>