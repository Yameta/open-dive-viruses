<?
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/scoder.collections/include.php");
use Bitrix\Main\Loader; 
use Bitrix\Main\Config\Option; 
use Bitrix\Main\Localization\Loc; 
$module_id = "scoder.collections";

$POST_RIGHT = $APPLICATION->GetGroupRight($module_id);

if ($POST_RIGHT < "R")
	$APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));

Loc::loadMessages($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/options.php"); 
Loc::loadMessages(__FILE__); 

if (Loader::includeSharewareModule($module_id) == Loader::MODULE_DEMO_EXPIRED)
{
	ShowError("Error! Demo period of the module has expired.");
	return false;
}
$aTabs = array(
	array("DIV" => "edit1", "TAB" => Loc::getMessage("SCODER_TAB1_TITLE"), "ICON" => "scoder_collections_settings", "TITLE" => Loc::getMessage("SCODER_TAB1_DESCRIPTION")),
	array("DIV" => "edit3", "TAB" => Loc::getMessage("SCODER_TAB2_PERMISSION_TITLE"), "ICON" => "scoder_collections_settings", "TITLE" => Loc::getMessage("SCODER_TAB2_PERMISSION_DESCRIPTION")),	
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

ob_start();
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");
$htmlGroupRights = ob_get_contents();
ob_end_clean();



$ar_iblocks_data = CScoderCollections::get_iblocks_data(); 	//возвращает иинофрмацию по инфоблокам и их типам

if($REQUEST_METHOD=="POST" && $POST_RIGHT == "W" && strlen($Update.$Apply.$RestoreDefaults)>0 && check_bitrix_sessid())
{
	if(strlen($RestoreDefaults)>0)
	{
		Option::delete($module_id);
		$z = CGroup::GetList($v1="id",$v2="asc", array("ACTIVE" => "Y", "ADMIN" => "N"));
		while($zr = $z->Fetch())
			$APPLICATION->DelGroupRight($module_id, array($zr["ID"]));
	}
	else
	{		
		Option::set($module_id, "STEP_COUNT",(int) $STEP_COUNT);
		Option::set($module_id, "DIS_ACTIVE_FILTER",$DIS_ACTIVE_FILTER);
		Option::set($module_id, "IS_ADD_SET",$IS_ADD_SET);
		Option::set($module_id, "ACTIVE_AGENT",$ACTIVE_AGENT);
		Option::set($module_id, "REINDEX_IBLOCKS",serialize($REINDEX_IBLOCKS));
		Option::set($module_id, "REINDEX_IBLOCKS_ALL",serialize($REINDEX_IBLOCKS_ALL));
		
		Option::set($module_id, "LAST_ID",(int) $LAST_ID);
		Option::set($module_id, "STEP_SET_COUNT",(int) $STEP_SET_COUNT);
		Option::set($module_id, "TIMEOUT",(int) $TIMEOUT);
	}
	if(strlen($Update)>0 && strlen($_REQUEST["back_url_settings"])>0)
		LocalRedirect($_REQUEST["back_url_settings"]);
	else
		LocalRedirect($APPLICATION->GetCurPage()."?mid=".urlencode($mid)."&lang=".urlencode(LANGUAGE_ID)."&back_url_settings=".urlencode($_REQUEST["back_url_settings"])."&".$tabControl->ActiveTabParam());
}
if(strlen($strWarning)>0)
	CAdminMessage::ShowNote($strWarning);

CJSCore::Init(array("jquery"));
$tabControl->Begin();
?>
<form method="post" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=urlencode($mid)?>&amp;lang=<?=LANGUAGE_ID?>">
<?=bitrix_sessid_post();?>

<?$tabControl->BeginNextTab();?>

	<tr>
		<td width="40%"><?=Loc::getMessage('SCODER_STEP_COUNT')?> </td>
		<td>
			<input type="text" name="STEP_COUNT" value="<?=Option::get($module_id, "STEP_COUNT")?>"/>
		</td>
	</tr>
	<tr>
		<td width="40%"><?=Loc::getMessage('SCODER_DIS_ACTIVE_FILTER')?> </td>
		<td>
			<input type="hidden" name="DIS_ACTIVE_FILTER" value="N"/>
			<input type="checkbox" name="DIS_ACTIVE_FILTER" value="Y" <?if (Option::get($module_id, "DIS_ACTIVE_FILTER") == 'Y') echo 'checked'?>/>
		</td>
	</tr>
	<tr>
		<td width="40%"><?=Loc::getMessage('SCODER_IS_ADD_SET')?> </td>
		<td>
			<input type="hidden" name="IS_ADD_SET" value="N"/>
			<input type="checkbox" name="IS_ADD_SET" value="Y" <?if (Option::get($module_id, "IS_ADD_SET") == 'Y') echo 'checked'?>/>
		</td>
	</tr>
	<tr>
		<td width="40%"><?=Loc::getMessage('SCODER_ACTIVE_AGENT')?> </td>
		<td>
			<input type="hidden" name="ACTIVE_AGENT" value="N"/>
			<input type="checkbox" name="ACTIVE_AGENT" value="Y" <?if (Option::get($module_id, "ACTIVE_AGENT") == 'Y') echo 'checked'?>/>
		</td>
	</tr>
	<tr>
		<td width="40%"><?=Loc::getMessage('SCODER_AGENT_IBLOCKS')?> </td>
		<td>
			<?
			$str = Option::get($module_id, "REINDEX_IBLOCKS");
			$arValues = unserialize($str);
			if (isset($ar_iblocks_data["IBLOCKS"]) && is_array($ar_iblocks_data["IBLOCKS"])):?>
				<select name="REINDEX_IBLOCKS[]" multiple size="7">
					<?
					$arIblocks = array();
					foreach ($ar_iblocks_data["IBLOCKS"] as $arItem)
					{
						$arIblocks[$arItem["IBLOCK_TYPE_ID"]][] = $arItem;
					}
					
					foreach ($arIblocks as $arItems):?>
						<optgroup label="<?=$arItems[0]["IBLOCK_TYPE_ID"]?>">
							<?foreach ($arItems as $arItem):?>
								<option value="<?=$arItem["ID"]?>" <?if (is_array($arValues) && in_array($arItem["ID"], $arValues)) echo 'selected'?>><?=$arItem["NAME"]?></option>
							<?endforeach?>
						</optgroup>
					<?endforeach?>
				</select>
			<?endif?>
		</td>
	</tr>
	<tr>
		<td width="40%"><?=Loc::getMessage('SCODER_TIMEOUT')?> </td>
		<td>
			<input type="text" name="TIMEOUT" value="<?=Option::get($module_id, "TIMEOUT",100);?>"/>
		</td>
	</tr>
	<tr class="heading">
		<td colspan="2">
			<?=Loc::getMessage('SCODER_COLLECTIONS_REINDEX')?>
		</td>
	</tr>
	<tr>
		<td width="40%"><?=Loc::getMessage('SCODER_STEP_SET_COUNT')?> </td>
		<td>
			<input type="text" name="STEP_SET_COUNT" value="<?=Option::get($module_id, "STEP_SET_COUNT",500)?>"/>
		</td>
	</tr>
	<tr>
		<td width="40%"><?=Loc::getMessage('SCODER_REINDEX_IBLOCKS')?></td>
		<td>
			<?
			$str = Option::get($module_id, "REINDEX_IBLOCKS_ALL");
			$arValues = unserialize($str);
			if (isset($ar_iblocks_data["IBLOCKS"]) && is_array($ar_iblocks_data["IBLOCKS"])):?>
				<select name="REINDEX_IBLOCKS_ALL[]" multiple size="7">
					<?
					$arIblocks = array();
					foreach ($ar_iblocks_data["IBLOCKS"] as $arItem)
					{
						$arIblocks[$arItem["IBLOCK_TYPE_ID"]][] = $arItem;
					}
					
					foreach ($arIblocks as $arItems):?>
						<optgroup label="<?=$arItems[0]["IBLOCK_TYPE_ID"]?>">
							<?foreach ($arItems as $arItem):?>
								<option value="<?=$arItem["ID"]?>" <?if (is_array($arValues) && in_array($arItem["ID"], $arValues)) echo 'selected'?>><?=$arItem["NAME"]?></option>
							<?endforeach?>
						</optgroup>
					<?endforeach?>
				</select>
			<?endif?>
		</td>
	</tr>
	<tr>
		<td width="40%"><?=Loc::getMessage('SCODER_LAST_ID')?> </td>
		<td>
			<input type="text" name="LAST_ID" value="<?=Option::get($module_id, "LAST_ID")?>"/>
		</td>
	</tr>
	<tr>
		<td width="40%"><?=Loc::getMessage('SCODER_COLLECTIONS_REINDEX_TITLE')?></td>
		<td>
			<span class="adm-list-table-top-wrapper" >
				<a href="javascript:Start();" class="adm-btn" title="<?=Loc::getMessage('SCODER_COLLECTIONS_REINDEX')?>"><?=Loc::getMessage('SCODER_COLLECTIONS_REINDEX_START')?></a>
			</span>
			<script>
				var stop = false;

				function Start()
				{
					stop = false;
					var start_id = 0;
					var set_start_id = 0;
					start_id = parseInt($("input[name='LAST_ID']").val());
					StartCollectionsReindex(start_id,set_start_id);
				}

				function Stop()
				{
					stop = true;
				}

				function StartCollectionsReindex(last_id,set_last_id)
				{
					if(stop)
					{
						return;
					}
					
					CHttpRequest.Action = function(result)
					{
						CloseWaitWindow();
						document.getElementById('collections_reindex_result').innerHTML = result;
					};
					ShowWaitWindow();
					
					var url = '/bitrix/admin/scoder_collections_all_reindex.php?lang=ru&<?=bitrix_sessid_get();?>&last_id='+last_id+'&set_last_id='+set_last_id;
					
					CHttpRequest.Send(url);
				}
			</script>
		</td>
	</tr>
	<tr>	
		<td width="40%"></td>
		<td>
			<div id="collections_reindex_result">	
				<div class="adm-info-message-wrap adm-info-message-red"></div>
			</div>			
		</td>
	</tr>
<?
$tabControl->BeginNextTab();
echo $htmlGroupRights;
?>
<?$tabControl->Buttons();?>
	<script type="text/javascript">
	function RestoreDefaults()
	{
		if (confirm('<? echo GetMessageJS("MAIN_HINT_RESTORE_DEFAULTS_WARNING"); ?>'))
			window.location = "<?echo $APPLICATION->GetCurPage()?>?RestoreDefaults=Y&lang=<?echo LANGUAGE_ID; ?>&mid=<?echo urlencode($mid)?>&<?=bitrix_sessid_get()?>";
	}
	</script>
	<input type="submit" <?if ($POST_RIGHT<"W") echo "disabled" ?> name="Update" class="adm-btn-save" value="<?=Loc::getMessage("MAIN_SAVE")?>" title="<?=Loc::getMessage("MAIN_OPT_SAVE_TITLE")?>">
	<input type="hidden" name="Update" value="Y">
	<?if(strlen($_REQUEST["back_url_settings"])>0):?>
		<input type="button" name="Cancel" value="<?=Loc::getMessage("MAIN_OPT_CANCEL")?>" title="<?=Loc::getMessage("MAIN_OPT_CANCEL_TITLE")?>" onclick="window.location='<?echo htmlspecialchars(CUtil::addslashes($_REQUEST["back_url_settings"]))?>'">
		<input type="hidden" name="back_url_settings" value="<?=htmlspecialchars($_REQUEST["back_url_settings"])?>">
	<?endif?>
	<input type="submit" <?if ($POST_RIGHT<"W") echo "disabled" ?> name="RestoreDefaults" title="<?echo Loc::getMessage("MAIN_HINT_RESTORE_DEFAULTS")?>" OnClick="return confirm('<?echo AddSlashes(Loc::getMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING"))?>')" value="<?echo Loc::getMessage("MAIN_RESTORE_DEFAULTS")?>">
</form>
<?$tabControl->End();?>