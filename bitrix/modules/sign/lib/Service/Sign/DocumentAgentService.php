<?php

namespace Bitrix\Sign\Service\Sign;

use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sign\Document;
use Bitrix\Sign\Integration\CRM\Model\EventData;
use Bitrix\Sign\Item\Api\Document\Signing\StartRequest;
use Bitrix\Sign\Main\User;
use Bitrix\Sign\Repository\BlankRepository;
use Bitrix\Sign\Repository\DocumentRepository;
use Bitrix\Sign\Service\Container;
use Bitrix\Sign\Item;
use Bitrix\Sign\Service;
use Bitrix\Sign\Operation;

use Bitrix\Main;
use Bitrix\Sign\Type\Document\EntityType;
use Bitrix\Sign\Type\DocumentScenario;
use Bitrix\Sign\Type\DocumentStatus;

class DocumentAgentService
{
	/**
	 * Add agent for start signing
	 * @param string $uid
	 *
	 * @return void
	 */
	public function addConfigureAndStartAgent(string $uid): void
	{
		$agentName = $this->getConfigureAndStartAgentName($uid);
		if (!$this->agentExists($agentName))
		{
			$this->addAgent($agentName);
		}
	}

	/**
	 * Configure and start signing
	 * @param string $uid
	 *
	 * @return string
	 */
	public static function configureAndStart(string $uid): string
	{
		$result = Container::instance()->getDocumentService()->configureAndStart($uid);
		if (!$result->isSuccess())
		{
			return Container::instance()->getDocumentAgentService()->getConfigureAndStartAgentName($uid);
		}
		return '';
	}

	private function addAgent(string $agentName, $interval = 60, $nextDateExec = '')
	{
		$agent = new \CAllAgent();
		$agent->AddAgent(
			$agentName,
			"sign",
			"N",
			(int) $interval,
			null,
			"Y",
			(string) $nextDateExec
		);
	}

	private function removeAgent(string $agentName)
	{
		$agent = new \CAllAgent();
		$list = $agent->getList(
			["ID" => "DESC"],
			["MODULE_ID" => "sign", "NAME" => $agentName]
		);
		while ($row = $list->fetch())
		{
			$agent->delete($row["ID"]);
		}
	}

	private function agentExists(string $agentName)
	{
		$agent = new \CAllAgent();
		return (bool)$agent->getList(
			["ID" => "DESC"],
			["MODULE_ID" => "sign", "NAME" => $agentName]
		)->fetch();
	}

	private function getConfigureAndStartAgentName(string $uid)
	{
		return "\\" . __CLASS__ . "::configureAndStart('$uid');";
	}
}
