<?php

namespace Bitrix\Sign\Helper;

class StringHelper
{
	public static function convertCssCaseToCamelCase(string $string): string
	{
		$word = ucwords($string, '-');
		$word = str_replace('-', '', $word);
		return lcfirst($word);
	}
}