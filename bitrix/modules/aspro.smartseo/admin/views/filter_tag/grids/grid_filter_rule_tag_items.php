<?php
/**
 *  @var array $data
 *  @var array $gridTagItems
 */

use Aspro\Smartseo\Admin\Helper,
    Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

global $APPLICATION;


$request = Bitrix\Main\Context::getCurrent()->getRequest();
if (
    $request->get('grid_action') &&
    $request->get('grid_id') &&
    $request->get('tag_id')
) {
    $filterTagId = $request->get('tag_id');
} else {
    $filterTagId = $data['ID'];
}

$contextMenuGridTagItemsVar = 'contextMenuGridTagItems'.$filterTagId;

$gridRows = [];
foreach ($gridTagItems['ROWS'] as $row) {
    $gridRow = $row;

    $gridRow['ACTIVE'] = $row['ACTIVE'] === 'Y' ? Loc::getMessage('SMARTSEO_GRID_TAG_ITEMS_VALUE_Y') : Loc::getMessage('SMARTSEO_GRID_TAG_ITEMS_VALUE_N');

    $gridRow['SECTION'] = $row['SECTION_NAME'] . ' [' . $row['SECTION_ID'] . ']';

    $name = $row['NAME'];
    ob_start();
    @include __DIR__.'/../_tag_item.php';
    $gridRow['NAME'] = trim(ob_get_clean());

    $rowData = [];
    foreach ($gridTagItems['COLUMNS'] as $column) {
        $rowData[$column['id']] = $gridRow[$column['field']];
    }

    $gridRows[] = [
        'data' => $rowData,
        'actions' => [
            [
                'text' => Loc::getMessage('SMARTSEO_GRID_TAG_ITEMS_ACTION_EDIT'),
                'default' => true,
                'onclick' => vsprintf("${contextMenuGridTagItemsVar}.actionEdit(%d, '%s')", [
                    'ID' => $row['ID'],
                    'NAME' => htmlspecialchars($row['NAME'], ENT_QUOTES),
                ])
            ],
            [
                'text' => $row['ACTIVE'] == 'Y'
                    ? Loc::getMessage('SMARTSEO_GRID_TAG_ITEMS_ACTION_DEACTIVATE')
                    : Loc::getMessage('SMARTSEO_GRID_TAG_ITEMS_ACTION_ACTIVATE'),
                'default' => true,
                'onclick' => $row['ACTIVE'] == 'Y'
                    ? "${contextMenuGridTagItemsVar}.actionDeactivate($row[ID])"
                    : "${contextMenuGridTagItemsVar}.actionActivate($row[ID])"
            ],
            [
              'delimiter' => true,
            ],
            [
                'text' => Loc::getMessage('SMARTSEO_GRID_TAG_ITEMS_ACTION_DELETE'),
                'default' => true,
                'onclick' => "${contextMenuGridTagItemsVar}.actionDelete($row[ID])"
            ],
        ]
    ];
}
?>
<div class="aspro-smartseo__form-detail__wrapper-filter-inline">
    <?
    $APPLICATION->IncludeComponent('bitrix:main.ui.filter', '', [
        'FILTER_ID' => $gridTagItems['FILTER_ID'],
        'GRID_ID' => $gridTagItems['GRID_ID'],
        'FILTER' => $gridTagItems['FILTER_FIELDS'],
        'ENABLE_LIVE_SEARCH' => true,
        'ENABLE_LABEL' => true
    ]);
    ?>
</div>
<?
$APPLICATION->IncludeComponent('bitrix:main.ui.grid', '', [
    'GRID_ID' => $gridTagItems['GRID_ID'],
    'COLUMNS' => $gridTagItems['COLUMNS'],
    'ROWS' => $gridRows,
    'FOOTER' => [
        'TOTAL_ROWS_COUNT' => $gridTagItems['TOTAL_ROWS_COUNT'],
    ],
    'SHOW_ROW_CHECKBOXES' => false,
    'NAV_OBJECT' => $gridTagItems['NAV_OBJECT'],
    'AJAX_MODE' => 'Y',
    'AJAX_ID' => \CAjax::getComponentID('bitrix:main.ui.grid', '.default', ''),
    'PAGE_SIZES' => [
        ['NAME' => '10', 'VALUE' => '10'],
        ['NAME' => '20', 'VALUE' => '20'],
        ['NAME' => '50', 'VALUE' => '50'],
        ['NAME' => '100', 'VALUE' => '100']
    ],
    'AJAX_OPTION_JUMP' => 'N',
    'SHOW_CHECK_ALL_CHECKBOXES' => false,
    'SHOW_ROW_ACTIONS_MENU' => true,
    'SHOW_GRID_SETTINGS_MENU' => true,
    'SHOW_NAVIGATION_PANEL' => true,
    'SHOW_PAGINATION' => true,
    'SHOW_SELECTED_COUNTER' => false,
    'SHOW_TOTAL_COUNTER' => true,
    'SHOW_PAGESIZE' => true,
    'SHOW_ACTION_PANEL' => true,
    'ALLOW_COLUMNS_SORT' => true,
    'ALLOW_COLUMNS_RESIZE' => false,
    'ALLOW_HORIZONTAL_SCROLL' => true,
    'ALLOW_SORT' => true,
    'ALLOW_PIN_HEADER' => false,
    'AJAX_OPTION_HISTORY' => 'N',
]);
?>
<script>
var phpObjectgridTagItems = <?=CUtil::PhpToJSObject([
    'gridId' => $gridTagItems['GRID_ID'],
    'gridFile' => $gridTagItems['GRID_FILE'],
    'filterTagId' => $filterTagId,
    'urls' => [
        'ACTION_ACTIVATE' => Helper::url('filter_tag_item/activate', ['sessid' => bitrix_sessid()]),
        'ACTION_DEACTIVATE' => Helper::url('filter_tag_item/deactivate', ['sessid' => bitrix_sessid()]),
        'ACTION_DELETE' => Helper::url('filter_tag_item/delete', ['sessid' => bitrix_sessid()]),
        'DETAIL_PAGE' => Helper::url('filter_tag_item/detail', ['sessid' => bitrix_sessid()]),
    ]
]) ?>;

