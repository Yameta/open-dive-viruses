<?php

namespace Bitrix\Sign\Access;

use Bitrix\Main\ArgumentException;
use Bitrix\Sign\Access\Rule\Factory\SignRuleFactory;
use Bitrix\Main\Access\AccessibleItem;
use Bitrix\Main\Access\BaseAccessController;
use Bitrix\Main\Access\Event\EventDictionary;
use Bitrix\Main\Access\User\AccessibleUser;
use Bitrix\Sign\Access\Model\UserModel;
use Bitrix\Sign\Contract;
use Bitrix\Sign\Item;

class AccessController extends BaseAccessController
{
	public const RULE_OR = 'OR';
	public const RULE_AND = 'AND';
	private SignRuleFactory $signRuleFactory;
	
	public function __construct($userId)
	{
		parent::__construct($userId);
		$this->signRuleFactory = new SignRuleFactory();
		
	}

	public function checkByItem(string $action, Contract\Item $item, array $params = null): bool
	{
		if (!$item instanceof Contract\Item\ItemWithOwner)
		{
			return false;
		}

		$accessibleItem = new Item\Access\SimpleAccessibleItemWithOwner($item->getId(), $item->getOwnerId());
		return $this->check($action, $accessibleItem, $params);
	}

	/**
	 * Checking access rights by action
	 *
	 * @param string $action
	 * @param AccessibleItem|null $item
	 * @param null $params
	 *
	 * @return bool
	 * @throws ArgumentException
	 */
	public function check(string $action, AccessibleItem $item = null, $params = null): bool
	{
		$ruleObject = $this->signRuleFactory->createFromAction($action ,$this);
		
		if (!$ruleObject)
		{
			throw new ArgumentException($action);
		}
		
		$event    = $this->sendEvent(EventDictionary::EVENT_ON_BEFORE_CHECK, $action, null, $params);
		$isAccess = $event->isAccess();
		
		if (!is_null($isAccess))
		{
			return $isAccess;
		}

		$params['action'] = $action;
		$isAccess = $ruleObject->execute($item, $params);
		
		if($isAccess)
		{
			return true;
		}
		
		$event = $this->sendEvent(EventDictionary::EVENT_ON_AFTER_CHECK,  $action,null, $params, false);

		return $event->isAccess() ?? false;
	}

	protected function loadItem(int $itemId = null): ?AccessibleItem
	{
		return null;
	}

	protected function loadUser(int $userId): AccessibleUser
	{
		return UserModel::createFromId($userId);
	}
}
