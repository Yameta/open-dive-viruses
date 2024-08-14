<?php

namespace Bitrix\Sign\Operation;

use Bitrix\Crm\Service\Container;
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sign\Connector\Field\CrmEntity;
use Bitrix\Sign\Connector\MemberConnectorFactory;
use Bitrix\Sign\Contract\RequisiteConnector;
use Bitrix\Sign\Integration\CRM;
use Bitrix\Sign\Item;
use Bitrix\Sign\Item\Api\Document\Signing\ConfigureRequest;
use Bitrix\Sign\Item\Api\Property\Request\Signing\Configure\Block;
use Bitrix\Sign\Item\Api\Property\Request\Signing\Configure\BlockCollection;
use Bitrix\Sign\Item\Api\Property\Request\Signing\Configure\FieldCollection;
use Bitrix\Sign\Item\Api\Property\Request\Signing\Configure\Member;
use Bitrix\Sign\Item\Api\Property\Request\Signing\Configure\MemberCollection;
use Bitrix\Sign\Item\Api\Property\Request\Signing\Configure\Owner;
use Bitrix\Sign\Repository\FileRepository;
use Bitrix\Sign\Service;
use Bitrix\Sign\Type\BlockCode;
use Bitrix\Sign\Type\DocumentStatus;
use Bitrix\Sign\Type\Field\ConnectorType;
use Bitrix\Sign\Type\Field\EntityType;
use Bitrix\Sign\Type\FieldType;
use Bitrix\Sign\Type\Member\ChannelType;
use Bitrix\Main\PhoneNumber;

class ConfigureDocument
{
	private const BLOCK_CODE_TO_FIELD_TYPE_MAP = [
		BlockCode::DATE => FieldType::STRING,
		BlockCode::TEXT => FieldType::STRING,
		BlockCode::NUMBER => FieldType::STRING,
		BlockCode::MY_STAMP => FieldType::STAMP,
		BlockCode::STAMP => FieldType::STAMP,
		BlockCode::MY_SIGN => FieldType::SIGNATURE,
		BlockCode::SIGN => FieldType::SIGNATURE,
	];
	private const SIGNATURE_BLOCK_CODES = [
		BlockCode::SIGN,
		BlockCode::MY_SIGN,
	];
	private const STAMP_BLOCK_CODES = [
		BlockCode::STAMP,
		BlockCode::MY_STAMP,
	];
	private const FIELD_NAME_HASH_SALT = 'FIELD_NAME_HASH_SALT_111';
	private const ADDRESS_SUBFIELD_CODES = [
		'ADDRESS_1',
		'ADDRESS_2',
		'CITY',
		'POSTAL_CODE',
		'REGION',
		'PROVINCE',
		'COUNTRY',
	];
	private const REQUIRED_ADDRESS_SUBFIELDS = [
		'ADDRESS_1',
		'CITY',
		'POSTAL_CODE'
	];

	private FileRepository $fileRepository;
	private MemberConnectorFactory $memberConnectorFactory;

	public function __construct(
		private string $uid,
		?MemberConnectorFactory $memberConnectorFactory = null,
	)
	{
		$this->fileRepository = Service\Container::instance()->getFileRepository();
		$this->memberConnectorFactory = $memberConnectorFactory ?? Service\Container::instance()->getMemberConnectorFactory();
	}

