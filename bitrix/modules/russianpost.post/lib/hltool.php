<?php
use Bitrix\Highloadblock as HL;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Entity;
use Bitrix\Main\Type\DateTime;

class CRussianpostHLtool
{
	const MODULE_ID = 'russianpost.post';

	public static function exportBlock($arParams=array())
	{
		CModule::IncludeModule("highloadblock");
		$arStepParams=$arParams;
		if( !empty($arParams["BLOCK_CODE"]) )
		{
			$hlblock=HL\HighloadBlockTable::getList(array("filter"=>array('=NAME'=>$arParams["BLOCK_CODE"])))->fetch();
			$arStepParams["object"]=$hlblock["ID"];
		}
		$stepRes=self::exportBlockStep($arStepParams);
		while(empty($stepRes["errors"])&&!$stepRes["finish"])
		{
			$stepRes=self::exportBlockStep($stepRes);
		}
		return $stepRes;
	}

	public static function exportBlockStep($arParams=array())
	{
		CModule::IncludeModule("highloadblock");

		$hls=array();//used in export. TODO -get only need block
		$hlsVisual=array();
		$res=HL\HighloadBlockTable::getList(array(
				'select'=>array(
					'*', 'NAME_LANG'=>'LANG.NAME'
				),
				'order'=>array(
					'NAME_LANG'=>'ASC', 'NAME'=>'ASC'
				)
		));
		while($row=$res->fetch())
		{
			$row['NAME_LANG']=$row['NAME_LANG']!='' ? $row['NAME_LANG'] : $row['NAME'];
			$hlsVisual[$row['ID']]=$row;
			unset($row['NAME_LANG']);
			$hls[$row['ID']]=$row;
		}

		global $USER_FIELD_MANAGER;
		$context=\Bitrix\Main\Application::getInstance()->getContext();
		$server=$context->getServer();

		$errors=array();
		$userFelds=array();

		// data for next hit
		$NS=array(
			'url_data_file'=>$arParams['url_data_file'], //file_path '/upload/hlback/LSStreet.xml'
			'object'=>$arParams['object'], //hlblock_id
			'export_hl'=>$arParams['export_hl'], //export hl_block description and props
			'export_data'=>$arParams['export_data'], //export hl_block elements
			'step'=>(int) $arParams['step'],
			'last_id'=>(int) $arParams['last_id'],
			'count'=>(int) $arParams['count'],
			'has_files'=>(int) $arParams['has_files'],
			'left_margin'=>0,
			'right_margin'=>0,
			'all'=>0,
			'percent'=>0,
			'time_limit'=>30,
			'finish'=>false
		);

		// check filename
		if( substr($NS['url_data_file'], -4)!='.xml' )
		{
			$errors[]=Loc::getMessage('XML_FILENAME_IS_NOT_XML');
		}

		//da($NS);
		if( empty($errors) )
		{
			// create / open file
			$export=new \Bitrix\Main\XmlWriter(array(
				'file'=>$NS['url_data_file'],
				'create_file'=>$NS['step']==0,
				'charset'=>SITE_CHARSET,
				'lowercase'=>true,
				'tab'=>$NS['step']==0 ? 0 : 2
			));
			$export->openFile();

			if( !$export->getErrors() )
			{
				// first step - meta-data and open items-tag
				if( $NS['step']==0 )
				{
					$export->writeBeginTag('hiblock');
					// write hlblock

					if( $NS['export_hl']&&$NS['object'] )
					{
						$export->writeItem(array(
							'hiblock'=>$hls[$NS['object']]
						));
						// write langs
						$export->writeBeginTag('langs');
						$res=HL\HighloadBlockLangTable::getList(array(
								'filter'=>array(
									'ID'=>$NS['object']
								)
						));
						while($row=$res->fetch())
						{
							$export->writeItem(array(
								'lang'=>$row
							));
						}
						$export->writeEndTag('langs');
					}
					// write fields
					if( $NS['export_hl']&&$NS['object'] )
					{
						$export->writeBeginTag('fields');
						$res=\CUserTypeEntity::GetList(
								array(), array(
								'ENTITY_ID'=>'HLBLOCK_'.$NS['object']
								)
						);
						while($row=$res->fetch())
						{
							$row=\CUserTypeEntity::getById($row['ID']);//for get langs
							$row['BASE_TYPE']='';
							if( isset($USER_FIELD_MANAGER) )
							{
								$type=$USER_FIELD_MANAGER->GetUserType($row['USER_TYPE_ID']);
								if( is_array($type)&&isset($type['BASE_TYPE']) )
								{
									$row['BASE_TYPE']=$type['BASE_TYPE'];
									// get enums
									if( $type['BASE_TYPE']=='enum' )
									{
										$i=0;
										$row['enums']=array();
										$enumValues=array();
										$resE=\CUserFieldEnum::GetList(
												array(), array(
												'USER_FIELD_ID'=>$row['ID']
												)
										);
										while($rowE=$resE->fetch())
										{
											$row['enums']['enum'.$i++]=$rowE;
											$enumValues[$rowE['ID']]=$rowE['VALUE'];
										}
									}
								}
							}
							// check some settings
							if( isset($row['SETTINGS'])&&is_array($row['SETTINGS']) )
							{
								if( isset($row['SETTINGS']['HLBLOCK_ID']) )
								{
									$hid=$row['SETTINGS']['HLBLOCK_ID'];
									$row['SETTINGS']['HLBLOCK_TABLE']=$hls[$hid]['TABLE_NAME'];
								}
								if( isset($row['SETTINGS']['EXTENSIONS'])&&is_array($row['SETTINGS']['EXTENSIONS'])&&$row['USER_TYPE_ID']=='file' )
								{
									$row['SETTINGS']['EXTENSIONS']=implode(', ', array_keys($row['SETTINGS']['EXTENSIONS']));
								}
							}
							$export->writeItem(array(
								'field'=>$row
							));
							$row['enum_values']=$enumValues;
							$userFelds[$row['FIELD_NAME']]=$row;
						}
						$export->writeEndTag('fields');
					}
					// begin write items
					if( $NS['export_data'] )
					{
						$export->writeBeginTag('items');
					}
				}

				// if not select user fields
				if( empty($userFelds)&&$NS['object'] )
				{
					$res=\CUserTypeEntity::GetList(
							array(), array(
							'ENTITY_ID'=>'HLBLOCK_'.$NS['object']
							)
					);
					while($row=$res->fetch())
					{
						$row['BASE_TYPE']='';
						if( isset($USER_FIELD_MANAGER) )
						{
							$type=$USER_FIELD_MANAGER->GetUserType($row['USER_TYPE_ID']);
							if( is_array($type)&&isset($type['BASE_TYPE']) )
							{
								$row['BASE_TYPE']=$type['BASE_TYPE'];
							}
						}
						$userFelds[$row['FIELD_NAME']]=$row;
					}
				}

				// write data
				if( $NS['export_data']&&$NS['object'] )
				{
					$dataExist=false;
					if( $hlblock=HL\HighloadBlockTable::getById($NS['object'])->fetch() )
					{
						$startTime=time();
						$filesPath=$server->getDocumentRoot().substr($NS['url_data_file'], 0, -4).'_files';
						$entity=HL\HighloadBlockTable::compileEntity($hlblock)->getDataClass();
						$res=$entity::getList(array(
								'filter'=>array(
									'>ID'=>$NS['last_id']
								),
								'order'=>array(
									'ID'=>'ASC'
								)
						));
						while($row=$res->fetch())
						{
							foreach($row as $k=> $v)
							{
								if( $userFelds[$k]['BASE_TYPE']=='file' )
								{
									$NS['has_files']=1;
								}
								$v=self::__hlExportPrepareField(
										$v, $userFelds[$k], array(
										'path'=>$filesPath,
										)
								);
								if( is_array($v) )
								{
									$v='serialize#'.serialize($v);
								}
								$row[$k]=$v;
							}
							$export->writeItem(array(
								'item'=>$row
							));
							$dataExist=true;
							$NS['count'] ++;
							$NS['last_id']=$row['ID'];
							if( time()-$NS['time_limit']>$startTime )
							{
								break;
							}
						}

						if( !$dataExist )
						{
							$NS['finish']=true;
						}

						// calculate margins
						$res=$entity::getList(array(
								'select'=>array(
									new \Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(*)'),
								)
						));
						if( $row=$res->fetch() )
						{
							$NS['all']=$row['CNT'];
							$NS['right_margin']=$row['CNT']-$NS['count'];
						}
						$NS['left_margin']=$NS['count'];
						if( $NS['all']!=0 )
						{
							$NS['percent']=round($NS['count']/$NS['all']*100, 2);
						}
						else
						{
							$NS['percent']=100;
						}
					}
				}
				else
				{
					$NS['percent']=100;
					$NS['finish']=true;
				}

				$NS['step'] ++;
			}

			if( $export->getErrors() )
			{
				foreach($export->getErrors() as $error)
				{
					$errors[]=Loc::getMessage($error->getCode());
				}
				/* \CAdminMessage::ShowMessage(array(
				  'MESSAGE'=>Loc::getMessage('ADMIN_TOOLS_ERROR_EXPORT'),
				  'DETAILS'=>implode('<br/>', $errors),
				  'HTML'=>true,
				  'TYPE'=>'ERROR',
				  )); */
			}
			else
			{ //. do nothing
				/* $details=Loc::getMessage('ADMIN_TOOLS_PROCESS_PERCENT', array(
				  '#percent#'=>$NS['percent'],
				  '#count#'=>$NS['count'],
				  '#all#'=>$NS['all'],
				  )); */
				/*
				  if( $NS['finish'] )
				  {
				  $pathInfo=pathinfo($NS['url_data_file']);
				  $pathInfo['files_dir']=$pathInfo['filename'].'_files';
				  $details.='<br/>'.Loc::getMessage('ADMIN_TOOLS_PROCESS_FINAL', array(
				  '#xml_link#'=>'<a href="/bitrix/admin/fileman_admin.php?lang='.LANG.'&amp;path='.htmlspecialcharsbx(urlencode($pathInfo['dirname'])).'&amp;set_filter=Y&amp;find_name='.htmlspecialcharsbx(urlencode($pathInfo['basename'])).'" target="_blank">'.
				  htmlspecialcharsbx($pathInfo['basename']).
				  '</a>'
				  ));
				  if( $NS['has_files'] )
				  {
				  $details.='<br/>'.Loc::getMessage('ADMIN_TOOLS_PROCESS_FILES_FINAL', array(
				  '#files_link#'=>'<a href="/bitrix/admin/fileman_admin.php?lang='.LANG.'&amp;path='.htmlspecialcharsbx(urlencode($pathInfo['dirname'])).'&amp;set_filter=Y&amp;find_name='.htmlspecialcharsbx(urlencode($pathInfo['files_dir'])).'" target="_blank">'.
				  htmlspecialcharsbx($pathInfo['files_dir']).
				  '</a>'
				  ));
				  }
				  }
				  CAdminMessage::ShowMessage(array(
				  'MESSAGE'=>Loc::getMessage('ADMIN_TOOLS_PROCESS_EXPORT'),
				  'DETAILS'=>$details,
				  'HTML'=>true,
				  'TYPE'=>'PROGRESS',
				  ));
				  if( $NS['finish'] )
				  {
				  \CAdminMessage::ShowMessage(array(
				  'MESSAGE'=>Loc::getMessage('ADMIN_TOOLS_PROCESS_FINISH_DELETE'),
				  'DETAILS'=>'',
				  'HTML'=>true,
				  'TYPE'=>'ERROR',
				  ));
				  } */
			}


			// final - errors or finished
			if( $export->getErrors()||$NS['finish'] )
			{
				// end write items
				if( $NS['export_data'] )
				{
					$export->writeEndTag('items');
				}
				$export->writeEndTag('hiblock');
				//	echo '<script>EndExport();</script>';
			}
			else
			{
				//	echo '<script>DoNext('.\CUtil::PhpToJSObject($NS).');</script>';
			}
			$export->closeFile();
		}
		$NS["errors"]=$errors;
		return $NS;
	}

