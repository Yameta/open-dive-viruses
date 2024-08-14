<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var \Bitrix\Bizproc\Activity\PropertiesDialog $dialog
 */

$blankIdValue = $dialog->getCurrentValue('blankId');

\Bitrix\Main\UI\Extension::load([
	'ui.forms',
	'sign.blank-selector',
]);
?>

<script>
	BX.ready(() => {
		const blankIdInput = document.querySelector('input[name="blankId"]');
		const setBlankId = (blankId) => {
			if (BX.Type.isDomNode(blankIdInput))
			{
				blankIdInput.value = BX.Text.encode(blankId);
			}
		};

		void new BX.Sign.BlankField({
			data: {
				blankId: '<?= CUtil::JSEscape($blankIdValue) ?>',
			},
			selectorOptions: {
				upload: {
					enabled: false,
				},
			},
			targetContainer: document.querySelector('.bizproc-automation-popup-blank-selector'),
			events: {
				onSelect(event) {
					setBlankId(event.getData().id);
				},
				onRemove() {
					setBlankId('');
				}
			},
		});
	});
</script>

<div class="bizproc-automation-popup-settings">
	<div class="bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-top bizproc-automation-popup-settings-title-autocomplete">
		<?= htmlspecialcharsbx($dialog->getMap()['initiatorName']['Name']) ?>:
	</div>
	<?= $dialog->renderFieldControl($dialog->getMap()['initiatorName']) ?>
</div>

<div class="bizproc-automation-popup-settings">
	<div class="bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-top bizproc-automation-popup-settings-title-autocomplete">
		<?= htmlspecialcharsbx($dialog->getMap()['blankId']['Name']) ?>:
	</div>
	<div class="bizproc-automation-popup-blank-selector">
		<input type="hidden" name="blankId" value="<?= htmlspecialcharsbx($blankIdValue) ?>">
	</div>
</div>