	public function launch(): Main\Result
	{
		$document = Service\Container::instance()->getDocumentService()->getByUid($this->uid);
		if (!$document)
		{
			return (new Main\Result())->addError(new Main\Error(Loc::getMessage('SIGN_OPERATION_DOCUMENT_NOT_FOUND')));
		}
		if ($document->blankId === null)
		{
			return (new Main\Result())->addError(new Main\Error('Document doesnt contains blank'));
		}

		$members = Service\Container::instance()->getMemberRepository()->listByDocumentId($document->id);

		$memberCollection = new MemberCollection();
		$owner = new Owner($document->initiator);
		foreach ($members as $member)
		{
			$memberChannelValue = $member->channelValue;
			if ($member->channelType === ChannelType::PHONE)
			{
				$memberChannelValue = PhoneNumber\Parser::getInstance()
					->parse($member->channelValue)
					->format(PhoneNumber\Format::E164);
			}

			if (
				!$owner->channelType
				&& $member->party === 1
				&& $member->entityType === \Bitrix\Sign\Type\Member\EntityType::COMPANY
			)
			{
				$company = Container::getInstance()
					->getFactory(\CCrmOwnerType::Company)
					->getItem($member->entityId)
				;

				$owner = new Owner(
					name: $document->initiator,
					companyName: $company?->getTitle(),
					channelType: $member->channelType,
					channelValue: $memberChannelValue
				);
			}

			$memberCollection->addItem(new Member(
				party: $member->party,
				channel: new Member\Channel(
					type: $member->channelType, value: $memberChannelValue,
				),
				uid: $member->uid,
				name: $this->getMemberName($member),
			));
		}

		$memberFieldsCollection = Service\Container::instance()->getSignBlockService()->loadBlocksAndDataByDocument($document);
		if (!$memberFieldsCollection->isSuccess())
		{
			return $memberFieldsCollection;
		}
		$blocks = $memberFieldsCollection->getBlocks();
		if ($blocks === null)
		{
			return (new Main\Result())->addError(new Main\Error('Blocks doesnt loaded'));
		}

		$requiredFields = $this->createRequiredFields($document);
		$requestBlocks = new BlockCollection();
		$registeredFields = new Item\FieldCollection(...$requiredFields);
		$requestFields = new FieldCollection(...array_map(
			fn ($field) => Item\Api\Property\Request\Signing\Configure\Field::createFromFieldItem($field),
			$requiredFields->toArray(),
		));

		foreach ($blocks as $block)
		{
			$requestBlock = new Block(
				party: $block->party,
				type: $block->type,
				blockPosition: Block\BlockPosition::createFromBlockItemPosition($block->position)
			);
			$requestBlock->style = $block->style === null
				? null
				: Block\BlockStyle::createFromBlockItemStyle($block->style)
			;
			$memberByParty = $members->findFirst(fn (Item\Member $member) => $member->party === $block->party);
			if ($memberByParty === null && $block->party !== 0)
			{
				return (new Main\Result())->addError(new Main\Error("Block has party: `{$block->party}` but member with party: `{$block->party}` doesnt exist"));
			}

			$fields = $this->getOrCreateFields($block, $memberByParty, $document, $registeredFields);
			foreach ($fields as $field)
			{
				$requestBlock->addFieldNames($field->name);
				if (!$requestFields->existWithName($field->name))
				{
					$requestFields->addItem(
						Item\Api\Property\Request\Signing\Configure\Field::createFromFieldItem($field)
					);
					$registeredFields->add($field);
				}
			}

			$requestBlocks->addItem(
				$requestBlock
			);
		}

		$response = Service\Container::instance()->getApiDocumentSigningService()->configure(
			new ConfigureRequest(
				documentUid: $document->uid,
				title: $document->title,
				owner: $owner,
				parties: $document->parties,
				scenario: $document->scenario,
				fields: $requestFields,
				blocks: $requestBlocks,
				members: $memberCollection,
				langId: $document->langId
			)
		);

		if (!$response->isSuccess())
		{
			return (new Main\Result())->addErrors($response->getErrors());
		}
		$memberFieldsCollection = new Item\Api\Property\Request\Field\Fill\MemberFieldsCollection();
		foreach ($registeredFields as $registeredField)
		{
			$value = $registeredField?->values?->getFirst();
			if ($value === null)
			{
				continue;
			}
			$fieldValue = null;
			if ($value->text !== null)
			{
				$fieldValue = new Item\Api\Property\Request\Field\Fill\Value\StringFieldValue($value->text);
			}
			elseif ($value->fileId !== null)
			{
				$file = $this->fileRepository->getById($value->fileId, true);
				if ($file === null)
				{
					continue;
				}

				$fieldValue = new Item\Api\Property\Request\Field\Fill\Value\FileFieldValue($file->type, base64_encode($file->content->data));
			}
			if ($fieldValue === null)
			{
				continue;
			}
			$memberFieldsCollection->addItem(new Item\Api\Property\Request\Field\Fill\MemberFields(
				$members->findFirstByParty($registeredField->party)?->uid ?? '',
				new Item\Api\Property\Request\Field\Fill\FieldCollection(
					new Item\Api\Property\Request\Field\Fill\Field(
						$registeredField->name,
						new Item\Api\Property\Request\Field\Fill\FieldValuesCollection($fieldValue),
					)
				)
			));
		}
		// fill address subfields
		foreach ($registeredFields as $registeredField)
		{
			foreach ($registeredField->subfields ?? new Item\FieldCollection() as $subfield)
			{
				$value = $subfield?->values?->getFirst();
				if ($value === null)
				{
					continue;
				}

				$fieldValue = new Item\Api\Property\Request\Field\Fill\Value\StringFieldValue($value->text);
				$memberFieldsCollection->addItem(
					new Item\Api\Property\Request\Field\Fill\MemberFields(
						$members->findFirstByParty($subfield->party)?->uid ?? '',
						new Item\Api\Property\Request\Field\Fill\FieldCollection(
							new Item\Api\Property\Request\Field\Fill\Field(
								$subfield->name,
								new Item\Api\Property\Request\Field\Fill\FieldValuesCollection($fieldValue),
							)
						)
					)
				);
			}
		}

		$result = Service\Container::instance()->getApiDocumentFieldService()->fill(
			new Item\Api\Document\Field\FillRequest(
				$document->uid,
				$memberFieldsCollection
			)
		);
		if (!$result->isSuccess())
		{
			return (new Main\Result())->addErrors($result->getErrors());
		}

		$document->status = DocumentStatus::READY;
		Service\Container::instance()->getDocumentRepository()->update($document);

		return new Main\Result();
	}

