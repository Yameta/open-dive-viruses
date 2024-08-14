<?php

namespace Bitrix\Sign\Callback;

use Bitrix\Main;

use Bitrix\Sign\Callback\Messages;
use Bitrix\Sign\Controllers;
use Bitrix\Sign\Document;
use Bitrix\Sign\Document as DocumentCore;
use Bitrix\Sign\File;
use Bitrix\Sign\Operation\FillFields;
use Bitrix\Sign\Service\Container;
use Bitrix\Sign\Type;

class Handler
{
	public const OLD_API_VERSION = 1;

	public static function execute(array $payload): Main\Result
	{
		$result = new Main\Result();

		$message = Messages\Factory::createMessage($payload['type'] ?? '', $payload['data'] ?? []);

		switch ($message::Type)
		{
			case Messages\DocumentStatus::Type:
			{
				/** @var Messages\DocumentStatus $message */
				$document = Document::getByHash($message->getCode());
				if (!$document)
				{
					return $result->addError(new Main\Error('Invalid callback token.'));
				}

				/** @var Messages\DocumentStatus $message */
				$isDone = self::processDocumentStatus($document, $message);

				if (!$isDone)
				{
					$result->addError(new Main\Error('Error handling document status setting.'));
				}

				return $result;
			}
			case Messages\TimelineEvent::Type:
			{
				/** @var Messages\TimelineEvent $message */
				Main\DI\ServiceLocator::getInstance()
					->get('sign.service.integration.crm.events')
					->handleTimelineEvent(
						$message->toArray(),
						$message->getSecurityCode()
					);
				break;
			}
			case Messages\ResultFile::Type:
			{
				/** @var Messages\ResultFile $message */
				self::processSaveResultFile($message, $result);
				break;
			}
			case Messages\ReadyLayoutCommand::Type:
			{
				/** @var Messages\ReadyLayoutCommand $message */
				self::processHandleReadyLayoutCommand($message, $result);
				break;
			}
			case Messages\DocumentOperation::Type:
			{
				/** @var Messages\DocumentOperation $message */
				self::processDocumentOperation($message, $result);
				break;
			}
			case Messages\FieldSet::Type:
			{
				/** @var Messages\FieldSet $message */
				self::processFieldSet($message, $result);
				break;
			}
			default:
				$result->addError(new Main\Error('Message of unknown type.'));
		}

		return $result;
	}

	private static function processFieldSet(Messages\FieldSet $message, Main\Result $result): void
	{
		$document = DocumentCore::getByHash($message->getDocumentCode());
		if (!$document)
		{
			$result->addError(new Main\Error('Invalid callback token.'));

			return;
		}

		if (!isset($message->getData()['fields']))
		{
			$result->addError(new Main\Error('Invalid fields data.'));

			return;
		}

		$result = (new FillFields(
			$message->getData()['fields'],
			Container::instance()->getMemberService()->getByUid($message->getMemberCode())
		))->launch();

		if (!$result->isSuccess())
		{
			$result->addError(new Main\Error('Failed to set fields'));
		}
	}

	private static function processDocumentStatus(
		Document $document,
		Messages\DocumentStatus $documentStatusMessage
	): bool
	{
		$version = $documentStatusMessage->getVersion() ?? self::OLD_API_VERSION;

		$statusColumnKey = 'PROCESSING_STATUS';
		$documentSignedStatus = Document\Status::READY;
		if ($version !== self::OLD_API_VERSION)
		{
			$documentSignedStatus = Type\DocumentStatus::DONE;
			$statusColumnKey = 'STATUS';
		}

		if ($documentStatusMessage->getStatus() !== $documentSignedStatus)
		{
			return $document->setData([
				$statusColumnKey => $documentStatusMessage->getStatus(),
			]);
		}

		$updatedData = [
			$statusColumnKey => $documentStatusMessage->getStatus(),
		];

		$signDate = $documentStatusMessage->getSignDate();
		if ($signDate !== null)
		{
			$signDate = clone $signDate;
			$signDate->setDefaultTimeZone();

			$updatedData += [
				'DATE_SIGN' => $signDate,
			];
		}

		return $document->setData($updatedData);
	}

	private static function processSaveResultFile(Messages\ResultFile $message, Main\Result $result): void
	{
		$document = DocumentCore::getByHash($message->getDocumentCode());
		if (!$document)
		{
			$result->addError(new Main\Error('Invalid callback token.'));

			return;
		}

		if (!isset($message->getData()['file']))
		{
			$result->addError(new Main\Error('Invalid file data.'));

			return;
		}

		$file = new File($message->getData()['file']);
		$isDone = $document->setResultFile($file);

		if (!$isDone)
		{
			$result->addError(new Main\Error('Failed to save file'));
		}
	}

	private static function processDocumentOperation(Messages\DocumentOperation $message, Main\Result $result): void
	{
		$document = DocumentCore::getByHash($message->getDocumentCode());
		if (!$document)
		{
			$result->addError(new Main\Error('Invalid callback token.'));

			return;
		}

		$member = $document->getMemberByHash($message->getMemberCode());
		if (!$member)
		{
			$result->addError(new Main\Error('Invalid member token.'));

			return;
		}

		$fieldData = $message->getOperationCode() === 'DOCUMENT_SET_USER_DATA' ? ['SIGNED' => 'Y']
			: ['VERIFIED' => 'Y'];
		$isDone = $member->setData($fieldData);

		if (!$isDone)
		{
			$result->addError(new Main\Error('Error handling document status setting.'));
		}
	}

	private static function processHandleReadyLayoutCommand(
		Messages\ReadyLayoutCommand $message,
		Main\Result $result
	): void
	{
		$document = DocumentCore::getByHash($message->getDocumentCode());
		if (!$document)
		{
			$result->addError(new Main\Error('Invalid callback token.'));

			return;
		}

		if (!\Bitrix\Main\Loader::includeModule('pull'))
		{
			$result->addError(new Main\Error('Does not include module pull'));

			return;
		}
		$version = (int)($message->getData()['version'] ?? self::OLD_API_VERSION);

		if ($version === self::OLD_API_VERSION)
		{
			$command = 'layoutIsReady';
			$params = $document->getLayout();
		}
		else
		{
			$command = 'blankIsReady';
			$params = (new Controllers\V1\Document\Pages())->listAction($message->getDocumentCode());
		}

		\Bitrix\Pull\Event::add($document->getModifiedUserId(), [
			'module_id' => 'sign',
			'command' => $command,
			'params' => $params,
		]);
	}
}
