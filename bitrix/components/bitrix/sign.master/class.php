<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sign\Access\Model\UserModel;
use Bitrix\Sign\Access\Permission\SignPermissionDictionary;
use Bitrix\Sign\Access\Service\RolePermissionService;
use Bitrix\Sign\Blank;
use Bitrix\Sign\Config\Storage;
use Bitrix\Sign\Document;
use Bitrix\Sign\Error;
use Bitrix\Sign\File;
use Bitrix\Sign\Internal\DocumentTable;
use Bitrix\Sign\Item\Api\Client\DomainRequest;
use Bitrix\Sign\Main\Application;
use Bitrix\Sign\Main\User;
use Bitrix\Sign\Service\Container;

\CBitrixComponent::includeComponentClass('bitrix:sign.base');

class SignMasterComponent extends SignBaseComponent
{
	/**
	 * Restricted size for images.
	 */
	private const IMAGE_SIZES = [
		'width' => 1275,
		'height' => 1650
	];

	/**
	 * Required params of component.
	 * If not specified, will be set to null.
	 * @var string[]
	 */
	protected static array $requiredParams = [
		'PAGE_URL_EDIT', 'CATEGORY_ID',
		'VAR_STEP_ID', 'VAR_DOC_ID',
		'CRM_ENTITY_TYPE_ID',
		'OPEN_URL_AFTER_CLOSE'
	];

	/**
	 * Returns true if SMS is allowed by tariff.
	 * @return bool
	 */
	public function isSmsAllowed(): bool
	{
		return \Bitrix\Sign\Restriction::isSmsAllowed();
	}

	/**
	 * Creates new document.
	 * @param array|null $file Blank file from $_FILE.
	 * @param array|null $fileMulti Blank fileS from $_FILE.
	 * @param int|null $loadBlank Blank id to create document by exists blank.
	 * @return void
	 */
	public function actionCreateDocument(?array $file = null, ?array $fileMulti = null, ?int $loadBlank = null): void
	{
		$blank = null;
		$files = [];

		if (!$file['error'][0])
		{
			$fileMulti = $file;
		}

		foreach ($fileMulti['error'] as $i => $error)
		{
			if (!$error)
			{
				$files[$i] = new File([
					'name' => $fileMulti['name'][$i],
					'type' => $fileMulti['type'][$i],
					'tmp_name' => $fileMulti['tmp_name'][$i],
					'error' => $fileMulti['error'][$i],
					'size' => $fileMulti['size'][$i]
				]);
				if ($files[$i]->isImage())
				{
					$files[$i]->resizeProportional($this::IMAGE_SIZES);
				}
			}
		}

		if ($files)
		{
			$fileBlank = array_shift($files);
			$blank = Blank::createFromFile($fileBlank);
			if ($blank && $fileBlank->isImage() && !empty($files))
			{
				if (!$blank->addFiles($files))
				{
					return;
				}
			}

			$loadBlank = null;
		}
		else if ($loadBlank)
		{
			$blank = Blank::getById($loadBlank);
		}

		if ($blank)
		{
			$oldDocument = $this->getResult('DOCUMENT');
			if ($oldDocument)
			{
				$oldDocument->unlink();
			}

			$entityTypeId = $this->getStringParam('ENTITY_TYPE_ID');
			$entityId = $this->getRequest($this->getStringParam('VAR_DOC_ID'));

			if (!\Bitrix\Sign\Integration\CRM\EntityValidator::checkEntity($entityTypeId, $entityId))
			{
				$this->addError('SIGN_DOCUMENT_ENTITY_NOT_FOUND',
					Loc::getMessage('SIGN_DOCUMENT_ENTITY_NOT_FOUND'));
				return;
			}

			$document = $blank->createDocument($entityTypeId, $entityId);
			if ($document)
			{
				$document->register($loadBlank === null);
			}

			$this->setResult('DOCUMENT', $document);
		}
	}