	private function getOrCreateFields(Item\Block $block, ?Item\Member $member, Item\Document $document, Item\FieldCollection $registeredFields): Item\FieldCollection
	{
		$fieldType = self::BLOCK_CODE_TO_FIELD_TYPE_MAP[$block->code] ?? null;
		if ($fieldType === null)
		{
			if ($member === null)
			{
				return new Item\FieldCollection();
			}
			return match ($block->code)
			{
				BlockCode::REFERENCE, BlockCode::MY_REFERENCE => $this->createCrmReferenceFields($block, $member,
					$document, $registeredFields),
				BlockCode::REQUISITES, BlockCode::MY_REQUISITES => $this->createRequisiteFields($block, $member,
					$document, $registeredFields),
			};
		}
		$value = null;

		if (in_array($block->code, self::SIGNATURE_BLOCK_CODES, true))
		{
			$signField = $registeredFields->findFirst(
				fn (Item\Field $field) => $field->type === FieldType::SIGNATURE && $field->party === $block->party
			);
			if ($signField !== null)
			{
				return new Item\FieldCollection($signField);
			}
			$valueFileId = $block->data['fileId'] ?? null;
			if ($valueFileId !== null)
			{
				$value = new Item\Field\Value(
					0,
					fileId: $valueFileId,
				);
			}
		}
		elseif (in_array($block->code, self::STAMP_BLOCK_CODES, true))
		{
			$stampField = $registeredFields->findFirst(
				fn (Item\Field $field) => $field->type === FieldType::STAMP && $field->party === $block->party
			);
			if ($stampField !== null)
			{
				return new Item\FieldCollection($stampField);
			}
			$valueFileId = $block->data['fileId'] ?? null;
			if ($valueFileId !== null)
			{
				$value = new Item\Field\Value(
					0,
					fileId: $valueFileId,
				);
			}
		}
		elseif ($block->code === BlockCode::NUMBER)
		{
			$numField = $registeredFields->findFirst(
				fn (Item\Field $field) => $this->parseFieldName($field->name)['blockCode'] === BlockCode::NUMBER
			);
			if ($numField !== null)
			{
				return new Item\FieldCollection($numField);
			}
			$valueString = $block->data['text'] ?? null;
			if (is_string($valueString))
			{
				$value = new Item\Field\Value(0, text: $valueString);
			}
		}
		elseif (in_array($block->code, [BlockCode::DATE, BlockCode::TEXT], true))
		{
			$valueString = $block->data['text'] ?? null;
			if (is_string($valueString))
			{
				$value = new Item\Field\Value(0, text: $valueString);
			}
		}

		$valueCollection = new Item\Field\ValueCollection();
		if ($value !== null)
		{
			$valueCollection->add($value);
		}


		$fieldName = $this->createFieldName($block->code, $fieldType, $block->party, fieldCode: null);
		return new Item\FieldCollection(new Item\Field(
			0,
			$block->party,
			$fieldType,
			$fieldName,
			label: null, // simple blocks doesnt contains label because it not include in form
			connectorType: '',
			values: $valueCollection
		));
	}

	private function createRequiredFields(Item\Document $document): Item\FieldCollection
	{
		$result = new Item\FieldCollection();
		foreach (range(1, $document->parties) as $party)
		{
			$result->add(
				new Item\Field(
					0,
					$party,
					FieldType::SIGNATURE,
					$this->createFieldName(
						$party === 1 ? BlockCode::MY_SIGN : BlockCode::SIGN,
						FieldType::SIGNATURE,
						$party,
						null
					),
					label: null,
					connectorType: ''
				)
			);
		}
		return $result;
	}

