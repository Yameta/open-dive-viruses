<?php
namespace Bitrix\Sign\Integration\CRM\Model;

use Bitrix\Sign\Document;

class EventData
{
	public const NOTIFICATION_DELIVERED = 'ON_NOTIFICATION_DELIVERED';
	public const NOTIFICATION_READ = 'ON_NOTIFICATION_READ';
	public const NOTIFICATION_ERROR = 'ON_NOTIFICATION_ERROR';
	private string $eventType;
	private ?Document $document = null;
	private ?Document\Member $member = null;
	private array $data = [];
	public const TYPE_ON_CREATE = 'ON_CREATE';
	public const TYPE_ON_SEND = 'ON_SEND';
	public const TYPE_ON_RESEND = 'ON_RESEND';
	public const TYPE_ON_VIEW = 'ON_VIEW';
	public const TYPE_ON_REQUEST_RESULT = 'ON_REQUEST_RESULT';
	public const TYPE_ON_SIGN = 'ON_SIGN';
	public const TYPE_ON_SIGN_COMPLETED = 'ON_SIGN_COMPLETED';
	public const TYPE_ON_SEND_FINAL = 'ON_SEND_FINAL';
	public const TYPE_ON_FILL = 'ON_FILL';
	public const TYPE_ON_PREPARE_TO_FILL = 'ON_PREPARE_TO_FILL';
	public const TYPE_ON_MESSAGE_STATUS_CHANGE = 'ON_MESSAGE_STATUS_CHANGE';
	public const TYPE_ON_COMPLETE = 'ON_COMPLETE';
	public const TYPE_ON_SEND_INTEGRITY_FAILURE_NOTICE = 'ON_SEND_INTEGRITY_FAILURE_NOTICE';
	public const TYPE_ON_SEND_REPEATEDLY = 'ON_SEND_REPEATEDLY';
	public const TYPE_ON_INTEGRITY_SUCCESS = 'ON_INTEGRITY_SUCCESS';
	public const TYPE_ON_REGISTER = 'ON_REGISTER';
	public const TYPE_ON_PIN_SEND_LIMIT_REACHED = 'ON_PIN_SEND_LIMIT_REACHED';

	/**
	 * @return string
	 */
	public function getEventType(): string
	{
		return $this->eventType;
	}

	/**
	 * @param string $eventType
	 * @return EventData
	 */
	public function setEventType(string $eventType): EventData
	{
		$this->eventType = $eventType;
		return $this;
	}

	/**
	 * @return Document|null
	 */
	public function getDocument(): ?Document
	{
		return $this->document;
	}

	/**
	 * @param Document|null $document
	 * @return EventData
	 */
	public function setDocument(?Document $document): EventData
	{
		$this->document = $document;
		return $this;
	}

	/**
	 * @return Document\Member|null
	 */
	public function getMember(): ?Document\Member
	{
		return $this->member;
	}

	/**
	 * @param Document\Member|null $member
	 * @return EventData
	 */
	public function setMember(?Document\Member $member): EventData
	{
		$this->member = $member;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getData(): array
	{
		return $this->data;
	}
	
	public function addDataValue(string $key, $value): EventData
	{
		$this->data[$key] = $value;
		return $this;
	}
}
