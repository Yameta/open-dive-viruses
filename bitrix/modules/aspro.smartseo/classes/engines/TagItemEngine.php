<?php

namespace Aspro\Smartseo\Engines;

use Aspro\Smartseo;

class TagItemEngine extends \Aspro\Smartseo\Engines\Engine
{

    private $filterTagId = null;
    private $filterTag = null;

    function __construct($tagId)
    {
        $this->filterTagId = $tagId;
        $this->loadData();
    }

    public function update()
    {
        Smartseo\Models\SmartseoFilterTagItemTable::deleteAllItems($this->filterTagId);

        $generatedTagItems = $this->getGeneratedTagItems();

        if (!$generatedTagItems) {
            $this->setResult([
                'FILTER_TAG_ID' => $this->filterTagId,
                'COUNT' => 0,
            ]);

            return true;
        }

        $tagItemsCollection = new Smartseo\Entity\FilterTagItems();

        $count = 0;
        foreach ($generatedTagItems as $item) {
            $count++;

            $tagItem = new Smartseo\Entity\FilterTagItem();

            $tagItem->setFilterTagId($this->filterTagId);
            $tagItem->setName($item['NAME']);
            $tagItem->setFilterConditionUrlId($item['ID']);
            $tagItem->setActive('Y');

            $tagItemsCollection[] = $tagItem;
        }

        $resultSave = $tagItemsCollection->save(true);

        $this->setResult([
            'FILTER_TAG_ID' => $this->filterTagId,
            'COUNT' => $count,
        ]);

        if (!$resultSave->isSuccess()) {
            $this->addError($resultSave->getErrorMessages());

            return false;
        }

        $this->onAfterUpdate();
        
        return true;
    }

    protected function getGeneratedTagItems()
    {
        if ($this->hasErrors()) {
            return false;
        }

        try {
            $template = $this->filterTag['TEMPLATE'];

            $items = [];

            $result = Smartseo\Models\SmartseoFilterConditionUrlTable::getList([
                'select' => [
                    'ID',
                ],
                'filter' => [
                    '=FILTER_CONDITION_ID' => $this->filterTag['FILTER_CONDITION_ID'],
                ]
            ]);
            while ($url = $result->Fetch()) {
                $element = new \Aspro\Smartseo\Template\Entity\FilterRuleUrl($url['ID']);

                $items[] = [
                    'ID' => $url['ID'],
                    'NAME' => \Bitrix\Main\Text\HtmlFilter::encode(\Bitrix\Iblock\Template\Engine::process($element, $template))
                ];
            }

            return $items;
        } catch (Exception $e) {
            $this->addError($e->getMessage());

            return null;
        }

        return null;
    }

    protected function loadData()
    {
        $filterTag = Smartseo\Models\SmartseoFilterTagTable::getRow([
              'select' => [
                    'ACTIVE',
                    'FILTER_CONDITION_ID',
                    'TEMPLATE',
              ],
              'filter' => [
                  '=ID' => $this->filterTagId
              ]
        ]);

        if (!$filterTag) {
            $this->addError('Filter tag element not found');

            return false;
        }

        $this->filterTag = $filterTag;
    }

    protected function onAfterUpdate()
    {
        $events = \Bitrix\Main\EventManager::getInstance()->findEventHandlers(
          Smartseo\General\Smartseo::MODULE_ID, 'onAfterUpdateTagItemEngine'
        );

        $result = $this->getResult();

        foreach ($events as $event) {
			ExecuteModuleEventEx($event, [$result]);
		}
    }

}
