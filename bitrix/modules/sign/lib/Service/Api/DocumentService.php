<?php

namespace Bitrix\Sign\Service\Api;

use Bitrix\Main;
use Bitrix\Sign\Contract;
use Bitrix\Sign\Item;
use Bitrix\Sign\Service;

class DocumentService
{
	private Service\ApiService $api;

	private Contract\Serializer $serializer;

	public function __construct(
		Service\ApiService $api,
		Contract\Serializer $serializer
	)
	{
		$this->api = $api;
		$this->serializer = $serializer;
	}

	public function register(Item\Api\Document\RegisterRequest $request): Item\Api\Document\RegisterResponse
	{
		$result = new Main\Result();
		if ($request->lang === '')
		{
			$result->addError(new Main\Error('Request: field `lang` is empty'));
		}

		if ($result->isSuccess())
		{
			$result = $this->api->post(
				'v1/document.register',
				$this->serializer->serialize($request)
			);
		}
		$data = $result->getData();
		$response = new Item\Api\Document\RegisterResponse(
			(string) ($data['id'] ?? '')
		);

		$response->addErrors($result->getErrors());
		if (!$response->isSuccess())
		{
			return $response;
		}

		if ($response->uid === '')
		{
			return $response->addError(new Main\Error('Empty document id'));
		}

		return $response;
	}

	public function upload(Item\Api\Document\UploadRequest $request): Item\Api\Document\UploadResponse
	{
		$result = new Main\Result();
		if ($request->uid === '')
		{
			$result->addError(new Main\Error('Request: field `uid` is empty'));
		}

		if ($result->isSuccess())
		{
			$result = $this->api->post(
				"v1/document.upload/$request->uid/",
				$this->serializer->serialize($request)
			);
		}
		$response = new Item\Api\Document\UploadResponse();

		return $response->addErrors($result->getErrors());
	}
}