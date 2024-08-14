<?php

use Bitrix\Sign\Service;
use Bitrix\Sign\Config;
use Bitrix\Sign\Connector;
use Bitrix\Sign\Repository;

return [
	'controllers' => [
		'value' => [
			'namespaces' => [
				'\\Bitrix\\Sign\\Controller' => 'api',
				'\\Bitrix\\Sign\\Controllers\\V1' => 'api_v1',
			],
			'defaultNamespace' => '\\Bitrix\\Sign\\Controller'
		],
		'readonly' => true
	],
	'ui.uploader' => [
		'value' => [
			'allowUseControllers' => true,
		],
		'readonly' => true,
	],
	'service.address' => [
		'value' => [
			'by' => 'https://sign.bitrix24.ru',
			'ru' => 'https://sign.bitrix24.ru',

			'eu' => 'https://sign.bitrix24.eu',
			'de' => 'https://sign.bitrix24.eu',
			'fr' => 'https://sign.bitrix24.eu',
			'it' => 'https://sign.bitrix24.eu',
			'pl' => 'https://sign.bitrix24.eu',
			'uk' => 'https://sign.bitrix24.eu',
			'ur' => 'https://sign.bitrix24.eu',

			'us' => 'https://sign.bitrix24.com',
			'en' => 'https://sign.bitrix24.com',
			'br' => 'https://sign.bitrix24.com',
			'la' => 'https://sign.bitrix24.com',
			'tr' => 'https://sign.bitrix24.com',
			'jp' => 'https://sign.bitrix24.com',
			'tc' => 'https://sign.bitrix24.com',
			'sc' => 'https://sign.bitrix24.com',
			'hi' => 'https://sign.bitrix24.com',
			'vn' => 'https://sign.bitrix24.com',
			'id' => 'https://sign.bitrix24.com',
			'ms' => 'https://sign.bitrix24.com',
			'th' => 'https://sign.bitrix24.com',
			'cn' => 'https://sign.bitrix24.com',
			'in' => 'https://sign.bitrix24.com',
			'co' => 'https://sign.bitrix24.com',
			'mx' => 'https://sign.bitrix24.com',
		],
		'readonly' => true,
	],
	'service.publicity' => [
		'value' => [
			'ru' => true,
			'by' => true,
			'eu' => true,
			'de' => true,
			'fr' => true,
			'it' => true,
			'pl' => true,
			'uk' => true,
			'ur' => true,
			'us' => true,
			'en' => true,
			'br' => true,
			'la' => true,
			'tr' => true,
			'jp' => true,
			'tc' => true,
			'sc' => true,
			'hi' => true,
			'vn' => true,
			'id' => true,
			'ms' => true,
			'th' => true,
			'cn' => true,
			'in' => true,
			'co' => true,
			'mx' => true,
		],
		'readonly' => true,
	],
	'service.doc.link' => [
		'value' => '#address#/#doc_hash#/#member_hash#/',
		'readonly' => true,
	],
	'services' => [
		'value' => [
			'sign.service.integration.crm.document' => [
				'className' => '\\Bitrix\\Sign\\Service\\Integration\\Crm\\BaseDocumentService',
				'constructorParams' => static function() {
					return [
						'signDocumentService' => Service\Container::instance()->getDocumentService(),
						'blankService' => Service\Container::instance()->getSignBlankService(),
						'memberService' => Service\Container::instance()->getMemberService(),
					];
				},
			],
			'sign.service.integration.crm.events' => [
				'className' => '\\Bitrix\\Sign\\Service\\Integration\\Crm\\EventHandlerService',
			],
			'sign.container' => [
				'className' => Service\Container::class,
			],
			'sign.service.api' => [
				'className' => Service\ApiService::class,
				'constructorParams' => static function() {
					return [
						'apiEndpoint' => Config\Storage::instance()->getApiEndpoint()
					];
				},
			],
			'sign.service.api.document' => [
				'className' => Service\Api\DocumentService::class,
				'constructorParams' => static function() {
					return [
						'api' => Service\Container::instance()->getApiService(),
						'serializer' => new \Bitrix\Sign\Serializer\ItemPropertyJsonSerializer
					];
				},
			],
			'sign.service.api.client.domain' => [
				'className' => Service\Api\Client\Domain::class,
				'constructorParams' => static function() {
					return [
						'api' => Service\Container::instance()->getApiService(),
						'serializer' => new \Bitrix\Sign\Serializer\ItemPropertyJsonSerializer
					];
				},
			],
			'sign.repository.document' => [
				'className' => Repository\DocumentRepository::class,
			],
			'sign.repository.blank' => [
				'className' => Repository\BlankRepository::class,
			],
			'sign.repository.block' => [
				'className' => Repository\BlockRepository::class,
				'constructorParams' => static function() {
					return [
						'serializer' => new \Bitrix\Sign\Serializer\ItemPropertyJsonSerializer(),
					];
				},
			],
			'sign.service.api.document.page' => [
				'className' => Service\Api\Document\PageService::class,
				'constructorParams' => static function() {
					return [
						'api' => Service\Container::instance()->getApiService(),
					];
				},
			],
			'sign.service.api.document.signed.file.load' => [
				'className' => Service\Api\Document\SignedFileLoadService::class,
				'constructorParams' => static function() {
					return [
						'api' => Service\Container::instance()->getApiService(),
					];
				},
			],
			'sign.service.api.document.signing' => [
				'className' => Service\Api\Document\SigningService::class,
				'constructorParams' => static function() {
					return [
						'api' => Service\Container::instance()->getApiService(),
						'serializer' => new \Bitrix\Sign\Serializer\ItemPropertyJsonSerializer
					];
				},
			],
			'sign.service.api.document.field' => [
				'className' => Service\Api\Document\FieldService::class,
				'constructorParams' => static function() {
					return [
						'api' => Service\Container::instance()->getApiService(),
						'serializer' => new \Bitrix\Sign\Serializer\ItemPropertyJsonSerializer
					];
				},
			],
			'sign.service.sign.blank.file' => [
				'className' => Service\Sign\BlankFileService::class,
			],
			'sign.service.sign.blank' => [
				'className' => Service\Sign\BlankService::class,
			],
			'sign.service.sign.document' => [
				'className' => Service\Sign\DocumentService::class,
			],
			'sign.service.sign.block' => [
				'className' => Service\Sign\BlockService::class,
			],
			'sign.repository.member' => [
				'className' => Repository\MemberRepository::class,
			],
			'sign.service.sign.document.agent' => [
				'className' =>  Service\Sign\DocumentAgentService::class,
			],
			'sign.repository.file' => [
				'className' => Repository\FileRepository::class,
				// TODO: 'autowire' => true,
				'constructorParams' => static function() {
					return [
						'path' => 'sign_doc_projects',
					];
				},
			],
			'sign.service.sign.member' => [
				'className' => Service\Sign\MemberService::class,
			],
			'sign.service.sign.document.filename' => [
				'className' => Service\Sign\DocumentFileNameService::class,
			],
			'sign.connector.field.factory' => [
				'className' => Connector\FieldConnectorFactory::class,
				'constructorParams' => static fn () => [
					'memberConnectorFactory' => Service\Container::instance()->getMemberConnectorFactory(),
					'fileRepository' => Service\Container::instance()->getFileRepository(),
					'documentRepository' => Service\Container::instance()->getDocumentRepository(),
				],
			],
			'sign.connector.member.factory' => [
				'className' => Connector\MemberConnectorFactory::class,
			],
		]
	],
	'service.new.ui' => [ 'value' => true,],
];
