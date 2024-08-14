<?php

namespace Bitrix\Sign\Callback\Messages;

use Bitrix\Sign\Callback\Message;

class Factory
{
	public static function createMessage(string $type, array $data): Message
	{
		$message = match($type)
		{
			DocumentStatus::Type => new DocumentStatus(),
			TimelineEvent::Type => new TimelineEvent(),
			ResultFile::Type => new ResultFile(),
			ReadyLayoutCommand::Type => new ReadyLayoutCommand(),
			DocumentOperation::Type => new DocumentOperation(),
			FieldSet::Type => new FieldSet(),
			default => new Message(),
		};

		return $message->setData($data);
	}
}
