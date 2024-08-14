<?php

namespace Aspro\Smartseo\Admin\Controllers;

use
    Aspro\Smartseo,
    Aspro\Smartseo\Admin\App\Controller,
    Aspro\Smartseo\Admin\Grids as GridSmartseo,
    Aspro\Smartseo\Admin\Traits,
    Aspro\Smartseo\Admin\UI,
    Aspro\Smartseo\Entity,
    Bitrix\Main\Web\Json,
    Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class FilterTagItemController extends Controller
{
    use Traits\BitrixCoreEntity;

    const ALIAS = 'FILTER_TAG_ITEM';

    public function getViewFolderName()
    {
        return 'filter_tag_item';
    }

    public function actionDetail($id = null, $filter_tag_id = null, $active_tab = null)
    {
        if(!$filterTag = Smartseo\Models\SmartseoFilterTagTable::getRowById($filter_tag_id)) {
            throw new \Exception('Parent element not found');
        }

        $data = null;
        if (
            $id &&
            (
                !$data = Smartseo\Models\SmartseoFilterTagItemTable::getRow([
                    'select' => [
                        '*',
                        'URL' => 'FILTER_CONDITION_URL.NEW_URL',
                        'SECTION_ID' => 'FILTER_CONDITION_URL.SECTION_ID',
                        'SECTION_NAME' => 'FILTER_CONDITION_URL.SECTION.NAME',
                    ],
                    'filter' => [
                        '=ID' => $id,
                    ]
                ])
            )
        ) {
            throw new \Exception('Tag not found');
        }

        $this->render('detail', [
            'activeTab' => $active_tab,
            'alias' => self::ALIAS,
            'data' => $data,
            'filterTagId' => $filterTag['ID'],
            // 'listTags' => $this->getTagsByFilterCondition($data['FILTER_CONDITION_ID'], $data['TEMPLATE']),
            'gridId' => GridSmartseo\FilterRuleTagItemGrid::getInstance($filter_tag_id)->getGridId(),
        ]);
    }

    public function actionUpdate()
    {
        if (!check_bitrix_sessid()) {
            echo Json::encode([
                'result' => false,
                'message' => 'Bitrix session not found',
            ]);

            return;
        }

        if (!$this->request->get(self::ALIAS) || !is_array($this->request->get(self::ALIAS))) {
            echo Json::encode([
                'result' => false,
                'message' => 'No update data found'
            ]);
            return;
        }

        $data = $this->request->get(self::ALIAS);

        if (!$data['ID']) {
            echo Json::encode([
                'result' => false,
                'message' => 'No tag item data ID found'
            ]);
            return;
        }

        global $DB;

        try {
            $DB->StartTransaction();

            $id = $this->update($data['ID'], $data);

            if ($this->hasErrors()) {
                throw new \Exception(implode('<br>', $this->getErrors()));
            }

            $DB->Commit();
        } catch (Exception $e) {
            $DB->Rollback();
        }

        $entity = \Aspro\Smartseo\Entity\FilterTagItem::wakeUp($id);
        $entity->fill(['NAME', 'FILTER_TAG_ID', 'FILTER_TAG', 'FILTER_CONDITION_URL_ID', 'FILTER_CONDITION_URL']);

        echo Json::encode([
            'result' => true,
            'fields' => [
                'ID' => $id,
                'NAME' => $entity->getName(),
                'FILTER_TAG_ID' => $entity->getFilterTagId(),
                'FILTER_CONDITION_URL_ID' => $entity->getFilterConditionUrlId(),
            ],
            'action' => $this->request->get('action'),
            'message' => 'Tag item saved successfully',
        ]);
    }

    public function actionDeactivate()
    {
        if (!$this->validateParamsForMenuAction()) {
            echo Json::encode([
                'result' => false,
                'message' => $this->getErrors(),
            ]);
        }

        $id = $this->request->get('id');

        if (!$this->deactivate($id)) {
            throw new \Exception(implode('<br>', $this->getErrors()));
        }

        echo Json::encode([
            'result' => true,
            'message' => 'Tag item deactivated successfully'
        ]);
    }

    public function actionActivate()
    {
        if (!$this->validateParamsForMenuAction()) {
            echo Json::encode([
                'result' => false,
                'message' => $this->getErrors(),
            ]);
        }

        $id = $this->request->get('id');

        if (!$this->activate($id)) {
            throw new \Exception(implode('<br>', $this->getErrors()));
        }

        echo Json::encode([
            'result' => true,
            'message' => 'Tag item activated successfully'
        ]);
    }

    public function actionDelete()
    {
        if (!$this->validateParamsForMenuAction()) {
            echo Json::encode([
                'result' => false,
                'message' => $this->getErrors(),
            ]);
        }

        $id = $this->request->get('id');

        if (!$this->delete($id)) {
            throw new \Exception(implode('<br>', $this->getErrors()));
        }

        echo Json::encode([
            'result' => true,
            'message' => 'Tag item deleted successfully'
        ]);
    }

    protected function validateParamsForMenuAction()
    {
        if (!$this->request->isAjaxRequest()) {
            throw new \Exception(Loc::getMessage('SMARTSEO_INDEX__ERROR__NOT_AJAX'));
        }

        if (!check_bitrix_sessid()) {
            $this->addError('Bitrix session not found');

            return false;
        }

        if (!$this->request->get('id')) {
            $this->addError('ID param expected');

            return false;
        }

        return true;
    }

    private function deactivate($id)
    {
        if (!Smartseo\Models\SmartseoFilterTagItemTable::getByPrimary($id)) {
            $this->addError('Tag not found');

            return false;
        }

        $entity = \Aspro\Smartseo\Entity\FilterTagItem::wakeUp($id);
        $entity->setActive('N');

        $result = $entity->save();

        if (!$result->isSuccess()) {
            $this->setErrors($result->getErrorMessages());

            return false;
        }

        return $result->getId();
    }

    private function activate($id)
    {
        if (!Smartseo\Models\SmartseoFilterTagItemTable::getByPrimary($id)) {
            $this->addError('Tag not found');

            return false;
        }

        $entity = \Aspro\Smartseo\Entity\FilterTagItem::wakeUp($id);
        $entity->setActive('Y');

        $result = $entity->save();

        if (!$result->isSuccess()) {
            $this->setErrors($result->getErrorMessages());

            return false;
        }

        return $result->getId();
    }

    private function delete($id)
    {
        if (!Smartseo\Models\SmartseoFilterTagItemTable::getByPrimary($id)) {
            $this->addError('Tag not found');

            return false;
        }

        $result = Smartseo\Models\SmartseoFilterTagItemTable::delete($id);

        if (!$result->isSuccess()) {
            $this->setErrors($result->getErrorMessages());
        }

        return true;
    }

    private function update($id, array $data)
    {
        if (!Smartseo\Models\SmartseoFilterTagItemTable::getByPrimary($id)) {
            throw new \Exception('Tag not found');
        }

        $entity = \Aspro\Smartseo\Entity\FilterTagItem::wakeUp($id);
        $entity->setActive($data['ACTIVE']);
        $entity->setSort($data['SORT']);
        $entity->setName($data['NAME']);

        $result = $entity->save();

        if (!$result->isSuccess()) {
            throw new \Exception(implode('<br>', $result->getErrorMessages()));
        }

        $id = $result->getId();

        return $id;
    }

    private function add(array $data)
    {
        $entity = new \Aspro\Smartseo\Entity\FilterTagItem();
        $entity->setActive($data['ACTIVE']);
        $entity->setSort($data['SORT']);
        $entity->setName($data['NAME']);

        $result = $entity->save();

        if (!$result->isSuccess()) {
            throw new \Exception(implode('<br>', $result->getErrorMessages()));
        }

        $id = $result->getId();

        return $id;
    }
}
