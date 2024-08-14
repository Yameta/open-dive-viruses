<?php

namespace Aspro\Smartseo\Admin\UI;

use Aspro\Smartseo,
    \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class FilterRuleTagItemAdminUI extends AbstractAdminUI
{
    const PREFIX_COLUMN = 'FRT_';

    private $filterTagId;

    function __construct($filterTagId = null)
    {
        $this->filterTagId = $filterTagId;
    }

    public function getGridId()
    {
        return 'grid_filter_rule_tag_items_'.$this->filterTagId;
    }

    public function getGridFile() {
        return 'grid_filter_rule_tag_items';
    }

    public function getColumnGridPrefix()
    {
        return self::PREFIX_COLUMN;
    }

    public function getFilterId()
    {
        if (!$this->filterTagId) {
            return '';
        }

        return 'filter_grid_tag_item_' . $this->filterTagId;
    }

    public function getFilterFields()
    {
        return [
            [
                'id' => 'ID',
                'name' => Loc::getMessage('SMARTSEO_TAG_UI_ENTITY_ID'),
                'type' => 'number',
            ],
            [
                'id' => 'ACTIVE',
                'name' => Loc::getMessage('SMARTSEO_TAG_ITEM_UI_ACTIVE'),
                'type' => 'list',
                'items' => [
                    'Y' => Loc::getMessage('SMARTSEO_TAG_ITEM_UI_VALUE_Y'),
                    'N' => Loc::getMessage('SMARTSEO_TAG_ITEM_UI_VALUE_N'),
                ]
            ],
            [
                'id' => 'NAME',
                'name' => Loc::getMessage('SMARTSEO_TAG_ITEM_UI_NAME'),
                'type' => 'text',
                'quickSearch' => '',
                'default' => true,
            ],
            [
                'id' => 'URL',
                'name' => Loc::getMessage('SMARTSEO_TAG_ITEM_UI_URL'),
                'type' => 'text',
                'quickSearch' => '',
                'default' => true,
            ],
            [
                'id' => 'SECTION_ID',
                'name' => Loc::getMessage('SMARTSEO_TAG_ITEM_UI_SECTION'),
                'type' => 'custom_entity',
                'params' => [
                    'multiple' => 'Y'
                ]
            ],
        ];
    }

    public function getContextMenu()
    {
        return [];
    }

    public function getGridColumns()
    {
        return [
            [
                'id' => self::PREFIX_COLUMN . 'ACTIVE',
                'field' => 'ACTIVE',
                'title' => Loc::getMessage('SMARTSEO_TAG_ITEM_UI_ACTIVE'),
                'name' => Loc::getMessage('SMARTSEO_TAG_ITEM_UI_ACTIVE'),
                'sort' => 'ACTIVE',
                'default' => true,
            ],
            [
                'id' => self::PREFIX_COLUMN . 'NAME',
                'field' => 'NAME',
                'title' => Loc::getMessage('SMARTSEO_TAG_ITEM_UI_NAME'),
                'name' => Loc::getMessage('SMARTSEO_TAG_ITEM_UI_NAME'),
                'sort' => 'NAME',
                'default' => true,
                
            ],
            [
                'id' => self::PREFIX_COLUMN . 'SORT',
                'field' => 'SORT',
                'title' => Loc::getMessage('SMARTSEO_TAG_ITEM_UI_SORT'),
                'name' => Loc::getMessage('SMARTSEO_TAG_ITEM_UI_SORT'),
                'sort' => 'SORT',
                'default' => true,
                
            ],
            [
                'id' => self::PREFIX_COLUMN . 'URL',
                'field' => 'URL',
                'title' => Loc::getMessage('SMARTSEO_TAG_ITEM_UI_URL'),
                'name' => Loc::getMessage('SMARTSEO_TAG_ITEM_UI_URL'),
                'sort' => 'URL',
                'default' => true,
                
            ],
            [
                'id' => self::PREFIX_COLUMN . 'SECTION_NAME',
                'field' => 'SECTION',
                'title' => Loc::getMessage('SMARTSEO_TAG_ITEM_UI_SECTION'),
                'name' => Loc::getMessage('SMARTSEO_TAG_ITEM_UI_SECTION'),
                'sort' => 'FILTER_CONDITION_URL.SECTION_ID',
                'width' => 200,
                'default' => true,
            ],
            [
                'id' => 'ID',
                'field' => 'ID',
                'title' => Loc::getMessage('SMARTSEO_TAG_UI_ENTITY_ID'),
                'name' => Loc::getMessage('SMARTSEO_TAG_UI_ENTITY_ID'),
                'sort' => 'ID',
                'default' => false,
            ],
        ];
    }

}