BX.message({
    SMARTSEO_POPUP_TAG_ITEMS_BTN_DELETE: '<?=Loc::getMessage('SMARTSEO_POPUP_TAG_ITEMS_BTN_DELETE')?>',
    SMARTSEO_POPUP_TAG_ITEMS_BTN_CANCEL: '<?=Loc::getMessage('SMARTSEO_POPUP_TAG_ITEMS_BTN_CANCEL')?>',
    SMARTSEO_POPUP_TAG_ITEMS_BTN_CLOSE: '<?=Loc::getMessage('SMARTSEO_POPUP_TAG_ITEMS_BTN_CLOSE')?>',
    SMARTSEO_POPUP_TAG_ITEMS_MESSAGE_DELETE: '<?=Loc::getMessage('SMARTSEO_POPUP_TAG_ITEMS_MESSAGE_DELETE')?>',
    SMARTSEO_TAG_ITEMS_GENERAL_NAME: '<?=Loc::getMessage('SMARTSEO_TAG_ITEMS_GENERAL_NAME')?>',
});

var <?=$contextMenuGridTagItemsVar?> = new AsproUI.ContextMenuInnerGrid();

BX.ready(function () {
    /*
    * Tabs Init
    */
    (function(obj) {
        let tabs = new AsproUI.DynamicTabs('tabs_tag-items_<?=$filterTagId?>', {}, {}, {
            newTabName: BX.message('SMARTSEO_TAG_ITEMS_GENERAL_NAME'),
        });

        tabs.onAfterAdd = function (elName, elBody, tab){
            if (!obj.urls.hasOwnProperty('DETAIL_PAGE')) {
                console.log('return');
                return;
            }

            let dataset = tab.dataset;
            let url = obj.urls.DETAIL_PAGE;

            if (obj.filterTagId) {
                url = url + '&filter_tag_id=' + obj.filterTagId;
            }

            if (dataset.ID) {
                url = url + '&id=' + dataset.ID;
            }

            let wait = BX.showWait(elBody);

            BX.ajax({
                url: url,
                data: {
                    module: 'smartseo'
                },
                method: 'POST',
                dataType: 'html',
                onsuccess: function (html)
                {
                    elBody.innerHTML = html;
                    BX.closeWait(elBody, wait);
                },
                onfailure: function (){}
            });
        }
    })(phpObjectgridTagItems);

    /*
    * Grid menu actions
    */
    <?=$contextMenuGridTagItemsVar?>.register(
        phpObjectgridTagItems.gridId,
        'tabs_tag-items_<?=$filterTagId?>',
        phpObjectgridTagItems.urls,
        {
            popupBtnDelete: BX.message('SMARTSEO_POPUP_TAG_ITEMS_BTN_DELETE'),
            popupBtnCancel: BX.message('SMARTSEO_POPUP_TAG_ITEMS_BTN_CANCEL'),
            popupBtnClose: BX.message('SMARTSEO_POPUP_TAG_ITEMS_BTN_CLOSE'),
            popupMessageDelete: BX.message('SMARTSEO_POPUP_TAG_ITEMS_MESSAGE_DELETE')
        }
    );

    /*
    * Grid action events
    */
    (function(obj){
        BX.addCustomEvent('Grid::beforeRequest', function(gridObject, eventArgs){            
            if (eventArgs.gridId == obj.gridId) {               
                eventArgs.data = {
                    tag_id: obj['filterTagId'],
                    grid_id: obj['gridFile'],
                };
                eventArgs.method = 'POST';
            }
        });
    })(phpObjectgridTagItems);

    /*
    * Reload grid
    */
    var gridObject = BX.Main.gridManager.getById('<?=$gridTagItems['GRID_ID']?>');
    if (
        typeof gridObject !== 'undefined' &&
        gridObject &&
        gridObject.hasOwnProperty('instance')
    ) {
        gridObject.instance.reloadTable(null, null);
    }
});
</script>