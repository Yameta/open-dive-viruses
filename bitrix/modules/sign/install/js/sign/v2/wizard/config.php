<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/wizard.bundle.css',
	'js' => 'dist/wizard.bundle.js',
	'rel' => [
		'main.core',
		'sign.v2.document-setup',
		'sign.v2.requisites',
		'sign.v2.document-summary',
		'sign.v2.preview',
		'sign.v2.api',
	],
	'skip_core' => false,
];