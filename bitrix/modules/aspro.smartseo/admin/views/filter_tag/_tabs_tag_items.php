<?php
/**
 *  @var string $_suffix
 *  @var string $gridTagItems[GRID_ID]
 *  @var string $gridTagItems[FILTER_ID]
 *  @var array $data
 *  @var array $gridTagItems[COLUMNS]
 *  @var array $gridTagItems[FILTER_FIELDS]
 *  @var array $gridTagItems[ROWS]
 *  @var int $gridTagItems[TOTAL_ROWS_COUNT]
 *  @var array $gridTagItems
 */

use Bitrix\Main\Localization\Loc,
    Aspro\Smartseo\Admin\Helper;
?>
<div class="aspro-smartseo__form-detail__tag-items-tabs-wrapper">
    <div id="tabs_tag-items_<?=$data['ID']?>" class="aspro-ui-tabs">
    <div tabs-role="tabs-head" class="aspro-ui-tabs__wrapper-tabs">
        <div tabs-role="tabs" class="aspro-ui-tabs__tabs">
        <div tabs-role="tab" data-target="#tabs_tag-items_<?=$data['ID']?>_tab_view" class="aspro-ui-tabs__tab aspro-ui-tabs__tab--active">
            <span tabs-role="tab-name" class="aspro-ui-tabs__tab__name"><?= Loc::getMessage('SMARTSEO_TAG_ITEMS_GENERAL_NAME') ?></span>
        </div>
        </div>
        <div tabs-role="toogle-fix" class="aspro-ui-tabs__toggle-fix aspro-ui-tabs__toggle-fix--off"></div>
    </div>
    <div tabs-role="tab-views" class="aspro-ui-tabs__view-tabs">
        <div tabs-role="tab-view" id="tabs_tag-items_<?=$data['ID']?>_tab_view" tabs-role="tab" class="aspro-ui-tabs__view-tab active">
        <div tabs-role="tab-view-title" class="aspro-ui-tabs__view-tab__title"><?= Loc::getMessage('SMARTSEO_TAG_ITEMS_GENERAL_NAME') ?></div>
        <div tabs-role="tab-view-body">
            <div class="aspro-smartseo__wrapper-tag-items">
                <? require $this->getViewPath() . 'grids/' . $gridTagItems['GRID_FILE'] . '.php'; ?>
            </div>
        </div>
        </div>
    </div>
    </div>
</div>