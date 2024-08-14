<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/document-summary.bundle.css',
	'js' => 'dist/document-summary.bundle.js',
	'rel' => [
		'main.core',
		'main.core.events',
		'main.popup',
		'sign.v2.api',
		'ui.buttons',
		'sign.v2.lang-selector',
	],
	'skip_core' => false,
];