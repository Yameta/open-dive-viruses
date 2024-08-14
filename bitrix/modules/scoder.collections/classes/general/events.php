<?
use Bitrix\Main;
use Bitrix\Main\Loader; 
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Application;
use Bitrix\Iblock;

Loc::loadMessages(__FILE__); 

Class CScoderCollectionsEvents
{	
	const MODULE_ID = 'scoder.collections';
	public static function OnBeforePrologHandler()
	{
		$request = Bitrix\Main\Application::getInstance()->getContext()->getRequest();
		$grid_id = $request->getPost("GRID_ID");
		$filter_id = $request->getPost("FILTER_ID");
		
		$btrue = false;
		if ($grid_id == 'SCODER_COLLECTIONS_FILTERS'
			&& $filter_id != ''
				&& $request->get("action")==Actions::SET_FILTER)
		{
			$btrue = true;
			$arPostList = $request->getPostList()->toArray();
		}
		else
		{
			$arPostList = $request->getPostList()->toArray();
			if (is_array($arPostList)
				&& is_array($arPostList['params'])	
					&& isset($arPostList['params']['GRID_ID'])	
						&& isset($arPostList['params']['GRID_ID'])
			)
			{
				$grid_id = $arPostList['params']['GRID_ID'];
				$filter_id = $arPostList['params']['FILTER_ID'];
				$btrue = true;
			}
		}
		//если изменяется форма фильтра / сохранение фильтра
		if ($btrue)
		{
			$id = str_replace("SCODER_COLLECTIONS_FILTER_ID_","",$filter_id);		//ид настроеннной записи
			if ($id>0)
			{				
				foreach ($arPostList as $key => $val)
				{
					if (!is_array($val))
						$arPostList[$key] = mb_convert_encoding($val, LANG_CHARSET, "UTF-8");
					elseif (is_array($val))
					{
						foreach ($val as $k => $v)
						{
							if (!is_array($v))
								$arPostList[$key][$k] = mb_convert_encoding($v, LANG_CHARSET, "UTF-8");
							elseif (is_array($v))
							{
								foreach ($v as $j3 => $v3)
								{
									$arPostList[$key][$k][$j3] = mb_convert_encoding($v3, LANG_CHARSET, "UTF-8");
								}
							}
						}
					}
				}
				
				if (is_array($arPostList['fields']))
				{
					foreach ($arPostList['fields'] as $key => $value)
					{
						if ($value=='')
							unset($arPostList['fields'][$key]);
					}
				}
				//сохранить фильтр
				$set = new CScoderCollectionsSet;
				$Data = array(
					"SECTION_ID" => $id,
					"TYPE_ID" => "F",
					'FILTER' => serialize($arPostList),
				);
				
				$result = $set->Edit($Data);
				
			}
		}
	}
	public static function OnAfterIBlockElementAddHandler($arFields)
	{
		$str = Option::get("scoder.collections", "REINDEX_IBLOCKS");
		$arIblocks = unserialize($str);
		
		if ($arFields["ID"]>0 
			&& $arFields["IBLOCK_ID"]>0
				&&is_array($arIblocks)
					&& in_array($arFields["IBLOCK_ID"],$arIblocks)
				)
		{
			CScoderCollections::ProductEdit($arFields,true);
		}
	}
	public static function OnAfterIBlockElementUpdateHandler($arFields)
	{
		$str = Option::get("scoder.collections", "REINDEX_IBLOCKS");
		$arIblocks = unserialize($str);
		if ($arFields["ID"]>0 
			&& $arFields["IBLOCK_ID"]>0
				&&is_array($arIblocks)
					&& in_array($arFields["IBLOCK_ID"],$arIblocks)
				)
		{
			CScoderCollections::ProductEdit($arFields,false);
		}
	}

	//После добавления раздела
	public static function OnAfterIBlockSectionAddHandler(&$arFields)
	{
		self::SectionEdit($arFields,true);
	}
	//После изменения раздела
	public static function OnAfterIBlockSectionUpdateHandler(&$arFields)
	{
		self::SectionEdit($arFields,false);
	}
	public static function SectionEdit(&$arFields,$is_new)
	{
		if (Loader::includeSharewareModule(self::MODULE_ID) == Loader::MODULE_DEMO_EXPIRED)
		{
			
		}
		else
		{
			
			global $APPLICATION;
			$request = Application::getInstance()->getContext()->getRequest(); 
			
			//СОХРАНЯЕМ КОЛЛЕКЦИЮ если изменился раздел инфоблока
			if((stripos($request->getRequestUri(),"/bitrix/admin/iblock_section_edit.php") === 0 || 
				stripos($request->getRequestUri(),"/bitrix/admin/cat_section_edit.php") === 0)
					&& $arFields["ID"]>0
				)
			{
				$count = 0;
				if (is_array($request->getPost("rule")))
					$count = count($request->getPost("rule"));
				$TYPE_ID = $request->getPost("TYPE_ID");
				if ($TYPE_ID=='')
					$TYPE_ID = 'D';
				
				$bCatalog = false;
				//установлен ли модуль Каталог
				if (Loader::includeModule("catalog"))
					$bCatalog = true;
				
				if ($count>1 || $TYPE_ID == "F")
				{
					$ID = $arFields["ID"];		//ид раздела
					$IBLOCK_ID = $request->getQuery("IBLOCK_ID");		//ид инфоблока
					$IS_SECTION_ACTIVE_UPDATE = $request->getPost("IS_SECTION_ACTIVE_UPDATE");
					
					$Data = Array(
						"SECTION_ID" => $ID,
						"IBLOCK_ID" => $IBLOCK_ID,
						"IS_SECTION_ACTIVE_UPDATE" => ($IS_SECTION_ACTIVE_UPDATE == "Y" ? "Y" : "N"),
						"TYPE_ID" => ($TYPE_ID == "F" ? "F" : "D"),
						"COLLECTION_IBLOKS" => "",
					);
					
					if ($bCatalog)
					{
						$CONDITIONS = '';
						$CHECK_PARENT = $request->getPost("CHECK_PARENT");
						$CATALOG_AVAILABLE = $request->getPost("CATALOG_AVAILABLE");
						$DISCOUNT_ACTION = $request->getPost("DISCOUNT_ACTION");
						$COLLECTION_IBLOKS = $request->getPost("COLLECTION_IBLOKS");
						$obCond = new CCatalogCondTree();
					
						$boolCond = $obCond->Init(BT_COND_MODE_PARSE, BT_COND_BUILD_CATALOG, array());
						if ($boolCond)
						{
							$boolCond = false;
							if (array_key_exists('CONDITIONS', $_POST) && array_key_exists('CONDITIONS_CHECK', $_POST))
							{
								if (is_string($request->getPost("CONDITIONS"))
									&& is_string($request->getPost("CONDITIONS_CHECK")) 
										&& md5($request->getPost("CONDITIONS")) == $request->getPost("CONDITIONS_CHECK")
										)
								{
									$CONDITIONS = base64_decode($request->getPost("CONDITIONS"));
									if (CheckSerializedData($CONDITIONS))
									{
										$CONDITIONS = unserialize($CONDITIONS);
										$boolCond = true;
									}
									else
									{
										$boolCondParseError = true;
									}
								}
							}

							if (!$boolCond)
								$CONDITIONS = $obCond->Parse();
						}
						$UNPACK = $obCond->Generate($CONDITIONS, array('FIELD' => '$arProduct'));
						
						$CONDITIONS = base64_encode(serialize($CONDITIONS));
						
						if (is_array($COLLECTION_IBLOKS))
						{
							foreach ($COLLECTION_IBLOKS as $val)
								if ($val !='' && $val>0)
									$ar[] = $val;
							
							if (is_array($ar))
								$Data['COLLECTION_IBLOKS'] = serialize($ar);
							else
								$Data['COLLECTION_IBLOKS'] = '';
						}
						$Data["CHECK_PARENT"] = ($CHECK_PARENT == "Y" ? "Y" : "N");
						$Data["CONDITIONS"] = $CONDITIONS;
						$Data["UNPACK"] = $UNPACK;
						$Data["CATALOG_AVAILABLE"] = ($CATALOG_AVAILABLE=="Y" ? "Y" : "N");
						$Data["DISCOUNT_ACTION"] = (isset($DISCOUNT_ACTION) ? $DISCOUNT_ACTION : "");
					}
					
					//Запись коллекции в БД
					$set = new CScoderCollectionsSet;
					$result = $set->Edit($Data);
				}
				else
				{
					$arSet = CScoderCollectionsSet::CheckRecord($arFields["ID"]);	//запрос коллекции
					if (is_array($arSet))
						CScoderCollectionsSet::Delete($arFields["ID"]);		//Удаляем коллекцию
				}
			}
		}
	}
	//После удаления раздела
	public static function OnAfterIBlockSectionDeleteHandler($arFields)
	{
		if ($arFields["ID"]>0)
		{
			$arSet = CScoderCollectionsSet::CheckRecord($arFields["ID"]);	//запрос коллекции
			
			if (is_array($arSet))
			{
				CScoderCollectionsSet::Delete($arFields["ID"]);		//Удаляем коллекцию
			}
		}
		
	}
	public static function OnAfterIBlockElementSetPropertyValuesHandler($ELEMENT_ID, $IBLOCK_ID, $PROPERTY_VALUES, $PROPERTY_CODE)
	{
		$str = Option::get("scoder.collections", "REINDEX_IBLOCKS");
		$arIblocks = unserialize($str);
		
		if ($ELEMENT_ID>0 
			&& $IBLOCK_ID>0
				&&is_array($arIblocks)
					&& in_array($IBLOCK_ID,$arIblocks)
				)
		{
			$arFields["ID"] = $ELEMENT_ID;
			$arFields["IBLOCK_ID"] = $IBLOCK_ID;
			CScoderCollections::ProductEdit($arFields,false);
		}
	}
	public static function OnAfterIBlockElementSetPropertyValuesExHandler($ELEMENT_ID, $IBLOCK_ID, $PROPERTY_VALUES, $FLAGS)
	{
		$str = Option::get("scoder.collections", "REINDEX_IBLOCKS");
		$arIblocks = unserialize($str);
		if ($ELEMENT_ID>0 
			&& $IBLOCK_ID>0
				&&is_array($arIblocks)
					&& in_array($IBLOCK_ID,$arIblocks)
				)
		{
			$arFields["ID"] = $ELEMENT_ID;
			$arFields["IBLOCK_ID"] = $IBLOCK_ID;
			CScoderCollections::ProductEdit($arFields,false);
		}
	}
	
	public static function OnAdminTabControlBeginHandler(&$obTabControl)
	{
		if (Loader::includeSharewareModule(self::MODULE_ID) == Loader::MODULE_DEMO_EXPIRED)
			return true;
		
		global $APPLICATION;
		$request = Application::getInstance()->getContext()->getRequest();
		
		$sect_id = $request->getQuery("ID");
		$iblock_id = $request->getQuery("IBLOCK_ID");
		
		//установлен ли модуль Каталог
		if (Loader::includeModule("catalog"))
			$bCatalog = true;
		
		if ($bCatalog)
		{
			$arIblocks = CScoderCollections::get_iblocks();
			
			//если инфоблок, не торговый каталог
			if (!is_array($arIblocks)
				|| !in_array($iblock_id,$arIblocks)
			)
			{
				$bCatalog = false;
			}
		}
		
		//ВЫВОДИМ КОЛЛЕКЦИЮ
		if(stripos($request->getRequestUri(),"/bitrix/admin/iblock_section_edit.php") === 0
			|| stripos($request->getRequestUri(),"/bitrix/admin/cat_section_edit.php") === 0
			)
		{
			$sect_name = '';
			$dis_active_filter = Option::get(self::MODULE_ID, "DIS_ACTIVE_FILTER");
			
			if ($sect_id>0)
			{
				$arFilter = array(
					'ID' => $sect_id,
					"IBLOCK_ID"=>$iblock_id
				);
				$rsSect = CIBlockSection::GetList(
					array('left_margin' => 'asc'),
					$arFilter,
					false,
					array("ID","IBLOCK_ID","NAME"),
					array('nTopCount'=>1)
				);
				if ($arSection = $rsSect->GetNext())
				{
					$sect_name = " - " . $arSection["NAME"];
					$arSet = CScoderCollectionsSet::CheckRecord($sect_id);	//запрос коллекции
					//Если интерфейс фильтра отключен - явно меняем тип фильтра, чтобы в итогое получился сброс
					if ($dis_active_filter=="Y")
						$arSet["TYPE_ID"] = "D";
				}
			}
			if (is_array($arSet))
			{
				$ID = $arSet["SECTION_ID"];
			}
			
			ob_start();
			?>
			<?if (isset($arSet) && is_array($arSet)):?>
				<tr>
					<td></td>
					<td>
						<span class="adm-list-table-top-wrapper" style="float:right;"><a href="javascript:Start();" class="adm-btn adm-btn-green" title=""><?=Loc::getMessage("SCODER_REINDEX_BUT",array("#NAME#"=>$sect_name))?></a></span>
					</td>
				</tr>
			<?endif?>
			<tr class="heading">
				<td colspan="2"><?=Loc::getMessage('SCODER_SUB_CONDITIONS');?></td>
			</tr>
			
			<tr>
				<td width="50%"><?=Loc::getMessage('SCODER_TYPE');?></td>
				<td>
					<input type="hidden" name="TYPE_ID" value="<?if ($bCatalog):?>D<?else:?>F<?endif;?>" >
					<?//--------------Если не установлен модуль каталога или инфоблок - не торговый каталог, всегда выбран?>
					<input 
						id = "sc-collections-type"
						type="checkbox" 
						name="TYPE_ID" 
						value="F" 
						<?if (($arSet["TYPE_ID"] == 'F' || !$bCatalog) && $dis_active_filter!="Y") echo 'checked'?> 
						<?if (!$bCatalog || $dis_active_filter=="Y") echo 'disabled="disabled"'?> 
						/>
					<style>
						<?if ($arSet["TYPE_ID"] == 'F'):?>
							.sc-hidden {
								display:none;
							}
						<?elseif ($bCatalog=="true"):?>
							.sc-filter-block {
								display:none;
							}
						<?endif?>
					</style>
					<?
					if ($bCatalog):
						CJSCore::Init(array("jquery"));
						?>
						<script>
							BX.ready(function(){
								$(document).on('change', 'input#sc-collections-type', function(e) {
									
									if ($(this).prop( "checked"))
									{
										$('.sc-hidden').hide();
										$('.sc-filter-block').show();
									}
									else
									{
										$('.sc-hidden').show();
										$('.sc-filter-block').hide();
									}
									return false;
								});
							});
						</script>
					<?endif?>
				</td>
			</tr>
			<tr>			
				<td colspan="2">
					<div id="generate_result">	
						<div class="adm-info-message-wrap adm-info-message-red"></div>
					</div>
					<?if ($ID>0):?>
						<script>
							var stop = false;

							function Start()
							{
								stop = false;
								Generate(0);
							}

							function Stop()
							{
								stop = true;
							}

							function Generate(last_id)
							{
								if(stop)
								{
									return;
								}
								
								CHttpRequest.Action = function(result)
								{
									CloseWaitWindow();
									document.getElementById('generate_result').innerHTML = result;
								};
								ShowWaitWindow();
								
								var url = '/bitrix/admin/scoder_generate.php?lang=ru&<?=bitrix_sessid_get();?>&set_id=<?=$ID;?>&last_id='+last_id;
								
								CHttpRequest.Send(url);
							}
						</script>
					<?endif?>
				</td>
			</tr>
			<tr class="sc-filter-block">
				<td width="50%"><?=Loc::getMessage('SCODER_FILTER');?></td>
				<td>
					<?
					if ($sect_id>0)
					{
						if ($dis_active_filter!="Y")
						{
							$type = '';
							if (isset($_REQUEST['type']) && is_string($_REQUEST['type']))
								$type = trim($_REQUEST['type']);
							
							$str_id = 'SCODER_COLLECTIONS_FILTER_ID_' . $sect_id;
							
							$arParams = array(
								"ID" => $sect_id,
								"IBLOCK_ID" => $iblock_id,
								"IBLOCK_TYPE" => $type,
								"TABLE_ID" => $str_id,
							);
							$all_fields = CScoderCollections::__get_filters($arParams);
							
							$filterFields = $all_fields["CATALOG_FIELDS"];
							
							if ($arSet["FILTER"]!='')
							{
								$ar_filter_value = unserialize($arSet["FILTER"]);
								
								if ($ar_filter_value["preset_id"]!='')
								{
									$preset_id = $ar_filter_value['preset_id'];
								}
								elseif ($ar_filter_value["data"]['preset_id']!='')
								{
									$ar_filter_value = $ar_filter_value["data"];
									$preset_id = $ar_filter_value['preset_id'];
								}
								//переписываем кэш
								if ($preset_id!='')
								{
									$options = new \Bitrix\Main\UI\Filter\Options($str_id, NULL, NULL);
									$options->setFilterSettings($preset_id, $ar_filter_value, true, false);		//переписываем кэш фильтра
									
									$options->save();
								}
							}
							
							$params = array(
								"FILTER_ID" => $str_id,
								"GRID_ID" => "SCODER_COLLECTIONS_FILTERS",
								"FILTER" => $filterFields,
								"FILTER_PRESETS" => array(),
								"ENABLE_LABEL" => true,
								"ENABLE_LIVE_SEARCH" => true
							);
							
							?>
							<?
							$APPLICATION->includeComponent(
								"bitrix:main.ui.filter",
								"",
								$params,
								false,
								array("HIDE_ICONS" => true)
							);
						}
					}
					else
						echo Loc::getMessage('SCODER_NEED_SAVE');
                    ?>
				</td>
			</tr>
			<?if ($bCatalog):		//если установлен модуль каталога и инфоблок - торговый каталог?>
				<tr  id="tr_CONDITIONS" class="sc-conditions-block sc-hidden">
					<td colspan="2">
						<div id="tree" style="position: relative; z-index: 1;"></div>
						<?
						if ($ID > 0)
						{
							$CONDITIONS = $arSet['CONDITIONS'];
						}
						$f_CONDITIONS = isset($CONDITIONS) ? $CONDITIONS : "";
						$f_CONDITIONS = unserialize(base64_decode($f_CONDITIONS));
						if (!is_array($f_CONDITIONS))
						{
							if (CheckSerializedData($f_CONDITIONS))
							{
								$f_CONDITIONS = unserialize($f_CONDITIONS);
							}
							else
							{
								$f_CONDITIONS = '';
							}
						}
						$obCond = new CCatalogCondTree();
						$boolCond = $obCond->Init(BT_COND_MODE_DEFAULT, BT_COND_BUILD_CATALOG, array('FORM_NAME' => 'form_section_'.$iblock_id.'_form', 'CONT_ID' => 'tree', 'JS_NAME' => 'JSCatCond'));
						if (!$boolCond)
						{
							if ($ex = $APPLICATION->GetException())
							echo $ex->GetString()."<br>";
						}
						else
						{
							$obCond->Show($f_CONDITIONS);		//показываем
						}
						
						?>
					</td>
				</tr>
			<?endif?>
			<tr class="heading">
				<td colspan="2"><?=Loc::getMessage('SCODER_DOP_CONDITIONS');?></td>
			</tr>
			<?if ($bCatalog):		//если установлен модуль каталога и инфоблок - торговый каталог?>
				<tr class="sc-conditions-block sc-hidden">
					<td width="50%"><?=Loc::getMessage('SCODER_IS_CHECK_PARENT');?></td>
					<td><input type="checkbox" name="CHECK_PARENT" value="Y" <?if ($arSet["CHECK_PARENT"] == 'Y') echo 'checked'?>/></td>
				</tr>
			
				<tr class="sc-conditions-block sc-hidden">
					<td width="50%"><?=Loc::getMessage('SCODER_IBLOCKS');?></td>
					<td>
						<?
						$arIblocksInfo = CScoderCollections::get_iblocks_info();
						if (!is_null($arSet['COLLECTION_IBLOKS']) && strlen($arSet['COLLECTION_IBLOKS'])>0)
						{
							$ar_iblocks = unserialize($arSet['COLLECTION_IBLOKS']);
						}
						?>
						<select name="COLLECTION_IBLOKS[]" multiple size="5">
							<option value="" <?if (!isset($ar_iblocks) || !is_array($ar_iblocks)) echo 'selected'?>><?=Loc::getMessage('SCODER_IBLOCKS_ALL');?></option>
							<?if (is_array($arIblocksInfo)):?>
								<?foreach ($arIblocksInfo as $arItem):?>
									<option value="<?=$arItem['ID']?>" <?if (is_array($ar_iblocks) && in_array($arItem['ID'],$ar_iblocks)) echo 'selected'?>><?=$arItem['NAME']?></option>
								<?endforeach;?>
							<?endif?>
						</select>
					</td>
				</tr>
				<tr class="sc-conditions-block sc-hidden">
					<td width="50%"><?=Loc::getMessage('SCODER_CATALOG_AVAILABLE');?></td>
					<td>
						<input type="checkbox" name="CATALOG_AVAILABLE" value="Y" <?if ($arSet["CATALOG_AVAILABLE"] == 'Y') echo 'checked'?>/>
					</td>
				</tr>
				<tr class="sc-conditions-block sc-hidden">
					<td width="50%"><?=Loc::getMessage('SCODER_DISCOUNT_PRODUCTS');?></td>
					<td>
						<?$arDiscountActions = array(
							"AND" => Loc::getMessage('SCODER_DISCOUNT_ACTION_AND'),
							"OR" => Loc::getMessage('SCODER_DISCOUNT_ACTION_OR'),
							"NOT" => Loc::getMessage('SCODER_DISCOUNT_ACTION_NOT'),
						);?>
						<select name="DISCOUNT_ACTION" >
							<option value="" ><?=Loc::getMessage('SCODER_NOT');?></option>
							<?if (is_array($arDiscountActions)):?>
								<?foreach ($arDiscountActions as $code =>  $name):?>
									<option value="<?=$code;?>" <?if ($arSet["DISCOUNT_ACTION"] == $code) echo 'selected'?>><?=$name?></option>
								<?endforeach;?>
							<?endif?>
						</select>
					</td>
				</tr>	
			<?endif?>
			<tr>
				<td width="50%"><?=Loc::getMessage('SCODER_IS_SECTION_ACTIVE_UPDATE');?></td>
				<td>
					<input type="checkbox" name="IS_SECTION_ACTIVE_UPDATE" value="Y" <?if ($arSet["IS_SECTION_ACTIVE_UPDATE"] == 'Y') echo 'checked'?>/>
				</td>
			</tr>
			
			<?$ptForm = ob_get_contents();
			ob_end_clean();
			
			$obTabControl->tabs[] = array(
				'DIV' 				=> 'scoder_collections', 
				'TAB' 				=> Loc::getMessage('SCODER_TAB_TITLE'),
				'TITLE' 			=> Loc::getMessage('SCODER_TAB_DESCRIPTION'),
				'CONTENT'		=> $ptForm,
			);	
		}
		
		return true;
	}
	public static function OnBeforeIBlockSectionUpdateHandler(&$arFields)
    {
		if (Loader::includeSharewareModule(self::MODULE_ID) == Loader::MODULE_DEMO_EXPIRED)
		{
			
		}
		elseif (isset($arFields['ACTIVE']))
		{
			$arIblocks = CScoderCollections::get_iblocks();
			if($arFields['ID']>0
				&& $arFields['IBLOCK_ID']>0
					&& is_array($arIblocks)
						&& in_array($arFields['IBLOCK_ID'],$arIblocks)
					)
			{
				$arSet = CScoderCollectionsSet::CheckRecord($arFields['ID']);	//запрос коллекции
				
				if (is_array($arSet) 
					&& $arSet['ID']>0
						&& $arSet['IS_SECTION_ACTIVE_UPDATE']=='Y'
						)
				{
					unset($arFields['ACTIVE']);
				}	
			}
		}
    }
}
?>