	private function createFieldName(string $blockCode, string $fieldType, int $party, ?string $fieldCode, ?string $subfieldCode = null): string
	{
		$fieldCode ??= md5(time() . self::FIELD_NAME_HASH_SALT . Main\Security\Random::getString(32)) . time();

		return "$fieldCode.$fieldType.$blockCode.$party.$subfieldCode";
	}

	/**
	 * @return array{fieldCode: string, fieldType: string, blockCode: string, party: int, subfieldCode: string}
	 * @see static::createFieldName
	 */
	private function parseFieldName(string $fieldName): array
	{
		$data = explode(".", $fieldName);
		return [
			'fieldCode' => $data[0] ?? '',
			'fieldType' => $data[1] ?? '',
			'blockCode' => $data[2] ?? '',
			'party' => (int)($data[3] ?? -1),
			'subfieldCode' => $data[4] ?? ''
		];
	}

	private function createCrmReferenceFields(Item\Block $block, Item\Member $member, Item\Document $document, Item\FieldCollection $registeredFields): Item\FieldCollection
	{
		$fieldCode = $block->data['field'] ?? '';
		if (!is_string($fieldCode))
		{
			return new Item\FieldCollection();
		}

		$fieldCode = new CRM\FieldCode($fieldCode);
		$fieldDescription = $fieldCode->getDescription();
		$fieldType = match ($fieldDescription['TYPE'])
		{
			'date', 'datetime' => FieldType::DATE,
			'list' => FieldType::LIST,
			'double' => FieldType::DOUBLE,
			'integer' => FieldType::INTEGER,
			default => FieldType::STRING,
		};
		$itemsDescription = $fieldDescription['ITEMS'] ?? null;
		$items = null;
		if ($itemsDescription !== null)
		{
			$items = new Item\Field\ItemCollection();
			foreach ($itemsDescription as $itemDescription)
			{
				$items->add(
					new Item\Field\Item(
						id: $itemDescription['ID'], value: $itemDescription['VALUE']
					)
				);
			}
		}

		$fieldName = $this->createFieldName($block->code, $fieldType, $block->party, $fieldCode->getCode());

		$field = $registeredFields->findFirst(fn (Item\Field $field) => $field->party === $block->party && $field->name === $fieldName);

		if ($field !== null)
		{
			return new Item\FieldCollection($field);
		}

		$field = new Item\Field(
			0,
			$block->party,
			$fieldType,
			$fieldName,
			$fieldDescription['CAPTION'] ?? null,
			connectorType: ConnectorType::CRM_ENTITY,
			// todo: dont use \CCrmOwnerType
			entityType: $fieldCode->getEntityTypeName() === \CCrmOwnerType::SmartDocumentName
				? EntityType::DOCUMENT
				: EntityType::MEMBER
			,
			entityCode: $fieldCode->getEntityFieldCode(),
			values: new Item\Field\ValueCollection(),
			items: $items,
		);
		$connector = new CrmEntity($field, $member);
		$fetchedFieldValue = $connector->fetchFields()->getFirst();
		if ($fetchedFieldValue === null)
		{
			return new Item\FieldCollection($field);
		}
		$value = match(true)
		{
			$fetchedFieldValue->data instanceof Main\Type\DateTime
			|| is_string($fetchedFieldValue->data)
			|| is_int($fetchedFieldValue->data)
			|| is_float($fetchedFieldValue->data) => new Item\Field\Value(0, text: (string)$fetchedFieldValue->data),
			default => null,
		};
		if ($value === null)
		{
			return new Item\FieldCollection($field);
		}
		$field->values->add($value);

		return new Item\FieldCollection($field);
	}