	public static function __hlExportPrepareField($value, $userField,
		array $params=array())
	{
		if( is_array($value) )
		{
			foreach($value as &$v)
			{
				$v=self::__hlExportPrepareField($v, $userField, $params);
			}
			unset($v);
		}
		elseif( trim($value)!='' )
		{
			// file save to local folder
			if( $userField['BASE_TYPE']=='file' )
			{
				if( $file=\CFile::getFileArray($value) )
				{
					$tmpFile=\CFile::makeFileArray($value);
					if( isset($tmpFile['tmp_name'])&&$tmpFile['tmp_name']!='' )
					{
						$fileName=$file['SUBDIR'].'/'.$file['FILE_NAME'];
						$strNewFile=str_replace('//', '/', $params['path'].'/'.$fileName);
						CheckDirPath($strNewFile);
						if( @copy($tmpFile['tmp_name'], $strNewFile) )
						{
							return $file['SUBDIR'].'/'.$file['FILE_NAME'];
						}
						else
						{
							return '';
						}
					}
				}
			}
			// for enums get the vals
			elseif( $userField['BASE_TYPE']=='enum' )
			{
				if( $value==0 )
				{
					$value='';
				}
				elseif( isset($userField['enum_values'][$value]) )
				{
					$value=$userField['enum_values'][$value];
				}
			}
		}

		return $value;
	}

