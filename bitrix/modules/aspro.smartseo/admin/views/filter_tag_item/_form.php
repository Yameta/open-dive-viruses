<?php

/**
 *  @var string $alias
 *  @var array $data
 */
use Aspro\Smartseo,
    Aspro\Smartseo\Admin\Helper,
    Bitrix\Main\Localization\Loc;

$_suffix = $this->getUnique();
?>
<form id="form_tag_<?= $_suffix ?>" method="POST" action="<?= Helper::url('filter_tag_item/update') ?>">
  <?= bitrix_sessid_post() ?>
  <input page-role="form-field-ID" type="hidden" name="<?=$alias?>[ID]" value="<?=$data['ID']?>">

  <table class="adm-detail-content-table edit-table">
    <tbody>
      <tr>
        <td width="40%" class="adm-detail-content-cell-l"><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_ACTIVE') ?>: </td>
        <td width="60%" class="adm-detail-content-cell-r">
          <div class="aspro-smartseo__form-control">
            <input id="<?=$alias?>_ACTIVE_<?= $_suffix ?>" class="adm-designed-checkbox" type="checkbox" name="<?=$alias?>[ACTIVE]" <?= $data['ACTIVE'] !== 'N' ? 'checked' : '' ?>  value="Y">
            <label class="adm-designed-checkbox-label" for="<?=$alias?>_ACTIVE_<?= $_suffix ?>" title="<?= Loc::getMessage('SMARTSEO_FORM_ENTITY_ACTIVE') ?>"></label>
          </div>
        </td>
      </tr>

      <tr>
        <td width="40%" class="adm-detail-content-cell-l"><?=Loc::getMessage('SMARTSEO_FORM_ENTITY_SORT')?>: </td>
        <td width="60%" class="adm-detail-content-cell-r">
          <div class="aspro-smartseo__form-control">
            <input type="text" name="<?=$alias?>[SORT]" size="7" maxlength="10" value="<?=intval($data['SORT']) ?: 500?>">
          </div>
        </td>
      </tr>

      <tr>
        <td width="40%" class="adm-detail-content-cell-l"><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_NAME') ?>: </td>
        <td width="60%" class="adm-detail-content-cell-r">
          <div class="aspro-smartseo__form-control">
            <input data-field="name" type="text" name="<?=$alias?>[NAME]" value="<?=htmlspecialchars($data['NAME'])?>" maxlength="255" class="aspro-smartseo__form-control__input">
            <span class="aspro-smartseo__loading"></span>
          </div>
        </td>
      </tr>

      <tr>
        <td width="40%" class="adm-detail-content-cell-l adm-detail-content-cell-l--align-top"><?=Loc::getMessage('SMARTSEO_FORM_ENTITY_URL')?>: </td>
        <td width="60%" class="adm-detail-content-cell-r">
          <div class="aspro-smartseo__form-control"><?=htmlspecialchars($data['URL'])?></div>
        </td>
      </tr>

      <tr>
        <td width="40%" class="adm-detail-content-cell-l adm-detail-content-cell-l--align-top"><?=Loc::getMessage('SMARTSEO_FORM_ENTITY_SECTION')?>: </td>
        <td width="60%" class="adm-detail-content-cell-r">
          <div class="aspro-smartseo__form-control"><?=htmlspecialchars($data['SECTION_NAME'].' ['.$data['SECTION_ID'].']')?></div>
        </td>
      </tr>
    </tbody>
  </table>
</form>