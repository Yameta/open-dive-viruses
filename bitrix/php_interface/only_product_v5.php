<?//OnBeforeIBlockElementUpdate
AddEventHandler("iblock", "OnBeforeIBlockElementUpdate", Array("Exchange1CtoBitrix", "OnBeforeIBlockElementUpdateHandler"));
//AddEventHandler("iblock", "OnBeforeIBlockSectionAdd", Array("Exchange1CtoBitrix", "OnBeforeIBlockSectionAddHandler"));
AddEventHandler("iblock", "OnBeforeIBlockSectionUpdate", Array("Exchange1CtoBitrix", "OnBeforeIBlockSectionUpdateHandler"));
AddEventHandler("iblock", "OnBeforeIBlockElementAdd", Array("Exchange1CtoBitrix", "OnBeforeIBlockElementAddHandler"));

//mb_internal_encoding('utf-8');

class Exchange1CtoBitrix
{
	const IBLOCK_PRODUCT_ID = 41;
	const IBLOCK_PRODUCT_FOLDER_DEFAULT_NAME = "Новые элементы";
	const IBLOCK_PRODUCT_FOLDER_DEFAULT_XML = "DefaultFolderUpdate1c";
    // создаем обработчик события "OnBeforeIBlockElementUpdate"
	
    public static function OnBeforeIBlockElementAddHandler(&$arFields)
    {
		if (isset($_GET['type'], $_GET['mode']) && $_GET['type'] === 'catalog' && $_GET['mode'] === 'import') {
			if(self::IBLOCK_PRODUCT_ID == $arFields["IBLOCK_ID"])
				$arFields["ACTIVE"] = "N";
			unset($arFields["IBLOCK_SECTION"]);
			$arFields["IBLOCK_SECTION_ID"] = self::GetDefaultFolder();
			$arFields["IBLOCK_SECTION"] = array(self::GetDefaultFolder());
		}
    }
	
    public static function OnBeforeIBlockElementUpdateHandler(&$arFields)
    {
		if (isset($_GET['type'], $_GET['mode']) && $_GET['type'] === 'catalog' && $_GET['mode'] === 'import')
		{
			foreach($arFields as $key=>$val)
			{
				if($key != "NAME")
					unset($arFields[$key]);
			}
			$arFields = [];
		}
    }
	
	public static function OnBeforeIBlockSectionAddHandler(&$arFields)
    {
		if (isset($_GET['type'], $_GET['mode']) && $_GET['type'] === 'catalog' && $_GET['mode'] === 'import' && $arFields["XML_ID"] !== self::IBLOCK_PRODUCT_FOLDER_DEFAULT_XML)
		{
            global $APPLICATION;
            $APPLICATION->throwException("На стороне сервера запрещено добавление папок при обмене.");
			unset($arFields["IBLOCK_ID"]);
			unset($arFields["NAME"]);
			return false;
		}
    }
	
	public static function OnBeforeIBlockSectionUpdateHandler(&$arFields)
    {
		if (isset($_GET['mode']) && ($_GET['mode'] == 'import' || $_GET['mode'] == 'deactivate'))
		{
			$arFields = [];
		}
    }
	
	public static function GetDefaultFolder(){
		CModule::IncludeModule('iblock');
		$res = CIBlockSection::GetList([], ["IBLOCK_ID" => self::IBLOCK_PRODUCT_ID, "XML_ID" => self::IBLOCK_PRODUCT_FOLDER_DEFAULT_XML],false,["ID"]);
		if($sec = $res->Fetch())
			return $sec["ID"];
		$sec = new CIBlockSection;
		$arParams = ["replace_space"=>"-","replace_other"=>"-"];
		$arFields = [
			"ACTIVE" => "N",
			"IBLOCK_SECTION_ID" => false,
			"IBLOCK_ID" => self::IBLOCK_PRODUCT_ID,
			"NAME" => self::IBLOCK_PRODUCT_FOLDER_DEFAULT_NAME,
			"CODE" => Cutil::translit(self::IBLOCK_PRODUCT_FOLDER_DEFAULT_NAME,"ru",$arParams),
			"SORT" => 999,
			"XML_ID" => self::IBLOCK_PRODUCT_FOLDER_DEFAULT_XML,
			"DESCRIPTION" => "Папка для нераспределенных элементов из обмена"
		];

		if($id = $sec->Add($arFields))
			return $id;

		return false;
	}
}

?>