	public static function importBlockStep($arParams=array())
	{
		CModule::IncludeModule("highloadblock");

		// init data
		$hls=array();
		$hlTables=array();
		$xmlFields=array();
		$res=HL\HighloadBlockTable::getList(array(
				'select'=>array(
					'*', 'NAME_LANG'=>'LANG.NAME'
				),
				'order'=>array(
					'NAME_LANG'=>'ASC', 'NAME'=>'ASC'
				)
		));
		while($row=$res->fetch())
		{
			$row['NAME']=$row['NAME_LANG']!='' ? $row['NAME_LANG'] : $row['NAME'];

			// get fields for HL
			$row['FIELDS']=array(
				'ID'=>'ID'
			);
			$resF=\CUserTypeEntity::GetList(
					array(), array(
					'ENTITY_ID'=>'HLBLOCK_'.$row['ID'],
					'LANG'=>LANG
					)
			);
			while($rowF=$resF->fetch())
			{
				if( isset($USER_FIELD_MANAGER) )
				{
					$type=$USER_FIELD_MANAGER->GetUserType($rowF['USER_TYPE_ID']);
					if( is_array($type)&&isset($type['BASE_TYPE']) )
					{
						if( in_array($type['BASE_TYPE'], array('string', 'int')) )
						{
							$row['FIELDS'][$rowF['FIELD_NAME']]=$rowF['EDIT_FORM_LABEL']!='' ? $rowF['EDIT_FORM_LABEL'] : $rowF['FIELD_NAME'];
						}
					}
				}
			}

			$xmlFields[$row['ID']]=$row['FIELDS'];
			$hls[$row['ID']]=$row;
			$hlTables[$row['HLBLOCK_TABLE']]=$row['ID'];
		}

		global $USER_FIELD_MANAGER;
		global $APPLICATION;
		$context=\Bitrix\Main\Application::getInstance()->getContext();
		$server=$context->getServer();

		// process
		$userFelds=array();
		// data for next hit
		$NS=array(
			'url_data_file'=>$arParams['url_data_file'],
			'object'=>$arParams['object'],
			'xml_id'=>$arParams['xml_id'], //xml_id (or ID) analog fiecl code
			'import_hl'=>!$arParams['object']||$arParams['import_hl'],
			'import_data'=>$arParams['import_data'],
			'save_reference'=>$arParams['save_reference'],
			'step'=>(int) $arParams['step'],
			'last_id'=>(int) $arParams['last_id'],
			'count'=>(int) $arParams['count'],
			'has_files'=>(int) $arParams['has_files'],
			'xml_pos'=>unserialize($arParams['xml_pos']),
			'left_margin'=>0,
			'right_margin'=>0,
			'all'=>0,
			'percent'=>0,
			'time_limit'=>30,
			'finish'=>false,
		);

		// init
		$errors=array();
		$langs=array();
		$userFelds=array();
		$userFieldsEnums=self::__getEnumUserFields();
		$dataExist=false;
		$startTime=time();
		$import=new CXMLFileStream;
		$filesPath=$server->getDocumentRoot().substr($NS['url_data_file'], 0, -4).'_files';

		// get langs
		$langs=array();
		$res=\CLanguage::GetList($lby='sort', $lorder='asc');
		while($row=$res->getNext())
		{
			$langs[$row['LID']]=$row;
		}

		// get user fields
		if( $NS['object']>0 )
		{
			$res=\CUserTypeEntity::GetList(
					array(), array(
					'ENTITY_ID'=>'HLBLOCK_'.$NS['object']
					)
			);
			while($row=$res->fetch())
			{
				$userFelds[$row['FIELD_NAME']]=$row;
			}
		}

		// import hiblock
		$import->registerNodeHandler(
			'/hiblock/hiblock', function (CDataXML $xmlObject) use (&$NS, &$hls, &$errors)
		{
			if( $NS['import_hl']&&!$NS['object']&&empty($errors) )
			{
				$hiblock=CRussianpostHLtool::__prepareArrayFromXml($xmlObject->GetArray(), 'hiblock');
				if( !empty($hiblock) )
				{
					if( isset($hiblock['ID']) )
					{
						unset($hiblock['ID']);
					}
					$result=HL\HighloadBlockTable::add($hiblock);
					if( $result->isSuccess() )
					{
						$NS['object']=$result->getId();
						$hls[$NS['object']]=$hiblock;
					}
					else
					{
						$errors=array_merge($errors, $result->getErrorMessages());
					}
				}
				else
				{
					//$errors[]=Loc::getMessage('ADMIN_TOOLS_ERROR_HB_NOT_CREATE');
					$errors[]='ADMIN_TOOLS_ERROR_HB_NOT_CREATE';
				}
			}
			elseif( !$NS['object'] )
			{
				//$errors[]=Loc::getMessage('ADMIN_TOOLS_ERROR_HB_NOT_FOUND');
				$errors[]='ADMIN_TOOLS_ERROR_HB_NOT_FOUND';
			}
			elseif( $NS['object'] )
			{
				if( !HL\HighloadBlockTable::getById($NS['object'])->fetch() )
				{
					//$errors[]=Loc::getMessage('ADMIN_TOOLS_ERROR_HB_NOT_FOUND');
					$errors[]='ADMIN_TOOLS_ERROR_HB_NOT_FOUND';
				}
			}
		}
		);

		// import langs
		$import->registerNodeHandler(
			'/hiblock/langs/lang', function (CDataXML $xmlObject) use (&$NS, &$errors)
		{
			if( $NS['import_hl']&&$NS['object']&&empty($errors) )
			{
				$lang=CRussianpostHLtool::__prepareArrayFromXml($xmlObject->GetArray(), 'lang');
				if( !empty($lang) )
				{
					$lang['ID']=$NS['object'];
					// delete if exist
					$res=HL\HighloadBlockLangTable::getList(array(
							'filter'=>array(
								'ID'=>$lang['ID'],
								'LID'=>$lang['LID']
							)
					));
					if( $row=$res->fetch() )
					{
						HL\HighloadBlockLangTable::delete($row['ID']);
					}
					// add new
					HL\HighloadBlockLangTable::add($lang);
				}
			}
		}
		);

		// import uf
		$import->registerNodeHandler(
			'/hiblock/fields/field', function (CDataXML $xmlObject) use (&$NS, $hlTables, &$userFelds, &$userFieldsEnums, $langs, &$errors, $APPLICATION)
		{
			if( $NS['import_hl']&&$NS['object']&&empty($errors) )
			{
				$field=CRussianpostHLtool::__prepareArrayFromXml($xmlObject->GetArray(), 'field');
				if( !empty($field) )
				{
					// add new field, if no exist
					if( !isset($userFelds[$field['FIELD_NAME']]) )
					{
						if( isset($field['ID']) )
						{
							unset($field['ID']);
						}
						// re-set some settings
						if( isset($field['SETTINGS'])&&is_array($field['SETTINGS']) )
						{
							if( isset($field['SETTINGS']['HLBLOCK_TABLE'])&&$field['SETTINGS']['HLBLOCK_TABLE']!='' )
							{
								$field['SETTINGS']['HLBLOCK_ID']=$hlTables[$field['HLBLOCK_TABLE']['HLBLOCK_TABLE']];
							}
						}
						// set language keys to lowercase
						$codes=array('EDIT_FORM_LABEL', 'LIST_COLUMN_LABEL', 'LIST_FILTER_LABEL',
							'ERROR_MESSAGE', 'HELP_MESSAGE');
						foreach($codes as $code)
						{
							if( isset($field[$code])&&is_array($field[$code]) )
							{
								foreach($langs as $lng=> $lang)
								{
									if( $lng!==strtoupper($lng)&&isset($field[$code][strtoupper($lng)]) )
									{
										$field[$code][$lng]=$field[$code][strtoupper($lng)];
										unset($field[$code][strtoupper($lng)]);
									}
								}
							}
						}
						// add field
						$field['ENTITY_ID']='HLBLOCK_'.$NS['object'];
						$userField=new \CUserTypeEntity;
						$fId=$userField->add($field);
						if( $fId>0 )
						{
							$userFelds[$field['FIELD_NAME']]=$field;
							// set enumeration list
							if(
								$fId&&$field['BASE_TYPE']=='enum'&&
								isset($field['ENUMS'])&&!empty($field['ENUMS'])
							)
							{
								$enums=array();
								foreach(array_values($field['ENUMS']) as $k=> $enum)
								{
									$enums['n'.$k]=array(
										'VALUE'=>$enum['VALUE'],
										'DEF'=>$enum['DEF'],
										'SORT'=>$enum['SORT'],
										'XML_ID'=>$enum['XML_ID']
									);
								}
								$userFieldEnums=new \CUserFieldEnum;
								$userFieldEnums->setEnumValues($fId, $enums);
								// add new values
								$userFieldsEnums[$fId]=CRussianpostHLtool::__getEnumUserFields($fId, true);
							}
						}
						else
						{
							if( $e=$APPLICATION->getException() )
							{
								$errors[]=$e->getString();
							}
						}
					}
				}
			}
		}
		);

		if( $NS["import_data"] )
		{
			// import data
			$import->registerNodeHandler(
				'/hiblock/items/item', function (CDataXML $xmlObject) use (&$NS, $hls, $filesPath, $userFelds, &$errors, $USER_FIELD_MANAGER)
			{
				static $class=null;
				static $hlLocal=null;
				static $userFeldsLocal=null;
				static $userFeldsEnumLocal=null;
				if( $NS['object']&&empty($errors) )
				{
					// first refill some arrays if need
					if( !isset($hls[$NS['object']]) )
					{
						if( $hlLocal===null )
						{
							$hlLocal=HL\HighloadBlockTable::getById($NS['object'])->fetch();
						}
						$hls[$NS['object']]=$hlLocal;
					}
					if( !$hls[$NS['object']] )
					{
						//$errors[]=Loc::getMessage('ADMIN_TOOLS_ERROR_HB_NOT_FOUND');
						$errors[]='ADMIN_TOOLS_ERROR_HB_NOT_FOUND';
						return;
					}
					if( empty($userFelds) )
					{
						if( $userFeldsLocal===null )
						{
							$userFeldsLocal=array();
							$res=\CUserTypeEntity::GetList(
									array(), array(
									'ENTITY_ID'=>'HLBLOCK_'.$NS['object']
									)
							);
							while($row=$res->fetch())
							{
								$userFeldsLocal[$row['FIELD_NAME']]=$row;
							}
						}
						$userFelds=$userFeldsLocal;
					}
					if( $userFeldsEnumLocal===null )
					{
						$userFeldsEnumLocal=CRussianpostHLtool::__getEnumUserFields(false, true);
					}
					$userFieldsEnums=$userFeldsEnumLocal;
					// then add
					$item=CRussianpostHLtool::__prepareArrayFromXml($xmlObject->GetArray(), 'item');
					if( !empty($item) )
					{
						$NS['count'] ++;
						if( !isset($item['ID']) )
						{
							$item['ID']='unknown';
						}
						//Bitrix\Main\Diag\Debug::Dump($hls);
						//Bitrix\Main\Diag\Debug::Dump($hls[$NS['object']]);
						if( $class===null )
						{
							$hlblockTemp = HL\HighloadBlockTable::getById($NS['object'])->fetch(); 
							if( $entity=HL\HighloadBlockTable::compileEntity($hlblockTemp) )
							{
								$class=$entity->getDataClass();
							}
						}
						if( $class )
						{
							// send event
							$event=new \Bitrix\Main\Event(self::MODULE_ID, 'onBeforeItemImportAdd', array(
								'ITEM'=>$item,
								'USER_FIELDS'=>$userFelds,
								'NS'=>$NS,
							));
							$event->send();
							foreach($event->getResults() as $result)
							{
								if( $result->getResultType()!=\Bitrix\Main\EventResult::ERROR )
								{
									if( ($modified=$result->getModified() ) )
									{
										if( isset($modified['ITEM']) )
										{
											$item=$modified['ITEM'];
										}
									}
									// here not used: $result->getUnset()
								}
								elseif( $result->getResultType()==\Bitrix\Main\EventResult::ERROR )
								{
									if( ($eventErrors=$result->getErrors() ) )
									{
										foreach($eventErrors as $error)
										{
											$errors[]=Loc::getMessage('ADMIN_TOOLS_ERROR_IMPORT_ITEM', array('#ID#'=>$item['ID'])).' '.$error->getMessage();
										}
										return;
									}
								}
							}
							// prepare array before add
							$filesExist=false;
							foreach($item as $key=> &$value)
							{
								if( $key!='ID'&&!isset($userFelds[$key]) )
								{
									/* 	$errors[]=Loc::getMessage('ADMIN_TOOLS_ERROR_IMPORT_ITEM', array('#ID#'=>$item['ID'])).' '.
									  Loc::getMessage('ADMIN_TOOLS_ERROR_IMPORT_ITEM_UNKNOWN', array('#CODE#'=>$key)); */
									$errors[]='ADMIN_TOOLS_ERROR_IMPORT_ITEM ID='.$item['ID'].' '.
										'ADMIN_TOOLS_ERROR_IMPORT_ITEM_UNKNOWN CODE='.$key;
									return;
								}
								if( substr($value, 0, 10)=='serialize#' )
								{
									$value=unserialize(substr($value, 10));
								}
								// get base type
								$userFelds[$key]['BASE_TYPE']='';
								if( isset($USER_FIELD_MANAGER) )
								{
									$type=$USER_FIELD_MANAGER->GetUserType($userFelds[$key]['USER_TYPE_ID']);
									if( is_array($type)&&isset($type['BASE_TYPE']) )
									{
										$userFelds[$key]['BASE_TYPE']=$type['BASE_TYPE'];
									}
								}
								// get enums
								if( $userFelds[$key]['BASE_TYPE']=='enum' )
								{
									$userFelds[$key]['ENUMS']=$userFieldsEnums[$userFelds[$key]['ID']];
								}
								if( $userFelds[$key]['BASE_TYPE']=='file' )
								{
									$filesExist=true;
								}
								// prepare value
								$value=CRussianpostHLtool::__hlImportPrepareField(
										$value, $userFelds[$key], array(
										'path'=>$filesPath,
								));
								// clear refernces
								if( !$NS['save_reference'] )
								{
									$codeReferences=array('employee', 'hlblock', 'crm',
										'iblock_section', 'iblock_element');
									if( in_array($userFelds[$key]['USER_TYPE_ID'], $codeReferences) )
									{
										$value='';
									}
								}
							}
							unset($value);
							// add / update item
							$exist=false;
							if( $NS['xml_id']&&isset($item[$NS['xml_id']])&&trim($item[$NS['xml_id']])!='' )
							{
								$exist=$class::getList($a=array(
										'filter'=>array(
											'='.$NS['xml_id']=>trim($item[$NS['xml_id']])
										)
									))->fetch();
								if( $exist )
								{
									if( isset($item['ID']) )
									{
										unset($item['ID']);
									}
									$result=$class::update($exist['ID'], $item);
								}
							}
							if( !$exist )
							{
								if( isset($item['ID']) )
								{
									unset($item['ID']);
								}
								$result=$class::add($item);
							}
							if( $result->isSuccess() )
							{
								// remove old files
								if( $exist&&$filesExist )
								{
									foreach($exist as $key=> $value)
									{
										if( $userFelds[$key]['BASE_TYPE']=='file' )
										{
											if( !is_array($value) )
											{
												$value=array($value);
											}
											foreach($value as $fid)
											{
												\CFile::delete($fid);
											}
										}
									}
								}
							}
							else
							{
								foreach($result->getErrorMessages() as $message)
								{
									//$errors[]=Loc::getMessage('ADMIN_TOOLS_ERROR_IMPORT_ITEM', array('#ID#'=>$item['ID'])).' '.$message;
									$errors[]='ADMIN_TOOLS_ERROR_IMPORT_ITEM ID='.$item['ID'].' '.$message;
								}
							}
						}
					}
				}
			}
			);
		}



		// work
		$import->setPosition($NS['xml_pos']);
		if( $import->openFile($server->getDocumentRoot().$NS['url_data_file']) )
		{
			while($import->findNext())
			{
				if( time()-$NS['time_limit']>$startTime )
				{
					break;
				}
			}
			// finish or not
			if( $import->endOfFile() )
			{
				$NS['percent']=100;
				$NS['finish']=true;
			}
			else
			{
				// calc percent
				$NS['xml_pos']=$import->getPosition();
				if( is_array($NS['xml_pos'])&&isset($NS['xml_pos'][1]) )
				{
					$curSize=$NS['xml_pos'][1];
					$allSize=filesize($server->getDocumentRoot().$NS['url_data_file']);
					$NS['percent']=round($curSize/$allSize*100, 2);
				}
				$NS['xml_pos']=serialize($NS['xml_pos']);
			}
		}
		else
		{
			$errors[]=Loc::getMessage('XML_FILE_NOT_ACCESSIBLE');
		}
		$NS['step'] ++;

		// show message (error or processing)
		if( !empty($errors) )
		{
			/* \CAdminMessage::ShowMessage(array(
			  'MESSAGE' => Loc::getMessage('ADMIN_TOOLS_ERROR_IMPORT'),
			  'DETAILS' => implode('<br/>', $errors),
			  'HTML' => true,
			  'TYPE' => 'ERROR',
			  )); */
		}
		else
		{
			/* $details = Loc::getMessage('ADMIN_TOOLS_PROCESS_PERCENT',
			  array(
			  '#percent#' => $NS['percent'],
			  '#count#' => $NS['count'],
			  '#all#' => $NS['all'],
			  ));
			  if ($NS['finish'])
			  {
			  $details .= '<br/>'.Loc::getMessage('ADMIN_TOOLS_PROCESS_FINAL');
			  }
			  \CAdminMessage::ShowMessage(array(
			  'MESSAGE' => Loc::getMessage('ADMIN_TOOLS_PROCESS_IMPORT'),
			  'DETAILS' => $details,
			  'HTML' => true,
			  'TYPE' => 'PROGRESS',
			  ));
			  if ($NS['finish'])
			  {
			  \CAdminMessage::ShowMessage(array(
			  'MESSAGE' => Loc::getMessage('ADMIN_TOOLS_PROCESS_FINISH_DELETE'),
			  'DETAILS' => '',
			  'HTML' => true,
			  'TYPE' => 'ERROR',
			  ));
			  } */
		}

		return $NS;
	}

