<?
use	Bitrix\Main;
use Bitrix\Main\Loader; 
use Bitrix\Main\Localization\Loc;
use Bitrix\Iblock\PropertyIndex; 
use Bitrix\Main\Config\Option;
use	Bitrix\Iblock;

global $APPLICATION;

Loc::loadMessages(__FILE__); 

if (!Loader::includeModule("iblock"))
{
	$APPLICATION->ThrowException(Loc::getMessage('SCODER_ERROR_IBLOCK_NOT_INSTALLED'));
	return false;
}

if (!Loader::includeModule("highloadblock"))
{
	$APPLICATION->ThrowException(Loc::getMessage('SCODER_ERROR_HIGHLOADBLOCK_NOT_INSTALLED'));
	return false;
}

if (Loader::includeModule("catalog")){}

CModule::AddAutoloadClasses(
	"scoder.collections",
	array(
		"CScoderCollectionsApi" => "classes/general/main.php",
		"CScoderCollectionsEvents" => "classes/general/events.php",
		"CScoderCollectionsSet" => "classes/mysql/sqlset.php",
		"CScoderCollectionsAgents" => "classes/general/agents.php",
	)
);

class CScoderCollections
{
	const MODULE_ID = 'scoder.collections';
	public static function __checkElement($strCond, $arProduct)
	{
		return eval('return ' . $strCond . ';');
	}
	public static function get_hl_values($arEnum,$TABLE_NAME)
	{
		$arRes = array();

		$rsData = \Bitrix\Highloadblock\HighloadBlockTable::getList(array('filter'=>array("TABLE_NAME" => $TABLE_NAME)));
		if ($arData = $rsData->fetch())
		{
			$Entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($arData);

			//Создадим объект - запрос
			$Query = new \Bitrix\Main\Entity\Query($Entity); 
			
			//Зададим параметры запроса, любой параметр можно опустить
			$Query->setSelect(array('UF_XML_ID'));
			$Query->setFilter(array("UF_XML_ID" => $arEnum));
			$Query->setOrder(array('UF_SORT' => 'ASC'));

			//Выполним запрос
			$result = $Query->exec();

			//Получаем результат по привычной схеме
			$result = new CDBResult($result);
			$arLang = array();
			while ($row = $result->Fetch())
			{
				$arRes[] = $row["UF_XML_ID"];
			}
		}
		return $arRes;
	}
	public static function get_parent_info($value)
	{
		if ($value>0)
		{
			$arFilter = Array(
				"ID"=>$value
			);
			$res = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>1), Array("ID", "IBLOCK_ID", "*"));
			if ($ob = $res->GetNextElement())
			{
				$arElement = $ob->GetFields();
				foreach ($arElement as $code => $val)
					$arRes['PARENT_'.$code] = $val;
				
				//parents props			
				$arProps = $ob->GetProperties();
				self::__format_properties($arRes,$arProps);			//пишем свойства родителя

			}
		}
		return $arRes;
	}
	public static function set_catalog_info($bCatalogProduct, &$arProduct, $arCatalogProduct = array())
	{
		if ($bCatalogProduct 
			&& is_array($arCatalogProduct))
		{
			foreach ($arCatalogProduct as $code => $val)
				$arProduct['CATALOG_'.$code] = $val;
		}
		
	}
	public static function get_iblocks()
	{
		$arIblocks = false;
		$bCatalog = false;
		
		if (Loader::includeModule("catalog"))
			$bCatalog = true;
		
		if ($bCatalog)
		{
			$rs = CCatalog::GetList(array('ID' => 'asc'),array(),false,false,array('ID','IBLOCK_ID'));
			while ($arRes = $rs->GetNext())
			{
				$arIblocks[] = $arRes['IBLOCK_ID'];
			}
		}
		
		return $arIblocks;
	}
	public static function get_iblocks_info()
	{
		$arIblocks = false;
		$bCatalog = false;
		
		if (Loader::includeModule("catalog"))
			$bCatalog = true;
				
		if ($bCatalog)
		{
			$rs = CCatalog::GetList(array('ID' => 'asc'),array(),false,false,array('ID','IBLOCK_ID','NAME'));
			while ($arRes = $rs->GetNext())
			{
				$arIblocks[] = $arRes;
			}
		}
		return $arIblocks;
	}
	public static function __format_properties(&$arProduct,$properties)
	{
		foreach($properties as $property)
		{
			if (!isset($arProduct["PROPERTY_".$property["ID"]."_VALUE"]))
				$arProduct["PROPERTY_".$property["ID"]."_VALUE"]  = array();
			
			if (!is_array($property["~VALUE"]))
				$arProduct["PROPERTY_".$property["ID"]."_VALUE"][] = $property["~VALUE"];
			elseif (is_array($arProduct["PROPERTY_".$property["ID"]."_VALUE"]))
				$arProduct["PROPERTY_".$property["ID"]."_VALUE"] = array_merge($arProduct["PROPERTY_".$property["ID"]."_VALUE"],$property["~VALUE"]);

			if($property["PROPERTY_TYPE"] == "L" && !empty($property["VALUE_ENUM_ID"]))
			{
				if (!is_array($property["VALUE_ENUM_ID"]))
					$arProduct["PROPERTY_".$property["ID"]."_VALUE"][] = $property["VALUE_ENUM_ID"];
				else
					$arProduct["PROPERTY_".$property["ID"]."_VALUE"] = array_merge($arProduct["PROPERTY_".$property["ID"]."_VALUE"],$property["VALUE_ENUM_ID"]);
			}
			
			if($property["PROPERTY_TYPE"] == "S" && !empty($property['USER_TYPE_SETTINGS']['TABLE_NAME']) && !empty($property["~VALUE"]))
			{
				if(!is_array($property["~VALUE"]))
					$property["~VALUE"] = array($property["~VALUE"]);
				
				$arEnum = $property["~VALUE"];
				
				if($arHLBlockValues = CScoderCollections::get_hl_values($arEnum, $property['USER_TYPE_SETTINGS']['TABLE_NAME']))
				{
					$arProduct["PROPERTY_".$property["ID"]."_VALUE"] = array_merge($arProduct["PROPERTY_".$property["ID"]."_VALUE"],$arHLBlockValues);
				}
				unset($arHLBlockValues);
			}
		}
	}
	public static function ProductEdit($arFields,$is_new, $arIblocks = array(), $arCollections = array())
	{
		if (Loader::includeSharewareModule(self::MODULE_ID) == Loader::MODULE_DEMO_EXPIRED){}
		elseif ((isset($arFields["DISREGARD"]) && $arFields["DISREGARD"]=='Y')
			|| Option::get('scoder.collections', "IS_ADD_SET") == "Y")
		{
			$bcheck = false;		//все коллекции используют интерфейс фильтра
			
			if (!is_array($arCollections) || count($arCollections)<=0)
			{
				unset($arCollections);
				$dis_active_filter = Option::get(self::MODULE_ID, "DIS_ACTIVE_FILTER");
				/************************ЗАПРОС ПРОЕРЯЕМЫХ КОЛЛЛЕКЦИЙ***************************/
				$arFilter = $params = Array();
				
				if ($arFields["IBLOCK_ID"]<=0)
					$arFields["IBLOCK_ID"] = CIBlockElement::GetIBlockByID($arFields["ID"]);
				
				//если указана конкретная коллекция
				if (isset($arFields["SET_ID"]) && $arFields["SET_ID"]>0)
				{
					$arFilter["SECTION_ID"] = $arFields["SET_ID"];
					
					$params["SET_ID"] = $arFields["SET_ID"];
				}
				//Запрос коллекций
				if (is_array($arIblocks) && count($arIblocks)>0)
				{
					$params["IBLOCK_ID"] = $arFilter["IBLOCK_ID"] = $arIblocks;
				}
				$obCache = new CPHPCache();
				if ($obCache->InitCache(3600, 'scoder_collections'.serialize($params), "/scoder/collections"))
				{
					$arCollections = $obCache->GetVars();
				}
				elseif ($obCache->StartDataCache())
				{
					$res = CScoderCollectionsApi::GetList(Array("ID" => "ASC"), $arFilter,false,false,array("FILTER","TYPE_ID","CONDITIONS","CHECK_PARENT","CATALOG_AVAILABLE","UNPACK","DISCOUNT_ACTION","SECTION_ID"));
					while ($arCollection = $res->GetNext())
					{
						if ($arCollection['TYPE_ID']=="F" && $arCollection['~FILTER'] != '')
						{
							if ($dis_active_filter != "Y")	//Если интерфейс фильтра не отключен
							{								
								$arCollections[] = $arCollection;
							}
						}
						elseif(($arCollection['TYPE_ID'] == "D" || $arCollection['TYPE_ID']=='') && strlen($arCollection['~CONDITIONS'])>0)
						{
							$arCollections[] = $arCollection;
						}
					}
					$obCache->EndDataCache($arCollections);
				}
			}
			//проверяем, есть ли условия с правилами работы с корзиной
			if (is_array($arCollections))
			{
				//Если интерфейс фильтра не отключен
				if ($dis_active_filter != "Y")
				{
					if (!isset($arFields["IBLOCK_TYPE"]) || $arFields["IBLOCK_TYPE"]=="")
					{
						$ar_types = CScoderCollections::get_iblocks_data(); 	//возвращает иинофрмацию по инфоблокам и их типам
						$arFields['IBLOCK_TYPE'] = $ar_types["IBLOCK_TYPES"][$arFields["IBLOCK_ID"]];
					}
					$params = array(
						//"ID" => $arFields["ID"],
						"IBLOCK_ID" => $arFields["IBLOCK_ID"],
						"IBLOCK_TYPE" => $arFields["IBLOCK_TYPE"],
					);
					
					$obCacheSub = new CPHPCache();
					if ($obCacheSub->InitCache(3600, 'scoder_' . serialize($params), "/scoder/collections"))
					{
						$all_fields = $obCacheSub->GetVars();
					}
					elseif ($obCacheSub->StartDataCache())
					{
						$all_fields = CScoderCollections::__get_filters($params);
						$obCacheSub->EndDataCache($all_fields);
					}
				}
				foreach ($arCollections as $key => $arCollection)
				{
					if(($arCollection['TYPE_ID'] == "D" || $arCollection['TYPE_ID'] == "")&& strlen($arCollection['~CONDITIONS'])>0)
					{
						$bcheck = true;
						break;
					}
					elseif ($arCollection['TYPE_ID']=="F" && $arCollection['~FILTER'] != '' && $dis_active_filter != "Y")	//Если интерфейс фильтра не отключен
					{
						$ar_str = unserialize($arCollection['~FILTER']);
						if (isset($ar_str['fields']))
							$val_fields = $ar_str['fields'];
						elseif (isset($ar_str['data']['fields']))
							$val_fields = $ar_str['data']['fields'];
						
						if (isset($val_fields))
						{
							$arCollectionFilter = array(
								"IBLOCK_ID" => $arFields["IBLOCK_ID"],
								"SHOW_NEW" => "Y",
								"CHECK_PERMISSIONS" => "Y",
								"MIN_PERMISSION" => "R",
							);
							
							self::CreateFilter($all_fields["CATALOG_FIELDS"], $arCollectionFilter, $val_fields);
							
							if (is_array($all_fields["OFFER_FIELDS"]))
							{
								$arCatalog = CCatalogSKU::GetInfoByIBlock($params["IBLOCK_ID"]);
								$arSubQuery = array();
								$arSubQuery = array("IBLOCK_ID" => $arCatalog["IBLOCK_ID"]);
								
								self::CreateFilter($all_fields["OFFER_FIELDS"], $arSubQuery, $val_fields);
								
								if (1 < sizeof($arSubQuery))
								{
									$arCollectionFilter["ID"] = CIBlockElement::SubQuery("PROPERTY_".$arCatalog["SKU_PROPERTY_ID"], $arSubQuery);
								}
							}
							$arCollections[$key]["REAL_FILTER"] = $arCollectionFilter;
						}
					}
				}
			}
			
			//------------------------END. ЗАПРОС ПРОВЕРЯЕМЫХ КОЛЛЛЕКЦИЙ------------------------
			//Запрос необходимой информации о товаре для коллекции			
			$ar_main_filter = array(
				"ID" => $arFields["ID"]
			);
			
			$rs_element = CIBlockElement::GetList(
				array("ID" => "ASC"), 
				$ar_main_filter, 
				false, 
				array("nTopCount" => 1), 
				array("ID", "IBLOCK_ID", "*")
			);
			if ($ob_element = $rs_element->GetNextElement())
			{
				$arProduct = $ob_element -> GetFields();
				$ar_element_props = $ob_element -> GetProperties();
				
				// get element groups
				$arSections = array();
				
				//Возвращает группы, которым принадлежит элемент, по его коду ID
				$db_old_groups = CIBlockElement::GetElementGroups($arProduct["ID"], true);
				while($ar_group = $db_old_groups->Fetch())
					$arGroups[] = $ar_group["ID"];
				
				if (is_array($arGroups) && count($arGroups)>0)
				{
					$arSections = $arGroups;
				}
				unset($arGroups);
				
				//если не все коллекции используют интерфейс фильтра		
				if ($bcheck)
				{					
					$arProduct["SECTION_ID"] = array();
					$arProduct["TIMESTAMP_X"] = $arProduct["TIMESTAMP_X_UNIX"];
					$arProduct["DATE_CREATE"] = $arProduct["DATE_CREATE_UNIX"];
					$arProduct["DATE_ACTIVE_FROM"] = MakeTimeStamp($arProduct["DATE_ACTIVE_FROM"]);
					$arProduct["DATE_ACTIVE_TO"] = MakeTimeStamp($arProduct["DATE_ACTIVE_TO"]);
					
					// get catalog fields
					$bCatalogProduct = (is_array($arCatalogProduct = CCatalogProduct::GetByID($arProduct["ID"])));
					if($bCatalogProduct)
					{
						CScoderCollections::set_catalog_info($bCatalogProduct, $arProduct, $arCatalogProduct);
					}
					unset($bCatalogProduct);
					
				}
				//если не все коллекции используют интерфейс фильтра		
				if ($bcheck)
				{
					self::__format_properties($arProduct,$ar_element_props);		//пишем свойства основного теучего элемента
					
					$arID = array();
					//Торговые предложения
					if(isset($ar_element_props["CML2_LINK"]) && $ar_element_props["CML2_LINK"]["~VALUE"] != '')
					{
						$arProduct['PARENT_ID'] = $parent_id = $ar_element_props["CML2_LINK"]["~VALUE"];
						
						//Поля родителя
						if($arParentFields = CScoderCollections::get_parent_info($parent_id))
						{
							$arProduct = array_merge($arProduct, $arParentFields);
						}
						unset($arParentFields);
						
						if ($arProduct['PARENT_ID']>0)
						{
							//Секции родителя
							$db_old_groups = CIBlockElement::GetElementGroups($arProduct['PARENT_ID'], true);
							while($ar_group = $db_old_groups->Fetch())
								$arParentGroups[] = $ar_group["ID"];
							
							if (is_array($arParentGroups) && count($arParentGroups)>0)
							{
								$arProduct["PARENT_SECTION_ID"] = $arParentGroups;
								
								$arSections = array_merge($arProduct["PARENT_SECTION_ID"], $arSections);
							}
						}
						unset($arParentGroups);
					}
					elseif (!isset($ar_element_props["CML2_LINK"]))
					{
						$productList = array($arProduct["ID"]);
						$ExistOffers = CCatalogSKU::getExistOffers($productList,$arProduct['IBLOCK_ID']);
						//Метод возвращает признак наличия торговых предложений для массива товаров из одного или нескольких инфоблоков
						if (isset($ExistOffers[$arProduct["ID"]]) && $ExistOffers[$arProduct["ID"]] === true)
						{
							$parent_id = $arProduct["ID"];
						}
					}
					if ($parent_id>0)
					{
						//запрос всех торговых предложений родительского товара
						$res_offers = CIBlockElement::GetList(
							Array("ID" => "asc"), 
							array(
								"PROPERTY_CML2_LINK" => $parent_id,
								"!ID" => $arProduct["ID"],			//текучий снова не запрашиваем
							), 
							false, 
							false, 
							array("ID")
						);
						while($ob_offers = $res_offers->GetNextElement())
						{
							$ar_offers = $ob_offers->GetFields();
							$arID[] = $ar_offers["ID"];
						}
						//запрос всех свойств торговых предложений
						if (count($arID)>0)
						{
							$res_offers_properties = CIBlockElement::GetList(
								Array("ID" => "asc"), 
								array("ID" => $arID), 
								false, 
								false, 
								array("ID","IBLOCK_ID","PROPERTY_*")
							);
							while($ob_offers_properties = $res_offers_properties->GetNextElement())
							{
								$offer = $ob_offers_properties->GetFields();
								$offer_properties = $ob_offers_properties -> GetProperties();

								self::__format_properties($arProduct,$offer_properties);
							}
						}
					}
				}

				$arGroupsSet = $arSections;
				
				if (isset($arCollections) && is_array($arCollections))
				{
					//перебираем все нужные коллекции, проверяем удовлетворяет ли условиям коллекции
					foreach ($arCollections as $arCollection)
					{
						$return = false;
						if ($arCollection['TYPE_ID']=="F")
						{
							$arProduct["SECTION_ID"] = $arSections;
							if (isset($arCollection["REAL_FILTER"]))
							{
								$arCollection["REAL_FILTER"]["=ID"] = $arFields["ID"];			//проверяем конкретный элемент
								
								//делаем доп. запрос по сохраненному фильтру, проверяем вернет ли указанный элемент
								$rs_sub = CIBlockElement::GetList(
									array("ID" => "ASC"), 
									$arCollection["REAL_FILTER"], 
									false, 
									array("nTopCount" => 1), 
									array("ID")
								);
								if ($ob_sub = $rs_sub->GetNextElement())
								{
									$ar_sub = $ob_sub -> GetFields();
									
									$return = true;
								}
							}
						}
						else
						{
							//если у колллекции отмечено "Только доступные" и элемент не доступен
							if ($arCollection["CATALOG_AVAILABLE"]=="Y" && $arProduct["CATALOG_AVAILABLE"]=="N")
							{
								$return = false;
							}
							else
							{
								//---------------------------------------------
								$arProduct["SECTION_ID"] = $arSections;
								//перебираем принадлежность к разделам родителям
								if ($arCollection["CHECK_PARENT"] == 'Y' && count($arSections)>0)
								{	
									foreach ($arSections as $sect_id)
									{
										$nav = CIBlockSection::GetNavChain(false,$sect_id,array('ID'));
										while($arSectionPath = $nav->GetNext())
										{
											if (!in_array($arSectionPath["ID"],$arProduct["SECTION_ID"]))
											{
												$arProduct["SECTION_ID"][] = $arSectionPath["ID"];
											}
										}
									}
								}	
								//---------------------------------------------
								$strCond = $arCollection["~UNPACK"];
								$return = CScoderCollections::__checkElement($strCond, $arProduct);
							}
						}
						
						//Если условия совпали
						if ($arCollection['TYPE_ID']=="F")
							$ELEMENT_ID = $arProduct["ID"];
						else
							$ELEMENT_ID = $arProduct["PARENT_ID"] ?: $arProduct["ID"];
						
						//если у колллекции отмечено "Только доступные" и элемент не доступен
						if ($arCollection["CATALOG_AVAILABLE"]=="Y" && $arProduct["CATALOG_AVAILABLE"]=="N")
						{
							$return = false;
						}
						else
						{
							if ($arCollection["DISCOUNT_ACTION"]!=""
								&& (($arCollection["DISCOUNT_ACTION"]=="OR" && !$return) || ($arCollection["DISCOUNT_ACTION"]=="AND" && $return) || ($arCollection["DISCOUNT_ACTION"]=="NOT" && $return))
									&& Loader::includeModule("catalog")
									)
							{
								$btrue = false;
								$rsSites = CIBlock::GetSite($arProduct["IBLOCK_ID"]);
								while($arSite = $rsSites->Fetch())
								{
									$arDiscounts = CCatalogDiscount::GetDiscountByProduct(
										$ELEMENT_ID,
										array(2),		//все пользователи
										"N",
										array(),
										$arSite["LID"]
									);
									if (is_array($arDiscounts) 
										&& count($arDiscounts)>0)
									{
										$btrue = true;
										break;
									}
								}
								//если условиям коллекции не удовлетворяет, но у коллекции отмечено Дополнять товарами со скидкой
								if ($arCollection["DISCOUNT_ACTION"]=="OR"
									&& !$return
										&& $btrue
									)
								{
									$return = true;								
								}
								//если условиям коллекции удовлетворяет, но у коллекции отмечено Учитывать только товары со скидкой
								if ($arCollection["DISCOUNT_ACTION"]=="AND"
									&& $return
										&& !$btrue
									)
								{
									$return = false;
								}
								//если условиям коллекции удовлетворяет, но у коллекции отмечено Исключать товары со скидкой
								if ($arCollection["DISCOUNT_ACTION"]=="NOT"
									&& $return
										&& $btrue
									)
								{
									$return = false;
								}
							}
						}
						$sectionId = null;
						if ($return === true)
						{
							//если товар еще не принадлежит разделу коллекции
							if (!in_array($arCollection["SECTION_ID"],$arSections))
							{
								$arGroupsSet[] = $arCollection["SECTION_ID"];
								
								foreach (GetModuleEvents("scoder.collections", "OnBeforeScoderCollectionElementEdit", true) as $arEvent)
									ExecuteModuleEventEx($arEvent, array($return, $ELEMENT_ID, $arCollection["SECTION_ID"], $arSections, &$arGroupsSet,&$sectionId));
									
								CIBlockElement::SetElementSection($ELEMENT_ID, $arGroupsSet, false, 0, $sectionId);
								//Переиндексация
								PropertyIndex\Manager::updateElementIndex($arProduct["IBLOCK_ID"], $ELEMENT_ID);
									
							}
						}
						elseif ($return !== true			
							&& is_array($arSections) 
								&& in_array($arCollection["SECTION_ID"],$arSections))		//Сброс товара. Если товар лежит в коллекции, но не должен там лежать
						{
							$arSectionsNew = array();
							
							foreach ($arSections as $section_id)
								if ($section_id != $arCollection["SECTION_ID"])
									$arSectionsNew[] = $section_id;
							
							foreach (GetModuleEvents("scoder.collections", "OnBeforeScoderCollectionElementEdit", true) as $arEvent)
								ExecuteModuleEventEx($arEvent, array($return, $ELEMENT_ID, $arCollection["SECTION_ID"], $arSections, &$arSectionsNew, &$sectionId));
							
							CIBlockElement::SetElementSection($ELEMENT_ID, $arSectionsNew, false, 0, $sectionId);
							//Переиндексация
							PropertyIndex\Manager::updateElementIndex($arProduct["IBLOCK_ID"], $ELEMENT_ID);
						}
					}
				}
			}
		}
	}
	public static function __get_filters($arParams = array())
	{
		//if (!is_object($USER)) $USER = new CUser;
		
		$arIblocks = $boolSKU = $bCatalog = $bWorkflow =false;
		
		
		if (Loader::includeModule("catalog"))
			$bCatalog = true;
		if (Loader::includeModule("workflow"))
			$bWorkflow = true;
		
		$boolCatalogSet = true;
		$productTypeList = array();
		$boolCatalogRead = true;
		$boolCatalogPrice = true;
		
		$arIBTYPE = CIBlockType::GetByIDLang($arParams["IBLOCK_TYPE"], LANGUAGE_ID);			//возвращает языковые настройки типа информационных блоков 
		
		//собираем разделы
		$sectionItems = array();
		$sectionQueryObject = CIBlockSection::GetTreeList(Array("IBLOCK_ID"=>$arParams["IBLOCK_ID"]), array("ID", "NAME", "DEPTH_LEVEL"));
		while($arSection = $sectionQueryObject->Fetch())
			$sectionItems[$arSection["ID"]] = str_repeat(" . ", $arSection["DEPTH_LEVEL"]).$arSection["NAME"];
		
		if ($bCatalog)
		{
			//$boolCatalogSet = CBXFeatures::IsFeatureEnabled('CatCompleteSet');
			//$boolCatalogRead = $USER->CanDoOperation('catalog_read');
			//$boolCatalogPrice = $USER->CanDoOperation('catalog_price');
			
			$arCatalog = CCatalogSKU::GetInfoByIBlock($arParams["IBLOCK_ID"]);
			if (empty($arCatalog))
			{
				$bCatalog = false;
			}
			else
			{
				if (CCatalogSKU::TYPE_PRODUCT == $arCatalog['CATALOG_TYPE'] || CCatalogSKU::TYPE_FULL == $arCatalog['CATALOG_TYPE'])
				{
					if (CIBlockRights::UserHasRightTo($arCatalog['IBLOCK_ID'], $arCatalog['IBLOCK_ID'], "iblock_admin_display"))
					{
						$boolSKU = true;
					}
				}
				if (!$boolCatalogRead && !$boolCatalogPrice)
					$bCatalog = false;
				
				$productTypeList = CCatalogAdminTools::getIblockProductTypeList($arParams["IBLOCK_ID"], true);
			}
		}
		
		$filterFields = array(
			array(
				"id" => "NAME",
				"name" => GetMessage("IBLOCK_FIELD_NAME"),
				"filterable" => "?",
				"quickSearch" => "?",
				"default" => true
			),
			array(
				"id" => "ID",
				"name" => rtrim(GetMessage("IBLOCK_FILTER_FROMTO_ID"), ":"),
				"type" => "number",
				"filterable" => ""
			)
		);
		if ($arIBTYPE["SECTIONS"] == "Y")
		{
			$filterFields[] = array(
				"id" => "SECTION_ID",
				"name" => GetMessage("IBLOCK_FIELD_SECTION_ID"),
				"type" => "list",
				"items" => $sectionItems,
				"filterable" => ""
			);
		}
		$filterFields[] = array(
			"id" => "DATE_MODIFY_FROM",
			"name" => GetMessage("IBLOCK_FIELD_TIMESTAMP_X"),
			"type" => "date",
			"filterable" => ""
		);
		$filterFields[] = array(
			"id" => "MODIFIED_USER_ID",
			"name" => GetMessage("IBLOCK_FIELD_MODIFIED_BY"),
			"type" => "custom_entity",
			"selector" => array("type" => "user"),
			"filterable" => ""
		);
		$filterFields[] = array(
			"id" => "DATE_CREATE",
			"name" => GetMessage("IBLOCK_EL_ADMIN_DCREATE"),
			"type" => "date",
			"filterable" => ""
		);
		$filterFields[] = array(
			"id" => "CREATED_USER_ID",
			"name" => rtrim(GetMessage("IBLOCK_EL_ADMIN_WCREATE"), ":"),
			"type" => "custom_entity",
			"selector" => array("type" => "user"),
			"filterable" => ""
		);
		$filterFields[] = array(
			"id" => "DATE_ACTIVE_FROM",
			"name" => GetMessage("IBEL_A_ACTFROM"),
			"type" => "date",
			"filterable" => ""
		);
		$filterFields[] = array(
			"id" => "DATE_ACTIVE_TO",
			"name" => GetMessage("IBEL_A_ACTTO"),
			"type" => "date",
			"filterable" => ""
		);
		$filterFields[] = array(
			"id" => "ACTIVE",
			"name" => GetMessage("IBLOCK_FIELD_ACTIVE"),
			"type" => "list",
			"items" => array(
				"Y" => GetMessage("IBLOCK_YES"),
				"N" => GetMessage("IBLOCK_NO")
			),
			"filterable" => ""
		);
		$filterFields[] = array(
			"id" => "SEARCHABLE_CONTENT",
			"name" => rtrim(GetMessage("IBLOCK_EL_ADMIN_DESC"), ":"),
			"filterable" => "?"
		);
		if ($bWorkFlow)
		{
			$workflowStatus = array();
			$rs = CWorkflowStatus::GetDropDownList("Y");
			while ($arRs = $rs->GetNext())
				$workflowStatus[$arRs["REFERENCE_ID"]] = $arRs["REFERENCE"];
			
			$filterFields[] = array(
				"id" => "WF_STATUS",
				"name" => GetMessage("IBLIST_A_STATUS"),
				"type" => "list",
				"items" => $workflowStatus,
				"filterable" => ""
			);
		}
		$filterFields[] = array(
			"id" => "CODE",
			"name" => GetMessage("IBEL_A_CODE"),
			"filterable" => ""
		);
		$filterFields[] = array(
			"id" => "EXTERNAL_ID",
			"name" => GetMessage("IBEL_A_EXTERNAL_ID"),
			"filterable" => ""
		);
		$filterFields[] = array(
			"id" => "TAGS",
			"name" => GetMessage("IBEL_A_TAGS"),
			"filterable" => "?"
		);

		if ($bCatalog)
		{			
			$filterFields[] = array(
				"id" => "CATALOG_TYPE",
				"name" => GetMessage("IBEL_CATALOG_TYPE"),
				"type" => "list",
				"items" => $productTypeList,
				"params" => array("multiple" => "Y"),
				"filterable" => ""
			);
			if ($boolCatalogSet)
			{
				$filterFields[] = array(
					"id" => "CATALOG_BUNDLE",
					"name" => GetMessage("IBEL_CATALOG_BUNDLE"),
					"type" => "list",
					"items" => array(
						"Y" => GetMessage("IBLOCK_YES"),
						"N" => GetMessage("IBLOCK_NO")
					),
					"filterable" => ""
				);
			}
			$filterFields[] = array(
				"id" => "CATALOG_AVAILABLE",
				"name" => GetMessage("IBEL_CATALOG_AVAILABLE"),
				"type" => "list",
				"items" => array(
					"Y" => GetMessage("IBLOCK_YES"),
					"N" => GetMessage("IBLOCK_NO")
				),
				"filterable" => ""
			);
		}
		$sTableID = $arParams["TABLE_ID"];
		$propertyManager = new Iblock\Helpers\Filter\PropertyManager($arParams["IBLOCK_ID"]);
		$filterFields = array_merge($filterFields, $propertyManager->getFilterFields());
		if ($sTableID != "")
			$propertyManager->renderCustomFields($sTableID);
		
		if ($boolSKU)
		{
			$propertySKUManager = new Iblock\Helpers\Filter\PropertyManager($arCatalog["IBLOCK_ID"]);
			$propertySKUFilterFields = $propertySKUManager->getFilterFields();
			$filterFields = array_merge($filterFields, $propertySKUFilterFields);
			if ($sTableID != "")
				$propertySKUManager->renderCustomFields($sTableID);
		}
		
		return array(
			"CATALOG_FIELDS" => $filterFields,
			"OFFER_FIELDS" => $propertySKUFilterFields,
		);
	}
	//вспомогательная функция, подготавливает фильтр
	public static function CreateFilter(array $filterFields, array &$arFilter , array $filterData)
	{
		$filterable = array();
		$quickSearchKey = "";
		foreach ($filterFields as $filterField)
		{
			if (isset($filterField["quickSearch"]))
			{
				$quickSearchKey = $filterField["quickSearch"].$filterField["id"];
			}
			$filterable[$filterField["id"]] = $filterField["filterable"];
		}

		foreach ($filterData as $fieldId => $fieldValue)
		{
			if ((is_array($fieldValue) && empty($fieldValue)) || (is_string($fieldValue) && strlen($fieldValue) <= 0))
			{
				continue;
			}

			if (substr($fieldId, -5) == "_from")
			{
				$realFieldId = substr($fieldId, 0, strlen($fieldId)-5);
				if (!array_key_exists($realFieldId, $filterable))
				{
					continue;
				}
				if (substr($realFieldId, -2) == "_1")
				{
					$arFilter[$realFieldId] = $fieldValue;
				}
				else
				{
					if (!empty($filterData[$realFieldId."_numsel"]) && $filterData[$realFieldId."_numsel"] == "more")
						$filterPrefix = ">";
					else
						$filterPrefix = ">=";
					$arFilter[$filterPrefix.$realFieldId] = trim($fieldValue);
				}
			}
			elseif (substr($fieldId, -3) == "_to")
			{
				$realFieldId = substr($fieldId, 0, strlen($fieldId)-3);
				if (!array_key_exists($realFieldId, $filterable))
				{
					continue;
				}
				if (substr($realFieldId, -2) == "_1")
				{
					$realFieldId = substr($realFieldId, 0, strlen($realFieldId)-2);
					$arFilter[$realFieldId."_2"] = $fieldValue;
				}
				else
				{
					if (!empty($filterData[$realFieldId."_numsel"]) && $filterData[$realFieldId."_numsel"] == "less")
						$filterPrefix = "<";
					else
						$filterPrefix = "<=";
					$arFilter[$filterPrefix.$realFieldId] = trim($fieldValue);
				}
			}
			else
			{
				if (array_key_exists($fieldId, $filterable))
				{
					$filterPrefix = $filterable[$fieldId];
					$arFilter[$filterPrefix.$fieldId] = $fieldValue;
				}
				if ($fieldId == "FIND" && trim($fieldValue) && $quickSearchKey)
				{
					$arFilter[$quickSearchKey] = $fieldValue;
				}
			}
		}
	}
	public static function get_iblocks_data()
	{
		$ar = false;
		$res = CIBlock::GetList(
			Array("IBLOCK_TYPE_ID" => "ASC"), 
			Array(), 
			false
		);
		while($ar_res = $res->Fetch())
		{
			$ar_types[$ar_res["ID"]] = $ar_res["IBLOCK_TYPE_ID"];
			$ar_iblocks[$ar_res["ID"]] = $ar_res;
		}
		if (isset($ar_types))
		{
			$ar["IBLOCK_TYPES"] = $ar_types;
			$ar["IBLOCKS"] = $ar_iblocks;
		}
		return $ar;
	}
}
?>