	private function createRequisiteFields(Item\Block $block, Item\Member $member, Item\Document $document, Item\FieldCollection $registeredFields): Item\FieldCollection
	{
		$memberConnector = (new MemberConnectorFactory())->create($member);
		if (!$memberConnector instanceof RequisiteConnector)
		{
			return new Item\FieldCollection();
		}

		$result = new Item\FieldCollection();

		$requisiteFields = $memberConnector->fetchRequisite(
			new Item\Connector\FetchRequisiteModifier($member->presetId)
		);
		foreach ($requisiteFields as $requisiteField)
		{
			$fieldCode = new CRM\FieldCode($requisiteField->name);
			$fieldDescription = $fieldCode->getDescription();

			$fieldType = match ($fieldDescription['TYPE'])
			{
				'date', 'datetime' => FieldType::DATE,
				'list' => FieldType::LIST,
				'double' => FieldType::DOUBLE,
				'integer' => FieldType::INTEGER,
				'address' => FieldType::ADDRESS,
				default => FieldType::STRING,
			};
			$itemsDescription = $fieldDescription['ITEMS'] ?? null;
			$items = null;
			if ($itemsDescription !== null)
			{
				$items = new Item\Field\ItemCollection();
				foreach ($itemsDescription as $itemDescription)
				{
					$items->add(
						new Item\Field\Item(
							id: $itemDescription['ID'], value: $itemDescription['VALUE']
						)
					);
				}
			}

			$fieldName = $this->createFieldName($block->code, $fieldType, $member->party, $requisiteField->name);
			$value = match (true)
			{
				$requisiteField->value instanceof Main\Type\DateTime
				|| is_string($requisiteField->value)
				|| is_int($requisiteField->value)
				|| is_float($requisiteField->value) => new Item\Field\Value(0, text: (string)$requisiteField->value),
				default => null,
			};
			$field = new Item\Field(
				0,
				$member->party,
				$fieldType,
				$fieldName,
				$requisiteField->label,
				ConnectorType::REQUISITE,
				// todo: dont use \CCrmOwnerType
				$fieldCode->getEntityTypeName() === \CCrmOwnerType::SmartDocumentName ? EntityType::DOCUMENT
					: EntityType::MEMBER,
				values: new Item\Field\ValueCollection(),
				items: $items,
			);

			if ($value !== null)
			{
				$field->values->add($value);
			}
			if ($field->type === FieldType::ADDRESS)
			{
				$field->subfields = $this->createAddressSubfieldsByField($field, $member);
			}

			$result->add($field);
		}

		return $result;
	}

	private function createAddressSubfieldsByField(Item\Field $field, Item\Member $member): Item\FieldCollection
	{
		$result = new Item\FieldCollection();
		$parsedParentFieldName = $this->parseFieldName($field->name);
		$addressFieldValues = $this->getFieldSetValues($member)[$parsedParentFieldName['fieldCode']] ?? [];

		foreach (static::ADDRESS_SUBFIELD_CODES as $addressSubfieldCode)
		{
			$field = new Item\Field(
				0,
				$field->party,
				FieldType::STRING, // all address subfields has string type
				$this->createFieldName(
					$parsedParentFieldName['blockCode'],
					$parsedParentFieldName['fieldType'],
					$parsedParentFieldName['party'],
					$parsedParentFieldName['fieldCode'],
					$addressSubfieldCode
				),
				$this->getAddressFieldLabels()[$addressSubfieldCode] ?? null,
				values: new Item\Field\ValueCollection(),
			);

			$field->required = in_array($addressSubfieldCode, static::REQUIRED_ADDRESS_SUBFIELDS, true);

			$subfieldValue = (string)($addressFieldValues[$addressSubfieldCode] ?? null);
			if ($subfieldValue !== '')
			{
				$field->values->add(
					new Item\Field\Value(
						0,
						text: $subfieldValue,
					)
				);
			}

			$result->add($field);
		}

		return $result;
	}

	private function getAddressFieldLabels(): array
	{
		// todo: encapsulate it to another class
		if (!Main\Loader::includeModule('crm'))
		{
			return [];
		}

		return \Bitrix\Crm\EntityAddress::getLabels();
	}

	/**
	 * @param Item\Member $member
	 *
	 * @return array<string, array>
	 */
	private function getFieldSetValues(Item\Member $member): array
	{
		if (!Main\Loader::includeModule('crm'))
		{
			return [];
		}

		return \Bitrix\Crm\Integration\Sign\Form::getFieldSetValues(
			match($member->entityType)
			{
				\Bitrix\Sign\Type\Member\EntityType::CONTACT => \CCrmOwnerType::Contact,
				\Bitrix\Sign\Type\Member\EntityType::COMPANY => \CCrmOwnerType::Company,
				default => 0,
			},
			$member->entityId,
			requisitePresetId: $member->presetId,
		);
	}

	private function getMemberName(Item\Member $member): ?string
	{
		$fields = $this->memberConnectorFactory->create($member)?->fetchFields() ?? new Item\Connector\FieldCollection();
		$fullName = $fields->getFirstByName('FULL_NAME')?->data;
		$title = $fields->getFirstByName('TITLE')?->data;

		return match($member->entityType)
		{
			\Bitrix\Sign\Type\Member\EntityType::COMPANY => $title === null ? null : (string)$title,
			\Bitrix\Sign\Type\Member\EntityType::CONTACT => $fullName === null ? null : (string)$fullName,
			default => null,
		};
	}
}