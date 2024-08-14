<?php

namespace Bitrix\Sign\Controllers\V1\Document;

use Bitrix\Sign\Access\ActionDictionary;
use Bitrix\Sign\Operation;
use Bitrix\Sign\Attribute;

class Signing extends \Bitrix\Sign\Engine\Controller
{
	/**
	 * @param string $uid
	 *
	 * @return array
	 */
	#[Attribute\ActionAccess(ActionDictionary::ACTION_DOCUMENT_EDIT)]
	public function startAction(string $uid): array
	{
		$result = (new Operation\SigningStart($uid))->launch();

		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());
		}

		return [];
	}

	/**
	 * @param string $uid
	 *
	 * @return array
	 */
	#[Attribute\ActionAccess(ActionDictionary::ACTION_DOCUMENT_EDIT)]
	public function stopAction(string $uid): array
	{
		$result = (new Operation\SigningStop($uid))->launch();

		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());
		}

		return [];
	}
}