	/**
	 * Assigns members for document.
	 * @param string|null $initiatorName Initiator name.
	 * @return void
	 */
	public function actionAssignMembers(?string $initiatorName = null): void
	{
		/** @var Document $document */
		$document = $this->getResult('DOCUMENT');
		if ($document)
		{
			if (!$initiatorName)
			{
				$initiatorName = $this->getResult('RESPONSIBLE_NAME');
			}

			$document->setMeta([
				'initiatorName' => $initiatorName
			]);
		}
	}

	/**
	 * Final step of master.
	 * @param array|null $muteMembers Array members to mute.
	 * @param array|null $communications Array of members communications.
	 * @return void
	 */
	public function actionSendDocument(?array $muteMembers = [], ?array $communications = []): void
	{
		/** @var Document $document */
		$document = $this->getResult('DOCUMENT');
		if ($document)
		{
			$members = $document->getMembers();
			$blocks = $document->getBlank()->getBlocks();

			// check that every block is correct
			foreach ($blocks as $block)
			{
				if (!$block->checkBeforeSave())
				{
					return;
				}
			}

			// part of members need to mute
			if ($muteMembers)
			{
				foreach ($members as $member)
				{
					if ($muteMembers[$member->getId()] === 'Y')
					{
						$member->mute();
					}
				}
			}

			// array of new members communications
			if ($communications)
			{
				foreach ($members as $member)
				{
					if (!isset($communications[$member->getId()]))
					{
						continue;
					}
					$communication = $communications[$member->getId()];
					if ($communication)
					{
						[$type, $value] = explode('|', $communication);

						if ($type === 'PHONE' && !$this->isSmsAllowed())
						{
							Error::getInstance()->addError(
								'SMS_IS_NOT_ALLOWED',
								Loc::getMessage('SIGN_CMP_MASTER_SMS_IS_NOT_ALLOWED')
							);
							return;
						}

						if ($type && $value)
						{
							$member->setCommunication($type, $value);
							continue;
						}
					}

					Error::getInstance()->addError(
						'COMMUNICATION_EMPTY',
						Loc::getMessage('SIGN_CMP_MASTER_ERROR_COMMUNICATION_EMPTY')
					);
					return;
				}
			}

			// send to proxy
			$document->send();
		}
	}

	/**
	 * Executing before actions.
	 * @return void
	 */
	protected function beforeActions(): void
	{
		$document = $this->getResult('DOCUMENT');

		if (!$document)
		{
			$entityType = $this->getStringParam('ENTITY_TYPE_ID');
			$entityId = $this->getRequest($this->getStringParam('VAR_DOC_ID'));
			if ($entityId)
			{
				$document = Document::resolveByEntity($entityType, $entityId);
				$this->setResult('DOCUMENT', $document);
			}
		}

		if ($document && !$document->canBeChanged())
		{
			Error::getInstance()->addError(
				'ACCESS_DENIED',
				Loc::getMessage('SIGN_CMP_BASE_ERROR_ACCESS_DENIED')
			);
			$this->setTemplate('denied');
		}

		$this->setResult('RESPONSIBLE_NAME', $this->getResponsibleName($document));
	}

	/**
	 * Executes component.
	 * @return void
	 */
	public function exec(): void
	{
		/** @var Document $document */
		$document = $this->getResult('DOCUMENT');

		if ($document && $this->getStringParam('OPEN_URL_AFTER_CLOSE'))
		{
			$this->setResult(
				'OPEN_URL_AFTER_CLOSE',
				str_replace('#id#', $document->getEntityId(), $this->getStringParam('OPEN_URL_AFTER_CLOSE'))
			);
		}

		$currentDomain = Storage::instance()->getSavedDomain();
		if ($currentDomain === null)
		{
			$currentDomain = Application::getServer()->getHttpHost();
			Container::instance()->getApiClientDomainService()->change(
				(new DomainRequest($currentDomain))
			);
			Storage::instance()->setCurrentDomain($currentDomain);
		}
		$this->setResult('WIZARD_CONFIG', $this->getWizardConfig());
		$this->setResult('STAGE_ID', $document ? $document->getStageId() : null);
		$this->setResult('BLANKS', Blank::getPublicList());
		$this->setResult('IS_MASTER_PERMISSIONS_FOR_USER_DENIED', $this->isMasterPermissionsForUserDenied());
		$this->resetParamsAsString(self::$requiredParams);
	}