	public  static function __prepareArrayFromXml(array $item, $code=false)
	{
		$fields=array();
		if( $code!==false )
		{
			if( isset($item[$code])&&is_array($item[$code]) )
			{
				$item=$item[$code];
			}
			else
			{
				$item=array();
			}
		}
		if( isset($item['#'])&&is_array($item['#']) )
		{
			foreach($item['#'] as $key=> $value)
			{
				if( is_array($value) )
				{
					$value=array_shift($value);
				}
				if( is_array($value['#']) )
				{
					$fields[strtoupper($key)]=self::__prepareArrayFromXml($value);
				}
				else
				{
					$fields[strtoupper($key)]=$value['#'];
				}
			}
		}

		return $fields;
	}

	public  static  function __getEnumUserFields($ufId=false, $clear=false)
	{
		static $userFieldsEnums=array();

		if( $ufId&&$clear&&array_key_exists($ufId, $userFieldsEnums) )
		{
			unset($userFieldsEnums[$ufId]);
		}

		if( $ufId===false||!array_key_exists($ufId, $userFieldsEnums) )
		{
			if( $ufId!==false )
			{
				$userFieldsEnums[$ufId]=array();
			}
			$res=\CUserFieldEnum::GetList(
					array(), array('USER_FIELD_ID'=>$fId)
			);
			while($row=$res->fetch())
			{
				if( !isset($userFieldsEnums[$row['USER_FIELD_ID']]) )
				{
					$userFieldsEnums[$row['USER_FIELD_ID']]=array();
				}
				$userFieldsEnums[$row['USER_FIELD_ID']][$row['ID']]=$row['VALUE'];
			}
		}

		return $ufId===false ? $userFieldsEnums : $userFieldsEnums[$ufId];
	}

