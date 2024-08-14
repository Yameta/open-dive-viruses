<?
namespace Russianpost\Post;

use Bitrix\Highloadblock as HL;
use Bitrix\Main\Entity;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Hllist
{
	const HL_LIST = 'PostListCountryCodes';
	const MODULE_ID = 'russianpost.post';


	public static function AddVacation($user_id, $date_start, $date_end, $type, $comment = '', $status = '')
	{
		Loader::includeModule('highloadblock');
		$hlblock = HL\HighloadBlockTable::getList(array("filter" => array('=NAME' => self::HL_VACATION)))->fetch();

		$hlID = $hlblock["ID"];
		$result=false;
		if ( $hlID )
		{
			$entity = HL\HighloadBlockTable::compileEntity($hlblock);
			$entity_data_class = $entity->getDataClass();
			$arFields['UF_DATE_START'] = $date_start;
			$arFields['UF_DATE_END'] = $date_end;
			$arFields['UF_TYPE'] = $type;
			$arFields['UF_USER_ID'] = $user_id;
			if($status != '')
			{
				$arFields['UF_STATUS'] = $status;
			}
			if($comment != '')
			{
				$arFields['UF_COMMENT'] = $comment;
			}
			$result = $entity_data_class::add($arFields);
		}
		return $result;
	}

	public static function GetCountryDigitalCode($bxCode, $nameCountry)
	{
		Loader::includeModule('highloadblock');
		$hlblock = HL\HighloadBlockTable::getList(array("filter" => array('=NAME' => self::HL_LIST)))->fetch();
		$hlID = $hlblock["ID"];
		$result=false;
		if ( $hlID )
		{
			$entity = HL\HighloadBlockTable::compileEntity($hlblock);
			$entity_data_class = $entity->getDataClass();
			$res=$entity_data_class::getList(array(
				'filter'=>array(
					'UF_BX_CODE'=>$bxCode
				),
				'order'=>array(
					'ID'=>'ASC'
				)
			));
			$hlresult = $res->fetch();
			if($hlresult['UF_DIGIT_CODE'] == '')
			{
				$res=$entity_data_class::getList(array(
					'filter'=>array(
						'UF_NAME'=>$nameCountry
					),
					'order'=>array(
						'ID'=>'ASC'
					)
				));
				$hlresult = $res->fetch();
				if($hlresult['UF_DIGIT_CODE'] != '')
				{
					$result = $hlresult['UF_DIGIT_CODE'];
				}
			}
			else
			{
				$result = $hlresult['UF_DIGIT_CODE'];
			}
		}
		return $result;
	}
}
?>