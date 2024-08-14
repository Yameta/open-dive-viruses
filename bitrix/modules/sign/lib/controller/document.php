<?php
namespace Bitrix\Sign\Controller;

use Bitrix\Crm\Integration\Sign\Form;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\Engine\Response\BFile;
use Bitrix\Main\Response;
use Bitrix\Main\Error;
use Bitrix\Sign\Document as DocumentCore;
use Bitrix\Sign\Document\Status;
use Bitrix\Sign\File;
use Bitrix\Sign\Main\Application;
use Bitrix\Sign\Proxy;

class Document extends Controller
{
	public function getDefaultPreFilters(): array
	{
		return [
		];
	}

	/**
	 * Mark member as signed.
	 * @param string $documentHash Document hash.
	 * @param string $memberHash Member hash.
	 * @param string $secCode Document security code.
	 * @return bool
	 */
	public function signMarkMemberAction(string $documentHash, string $memberHash, string $secCode): bool
	{
		$document = DocumentCore::getByHash($documentHash);
		if ($document)
		{
			if ($document->getSecCode() === $secCode)
			{
				if ($member = $document->getMemberByHash($memberHash))
				{
					return $member->setData(['SIGNED' => 'Y']);
				}
			}
		}

		return false;
	}

	/**
	 * Mark member as verified.
	 * @param string $documentHash Document hash.
	 * @param string $memberHash Member hash.
	 * @param string $secCode Document security code.
	 * @return bool
	 */
	public function verifyMarkMemberAction(string $documentHash, string $memberHash, string $secCode): bool
	{
		$document = DocumentCore::getByHash($documentHash);
		if ($document)
		{
			if ($document->getSecCode() === $secCode)
			{
				if ($member = $document->getMemberByHash($memberHash))
				{
					return $member->setData(['VERIFIED' => 'Y']);
				}
			}
		}

		return false;
	}

	/**
	 * Sets processing status for document.
	 * @param string $documentHash Document hash.
	 * @param string $status Status code.
	 * @param string $secCode Document security code.
	 * @return bool
	 */
	public function setProcessingStatusAction(string $documentHash, string $status, string $secCode): bool
	{
		$document = DocumentCore::getByHash($documentHash);
		if ($document)
		{
			if ($document->getSecCode() === $secCode)
			{
				return $document->setData([
					'PROCESSING_STATUS' => $status
				]);
			}
		}

		return false;
	}

	/**
	 * Saves new title to Document.
	 *
	 * @param int $documentId Document id.
	 * @param string $title New title.
	 * @return bool
	 */
	public function setTitleAction(int $documentId, string $title): bool
	{
		$document = DocumentCore::getById($documentId);
		if ($document)
		{
			return $document->setTitle($title);
		}

		return false;
	}

	/**
	 * Gets document for Rendering
	 *
	 * @param string $documentHash Document hash.
	 * @param string|null $memberHash Member hash.
	 * @return Response
	 */
	public function getFileForSrcAction(string $documentHash, ?string $memberHash = null): Response
	{
		$response = \Bitrix\Main\Context::getCurrent()->getResponse();
		$document = DocumentCore::getByHash($documentHash);
		if ($document)
		{

			$data = [
				'documentHash' => $document->getHash(),
				'secCode' => $document->getSecCode(),
			];

			if ($memberHash)
			{
				$data['memberHash'] = $memberHash;
			}
			$fileToken = Proxy::sendCommand('document.file.getFileToken', $data);

			if ($fileToken)
			{
				$this->prepareFileResponse($document, $fileToken);
				return $response;
			}

			if (!$memberHash)
			{
				$file = $document->getResultFile();
				if ($file && $file->isExist())
				{
					\CFile::viewByUser($file->getId(), [
						'attachment_name' => $this->getDocumentFilenameForResponse($document),
					]);
				}

				return $response;
			}
		}

		return $response;
	}

	/**
	 * Download result file for document by its hash
	 *
	 * @param string $documentHash
	 * @return BFile|array
	 */
	public function downloadResultFileAction(string $documentHash): BFile | array
	{
		$document = DocumentCore::getByHash($documentHash);
		if (!$document)
		{
			$this->addError(new Error('Document not found'));
			return [];
		}

		if ($document->getProcessingStatus() !== Status::READY)
		{
			$this->addError(new Error('Wrong status'));
			return [];
		}

		$file = $document->getResultFile();
		if (!$file || !$file->isExist())
		{
			$this->addError(new Error('No result file'));
			return [];
		}

		return BFile::createByFileId(
			$file->getId(),
			$this->getDocumentFilenameForResponse($document)
		)->showInline(false);
	}

	/**
	 * Creates document blank from file. $_FILE with key 'file' must contain 'name' equal document hash.
	 * @param string $documentHash Document hash.
	 * @param string $secCode Document security code.
	 * @return bool
	 */
	public function setResultFileAction(string $documentHash, string $secCode): bool
	{
		$document = DocumentCore::getByHash($documentHash);
		if ($document)
		{
			if ($document->getSecCode() === $secCode)
			{
				$file = new File(Application::getFileList()->get('file'));
				return $document->setResultFile($file);
			}
		}

		return false;
	}

	/**
	 * Method will ping if document layout is ready.
	 *
	 * @param string $documentHash Document hash.
	 * @param string $secCode Document security code.
	 * @return void
	 */
	public function layoutIsReadyAction(string $documentHash, string $secCode): void
	{
		$document = DocumentCore::getByHash($documentHash);
		if ($document && ($document->getSecCode() === $secCode))
		{
			if (\Bitrix\Main\Loader::includeModule('pull'))
			{
				\Bitrix\Pull\Event::add($document->getModifiedUserId(), [
					'module_id' => 'sign',
					'command' => 'layoutIsReady',
					'params' => $document->getLayout(),
				]);
			}
		}
	}

	public function restoreRequisiteFieldsAction(string $presetId, string $code): void
	{
		Form::restoreDefaultFieldSet(
			$code === 'myrequisites' ? \CCrmOwnerType::Company : \CCrmOwnerType::Contact,
			$presetId
		);
	}

	/**
	 * @param DocumentCore $document
	 * @param $fileToken
	 * @throws ArgumentNullException
	 * @throws ArgumentTypeException
	 */
	private function prepareFileResponse(DocumentCore $document, $fileToken)
	{
		$url = Proxy::getCommandUrl('document.file.getFileContent', [
			'data' => [
				'documentHash' => $document->getHash(),
				'fileToken' => $fileToken,
			]
		]);

		$response = \Bitrix\Main\Application::getInstance()->getContext()->getResponse();
		$response->addHeader('Location', $url);
	}

	private function getDocumentFilenameForResponse(DocumentCore $document): string
	{
		return 'Smart_Document_' . $document->getEntityId() . '.pdf';
	}
}
