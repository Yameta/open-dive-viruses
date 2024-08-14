<?php
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
if($_POST["load_ajax"] == "y")
{
	define("NO_AGENT_CHECK", true);
	define('PUBLIC_AJAX_MODE', true);
	define('STOP_STATISTICS', true);
}
$start = time();
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions == "D")
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

function loadCsvToArray($content, $delimetr=";")
{
	$arContent = explode("\r\n",$content);
	foreach($arContent as $arStr)
	{
		$arFields = str_getcsv($arStr,$delimetr);
		$arResult[] = $arFields;
	}
	
	return $arResult;
}

function getPrice($price)
{
	return trim(str_replace(",",".",$price));
}


function removeBOM($str="") {
	$str=urlencode($str);
	$str=str_replace("%EF%BB%BF","",$str);
    if(substr($str, 0, 3) == pack('CCC', 0xef, 0xbb, 0xbf)) {
        $str = substr($str, 3);
    }
	if(substr($str, 0, 3) == "\xEF\xBB\xBF") {
		$str = substr($str, 3);
	}
	$str=urldecode($str);
    return $str;
}


if(array_key_exists("stop", $_POST))
{
	unset($_SESSION["BX_ADMIN_REDIRECT_ARRAY_LOAD"]);
}
$step = "loadfile";
$msg = "";
if(array_key_exists("next_step", $_POST))
{
	$messageErrors = array();
	$uploadFile = $_SERVER["DOCUMENT_ROOT"].'/upload/redirects/'.basename("redirects.csv");
	
	
	
	switch($_POST["step"])
	{
		case "loadfile":
			$arPath1 = pathinfo($uploadFile);
			$arPath = pathinfo($_FILES['userfile']['name']);
			if(!in_array($arPath["extension"], array("csv")))
			{
				$messageErrors[] = "<strong>Внимание!</strong> необходим фал в формате .csv";
			}
			else
			{
				@mkdir($arPath1['dirname'],0777,true);
				if (!move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadFile)) 
				{
					$messageErrors[] = "<strong>Внимание!</strong> не удалось загрузить файл";
				}
				else
				{
					$_SESSION["BX_ADMIN_REDIRECT_ARRAY_LOAD"] = array("ITEMS" => array(), "COUNT" => array());
					$_SESSION["BX_ADMIN_REDIRECT_ARRAY_LOAD"]["ITEMS"] = loadCsvToArray(file_get_contents($uploadFile));
					$_SESSION["BX_ADMIN_REDIRECT_ARRAY_LOAD"]["COUNT"] = count($_SESSION["BX_ADMIN_REDIRECT_ARRAY_LOAD"]["ITEMS"]);
					unlink($uploadFile);
					$step = "update";
				}
			}
		break;
		case "update":
			if(!empty($_SESSION["BX_ADMIN_REDIRECT_ARRAY_LOAD"]["ITEMS"]))
			{
				if($_POST["load_ajax"] == "y")
				{
					
					$first = count($_SESSION["BX_ADMIN_REDIRECT_ARRAY_LOAD"]["ITEMS"]) == $_SESSION["BX_ADMIN_REDIRECT_ARRAY_LOAD"]["COUNT"];
					$_result = $_SESSION["BX_ADMIN_REDIRECT_ARRAY_LOAD"]["ITEMS"];
					$_SESSION["BX_ADMIN_REDIRECT_ARRAY_LOAD"]["ITEMS"]=array_splice($_result, 50);
					$result = array();
					foreach($_result as $val)
					{
						if(!empty(trim($val[0])) && !empty(trim($val[1])))
						{
							$result[trim(removeBOM($val[0]))] = trim($val[1]);
						}
					}
					unset($_result);
					if(!empty($result))
					{
						WRPRedirect::loadRedirect("s2",$result);
					}
					
					unset($result);
					$APPLICATION->RestartBuffer();
					$response = array(
						"next" => count($_SESSION["BX_ADMIN_REDIRECT_ARRAY_LOAD"]["ITEMS"]) > 0,
						"msg" => "<strong>Обработано:</strong> ".($_SESSION["BX_ADMIN_REDIRECT_ARRAY_LOAD"]["COUNT"] - count($_SESSION["BX_ADMIN_REDIRECT_ARRAY_LOAD"]["ITEMS"]))." из ".$_SESSION["BX_ADMIN_REDIRECT_ARRAY_LOAD"]["COUNT"]
					);
					
					echo json_encode($response);
					require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_after.php");
					die;
				}
				
			}
			$step = "ready";
		break;
		case "":
			unlink($uploadFile);
		break;
	}	
}


