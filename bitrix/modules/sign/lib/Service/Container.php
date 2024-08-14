<?php

namespace Bitrix\Sign\Service;

use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Sign\Repository;
use Bitrix\Sign\Connector;
use Bitrix\Sign\Service;
use Bitrix\Sign\Operation;

class Container
{
	public static function instance(): Container
	{
		return self::getService('sign.container');
	}

	private static function getService(string $name): mixed
	{
		$prefix = 'sign.';
		if (mb_strpos($name, $prefix) !== 0)
		{
			$name = $prefix . $name;
		}
		$locator = ServiceLocator::getInstance();
		return $locator->has($name)
			? $locator->get($name)
			: null
		;
	}

	public function getApiService(): ApiService
	{
		return self::getService('sign.service.api');
	}

	public function getApiDocumentService(): Api\DocumentService
	{
		return self::getService('sign.service.api.document');
	}

	public function getApiClientDomainService(): Api\Client\Domain
	{
		return self::getService('sign.service.api.client.domain');
	}

	public function getDocumentRepository(): Repository\DocumentRepository
	{
		return static::getService('sign.repository.document');
	}

	public function getBlankRepository(): Repository\BlankRepository
	{
		return static::getService('sign.repository.blank');
	}

	public function getBlockRepository(): Repository\BlockRepository
	{
		return static::getService('sign.repository.block');
	}

	public function getApiDocumentPageService(): Api\Document\PageService
	{
		return self::getService('sign.service.api.document.page');
	}

	public function getApiDocumentSigningService(): Api\Document\SigningService
	{
		return self::getService('sign.service.api.document.signing');
	}

	public function getApiDocumentFieldService(): Api\Document\FieldService
	{
		return self::getService('sign.service.api.document.field');
	}
	public function getSignBlankFileService(): Service\Sign\BlankFileService
	{
		return self::getService('sign.service.sign.blank.file');
	}
	public function getSignBlankService(): Service\Sign\BlankService
	{
		return self::getService('sign.service.sign.blank');
	}

	public function getSignBlockService(): Service\Sign\BlockService
	{
		return self::getService('sign.service.sign.block');
	}

	public function getDocumentService(): Service\Sign\DocumentService
	{
		return self::getService('sign.service.sign.document');
	}
	public function getCrmSignDocumentService(): Service\Integration\Crm\DocumentService
	{
		return self::getService('sign.service.integration.crm.document');
	}

	public function getEventHandlerService(): Service\Integration\Crm\EventHandlerService
	{
		return self::getService('sign.service.integration.crm.events');
	}

	public function getMemberRepository(): Repository\MemberRepository
	{
		return static::getService('sign.repository.member');
	}

	public function getFileRepository(): Repository\FileRepository
	{
		return static::getService('sign.repository.file');
	}

	public function getMemberService(): Service\Sign\MemberService
	{
		return static::getService('sign.service.sign.member');
	}

	public function getMemberConnectorFactory(): Connector\MemberConnectorFactory
	{
		return static::getService('sign.connector.member.factory');
	}

	public function getFieldConnectorFactory(): Connector\FieldConnectorFactory
	{
		return static::getService('sign.connector.field.factory');
	}

	public function getSignedFileLoadService(): Service\Api\Document\SignedFileLoadService
	{
		return static::getService('sign.service.api.document.signed.file.load');
	}

	public function getDocumentFileNameService(): Service\Sign\DocumentFileNameService
	{
		return static::getService('sign.service.sign.document.filename');
	}

	public function getDocumentAgentService(): Service\Sign\DocumentAgentService
	{
		return static::getService('sign.service.sign.document.agent');
	}

	public function getAccessibleItemFactory(): \Bitrix\Sign\Access\Model\Factory\AccessibleItem
	{
		return static::getService('sign.access.model.factory.accessibleItem');
	}
}