	public  static  function __hlImportPrepareField($value, &$userField, array $params=array())
	{
		if( is_array($value) )
		{
			foreach($value as &$v)
			{
				$v=self::__hlImportPrepareField($v, $userField, $params);
			}
			unset($v);
		}
		elseif( trim($value)!='' )
		{
			// file get from local folder
			if( $userField['BASE_TYPE']=='file' )
			{
				if( file_exists($value) )
				{
					$value=\CFile::MakeFileArray($value);
				}
				else
				{
					$value=\CFile::MakeFileArray($params['path'].'/'.$value);
				}
			}
			// for enums get the vals
			elseif( $userField['BASE_TYPE']=='enum'&&is_array($userField['ENUMS']) )
			{
				$enums=array_flip($userField['ENUMS']);
				if( isset($enums[$value]) )
				{
					$value=$enums[$value];
				}
				// add new enum
				else
				{
					$userFieldEnums=new \CUserFieldEnum;
					$userFieldEnums->setEnumValues($userField['ID'], array(
						'n0'=>array(
							'VALUE'=>$value
						)
					));
					$userField['ENUMS']=self::__getEnumUserFields($userField['ID'], true);
					$enums=array_flip($userField['ENUMS']);
					if( isset($enums[$value]) )
					{
						$value=$enums[$value];
					}
				}
			}
		}

		return $value;
	}

}

?>