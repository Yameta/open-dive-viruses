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

<tr>
	<td align="right" width="40%" valign="top">
		<span class="adm-required-field">
			<?= htmlspecialcharsbx($dialog->getMap()['initiatorName']['Name']) ?>
		</span>:
	</td>
	<td width="60%">
		<?= $dialog->renderFieldControl($dialog->getMap()['initiatorName'], null, true, 0) ?>
	</td>
</tr>

<tr>
	<td align="right" width="40%" valign="top">
		<?= htmlspecialcharsbx($dialog->getMap()['blankId']['Name']) ?>
	</td>
	<td width="60%">
		<div class="bizproc-automation-popup-blank-selector">
			<input type="hidden" name="blankId" value="<?= htmlspecialcharsbx($blankIdValue) ?>">
		</div>
	</td>
</tr>