	private function getWizardConfig(): array
	{
		$isEdoRegion = in_array(\Bitrix\Main\Application::getInstance()->getLicense()->getRegion(), ['ru', 'by'], true);
		return [
			'blankSelectorConfig' => [
				'uploaderOptions' => [
					// TODO add acceptedFileTypes[]
					'maxFileSize' =>
						\Bitrix\Sign\Config\Storage::instance()->getUploadDocumentMaxSize(),
					'imageMaxFileSize' =>
						\Bitrix\Sign\Config\Storage::instance()->getUploadImagesMaxSize(),
					'maxTotalFileSize' =>
						\Bitrix\Sign\Config\Storage::instance()->getUploadTotalMaxSize(),
					'maxFileCount' =>
						\Bitrix\Sign\Config\Storage::instance()->getImagesCountLimitForBlankUpload(),

				],
				'portalConfig' => [
					'isDomainChanged' => $this->isDomainChanged(Storage::instance()->getSavedDomain()),
					'isUnsecuredScheme' => $this->isUnsecuredScheme(),
					'isEdoRegion' => $isEdoRegion,
				],
			],
			'documentSummaryConfig' => [
				'region' => \Bitrix\Main\Application::getInstance()->getLicense()->getRegion(),
				'languages' => \Bitrix\Sign\Config\Storage::instance()->getLanguages(),
			],
		];
	}

	private function getResponsibleName(?Document $document): string
	{
		$responsibleName = $document?->getInitiatorName();

		if ($responsibleName !== null)
		{
			return $responsibleName;
		}

		$lastUserDocuments = $this->getLastUserDocuments(
			User::getInstance()->getId(),
			5
		);

		foreach ($lastUserDocuments as $userDocument)
		{
			if ($userDocument === null)
			{
				continue;
			}

			$initiatorName = $userDocument->getInitiatorName();
			if ($initiatorName === null)
			{
				continue;
			}

			if ($document === null || $userDocument->getId() !== $document->getId())
			{
				return $initiatorName;
			}
		}

		return User::getCurrentUserName();
	}

	/**
	 * @param int $userId
	 * @param int $amount
	 *
	 * @return array<?Document>
	 */
	private function getLastUserDocuments(int $userId, int $amount): array
	{
		$rows = DocumentTable
			::query()
			->addSelect('*')
			->where('CREATED_BY_ID', $userId)
			->addOrder('DATE_CREATE', 'DESC')
			->setLimit($amount)
			->fetchAll()
		;

		return array_map(
			static fn (array $row) => Document::tryCreateByRow($row),
			$rows
		);
	}

	private function isDomainChanged($currentDomain): bool
	{
		return $currentDomain !== Application::getServer()->getHttpHost();
	}

	private function isUnsecuredScheme(): bool
	{
		return !Context::getCurrent()->getRequest()->isHttps();
	}

	private function isMasterPermissionsForUserDenied(): bool
	{
		$userId = \Bitrix\Main\Engine\CurrentUser::get()->getId();
		$accessController = new \Bitrix\Sign\Access\AccessController($userId);

		$requiredPermissions = [
			\Bitrix\Sign\Access\ActionDictionary::ACTION_DOCUMENT_ADD,
			\Bitrix\Sign\Access\ActionDictionary::ACTION_DOCUMENT_EDIT,
		];

		$allRequiredPermissionsCheckArePassed = \Bitrix\Sign\Helper\IterationHelper::all(
			$requiredPermissions,
			static fn($permission) => $accessController->check($permission),
		);

		return !$allRequiredPermissionsCheckArePassed;
	}
}
