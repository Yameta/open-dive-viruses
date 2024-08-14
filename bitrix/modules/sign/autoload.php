<?php

\CModule::AddAutoloadClasses('sign', [
	'\\Bitrix\\Sign\\Controllers\\V1\\Document' => 'lib/Controllers/V1/Document.php',
	'\\Bitrix\\Sign\\Controllers\\V1\\Portal' => 'lib/Controllers/V1/Portal.php',
	'\\Bitrix\\Sign\\Controllers\\V1\\Document\\Blank' => 'lib/Controllers/V1/Document/Blank.php',
	'\\Bitrix\\Sign\\Controllers\\V1\\Document\\Blank\\Block' => 'lib/Controllers/V1/Document/Blank/Block.php',
	'\\Bitrix\\Sign\\Controllers\\V1\\Document\\Member' => 'lib/Controllers/V1/Document/Member.php',
	'\\Bitrix\\Sign\\Controllers\\V1\\Document\\Pages' => 'lib/Controllers/V1/Document/Pages.php',
	'\\Bitrix\\Sign\\Controllers\\V1\\Document\\SignedFile' => 'lib/Controllers/V1/Document/SignedFile.php',
	'\\Bitrix\\Sign\\Controllers\\V1\\Document\\Signing' => 'lib/Controllers/V1/Document/Signing.php',
	'\\Bitrix\\Sign\\Controllers\\V1\\Integration\\Crm\\Field' => 'lib/Controllers/V1/Integration/Crm/Field.php',
]);