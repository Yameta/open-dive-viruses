<?
use Bitrix\Main\Config\Option,
	Bitrix\Main\Localization\Loc;

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');

global $APPLICATION;
IncludeModuleLangFile(__FILE__);

$moduleID = 'aspro.smartseo';
\Bitrix\Main\Loader::includeModule($moduleID);

// title
$APPLICATION->SetTitle(Loc::getMessage('ASPRO_SMARTSEO_PAGE_TITLE'));

// css & js
$APPLICATION->SetAdditionalCss('/bitrix/css/'.$moduleID.'/style.css');
CJSCore::Init(array('jquery'));

// rights
$RIGHT = $APPLICATION->GetGroupRight($moduleID);
if($RIGHT < 'R'){
	echo CAdminMessage::ShowMessage(GetMessage('ASPRO_SMARTSEO_NO_RIGHTS_FOR_VIEWING'));
}

$bReadOnly = $RIGHT < 'W';

/////////////////////////
//check rights for obf files
$arTestFiles = [
    '/bitrix/modules/'. $moduleID .'/lib/thematics.php',
    '/bitrix/modules/'. $moduleID .'/admin/update_include.php',
];

$bShowNotify = false;
foreach ($arTestFiles as $key => $file) {
    $filePath = $_SERVER['DOCUMENT_ROOT'] . $file;
    if(!is_readable($filePath)){
        $bShowNotify = true;
    }
}

if($bShowNotify){
    \CAdminNotify::Add(
        [
            "MESSAGE" => Loc::getMessage('ASPRO_SMARTSEO_UPDATE_OBF_FILES_ERROR'),
            "TAG" => 'aspro_smartseo',
            "MODULE_ID" => $moduleID,
        ]
    );
}
//////////////////


