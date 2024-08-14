<?php

/**
 *  @var array $listTags
 */
use Aspro\Smartseo,
    Aspro\Smartseo\Admin\Helper,
    Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
?>
<? if ($listTags) : ?>
    <div class="aspro-smartseo__exemple-tags__label">
      <?= loc::getMessage('SMARTSEO_FORM_SAMPLE_EXAMPLE_LABEL') ?>:
    </div>
    <div class="aspro-smartseo__exemple-tags">
      <? foreach ($listTags as $tag) : ?>
          <div class="ui-alert ui-alert-primary ui-alert-xs">
            <span class="ui-alert-message"><?= htmlspecialcharsback($tag) ?></span>
          </div>
      <? endforeach ?>
    </div>
<? endif ?>