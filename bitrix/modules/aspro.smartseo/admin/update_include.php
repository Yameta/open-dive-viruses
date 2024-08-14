<?
use Bitrix\Main\Localization\Loc,
	Aspro\Smartseo\Thematics;

$moduleID = 'aspro.smartseo';
Bitrix\Main\Loader::includeModule($moduleID);

$smartSeoTemplateId = 'smartseo';

// ajax result
$arResult = [];

$action = $_POST['action'] ?: '';
$step = $_POST['step'] ?: '';

try {
	if ($action === 'download') {
		if ($step === 'check') {
			$arFiles = Thematics::check([
				'templateID' => $smartSeoTemplateId,
				'moduleID' => $moduleID,
				'bSeparateModule' => true,
				'bUpdate' => true,
			]);

			$arResult = [
				'title' => Loc::getMessage('ASPRO_SMARTSEO_CLEAR'),
				'nextStep' => 'clear',
				'procent' => 10,
			];
		}
		elseif ($step === 'clear') {
			Thematics::clear();

			$arResult = [
				'title' => Loc::getMessage('ASPRO_SMARTSEO_DOWNLOAD'),
				'nextStep' => 'download',
				'procent' => 20,
			];
		}
		elseif ($step === 'download') {
			$result = Thematics::download([
				'templateID' => $smartSeoTemplateId,
				'moduleID' => $moduleID,
			]);

			if ($result['arDownloadFile']) {
				$status = Loc::getMessage(
					'ASPRO_SMARTSEO_DOWNLOAD_PART',
					$result['arStatus']
				);

				$arResult = [
					'title' => $status,
					'nextStep' => 'download', 
					'procent' => 20,
				];
			}
			else {
				$arResult = [
					'title' => Loc::getMessage('ASPRO_SMARTSEO_UNZIP'),
					'nextStep' => 'unzip', 
					'procent' => 50,
				];
			}
		}
		elseif ($step === 'unzip') {
			$result = Thematics::unzip([
				'templateID' => $smartSeoTemplateId,
				'moduleID' => $moduleID,
				'bUpdate' => true,
			]);

			if ($result['unZipFile']) {
				$status = Loc::getMessage(
					'ASPRO_SMARTSEO_UNZIP_PART',
					$result['arStatus']
				);

				$arResult = [
					'title' => $status,
					'nextStep' => 'unzip', 
					'procent' => 50,
				];
			}
			else {
				$arResult = [
					'title' => Loc::getMessage('ASPRO_SMARTSEO_CLEAR_FINAL'),
					'nextStep' => 'clear_final', 
					'procent' => 80,
				];
			}
		}
		elseif ($step === 'clear_final') {
			Thematics::clear();

			$arResult = [
				'title' => Loc::getMessage('ASPRO_SMARTSEO_FINISH'),
				'nextStep' => 'finish',
				'procent' => 100,
			];
		}
	}
	elseif ($action === 'check_updates') {
		$arFiles = Thematics::check([
			'templateID' => $smartSeoTemplateId,
			'moduleID' => $moduleID,
			'bSeparateModule' => true,
			'bUpdate' => true,
		]);

		$arResult = [
			'title' => $arFiles ? Loc::getMessage('ASPRO_SMARTSEO_UPDATES_AVAILABLE', ['#COUNT_UPDATES#' => count($arFiles)]) : Loc::getMessage('ASPRO_SMARTSEO_NO_UPDATES_AVAILABLE'),
			'nextStep' => 'finish',
			'need_update' => (bool)$arFiles,
		];

	}
	elseif ($action === 'get_description') {
		$arDescriptions = Thematics::description([
			'moduleID' => $moduleID,
			'bSeparateModule' => true,
			'bUpdate' => true,
		]);
		$arDescriptions = is_array($arDescriptions) ? array_reverse($arDescriptions) : [];
		
		ob_start();
		foreach ($arDescriptions as $version => $description) {
			foreach ($description as $keyDesc => $valueDesc) {?>
				<div class="smartseo-version-info">
					<div class="smartseo-version-info__title">
						<?=Loc::getMessage('ASPRO_SMARTSEO_DESCRIPTION_VERSION').$version;?>
					</div>
					<div class="smartseo-version-info__description">
						<?=$valueDesc;?>
						<div class="smartseo-backup-alert"><?=Loc::getMessage('ASPRO_SMARTSEO_DESCRIPTION_ALERT')?></div>
					</div>
				</div>
			<?}
		}
		$htmlDescription = ob_get_clean();

		$arResult = [
			'content' => $htmlDescription,
		];

	}
}
catch (\Exception $e) {
	$arResult['errors'] = $e->getMessage();
	$arResult['title'] = Loc::getMessage('ASPRO_SMARTSEO_ERROR');
}

echo \Bitrix\Main\Web\Json::encode($arResult);
die();
