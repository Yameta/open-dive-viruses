<?php

namespace Bitrix\Sign\Controllers\V1\Document;

use Bitrix\Sign\Access\ActionDictionary;
use Bitrix\Sign\Service;
use Bitrix\Sign\Item;
use Bitrix\Sign\Attribute;

use Bitrix\Main;

class Pages extends \Bitrix\Sign\Engine\Controller
{

	#[Attribute\ActionAccess(ActionDictionary::ACTION_DOCUMENT_EDIT)]
	public function listAction(string $uid): array
	{
		$document = Service\Container::instance()->getDocumentRepository()->getByUid($uid);
		if (!$document)
		{
			$this->addError(new Main\Error('Document not found'));
		}

		$listRequest = new Item\Api\Document\Page\ListRequest($uid);

		$apiPages = Service\Container::instance()->getApiDocumentPageService();
		$listResponse = $apiPages->getList($listRequest);
		if (!$listResponse->isSuccess())
		{
			$this->addErrors($listResponse->getErrors());
		}

		return [
			'ready' => $listResponse->ready,
			'pages' => array_map(
				function (Item\Api\Property\Response\Page\List\Page $page)
				{
					return [
						'url' => $page->url
					];
				},
				$listResponse->pages->toArray()
			)
		];
	}
}