<?
use Bitrix\Main; 
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader; 
use Bitrix\Sale\Internals;	

Loc::loadMessages(__FILE__); 

Class CScoderCollectionsAgents
{	
	const MODULE_ID = 'scoder.collections';
	public static function Reindex()
	{
		if (Loader::includeSharewareModule(self::MODULE_ID) == Loader::MODULE_DEMO_EXPIRED)
		{
			
		}
		else
		{
			global $USER;
			if (!is_object($USER)) $USER = new CUser;
			
			$bactive = Option::get("scoder.collections", "ACTIVE_AGENT");
			if ($bactive=="Y")
			{
				$str = Option::get("scoder.collections", "REINDEX_IBLOCKS");
				
				$arValues = unserialize($str);
				if (is_array($arValues) && count($arValues)>0)
				{
					$ar_types = CScoderCollections::get_iblocks_data(); 	//возвращает иинофрмацию по инфоблокам и их типам
					
					$ar_filter = array(
						"IBLOCK_ID" => $arValues,
					);
					$rs = CIBlockElement::GetList(
						array("ID" => "ASC"), 
						$ar_filter, 
						false, 
						false, 
						array("ID","IBLOCK_ID")
					);
					CCatalogProduct::setUseDiscount(true);
					while ($ob = $rs->GetNextElement())
					{
						$arItem = $ob -> GetFields();
						$arItem["IBLOCK_TYPE"] = $ar_types["IBLOCK_TYPES"][$arItem["IBLOCK_ID"]];
						$arItem["DISREGARD"] = 'Y';
						
						CScoderCollections::ProductEdit($arItem,false);
					}
				}
			}
		}
		return "CScoderCollectionsAgents::Reindex();";
	}
}
?>