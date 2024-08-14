<?php
namespace Bitrix\Sign\Engine\ActionFilter;

use Bitrix\Main;
use Bitrix\Sign\Access\AccessController;

final class AccessCheck extends Main\Engine\ActionFilter\Base
{
	private const ERROR_INVALID_AUTHENTICATION = 'invalid_authentication';

	private AccessController $accessController;
	public function __construct(
		private string $accessPermission,
	)
	{
		parent::__construct();
		$this->accessController = new AccessController(Main\Engine\CurrentUser::get()->getId());
	}

	public function onBeforeAction(Main\Event $event): ?Main\EventResult
	{
		if (!$this->accessController->check($this->accessPermission))
		{
			return $this->getAuthErrorResult();
		}

		return null;
	}

	private function getAuthErrorResult(): Main\EventResult
	{
		Main\Context::getCurrent()->getResponse()->setStatus(401);
		$this->addError(new Main\Error(
				Main\Localization\Loc::getMessage("MAIN_ENGINE_FILTER_AUTHENTICATION_ERROR"),
				self::ERROR_INVALID_AUTHENTICATION)
		);
		return new Main\EventResult(Main\EventResult::ERROR, null, null, $this);
	}
}