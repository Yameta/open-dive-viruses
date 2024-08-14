<?
define("NO_AGENT_CHECK", true);
define("NO_AGENT_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
define("DisableEventsCheck", true);
require_once $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php";
use \Bitrix\Main\Error;
use \Bitrix\Sale\Shipment;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\ArgumentNullException;
use \Bitrix\Sale\Delivery\Services\Manager;
use \Bitrix\Sale\Delivery\CalculationResult;
use \Bitrix\Main\Loader;
use \Bitrix\Sale\Delivery;
use Bitrix\Main\Config\Option;
ini_set("xdebug.overload_var_dump", "off");
if(!Loader::includeModule('sale'))
{
	CHTTP::SetStatus("500 Internal Server Error");
	die('{"error":"Module \"sale\" not installed"}');
}
if(!Loader::includeModule('catalog'))
{
	CHTTP::SetStatus("500 Internal Server Error");
	die('{"error":"Module \"sale\" not installed"}');
}
if(!Loader::includeModule('russianpost.post'))
{
	CHTTP::SetStatus("500 Internal Server Error");
	die('{"error":"Module \"russianpost\" not installed"}');
}

$postData = '';
$debugKey = Option::get('russianpost.post', "RUSSIANPOST_KEY_DEBUG");
if($_SERVER['REQUEST_METHOD'] == 'POST' && count($_POST) <= 0)
{
	$postData = file_get_contents("php://input");
}
$result = \Russianpost\Post\Tools::SaveTrackNumber($postData);
if($debugKey == 'Y')
{
	\Bitrix\Main\Diag\Debug::dumpToFile($postData, "requestTrack", "bitrix/js/russianpost.post/log/log_key.log");
	\Bitrix\Main\Diag\Debug::dumpToFile($result, "answerTrack", "bitrix/js/russianpost.post/log/log_key.log");
}


$APPLICATION->RestartBuffer();
echo $result;
/*$APPLICATION->RestartBuffer();
echo '$orderId-'.$orderId;
echo '$action-'.$action;
echo '$postData-'.$postData;*/
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_after.php");
?>