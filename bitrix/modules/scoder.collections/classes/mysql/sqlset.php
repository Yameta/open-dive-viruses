<?
class CScoderCollectionsSet
{
	private static $tableName = "sc_collections";
	public static function Edit($Data)
    {		
		if (is_array($Data))
		{
			// $cache = new CPHPCache();		
			// $cache_id = 'scoder_collections';
			// $cache_path = '/scoder/collections';
			// if ($Data['SECTION_ID']>0)
			// {
				// $cache_id = 'scoder_collections_'.$Data['SECTION_ID'];
			// }
			// $cache->Clean($cache_id, $cache_path);
			// if ($cache_id != 'scoder_collections')
			// {
				// $cache_id = 'scoder_collections';
				// $cache->Clean($cache_id, $cache_path);
			// }
			BXClearCache(true, "/scoder/");
		}
        // = $Data = format:
		// IBLOCK_ID - Ид инфблока
		// SECTION_ID - Ид секции
		// CONDITIONS - CONDITIONS
		// UNPACK - UNPACK
		// DATE_GENERATION - Дата последней генерации фильтра
		
		global $DB;

		$rec = self::CheckRecord($Data['SECTION_ID']);
		if($rec)
		{
			$strUpdate = $DB->PrepareUpdate(self::$tableName, $Data);
			$strSql = "UPDATE ".self::$tableName." SET ".$strUpdate." WHERE ID=".$rec['ID'];
			$DB->Query($strSql, false, $err_mess.__LINE__);
		}
		else
		{
			$arInsert = $DB->PrepareInsert(self::$tableName, $Data);
			$strSql =
				"INSERT INTO ".self::$tableName."(".$arInsert[0].") ".
				"VALUES(".$arInsert[1].")";
			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}
		foreach (GetModuleEvents("scoder.collections", "OnAfterScoderCollectionEdit", true) as $arEvent)
            ExecuteModuleEventEx($arEvent, array(&$Data));
		
		return self::CheckRecord($Data['SECTION_ID']); 
    }
	public static function Delete($section_id)
    {
		// $cache = new CPHPCache();		
		// $cache_id = 'scoder_collections';
		// $cache_path = '/scoder/collections';
		// $cache->Clean($cache_id, $cache_path);
		BXClearCache(true, "/scoder/");
		
		global $DB;
		$section_id = $DB->ForSql($section_id);
		$strSql =
            "DELETE FROM ".self::$tableName." 
            WHERE SECTION_ID='".$section_id."'";
		$DB->Query($strSql, true);
        
		foreach (GetModuleEvents("scoder.collections", "OnAfterScoderCollectionDelete", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($section_id));
			
        return true; 
    }
	//запросить коллекцию по ИД секции
	public static function CheckRecord($section_id)
	{
		global $DB;
		
		$section_id = $DB->ForSql($section_id);
        $strSql =
            "SELECT ID, CHECK_PARENT, IBLOCK_ID, SECTION_ID, CONDITIONS, UNPACK, COLLECTION_IBLOKS, CATALOG_AVAILABLE, IS_SECTION_ACTIVE_UPDATE, TYPE_ID, FILTER, DISCOUNT_ACTION ".
            "FROM ".self::$tableName." ".
			"WHERE SECTION_ID = '".$section_id."'";
	
		$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		if($res)
		{
			if($arr = $res->Fetch())
				return $arr;
		}
		return false;
	}
	public static function select($arOrder=array("ID","DESC"),$arFilter=array(),$arNavStartParams=array(), $arSelect = array())
	{
		global $DB;
		if (!is_array($arSelect) || count($arSelect)<=0)
			$arSelect = array('ID','CHECK_PARENT','IBLOCK_ID','SECTION_ID','CONDITIONS','UNPACK','DATE_GENERATION','COLLECTION_IBLOKS', 'CATALOG_AVAILABLE, IS_SECTION_ACTIVE_UPDATE, TYPE_ID, FILTER, DISCOUNT_ACTION');
		
		$strSql='';
		
		$where='';
		if(strpos($arFilter['>=DATE_GENERATION'],".")!==false)
			$arFilter['>=DATE_GENERATION']=strtotime($arFilter['>=DATE_GENERATION']);
		if(strpos($arFilter['<=DATE_GENERATION'],".")!==false)
			$arFilter['<=DATE_GENERATION']=strtotime($arFilter['<=DATE_GENERATION']);

	 	if(count($arFilter)>0)
			foreach($arFilter as $field => $value)
			{
				$sign = " = ";
				$signLength = 1;
				
				$arSigns = array(
					"!" => " != ", 
					"<=" => " <= ", 
					">=" => " >= ", 
					">" => " > ", 
					"<" => " < "
				);
				
				foreach ($arSigns as $oneSign => $resSign)
					if(strpos($field, $oneSign) !== false)
					{
						$sign = $resSign;
						$signLength = strlen($oneSign);
						
						$field = substr($field, $signLength);
					}
				
				if ($where)
					$where.=' AND ';
				
				if(is_array($value))
				{
					foreach($value as $val)
					{
						if ($where)
							$where.=' AND ';
						
						$where .= $field. $sign . "'" .$val . "'";
					}
				}
				else
				{
					if ($value === false)
					{
						$where .= $field . " IS ";
						if ($sign != " = ")
							$where .= "NOT";
						$where .= " NULL";
					}
					else
						$where .= $field . $sign . $value;
				}
			}
			
		
		if($where) 
			$strSql.="WHERE ".$where;
		
		if(in_array($arOrder[0],array('ID','CHECK_PARENT','IBLOCK_ID','SECTION_ID','CONDITIONS','UNPACK','DATE_GENERATION','COLLECTION_IBLOKS', 'CATALOG_AVAILABLE, IS_SECTION_ACTIVE_UPDATE, TYPE_ID, FILTER, DISCOUNT_ACTION'))&&($arOrder[1]=='ASC'||$arOrder[1]=='DESC'))
			$strSql.="
			ORDER BY ".$arOrder[0]." ".$arOrder[1];
		
		if (count($arNavStartParams)<=0 && count($arRealSelect)<=0)
		{
			$res = $DB->Query("
                SELECT COUNT(ID) as C
                FROM ".self::$tableName."
                WHERE ".$where);
            $res = $res->Fetch();
			
            return $res["C"];
		}
		else
		{
			$cnt=$DB->Query("SELECT COUNT(*) as C FROM ".self::$tableName." ".$strSql, false, $err_mess.__LINE__)->Fetch();
			
			if($arNavStartParams['nPageSize']==0)
				$arNavStartParams['nPageSize']=$cnt['C'];
			
			
			$strSql="SELECT * FROM ".self::$tableName." ".$strSql;
			
			$res = new CDBResult();
			$res->NavQuery($strSql,$cnt['C'],$arNavStartParams);

			return $res;
		}
	}
}
?>