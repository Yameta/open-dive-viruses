<?
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Highloadblock as HL;
use Bitrix\Main\SiteTable;


Loc::loadMessages($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/options.php");
Loc::loadMessages(__FILE__);

$module_id = "russianpost.post";

Loader::includeModule($module_id);
Loader::includeModule('sale');
Loader::includeModule('iblock');

$RIGHT = $APPLICATION->GetGroupRight($module_id);
$RIGHT_W = ($RIGHT>="W");
$RIGHT_R = ($RIGHT>="R");

// Payers
$tmpValue=CSalePersonType::GetList(array('ID'=>'asc'), array('ACTIVE'=>'Y'));
$arPayers=array();
while($payer=$tmpValue->Fetch()){
	$arPayers[$payer['LID']][$payer['ID']]=array('NAME'=>$payer['NAME']." [".$payer['LID']."]");
	$arPayers[$payer['LID']][$payer['ID']]['sel']=true;
}
// Locations
$tmpValue = CSaleOrderProps::GetList(array(),array("IS_LOCATION"=>"Y"));
$locProps = array();
while($element=$tmpValue->Fetch())
	$locProps[$element['CODE']] = $element['NAME'];

$statusResult = \Bitrix\Sale\Internals\StatusLangTable::getList(array(

	'order' => array('STATUS.SORT'=>'ASC'),

	'filter' => array('STATUS.TYPE'=>'O','LID'=>LANGUAGE_ID),

	'select' => array('STATUS_ID','NAME','DESCRIPTION'),

));

CJSCore::Init(array("jquery"));
CJSCore::Init(array("jquery2"));

$siteList = array();
$siteIterator = SiteTable::getList(array(
	'select' => array('LID', 'NAME'),
	'order' => array('SORT' => 'ASC')
));
while ($oneSite = $siteIterator->fetch())
{
	$siteList[] = array('ID' => $oneSite['LID'], 'NAME' => $oneSite['NAME']);
}
unset($oneSite, $siteIterator);
$siteCount = count($siteList);

$arIblocks = array();
for ($i = 0; $i < $siteCount; $i++)
{
	$res = \CIBlock::GetList(
		Array(),
		Array(
			'ACTIVE'=>'Y',
			"CHECK_PERMISSIONS" => "N",
            'SITE_ID' => $siteList[$i]["ID"],
		)
	);
	while($ar_res = $res->Fetch())
	{
		$arIblocks[$siteList[$i]["ID"]][$ar_res['ID']] = $ar_res;
	}
}

if ($RIGHT_R)
{
    if (
        $REQUEST_METHOD=="POST"
        && $RIGHT_W
        && check_bitrix_sessid()
    )
    {
	    if(isset($_REQUEST['get_auth_key']))
	    {
		    $request = new Russianpost\Post\Request();
		    $resultAuth = $request->GetAuthKey($_SERVER['SERVER_NAME']);
		    Option::set($module_id, "GUID_ID", $resultAuth['guidId']);
		    Option::set($module_id, "GUID_KEY", $resultAuth['guidKey']);
	    }
	    if(isset($_REQUEST['clear_log_order']))
        {
	        file_put_contents($_SERVER["DOCUMENT_ROOT"].'/bitrix/js/'.$module_id.'/log/log_order.log', '');
        }
	    if(isset($_REQUEST['clear_log_calculate']))
	    {
		    file_put_contents($_SERVER["DOCUMENT_ROOT"].'/bitrix/js/'.$module_id.'/log/log_calculate.log', '');
	    }
	    if(isset($_REQUEST['clear_log_key']))
	    {
		    file_put_contents($_SERVER["DOCUMENT_ROOT"].'/bitrix/js/'.$module_id.'/log/log_key.log', '');
	    }
        if($_REQUEST['RUSSIANPOST_JQUERY_OFF'])
        {
	        Option::set($module_id, "RUSSIANPOST_JQUERY_OFF", 'Y');
        }
        else
        {
	        Option::set($module_id, "RUSSIANPOST_JQUERY_OFF", 'N');
        }
	    if($_REQUEST['RUSSIANPOST_AUTOOPEN_CARD'])
	    {
		    Option::set($module_id, "RUSSIANPOST_AUTOOPEN_CARD", 'Y');
	    }
	    else
	    {
		    Option::set($module_id, "RUSSIANPOST_AUTOOPEN_CARD", 'N');
	    }
	    if($_REQUEST['RUSSIANPOST_ORDER_DEBUG'])
	    {
		    Option::set($module_id, "RUSSIANPOST_ORDER_DEBUG", 'Y');
	    }
	    else
	    {
		    Option::set($module_id, "RUSSIANPOST_ORDER_DEBUG", 'N');
	    }
	    if($_REQUEST['RUSSIANPOST_CALCULATE_DEBUG'])
	    {
		    Option::set($module_id, "RUSSIANPOST_CALCULATE_DEBUG", 'Y');
	    }
	    else
	    {
		    Option::set($module_id, "RUSSIANPOST_CALCULATE_DEBUG", 'N');
	    }
	    if($_REQUEST['RUSSIANPOST_KEY_DEBUG'])
	    {
		    Option::set($module_id, "RUSSIANPOST_KEY_DEBUG", 'Y');
	    }
	    else
	    {
		    Option::set($module_id, "RUSSIANPOST_KEY_DEBUG", 'N');
	    }
	    if($_REQUEST['RUSSIANPOST_MARK_OFF'])
	    {
		    Option::set($module_id, "RUSSIANPOST_MARK_OFF", 'Y');
	    }
	    else
	    {
		    Option::set($module_id, "RUSSIANPOST_MARK_OFF", 'N');
	    }
	    if($_REQUEST['RUSSIANPOST_INDEX_VALIDATION'])
	    {
		    Option::set($module_id, "RUSSIANPOST_INDEX_VALIDATION", 'Y');
	    }
	    else
	    {
		    Option::set($module_id, "RUSSIANPOST_INDEX_VALIDATION", 'N');
	    }
	    if($_REQUEST['RUSSIANPOST_MARK_STANDART'])
	    {
		    Option::set($module_id, "RUSSIANPOST_MARK_STANDART", 'Y');
	    }
	    else
	    {
		    Option::set($module_id, "RUSSIANPOST_MARK_STANDART", 'N');
	    }

	    $arAllOptions = \Russianpost\Post\Optionpost::toOptions();
	    foreach($arAllOptions as $aOptGroup){
		    foreach($aOptGroup as $option){		        
			    COption::RemoveOption($module_id, $option[0]);
		//	    __AdmSettingsSaveOption($module_id, $option);
		    }
	    }

	    if (!empty($_REQUEST["RUSSIANPOST_ORDER_dif_settings"]))
	    {
		    for ($i = 0; $i < $siteCount; $i++)
		    {
			    foreach($arAllOptions as $aOptGroup){
				    foreach($aOptGroup as $option){
					    //COption::SetOptionString($module_id, $option[0], trim($_REQUEST[$option[0]][$siteList[$i]["ID"]]), false, $siteList[$i]["ID"]);
					    Option::set($module_id, $option[0], trim($_REQUEST[$option[0]][$siteList[$i]["ID"]]), $siteList[$i]["ID"]);
					    //	    __AdmSettingsSaveOption($module_id, $option);
				    }
			    }
			    Option::set($module_id, 'RUSSIANPOST_ORDER_PAID_STATUS', trim($_REQUEST['RUSSIANPOST_ORDER_PAID_STATUS'][$siteList[$i]["ID"]]), $siteList[$i]["ID"]);
			    Option::set($module_id, 'RUSSIANPOST_MARK_IBLOCK', trim($_REQUEST['RUSSIANPOST_MARK_IBLOCK'][$siteList[$i]["ID"]]), $siteList[$i]["ID"]);
			    Option::set($module_id, 'RUSSIANPOST_MARK_PROP', trim($_REQUEST['RUSSIANPOST_MARK_PROP'][$siteList[$i]["ID"]]), $siteList[$i]["ID"]);

		    }
		    Option::set($module_id, "RUSSIANPOST_ORDER_dif_settings", 'Y');
	    }
	    else
	    {
		    $site_id = trim($_REQUEST["RUSSIANPOST_ORDER_current_site"]);
		    Option::set($module_id, "RUSSIANPOST_ORDER_PAID_STATUS", $_REQUEST['RUSSIANPOST_ORDER_PAID_STATUS'][$site_id]);
		    Option::set($module_id, 'RUSSIANPOST_MARK_IBLOCK', trim($_REQUEST['RUSSIANPOST_MARK_IBLOCK'][$site_id]));
		    Option::set($module_id, 'RUSSIANPOST_MARK_PROP', trim($_REQUEST['RUSSIANPOST_MARK_PROP'][$site_id]));
		    foreach($arAllOptions as $aOptGroup){
			    foreach($aOptGroup as $option){
				    Option::set($module_id, $option[0], trim($_REQUEST[$option[0]][$site_id]));
				    //	    __AdmSettingsSaveOption($module_id, $option);
			    }
		    }
		    Option::set($module_id, "RUSSIANPOST_ORDER_dif_settings", 'N');
	    }

    }

    $guid_id = Option::get($module_id, "GUID_ID");
    $guid_key = Option::get($module_id, "GUID_KEY");
    $checkOpt = Option::get($module_id, "RUSSIANPOST_JQUERY_OFF");
	$checkAutoOpt = Option::get($module_id, "RUSSIANPOST_AUTOOPEN_CARD");
	$debugOrder = Option::get($module_id, "RUSSIANPOST_ORDER_DEBUG");
	$debugCalculate = Option::get($module_id, "RUSSIANPOST_CALCULATE_DEBUG");
	$debugKey = Option::get($module_id, "RUSSIANPOST_KEY_DEBUG");
	$markOff = Option::get($module_id, "RUSSIANPOST_MARK_OFF");
	$validationOn = Option::get($module_id, "RUSSIANPOST_INDEX_VALIDATION");
	$markStandartOff = Option::get($module_id, "RUSSIANPOST_MARK_STANDART");
    $jqueryCheck = '';
    if($checkOpt == 'Y')
    {
        $jqueryCheck = 'checked';
    }
	$autoopenCheck = '';
	if($checkAutoOpt == 'Y')
	{
		$autoopenCheck = 'checked';
	}
	if($debugOrder == 'Y')
	{
		$debugOrderCheck = 'checked';
	}
	if($debugCalculate == 'Y')
	{
		$debugCalculateCheck = 'checked';
	}
	if($debugKey == 'Y')
	{
		$debugKeyCheck = 'checked';
	}
	if($markOff == 'Y')
    {
        $markCheck = 'checked';
    }
	if($validationOn == 'Y')
	{
		$validationCheck = 'checked';
	}
	if($markStandartOff == 'Y')
	{
		$markStandartCheck = 'checked';
	}
	$arOrderStasuses = array();
	while($status=$statusResult->fetch())
	{
        $arOrderStasuses[$status['STATUS_ID']] = $status['NAME'];
	}
    $aTabs = array(
        array("DIV" => "edit1", "TAB" => Loc::getMessage("RUSSIANPOST_POST_NASTROYKI"), "ICON" => "", "TITLE" => Loc::getMessage("RUSSIANPOST_POST_NASTROYKA_PARAMETROV")),
	    array("DIV" => "edit2", "TAB" => Loc::getMessage("RUSSIANPOST_TAB_INFORM"), "ICON" => "", "TITLE" => Loc::getMessage("RUSSIANPOST_TAB_INFORM")),
	    array("DIV" => "edit3", "TAB" => Loc::getMessage("RUSSIANPOST_TAB_DEBUG"), "ICON" => "", "TITLE" => Loc::getMessage("RUSSIANPOST_TAB_DEBUG")),
        array("DIV" => "edit4", "TAB" => Loc::getMessage("MAIN_TAB_RIGHTS"), "ICON" => "", "TITLE" => Loc::getMessage("MAIN_TAB_TITLE_RIGHTS")),
    );
    $tabControl = new CAdminTabControl("tabControl", $aTabs);
    $tabControl->Begin();

?>
    <style>
        .PropHint {
            background: url("/bitrix/js/<?=$module_id?>/images/hint.gif") no-repeat transparent;
            display: inline-block;
            height: 12px;
            position: relative;
            width: 12px;
        }
        .b-popup {
            background-color: #FEFEFE;
            border: 1px solid #9A9B9B;
            box-shadow: 0px 0px 10px #B9B9B9;
            display: none;
            font-size: 12px;
            padding: 19px 13px 15px;
            position: absolute;
            top: 38px;
            width: 300px;
            z-index: 50;
        }
        .b-popup .pop-text {
            margin-bottom: 10px;
            color:#000;
        }
        .pop-text i {color:#AC12B1;}
        .b-popup .close {
            background: url("/bitrix/js/<?=$module_id?>/images/popup_close.gif") no-repeat transparent;
            cursor: pointer;
            height: 10px;
            position: absolute;
            right: 4px;
            top: 4px;
            width: 10px;
        }
        .errorText{
            color:red;
            font-size:11px;
        }
        .\:table-grid {
            display: -ms-grid;
            display: grid;
        }

        .\:table-grid.\:seven {
            -ms-grid-columns: (auto)[7];
            grid-template-columns: repeat(7, auto);
        }

        .\:table-grid.\:six {
            -ms-grid-columns: (auto)[6];
            grid-template-columns: repeat(6, auto);
        }

        .\:table-grid.\:five {
            -ms-grid-columns: (auto)[5];
            grid-template-columns: repeat(5, auto);
        }

        .\:table-grid.\:four {
            -ms-grid-columns: (auto)[4];
            grid-template-columns: repeat(4, auto);
        }

        .\:table-grid.\:three {
            -ms-grid-columns: (auto)[3];
            grid-template-columns: repeat(3, auto);
        }

        .\:table-grid .\:table-cell {
            padding: 10px;
            border-bottom: 1px solid #ccc;
        }
    </style>
<form method="post" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=htmlspecialchars($mid)?>&lang=<?=LANGUAGE_ID?>">
    <?=bitrix_sessid_post()?>
    <?$tabControl->BeginNextTab();?>
    <?
    if ( !IsModuleInstalled('sale') )
    {
	    ?>
        <tr><td>
        <div class="adm-detail-content-item-block">
            <p>
                <b style="color: red"><?=Loc::getMessage("RUSSIANPOST_ERROR_SALE_INSTALL");?></b>
            </p>
        </div>
        </td></tr>
        <?
    }
    ?>
	<?
	if ( !IsModuleInstalled('highloadblock') )
	{
		?>
        <tr><td>
        <div class="adm-detail-content-item-block">
            <p>
                <b style="color: red"><?=Loc::getMessage("RUSSIANPOST_ERROR_HL_INSTALL");?></b>
            </p>
        </div>
        </td></tr>
		<?
	}
	else
    {
	    Loader::includeModule('highloadblock');
	    $hlblock = HL\HighloadBlockTable::getList(array("filter" => array('=NAME' => 'PostListCountryCodes')))->fetch();
	    $hlID = $hlblock["ID"];
	    $result=false;
	    if ( $hlID )
        {

        }
        else
        {
            ?>
            <tr><td>
            <div class="adm-detail-content-item-block">
                <p>
                    <b style="color: red"><?=Loc::getMessage("RUSSIANPOST_ERROR_HLCODE_INSTALL");?></b>
                </p>
            </div>
            </td></tr>
            <?
        }
    }
	?>
    <tr><td>
    <div class="adm-detail-content-item-block">
        <div>
            <input type="checkbox" value="Y" <?=$jqueryCheck;?> name="RUSSIANPOST_JQUERY_OFF"><?=Loc::getMessage("RUSSIANPOST_JQUERY_OFF")?>
        </div>
    </div>
    </td></tr>
    <tr><td>
    <div class="adm-detail-content-item-block">
        <div>
            <input type="checkbox" value="Y" <?=$autoopenCheck;?> name="RUSSIANPOST_AUTOOPEN_CARD"><?=Loc::getMessage("RUSSIANPOST_AUTOOPEN_CARD")?>
        </div>
    </div>
    </td></tr>
    <tr><td>
    <div class="adm-detail-content-item-block">
        <div>
            <input type="checkbox" value="Y" <?=$markCheck;?> name="RUSSIANPOST_MARK_OFF"><?=Loc::getMessage("RUSSIANPOST_MARK_OFF")?>
        </div>
    </div>

    </td></tr>
    <tr><td>
            <div class="adm-detail-content-item-block">
                <div>
                    <input type="checkbox" value="Y" <?=$validationCheck;?> name="RUSSIANPOST_INDEX_VALIDATION"><?=Loc::getMessage("RUSSIANPOST_INDEX_VALIDATION")?>
                </div>
            </div>
        </td></tr>
    <tr><td>
            <div class="adm-detail-content-item-block">
                <div>
                    <input type="checkbox" value="Y" <?=$markStandartCheck;?> name="RUSSIANPOST_MARK_STANDART"><?=Loc::getMessage("RUSSIANPOST_MARK_STANDART")?>
                </div>
            </div>

        </td></tr>
    <tr><td>
<?
    if($guid_key == '')
    {
        ?>
    <div class="adm-detail-content-item-block">
        <p><?=Loc::getMessage("RUSSIANPOST_POST_TXT_AUTH")?></p>
        <p><?=Loc::getMessage("RUSSIANPOST_POST_TXT_AUTH_DESCR")?></p>
        <div>
            <input type="submit" value="<?=Loc::getMessage("RUSSIANPOST_POST_BTN_AUTH");?>" name="get_auth_key">
        </div>
    </div>
        <?
    }
    else
    {
        ?>
        <div class="adm-detail-content-item-block">
            <p><?=Loc::getMessage("RUSSIANPOST_POST_TXT_OPTIONS")?></p>
            <div>
                <a target="_blank" href="https://cms.pochta.ru/authorization/cms?guidId=<?=$guid_id?>&guidKey=<?=$guid_key?>"><?=Loc::getMessage("RUSSIANPOST_POST_OPTIONS_LINK");?></a>
            </div>
            <p><b><?=Loc::getMessage("RUSSIANPOST_NEWAUTH_TXT")?></b></p>
            <div>
                <input type="submit" value="<?=Loc::getMessage("RUSSIANPOST_POST_BTN_NEWAUTH");?>" name="get_auth_key">
            </div>
        </div>
        <?
    }
    ?>
    </td></tr>
    <script type="text/javascript">
		var cur_site = {ORDER:'<?=CUtil::JSEscape($siteList[0]["ID"])?>',ADDRESS:'<?=CUtil::JSEscape($siteList[0]["ID"])?>'};
		function changeSiteList(value, add_id)
		{
			var SLHandler = document.getElementById(add_id + '_site_id');
			SLHandler.disabled = value;
		}


		function selectSite(current, add_id)
		{
			if (current == cur_site[add_id]) return;

			var last_handler = document.getElementById('par_' + add_id + '_' +cur_site[add_id]);
			var current_handler = document.getElementById('par_' + add_id + '_' + current);
			var CSHandler = document.getElementById(add_id + '_current_site');

			last_handler.style.display = 'none';
			current_handler.style.display = 'inline';

			cur_site[add_id] = current;
			CSHandler.value = current;
			var site_id = $('#ORDER_current_site').val();
            checkName(site_id);
			return;
		}
</script>
		<tr><td>
    <div class="adm-detail-content-item-block">
        <div>
	        <?=Loc::getMessage("RUSSIANPOST_DIFF_SETTINGS")?>
            <input type="checkbox" name="RUSSIANPOST_ORDER_dif_settings" id="dif_settings" <? if(COption::GetOptionString($module_id, "RUSSIANPOST_ORDER_dif_settings", "N") == "Y") echo " checked=\"checked\"";?> OnClick="changeSiteList(!this.checked, 'ORDER')" />
        </div>
        <br>
        <div>
		    <?=Loc::getMessage("RUSSIANPOST_SITE_SETTINGS")?>
            <select name="site" id="ORDER_site_id"<? if(COption::GetOptionString($module_id, "RUSSIANPOST_ORDER_dif_settings", "N") != "Y") echo " disabled=\"disabled\""; ?> OnChange="selectSite(this.value, 'ORDER')">
		        <?
		        for($i = 0; $i < $siteCount; $i++)
			        echo "<option value=\"".htmlspecialcharsbx($siteList[$i]["ID"])."\">".htmlspecialcharsbx($siteList[$i]["NAME"])."</option>";
		        ?></select><input type="hidden" name="RUSSIANPOST_ORDER_current_site" id="ORDER_current_site" value="<?=htmlspecialcharsbx($siteList[0]["ID"]);?>" />
        </div>
    </div>
		    <?for ($i = 0; $i < $siteCount; $i++):?>
                <?
			    $orderStatus = Option::get($module_id, "RUSSIANPOST_ORDER_PAID_STATUS", "", $siteList[$i]["ID"]);
			    $iblockMarkId = Option::get($module_id, "RUSSIANPOST_MARK_IBLOCK", "", $siteList[$i]["ID"]);
			    $arSiteIblocks = $arIblocks[$siteList[$i]["ID"]];
			    $markProp = Option::get($module_id, "RUSSIANPOST_MARK_PROP", "", $siteList[$i]["ID"]);
                ?>
            <div id="par_ORDER_<?=($siteList[$i]["ID"])?>" style="display: <?=($i == 0 ? "inline" : "none");?>">
    <div class="adm-detail-content-item-block">
        <p><b><?=Loc::getMessage("RUSSIANPOST_ORDER_PAID_STATUS_TXT")?></b></p>
        <?if(!$orderStatus):?>
            <p style="color: red"><b><?=Loc::getMessage("RUSSIANPOST_ORDER_PAID_STATUS_TXT_WARNING")?></b></p>
        <?endif;?>
        <div>
            <select name="RUSSIANPOST_ORDER_PAID_STATUS[<?=htmlspecialcharsbx($siteList[$i]["ID"])?>]">
                <option value=""><?=Loc::getMessage("RUSSIANPOST_ORDER_PAID_STATUS_SELECT")?></option>
                <?foreach ($arOrderStasuses as $statusId=>$statusName):?>
                    <option value="<?=$statusId;?>" <?if($orderStatus == $statusId):?>selected<?endif;?>><?=$statusName?></option>
                <?endforeach;?>
            </select>
        </div>
    </div>
    <div class="adm-detail-content-item-block">
        <p><b><?=Loc::getMessage("RUSSIANPOST_OPT_HEAD")?></b></p>
        <div>
	        <?\Russianpost\Post\Optionpost::showOrderOptions($siteList[$i]["ID"]);?>
        </div>
    </div>
                <div class="adm-detail-content-item-block">
                    <p><b><?=Loc::getMessage("RUSSIANPOST_MARKER_SETTINGS")?></b></p>
                    <p><?=Loc::getMessage("RUSSIANPOST_SITE_IBLOCK")?></p>
                    <div>
                        <select name="RUSSIANPOST_MARK_IBLOCK[<?=htmlspecialcharsbx($siteList[$i]["ID"])?>]">
                            <option value=""><?=Loc::getMessage("RUSSIANPOST_IBLOCK_SELECT")?></option>
				            <?foreach ($arSiteIblocks as $iblockId=>$arIblock):?>
                                <option value="<?=$iblockId;?>" <?if($iblockMarkId == $iblockId):?>selected<?endif;?>><?=$arIblock['NAME']?></option>
				            <?endforeach;?>
                        </select>
                    </div>
                    <p><?=Loc::getMessage("RUSSIANPOST_IBLOCK_PROP")?></p>
                    <div>
                        <input type="text" value="<?=$markProp;?>" name="RUSSIANPOST_MARK_PROP[<?=htmlspecialcharsbx($siteList[$i]["ID"])?>]">
                    </div>
                </div>
            </div>
            <?endfor;?>
    </td></tr>
	<?$tabControl->BeginNextTab();?>
    <div class="adm-detail-content-item-block">
    <p><b><?=Loc::getMessage("RUSSIANPOST_HEAD_DELIVERY")?></b></p>
    <div>
		<?\Russianpost\Post\Deliveryinfo::showDeliveryInfo();?>
    </div>
    </div>
    <div class="adm-detail-content-item-block">
    <p><b><?=Loc::getMessage("RUSSIANPOST_HEAD_PROP")?></b></p>
    <div>
		<?\Russianpost\Post\Deliveryinfo::showPropsRestriction();?>
    </div>
    </div>
	<?$tabControl->BeginNextTab();?>
    <div class="adm-detail-content-item-block">
        <div>
            <input type="checkbox" value="Y" <?=$debugOrderCheck;?> name="RUSSIANPOST_ORDER_DEBUG"><?=Loc::getMessage("RUSSIANPOST_ORDER_DEBUG")?>
        </div>
        <?if($debugOrder == 'Y'):?>
	        <?
	        $fileSizeOrder = filesize($_SERVER["DOCUMENT_ROOT"]."/bitrix/js/".$module_id."/log/log_order.log")
	        ?>
            <div>
                <br><a target="_blank" href="/bitrix/js/<?=$module_id?>/log/log_order.log"><?=Loc::getMessage("RUSSIANPOST_ORDER_LOG_LINK");?></a> (<?=\CFile::FormatSize($fileSizeOrder);?>)
            </div>
            <?if($fileSizeOrder > 0):?>
                <div>
                    <br><input type="submit" value="<?=Loc::getMessage("RUSSIANPOST_ORDER_LOG_BTN");?>" name="clear_log_order">
                </div>
            <?endif;?>
        <?endif;?>
    </div>
    <div class="adm-detail-content-item-block">
        <div>
            <input type="checkbox" value="Y" <?=$debugCalculateCheck;?> name="RUSSIANPOST_CALCULATE_DEBUG"><?=Loc::getMessage("RUSSIANPOST_CALCULATE_DEBUG")?>
        </div>
	    <?if($debugCalculate == 'Y'):?>
            <?
            $fileSizeCalculate = filesize($_SERVER["DOCUMENT_ROOT"]."/bitrix/js/".$module_id."/log/log_calculate.log")
            ?>
            <div>
                <br><a target="_blank" href="/bitrix/js/<?=$module_id?>/log/log_calculate.log"><?=Loc::getMessage("RUSSIANPOST_CALCULATE_LOG_LINK");?></a> (<?=\CFile::FormatSize($fileSizeCalculate);?>)
            </div>
		    <?if($fileSizeCalculate > 0):?>
                <div>
                    <br><input type="submit" value="<?=Loc::getMessage("RUSSIANPOST_CALCULATE_LOG_BTN");?>" name="clear_log_calculate">
                </div>
		    <?endif;?>
	    <?endif;?>
    </div>
    <div class="adm-detail-content-item-block">
        <div>
            <input type="checkbox" value="Y" <?=$debugKeyCheck;?> name="RUSSIANPOST_KEY_DEBUG"><?=Loc::getMessage("RUSSIANPOST_KEY_DEBUG")?>
        </div>
	    <?if($debugKey == 'Y'):?>
		    <?
		    $fileSizeKey = filesize($_SERVER["DOCUMENT_ROOT"]."/bitrix/js/".$module_id."/log/log_key.log")
		    ?>
            <div>
                <br><a target="_blank" href="/bitrix/js/<?=$module_id?>/log/log_key.log"><?=Loc::getMessage("RUSSIANPOST_KEY_LOG_LINK");?></a> (<?=\CFile::FormatSize($fileSizeKey);?>)
            </div>
		    <?if($fileSizeKey > 0):?>
                <div>
                    <br><input type="submit" value="<?=Loc::getMessage("RUSSIANPOST_KEY_LOG_BTN");?>" name="clear_log_key">
                </div>
		    <?endif;?>
	    <?endif;?>
    </div>
    <?$tabControl->BeginNextTab();?>
    <?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");?>
    <?$tabControl->Buttons();?>
    <input <?if(!$RIGHT_W) echo "disabled" ?> type="submit" name="Update" value="<?=GetMessage("MAIN_SAVE")?>" title="<?=GetMessage("MAIN_OPT_SAVE_TITLE")?>">
    <input <?if(!$RIGHT_W) echo "disabled" ?> type="submit" name="RestoreDefaults" title="<?echo GetMessage("MAIN_HINT_RESTORE_DEFAULTS")?>" OnClick="confirm('<?echo AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING"))?>')" value="<?echo GetMessage("MAIN_RESTORE_DEFAULTS")?>">
    <?$tabControl->End();?>
</form>
    <script>
        function checkName(site_id)
        {
	        if($('[name="extendName['+site_id+']"]').val()==='Y')
            {
	            $('[name="name['+site_id+']"]').closest('div').css('display','none');
	            $('[name="fName['+site_id+']"]').closest('div').css('display','');
	            $('[name="sName['+site_id+']"]').closest('div').css('display','');
	            $('[name="mName['+site_id+']"]').closest('div').css('display','');
            }
            else
            {
	            $('[name="name['+site_id+']"]').closest('div').css('display','');
	            $('[name="fName['+site_id+']"]').closest('div').css('display','none');
	            $('[name="sName['+site_id+']"]').closest('div').css('display','none');
	            $('[name="mName['+site_id+']"]').closest('div').css('display','none');
            }
        }
        function splitName(site_id)
        {
	        $('[name="extendName['+site_id+']"]').val('Y');
	        checkName(site_id);
        }
        function implodeName(site_id)
        {
	        $('[name="extendName['+site_id+']"]').val('N');
	        checkName(site_id);
        }
        function showPopup(code, info){
	        $('.b-popup').hide();

	        var LEFT = $(info).offset().left;
	        var obj = $('#'+code);

	        LEFT -= parseInt(parseInt(obj.css('width'))/2);

	        obj.css({
		        top: ($(info).position().top+15)+'px',
		        left: LEFT,
		        display: 'block'
	        });

	        return false;
        }
        $(document).ready(function(){
        	var site_id = $('#ORDER_current_site').val();
	        checkName(site_id);
        });
    </script>
    <?
}
?>


