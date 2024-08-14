<?php

namespace Bitrix\Sign\Controller;

use Bitrix\Main;
use Bitrix\Sign\Callback\Handler;
use Bitrix\Sign\Engine;

class Callback extends Controller
{
	public function getDefaultPreFilters(): array
	{
		return [
			new Engine\ActionFilter\ClientAuth()
		];
	}

	public function handleAction(array $payload): array
	{
		Main\Application::getInstance()->addBackgroundJob(function() use ($payload) {
			Handler::execute($payload);
		});

		return [];
	}
}