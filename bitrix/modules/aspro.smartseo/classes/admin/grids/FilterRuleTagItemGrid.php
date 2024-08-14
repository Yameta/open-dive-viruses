<?php

namespace Aspro\Smartseo\Admin\Grids;

use Aspro\Smartseo,
    Aspro\Smartseo\Admin\UI;

class FilterRuleTagItemGrid extends InnerGrid
{

    private $filterTagId = null;

    function __construct($filterTagId)
    {
        $this->filterTagId = $filterTagId;
        $this->ui = new UI\FilterRuleTagItemAdminUI($filterTagId);
        $this->filterFields = $this->ui->getFilterFields();
    }

    public function getFilter()
    {
        return array_filter(
          array_merge($this->filter, [
            'FILTER_TAG_ID' => $this->filterTagId,
          ])
        );
    }

    protected function getRsList($navigation)
    {
        if (!$this->getFilter()) {
            return [];
        }

        $rsResultList = Smartseo\Models\SmartseoFilterTagItemTable::getList([
                'select' => [
                    '*',
                    'URL' => 'FILTER_CONDITION_URL.NEW_URL',
                    'SECTION_ID' => 'FILTER_CONDITION_URL.SECTION_ID',
                    'SECTION_NAME' => 'FILTER_CONDITION_URL.SECTION.NAME',
                ],
                'filter' => $this->getFilter(),
                'count_total' => true,
                'offset' => $navigation->getOffset(),
                'limit' => $navigation->getLimit(),
                'order' => $this->getSort() ?: [
                'ID' => 'ASC',
            ]
        ]);

        return $rsResultList;
    }

    protected function modifiedResultRows($rows)
    {
        return $rows;
    }

    protected function getCurrentPageSessionName()
    {
        return $this->getGridId() . '_current_page_' . $this->filterTagId;
    }

}
