<?php

/**
 *  @var array $name
 */
use Aspro\Smartseo,
    Aspro\Smartseo\Admin\Helper,
    Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
?>
<div class="aspro-smartseo__exemple-tags aspro-smartseo__exemple-tags--mt0">
  <div class="ui-alert ui-alert-primary ui-alert-xs">
    <span class="ui-alert-message"><?=htmlspecialcharsback($name) ?></span>
  </div>
</div>
