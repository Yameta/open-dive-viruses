<?php

namespace Aspro\Smartseo\Models;

use
    Aspro\Smartseo\Admin\Settings\SettingSmartseo,
    Aspro\Smartseo\Entity as SmartseoEntity,
    Bitrix\Main,
    Bitrix\Main\Localization\Loc,
    Bitrix\Main\ORM\Fields\Relations\Reference,
    Bitrix\Main\ORM\Query\Join;

Loc::loadMessages(__FILE__);

/**
 * Class SmartseoFilterConditionUrlItemTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> FILTER_TAG_ID int mandatory
 * <li> FILTER_CONDITION_URL_ID int mandatory
 * <li> ACTIVE bool optional default 'Y'
 * <li> SORT int optional default 500
 * <li> NAME string(255) optional
 * </ul>
 *
 * @package Bitrix\Aspro
 * */
class SmartseoFilterTagItemTable extends Main\Entity\DataManager
{
    const DEFAULT_CACHE_TTL = 0;

    public static function getObjectClass()
    {
        return SmartseoEntity\FilterTagItem::class;
    }

    public static function getCollectionClass()
    {
        return SmartseoEntity\FilterTagItems::class;
    }

    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_aspro_smartseo_tag_item';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        return array(
            'ID' => array(
                'data_type' => 'integer',
                'primary' => true,
                'autocomplete' => true,
                'title' => Loc::getMessage('SMARTSEO_FILTER_TAG_ITEM_ENTITY_ID_FIELD'),
            ),
            'FILTER_TAG_ID' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => Loc::getMessage('SMARTSEO_FILTER_TAG_ITEM_ENTITY_FILTER_TAG_ID_FIELD'),
            ),
            'FILTER_CONDITION_URL_ID' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => Loc::getMessage('SMARTSEO_FILTER_TAG_ITEM_ENTITY_FILTER_CONDITION_URL_ID_FIELD'),
            ),
            'ACTIVE' => array(
                'data_type' => 'boolean',
                'values' => array('N', 'Y'),
                'title' => Loc::getMessage('SMARTSEO_FILTER_TAG_ITEM_ENTITY_ACTIVE_FIELD'),
                'default_value' => 'Y',
            ),
            'SORT' => array(
                'data_type' => 'integer',
                'title' => Loc::getMessage('SMARTSEO_FILTER_TAG_ITEM_ENTITY_SORT_FIELD'),
                'default_value' => 500,
            ),
            'NAME' => array(
                'data_type' => 'string',
                'validation' => array(__CLASS__, 'validateName'),
                'title' => Loc::getMessage('SMARTSEO_FILTER_TAG_ITEM_ENTITY_NAME_FIELD'),
            ),
            
            (new Reference('FILTER_TAG', SmartseoFilterTagTable::class, Join::on('this.FILTER_TAG_ID', 'ref.ID')))
                ->configureJoinType('left'),
            (new Reference('FILTER_CONDITION_URL', SmartseoFilterConditionUrlTable::class, Join::on('this.FILTER_CONDITION_URL_ID', 'ref.ID')))
                ->configureJoinType('left'),
        );
    }

    /**
     * Returns validators for NAME field.
     *
     * @return array
     */
    public static function validateName()
    {
        return array(
            new Main\Entity\Validator\Length(null, 255),
        );
    }

    public static function getCacheTtl()
    {
        $ttl = SettingSmartseo::getInstance()->getCacheTable();

        return $ttl ?: self::DEFAULT_CACHE_TTL;
    }

    public static function deleteAllItems($tagId)
    {
        $sql = 'DELETE FROM ' . self::getTableName()
          . ' WHERE FILTER_TAG_ID = ' . (int) $tagId;

        $connection = \Bitrix\Main\Application::getConnection();

        $connection->queryExecute($sql);
    }
}
