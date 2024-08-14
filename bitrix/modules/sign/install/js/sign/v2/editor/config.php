<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/editor.bundle.css',
	'js' => 'dist/editor.bundle.js',
	'rel' => [
		'main.popup',
		'sign.tour',
		'spotlight',
		'ui.buttons',
		'ui.dialogs.messagebox',
		'ui.info-helper',
		'sign.backend',
		'date',
		'ui.notification',
		'ui.stamp.uploader',
		'sign.v2.api',
		'crm.form.fields.selector',
		'crm.requisite.fieldset-viewer',
		'sign.ui',
		'color_picker',
		'sign.document',
		'main.core',
		'ui.draganddrop.draggable',
		'main.core.events',
	],
	'skip_core' => false,
];