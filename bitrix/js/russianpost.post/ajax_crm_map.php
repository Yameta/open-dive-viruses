<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
use \Bitrix\Main\Loader;
$module_id = "russianpost.post";
use Bitrix\Main\Config\Option;
Loader::includeModule('sale');
Loader::includeModule($module_id);

$GLOBALS['APPLICATION']->AddHeadScript("https://widget.pochta.ru/map/widget/widget.js");
$guid_id = Option::get('russianpost.post', "GUID_ID");
//0000073738
//0000445112
$res = \Bitrix\Sale\Location\LocationTable::getList(array(
	'filter' => array(
		'CODE' => array($_REQUEST['location']),
		//'CODE' => array('0000073738'),
	),
	'select' => array(
		'EXTERNAL.*',
		'EXTERNAL.SERVICE.CODE'
	)
));
$arZip = array();
while($item = $res->fetch())
{
	if($item['SALE_LOCATION_LOCATION_EXTERNAL_SERVICE_CODE'] == 'ZIP_LOWER'
		|| $item['SALE_LOCATION_LOCATION_EXTERNAL_SERVICE_CODE'] == 'ZIP')
	{
		$threeDigits = substr($item['SALE_LOCATION_LOCATION_EXTERNAL_XML_ID'], 0, 3);
		$arZip[$threeDigits] = "'".$threeDigits."'";
	}
}
$strZip = implode(", ", $arZip);
$resS = \Bitrix\Sale\Location\LocationTable::getList(array(
	'filter' => array(
		'=CODE' => $_REQUEST['location'],
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
?>
<script
        src="https://widget.pochta.ru/map/widget/widget.js">
</script>
<script>
	ecomStartWidget({
		accountId: '<?=$guid_id?>',
		accountType: 'bitrix_cms',
		weight: <?=$_REQUEST['weight'];?>,
		sumoc: <?=$_REQUEST['price'];?>,
		startZip: [<?=$strZip;?>],
		//startZip: ['344'],
		callbackFunction: callbackCrmFunction,
		containerId: 'ecom-widget',
		start_location: '<?=$locationFullName;?>'
	});
</script>