<?require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/usertypefloat.php");
use \Bitrix\Main\Application;
use \Bitrix\Sale\PaySystem;
use \Bitrix\Main\UserField\TypeFloat;

define("STOP_STATISTICS", true);
define('NO_AGENT_CHECK', true);
define("DisableEventsCheck", true);

if (CModule::IncludeModule("sale"))
{
	$context = Application::getInstance()->getContext();
	$request = $context->getRequest();
	if ( ! CUserTypeFloat::CheckResult($request->get('sum'))) return false;
	$item = PaySystem\Manager::searchByRequest($request);
	if ($item !== false)
	{
		$service = new PaySystem\Service($item);
		if ($service instanceof PaySystem\Service)
			$result = $service->processRequest($request);
				
	}
}