$APPLICATION->SetTitle("Загрузка редиректов");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
\Bitrix\Main\UI\Extension::load("ui.alerts");
?>


<form method="POST" enctype="multipart/form-data" action="<?echo $APPLICATION->GetCurUri()?>">
	<input type="hidden" name="step" value="<?=$step?>"/>
	<div class="adm-detail-content-wrap">
		<div class="adm-detail-content">
			<?foreach($messageErrors as $text):?>
			<div class="ui-alert ui-alert-danger">
				<span class="ui-alert-message"><?=$text?></span>
			</div>
			<?endforeach;?>
			<?if($step == "update"):?>
				<?$idBlockAjax = "info_".randString(7, array("abcdefghijklnmopqrstuvwxyz","ABCDEFGHIJKLNMOPQRSTUVWX­YZ","0123456789"));?>
				<div class="ui-alert ui-alert-warning">
					<span class="ui-alert-message" id="<?=$idBlockAjax?>"><strong>Обработано:</strong> <?=($_SESSION["BX_ADMIN_REDIRECT_ARRAY_LOAD"]["COUNT"] - count($_SESSION["BX_ADMIN_REDIRECT_ARRAY_LOAD"]["ITEMS"]))?> из <?=$_SESSION["BX_ADMIN_REDIRECT_ARRAY_LOAD"]["COUNT"]?></span>
				</div>
				<script type="text/javascript">
						var max_call_i=50;
						function work_with_row(wait,call_self){
							call_self++;
							
							BX.ajax({   
								url: '<?echo $APPLICATION->GetCurUri()?>',
								data: {
									step:'<?=$step?>',
									next_step:'1',
									load_ajax:'y'
								},
								method: 'POST',
								dataType: 'json',
								timeout: 100,
								async: true,
								processData: true,
								scriptsRunFirst: true,
								emulateOnload: true,
								start: true,
								cache: false,
								onsuccess: function(data){
										BX.adjust(BX('<?=$idBlockAjax?>'), {html:data.msg});	
									if(data.next){
										if(call_self < max_call_i)
										{
											work_with_row(wait,call_self);
										}
										else
										{
											setTimeout(function(){work_with_row(wait,0);}, 0);
										}
									}else{
										BX.closeWait('xls_container',wait);
										
										BX.adjust(BX('stop_button'), {props: {value:"Загрузить снова"}});
									}
								}
							});
						}

						BX.ready(function(){
							 var wait = BX.showWait('xls_container');
							 
							 work_with_row(wait,0);
						});
				</script>
				<?endif;?>
			<?if($step == "loadfile"):?>
			<div class="adm-detail-content-item-block">
				
				<table class="adm-detail-content-table edit-table" id="edit1_edit_table">
					<tbody>		
						<tr>
							<td class="adm-detail-content-cell-l" style="width:20%">Прикрепите файл:</td>
							<td class="adm-detail-content-cell-r">
								<input name="userfile" type="file" />
							</td>
						</tr>
						
					</tbody>
				</table>
			</div>
			<?endif;?>
			
		</div>
		<div class="adm-detail-content-btns-wrap" id="tabControl_buttons_div" style="left: 0px;">
			<div class="adm-detail-content-btns">
			<?if($step == "loadfile"):?>
			<input type="submit" id="start_button" value="Загрузить" name="next_step" class="adm-btn-save">
			<?else:?>
				<input type="submit" id="stop_button" name="stop" value="Остановить">
			<?endif;?>
			</div>
		</div>
	</div>
</form>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>