// ajax action
if(
	$_SERVER['REQUEST_METHOD'] === 'POST' &&
	isset($_POST['action']) &&
	in_array($_POST['action'], array('download', 'check_updates', 'get_description'))
){
	$APPLICATION->RestartBuffer();

	$error = $message = false;

	if(
		check_bitrix_sessid() &&
		!$bReadOnly
	){
		include('update_include.php');
	}

	die();
}
?>
<?if($RIGHT >= 'R'):?>
	<?
	$arTabs = [
		[
			'DIV' => 'smartseo-update',
			'TAB' => Loc::getMessage('ASPRO_SMARTSEO_UPDATE_TAB'),
			'TITLE'=> Loc::getMessage('ASPRO_SMARTSEO_UPDATE_TAB_TITLE'),
		]
	];
	$tabControl = new CAdminTabControl("tabControl", $arTabs);
	$tabControl->Begin();
	?>
	<div class="aspro-smartseo-update-admin-area">        
		<form method="post" class="smartseo-download-form" enctype="multipart/form-data" action="<?=$APPLICATION->GetCurPage()?>?lang=<?=LANGUAGE_ID?>">
			<?=bitrix_sessid_post();?>
			<?foreach($arTabs as $key => $arTab){
				$arTabViewOptions = [
					'showTitle' => false,
					'className' => 'adm-detail-content-without-bg',
				];
				$tabControl->BeginNextTab($arTabViewOptions);?>
				<div class="aspro-smartseo-update-wrap" style="max-width:540px;">

					<div class="aspro-smartseo__updates-info">
						<div class="aspro-smartseo__updates-title">
							<?=Loc::getMessage('ASPRO_SMARTSEO_CHECK_UPDATES_AVAIBLE')?>
						</div>
						<div class="aspro-smartseo__updates-description" style="display: none;">
							<input type="button" class="description-smartseo-button adm-btn" name="description-smartseo" value="<?=Loc::getMessage('ASPRO_SMARTSEO_BUTTON_DESCRIPTION')?>" title="<?=Loc::getMessage('ASPRO_SMARTSEO_BUTTON_DESCRIPTION')?>">
						</div>
					</div>

					<div class="aspro-smartseo-admin-waiter"></div>

					<div class="download-smartseo-wrap" style="display: none;">
						<div class="download-smartseo-wrap__title">
							<?=Loc::getMessage('ASPRO_SMARTSEO_CAN_DOWNLOAD')?>
						</div>
						<div class="smartseo-backup-alert"><?=Loc::getMessage('ASPRO_SMARTSEO_DESCRIPTION_ALERT')?></div>
						<br>
						<input type="button" class="download-smartseo submit-btn adm-btn-save" name="download-smartseo" value="<?=Loc::getMessage('ASPRO_SMARTSEO_BUTTON_DOWNLOAD')?>" title="<?=Loc::getMessage('ASPRO_SMARTSEO_BUTTON_DOWNLOAD')?>">
					</div>
					
					<div class="aspro-smartseo__progress-download" style="display:none;">
						<div class="aspro-smartseo__progress-download__title"><?=Loc::getMessage('ASPRO_SMARTSEO_CHECK')?></div>
						<div class="aspro-smartseo__progress-download__bar" >
							<div class="aspro-smartseo__progress-download__bar-inner" style="width: 0%;"></div>
						</div>
					</div>
					<div class="adm-info-message-red">
						<div class="download-errors adm-info-message" style="display:none;">
							<div class="adm-info-message-title"></div>
							<div class="adm-info-message-icon"></div>
						</div>
					</div>
				</div>
			<?
			}
			$tabControl->End();
			?>
			<script>
			/*lang text*/
			BX.message({
				'ASPRO_SMARTSEO_TITLE_DESCRIPTION': '<?=Loc::getMessage("ASPRO_SMARTSEO_TITLE_DESCRIPTION")?>',
			});
			/**/

			$(document).ready(function(){
				function sendAction(action, step){
					if(
						action === 'download' || action === 'check_updates'
					){
						var $form = $('.smartseo-download-form');
						if($form.length){
							var data = {
								sessid: $form.find('input[name=sessid]').val(),
								action: action,
								step: step
							};
							
							$.ajax({
								type: 'POST',
								data: data,								
								dataType: 'json',
								success: function(jsonData){
									if(jsonData){
										if(jsonData['errors']){
											console.log(jsonData['errors']);
											$('.download-errors .adm-info-message-title').html(jsonData['errors']);
											$('.download-errors').show();
											$('.aspro-smartseo__progress-download').hide();
										} else {
											if(jsonData['nextStep'] && jsonData['nextStep'] !== 'finish'){
												let nextStep = jsonData['nextStep'];												
												sendAction(action, nextStep);
											}
											if(jsonData['procent']){
												$('.aspro-smartseo__progress-download__bar-inner').css('width', jsonData['procent'] + '%');
											}
											if(jsonData['title']){
                                                if(action === "check_updates"){
                                                    $('.aspro-smartseo__updates-title').html(jsonData['title']);
                                                } else{
                                                    $('.aspro-smartseo__progress-download__title').html(jsonData['title']);
                                                }
											}

											if(action === "download" && jsonData['nextStep'] === 'finish'){
												$('.download-smartseo-wrap').hide();
											}
											if(action === "check_updates" && jsonData['need_update'] === true){
                                                $('.download-smartseo-wrap').show();
												$('.aspro-smartseo__updates-description').show();
                                            }
										}
									}
                                    $('.aspro-smartseo-admin-waiter').hide();
								},
								error: function(){
									
								}
							});
						}
					} else if(action === 'get_description'){
						var $form = $('.smartseo-download-form');
						if($form.length){
							var data = {
								sessid: $form.find('input[name=sessid]').val(),
								action: action,
								step: step
							};
							
							$.ajax({
								type: 'POST',
								data: data,								
								dataType: 'json',
								success: function(jsonData){
									if(jsonData){
										if(jsonData['errors']){
											console.log(jsonData['errors']);											
											window.smartSeoUpdatePopup.SetContent('<div class="smartseo-description-error">'+jsonData['errors']+'</div>');
										} else {
											if(jsonData['content'] && typeof window.smartSeoUpdatePopup !== 'undefined'){
												window.smartSeoUpdatePopup.SetContent(jsonData['content']);
											}
										}
									}
								},
								error: function(){
									
								}
							});
						}
					}
				}

				$(document).on('click', '.download-smartseo', function(){
					$('.download-smartseo-wrap').hide();
					$('.aspro-smartseo__progress-download').show();
					sendAction('download', 'check');
				});

				$(document).on('click', '.description-smartseo-button', function(){
					
					if(typeof window.smartSeoUpdatePopup === 'undefined'){
						window.smartSeoUpdatePopup = new BX.CDialog({
							'title': BX.message('ASPRO_SMARTSEO_TITLE_DESCRIPTION'),
							'content': '<br><div class="aspro-smartseo-admin-waiter"></div>',
							'width': 615,
							'height': 500,
							'draggable': true,
							'resizable': false,
							//'buttons': [BX.CDialog.btnClose]
						});

						window.smartSeoUpdatePopup.DIV.classList.add('smartseo-description-popup');
						let popupInner = window.smartSeoUpdatePopup.DIV.querySelector('.bx-core-adm-dialog-content');
						if(popupInner){
							popupInner.classList.add('smartseo-scrollblock');
						}
						sendAction('get_description', '');
					}
					window.smartSeoUpdatePopup.Show();
				});
                sendAction('check_updates', '');                
			});
			</script>
		</form>
	</div>
	<?CUtil::InitJSCore(array('window'));?>
<?endif;?>

<?require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');?>