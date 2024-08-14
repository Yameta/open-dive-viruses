<?php

namespace Aspro\Max\Controller;

use \Bitrix\Main\Error;

use CMax as Solution,
    \Aspro\Max\ItemAction\Favorite;

class ItemAction extends \Bitrix\Main\Engine\Controller
{
    public function configureActions()
    {
        return [
            'favorite' => [
				'prefilters' => [],
			],
        ];
    }

    /**
     * Place/remove emelent in favorites
     * @param array $fields transfer params
     * @return array|null
     */
    public function favoriteAction(array $fields): ?array
    {
        if (!check_bitrix_sessid()) {
            $this->addError(new Error('Wrong session id'));
        }
        
        $arItems = $this->getItems($fields);

        if ($this->getErrors()) {
            return null;
        }

        foreach ($arItems as $id) {
            if ($fields['state']) {
                Favorite::addItem($id);
            } else {
                Favorite::removeItem($id);
            }
        }

        $arResult['items'] = Favorite::getItems();
        $arResult['count'] = Favorite::getCount();
        $arResult['title'] = Favorite::getTitle();

        Solution::clearBasketCounters(); // unset session for basket fly

        return $arResult;
    }

    private function getItems(array $fields): array
    {
        $arItems = [];
        if ($fields['type'] === 'multiple') {
            foreach ((array)$fields['items'] as $arItem) {
                $arItems[] = intval(is_array($arItem) ? $arItem['id'] : $arItem);
            }
        } else {
            if ($id = $fields['item']) {
                $arItems = [$id];
            }
        }

        if (!$arItems) {
            $this->addError(new Error('Invalid items'));
        }

        return $arItems;
    }
}
