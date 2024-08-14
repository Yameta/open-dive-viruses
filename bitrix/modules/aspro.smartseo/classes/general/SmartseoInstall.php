<?php

namespace Aspro\Smartseo\General;

use \Bitrix\Main\Loader,
    \Bitrix\Main\Application,
    \Bitrix\Main\Entity\Base;

class SmartseoInstall
{

    const MODULE_ID = 'aspro.smartseo';

    static public function install()
    {
        self::installDB();
        self::installEvents();
    }

    static public function unInstall()
    {
        self::unInstallDB();
        self::unInstallEvents();
    }

    static public function installDB()
    {
        Loader::includeModule(self::MODULE_ID);

        if (!Application::getConnection(\Aspro\Smartseo\Models\SmartseoFilterSectionTable::getConnectionName())->isTableExists(
            Base::getInstance('\Aspro\Smartseo\Models\SmartseoFilterSectionTable')->getDBTableName()
          )
        ) {
            Base::getInstance('\Aspro\Smartseo\Models\SmartseoFilterSectionTable')->createDbTable();
        }

        if (!Application::getConnection(\Aspro\Smartseo\Models\SmartseoFilterRuleTable::getConnectionName())->isTableExists(
            Base::getInstance('\Aspro\Smartseo\Models\SmartseoFilterRuleTable')->getDBTableName()
          )
        ) {
            Base::getInstance('\Aspro\Smartseo\Models\SmartseoFilterRuleTable')->createDbTable();
        }

        if (!Application::getConnection(\Aspro\Smartseo\Models\SmartseoFilterConditionTable::getConnectionName())->isTableExists(
            Base::getInstance('\Aspro\Smartseo\Models\SmartseoFilterConditionTable')->getDBTableName()
          )
        ) {
            Base::getInstance('\Aspro\Smartseo\Models\SmartseoFilterConditionTable')->createDbTable();
        }

        if (!Application::getConnection(\Aspro\Smartseo\Models\SmartseoFilterConditionUrlTable::getConnectionName())->isTableExists(
            Base::getInstance('\Aspro\Smartseo\Models\SmartseoFilterConditionUrlTable')->getDBTableName()
          )
        ) {
            Base::getInstance('\Aspro\Smartseo\Models\SmartseoFilterConditionUrlTable')->createDbTable();
        }

        if (!Application::getConnection(\Aspro\Smartseo\Models\SmartseoFilterIblockSectionsTable::getConnectionName())->isTableExists(
            Base::getInstance('\Aspro\Smartseo\Models\SmartseoFilterIblockSectionsTable')->getDBTableName()
          )
        ) {
            Base::getInstance('\Aspro\Smartseo\Models\SmartseoFilterIblockSectionsTable')->createDbTable();
        }

        if (!Application::getConnection(\Aspro\Smartseo\Models\SmartseoFilterSearchTable::getConnectionName())->isTableExists(
            Base::getInstance('\Aspro\Smartseo\Models\SmartseoFilterSearchTable')->getDBTableName()
          )
        ) {
            Base::getInstance('\Aspro\Smartseo\Models\SmartseoFilterSearchTable')->createDbTable();
        }

        if (!Application::getConnection(\Aspro\Smartseo\Models\SmartseoFilterSitemapTable::getConnectionName())->isTableExists(
            Base::getInstance('\Aspro\Smartseo\Models\SmartseoFilterSitemapTable')->getDBTableName()
          )
        ) {
            Base::getInstance('\Aspro\Smartseo\Models\SmartseoFilterSitemapTable')->createDbTable();
        }

        if (!Application::getConnection(\Aspro\Smartseo\Models\SmartseoFilterTagTable::getConnectionName())->isTableExists(
            Base::getInstance('\Aspro\Smartseo\Models\SmartseoFilterTagTable')->getDBTableName()
          )
        ) {
            Base::getInstance('\Aspro\Smartseo\Models\SmartseoFilterTagTable')->createDbTable();
        }

        if (!Application::getConnection(\Aspro\Smartseo\Models\SmartseoFilterTagItemTable::getConnectionName())->isTableExists(
            Base::getInstance('\Aspro\Smartseo\Models\SmartseoFilterTagItemTable')->getDBTableName()
          )
        ) {
            Base::getInstance('\Aspro\Smartseo\Models\SmartseoFilterTagItemTable')->createDbTable();
        }

        if (!Application::getConnection(\Aspro\Smartseo\Models\SmartseoSeoTemplateTable::getConnectionName())->isTableExists(
            Base::getInstance('\Aspro\Smartseo\Models\SmartseoSeoTemplateTable')->getDBTableName()
          )
        ) {
            Base::getInstance('\Aspro\Smartseo\Models\SmartseoSeoTemplateTable')->createDbTable();
        }

        if (!Application::getConnection(\Aspro\Smartseo\Models\SmartseoSettingTable::getConnectionName())->isTableExists(
            Base::getInstance('\Aspro\Smartseo\Models\SmartseoSettingTable')->getDBTableName()
          )
        ) {
            Base::getInstance('\Aspro\Smartseo\Models\SmartseoSettingTable')->createDbTable();
        }

        if (!Application::getConnection(\Aspro\Smartseo\Models\SmartseoSitemapTable::getConnectionName())->isTableExists(
            Base::getInstance('\Aspro\Smartseo\Models\SmartseoSitemapTable')->getDBTableName()
          )
        ) {
            Base::getInstance('\Aspro\Smartseo\Models\SmartseoSitemapTable')->createDbTable();
        }

        if (!Application::getConnection(\Aspro\Smartseo\Models\SmartseoSeoTextTable::getConnectionName())->isTableExists(
            Base::getInstance('\Aspro\Smartseo\Models\SmartseoSeoTextTable')->getDBTableName()
          )
        ) {
            Base::getInstance('\Aspro\Smartseo\Models\SmartseoSeoTextTable')->createDbTable();
        }

        if (!Application::getConnection(\Aspro\Smartseo\Models\SmartseoSeoTextIblockSectionsTable::getConnectionName())->isTableExists(
            Base::getInstance('\Aspro\Smartseo\Models\SmartseoSeoTextIblockSectionsTable')->getDBTableName()
          )
        ) {
            Base::getInstance('\Aspro\Smartseo\Models\SmartseoSeoTextIblockSectionsTable')->createDbTable();
        }

        if (!Application::getConnection(\Aspro\Smartseo\Models\SmartseoSeoTextPropertyTable::getConnectionName())->isTableExists(
            Base::getInstance('\Aspro\Smartseo\Models\SmartseoSeoTextPropertyTable')->getDBTableName()
          )
        ) {
            Base::getInstance('\Aspro\Smartseo\Models\SmartseoSeoTextPropertyTable')->createDbTable();
        }

        if (!Application::getConnection(\Aspro\Smartseo\Models\SmartseoNoindexRuleTable::getConnectionName())->isTableExists(
            Base::getInstance('\Aspro\Smartseo\Models\SmartseoNoindexRuleTable')->getDBTableName()
          )
        ) {
            Base::getInstance('\Aspro\Smartseo\Models\SmartseoNoindexRuleTable')->createDbTable();
        }

        if (!Application::getConnection(\Aspro\Smartseo\Models\SmartseoNoindexIblockSectionsTable::getConnectionName())->isTableExists(
            Base::getInstance('\Aspro\Smartseo\Models\SmartseoNoindexIblockSectionsTable')->getDBTableName()
          )
        ) {
            Base::getInstance('\Aspro\Smartseo\Models\SmartseoNoindexIblockSectionsTable')->createDbTable();
        }

        if (!Application::getConnection(\Aspro\Smartseo\Models\SmartseoNoindexConditionTable::getConnectionName())->isTableExists(
            Base::getInstance('\Aspro\Smartseo\Models\SmartseoNoindexConditionTable')->getDBTableName()
          )
        ) {
            Base::getInstance('\Aspro\Smartseo\Models\SmartseoNoindexConditionTable')->createDbTable();
        }

        if (!Application::getConnection(\Aspro\Smartseo\Models\SmartseoNoindexUrlTable::getConnectionName())->isTableExists(
            Base::getInstance('\Aspro\Smartseo\Models\SmartseoNoindexUrlTable')->getDBTableName()
          )
        ) {
            Base::getInstance('\Aspro\Smartseo\Models\SmartseoNoindexUrlTable')->createDbTable();
        }
    }

    static public function unInstallDB()
    {
        Loader::includeModule(self::MODULE_ID);

        Application::getConnection(\Aspro\Smartseo\Models\SmartseoFilterSectionTable::getConnectionName())->
          queryExecute('drop table if exists ' . Base::getInstance('\Aspro\Smartseo\Models\SmartseoFilterSectionTable')->getDBTableName());

        Application::getConnection(\Aspro\Smartseo\Models\SmartseoFilterRuleTable::getConnectionName())->
          queryExecute('drop table if exists ' . Base::getInstance('\Aspro\Smartseo\Models\SmartseoFilterRuleTable')->getDBTableName());

        Application::getConnection(\Aspro\Smartseo\Models\SmartseoFilterConditionTable::getConnectionName())->
          queryExecute('drop table if exists ' . Base::getInstance('\Aspro\Smartseo\Models\SmartseoFilterConditionTable')->getDBTableName());

        Application::getConnection(\Aspro\Smartseo\Models\SmartseoFilterConditionUrlTable::getConnectionName())->
          queryExecute('drop table if exists ' . Base::getInstance('\Aspro\Smartseo\Models\SmartseoFilterConditionUrlTable')->getDBTableName());

        Application::getConnection(\Aspro\Smartseo\Models\SmartseoFilterIblockSectionsTable::getConnectionName())->
          queryExecute('drop table if exists ' . Base::getInstance('\Aspro\Smartseo\Models\SmartseoFilterIblockSectionsTable')->getDBTableName());

        Application::getConnection(\Aspro\Smartseo\Models\SmartseoFilterSearchTable::getConnectionName())->
          queryExecute('drop table if exists ' . Base::getInstance('\Aspro\Smartseo\Models\SmartseoFilterSearchTable')->getDBTableName());

        Application::getConnection(\Aspro\Smartseo\Models\SmartseoFilterSitemapTable::getConnectionName())->
          queryExecute('drop table if exists ' . Base::getInstance('\Aspro\Smartseo\Models\SmartseoFilterSitemapTable')->getDBTableName());

        Application::getConnection(\Aspro\Smartseo\Models\SmartseoFilterTagItemTable::getConnectionName())->
          queryExecute('drop table if exists ' . Base::getInstance('\Aspro\Smartseo\Models\SmartseoFilterTagItemTable')->getDBTableName());

        Application::getConnection(\Aspro\Smartseo\Models\SmartseoFilterTagTable::getConnectionName())->
          queryExecute('drop table if exists ' . Base::getInstance('\Aspro\Smartseo\Models\SmartseoFilterTagTable')->getDBTableName());

        Application::getConnection(\Aspro\Smartseo\Models\SmartseoSeoTemplateTable::getConnectionName())->
          queryExecute('drop table if exists ' . Base::getInstance('\Aspro\Smartseo\Models\SmartseoSeoTemplateTable')->getDBTableName());

        Application::getConnection(\Aspro\Smartseo\Models\SmartseoSettingTable::getConnectionName())->
          queryExecute('drop table if exists ' . Base::getInstance('\Aspro\Smartseo\Models\SmartseoSettingTable')->getDBTableName());

        Application::getConnection(\Aspro\Smartseo\Models\SmartseoSitemapTable::getConnectionName())->
          queryExecute('drop table if exists ' . Base::getInstance('\Aspro\Smartseo\Models\SmartseoSitemapTable')->getDBTableName());

        Application::getConnection(\Aspro\Smartseo\Models\SmartseoSeoTextTable::getConnectionName())->
          queryExecute('drop table if exists ' . Base::getInstance('\Aspro\Smartseo\Models\SmartseoSeoTextTable')->getDBTableName());

        Application::getConnection(\Aspro\Smartseo\Models\SmartseoSeoTextIblockSectionsTable::getConnectionName())->
          queryExecute('drop table if exists ' . Base::getInstance('\Aspro\Smartseo\Models\SmartseoSeoTextIblockSectionsTable')->getDBTableName());

        Application::getConnection(\Aspro\Smartseo\Models\SmartseoSeoTextPropertyTable::getConnectionName())->
          queryExecute('drop table if exists ' . Base::getInstance('\Aspro\Smartseo\Models\SmartseoSeoTextPropertyTable')->getDBTableName());

        Application::getConnection(\Aspro\Smartseo\Models\SmartseoNoindexRuleTable::getConnectionName())->
          queryExecute('drop table if exists ' . Base::getInstance('\Aspro\Smartseo\Models\SmartseoNoindexRuleTable')->getDBTableName());

        Application::getConnection(\Aspro\Smartseo\Models\SmartseoNoindexIblockSectionsTable::getConnectionName())->
          queryExecute('drop table if exists ' . Base::getInstance('\Aspro\Smartseo\Models\SmartseoNoindexIblockSectionsTable')->getDBTableName());

        Application::getConnection(\Aspro\Smartseo\Models\SmartseoNoindexConditionTable::getConnectionName())->
          queryExecute('drop table if exists ' . Base::getInstance('\Aspro\Smartseo\Models\SmartseoNoindexConditionTable')->getDBTableName());

        Application::getConnection(\Aspro\Smartseo\Models\SmartseoNoindexUrlTable::getConnectionName())->
          queryExecute('drop table if exists ' . Base::getInstance('\Aspro\Smartseo\Models\SmartseoNoindexUrlTable')->getDBTableName());
    }

    static public function installEvents()
    {
        $eventManager = \Bitrix\Main\EventManager::getInstance();

        $eventManager->registerEventHandler('main', 'OnPageStart', self::MODULE_ID,
          '\Aspro\Smartseo\General\SmartseoEventHandler', 'onPageStart');
        $eventManager->registerEventHandler('main', 'OnEpilog', self::MODULE_ID,
          '\Aspro\Smartseo\General\SmartseoEventHandler', 'onEpilog');
        $eventManager->registerEventHandler('main', 'OnEndBufferContent', self::MODULE_ID,
          '\Aspro\Smartseo\General\SmartseoEventHandler', 'onEndBufferContent');
        $eventManager->registerEventHandler('main', 'OnBeforeRestartBuffer', self::MODULE_ID,
          '\Aspro\Smartseo\General\SmartseoEventHandler', 'onBeforeRestartBuffer');
        $eventManager->registerEventHandler('main', 'OnAfterEpilog', self::MODULE_ID,
          '\Aspro\Smartseo\General\SmartseoEventHandler', 'onAfterEpilog');
        $eventManager->registerEventHandler('iblock', 'OnTemplateGetFunctionClass', self::MODULE_ID,
          '\Aspro\Smartseo\General\SmartseoEventHandler', 'onTemplateGetFunctionClass');
        $eventManager->registerEventHandler('search', 'OnReindex', self::MODULE_ID,
          '\Aspro\Smartseo\General\SmartseoEventHandler', 'onSearchReindex');
        $eventManager->registerEventHandler('search', 'OnAfterIndexAdd', self::MODULE_ID,
          '\Aspro\Smartseo\General\SmartseoEventHandler', 'onSearchAfterIndexAdd');
        $eventManager->registerEventHandler('search', 'BeforeIndex', self::MODULE_ID,
          '\Aspro\Smartseo\General\SmartseoEventHandler', 'onSearchBeforeIndex');
        $eventManager->registerEventHandler('seo', '\\Bitrix\\Seo\\Sitemap::OnAfterUpdate', self::MODULE_ID,
          '\Aspro\Smartseo\General\SmartseoEventHandler', 'onSitemapAfterUpdate');
        $eventManager->registerEventHandler(self::MODULE_ID, 'onAfterUpdateUrlEngine', self::MODULE_ID,
          '\Aspro\Smartseo\General\SmartseoEventHandler', 'onAfterUpdateUrlEngine');
    }

    static public function unInstallEvents()
    {
        $eventManager = \Bitrix\Main\EventManager::getInstance();

        $eventManager->unRegisterEventHandler('main', 'OnPageStart', self::MODULE_ID,
          '\Aspro\Smartseo\General\SmartseoEventHandler', 'onPageStart');
        $eventManager->unRegisterEventHandler('main', 'OnEpilog', self::MODULE_ID,
          '\Aspro\Smartseo\General\SmartseoEventHandler', 'onEpilog');
        $eventManager->unRegisterEventHandler('main', 'OnEndBufferContent', self::MODULE_ID,
          '\Aspro\Smartseo\General\SmartseoEventHandler', 'onEndBufferContent');
        $eventManager->unRegisterEventHandler('main', 'OnBeforeRestartBuffer', self::MODULE_ID,
          '\Aspro\Smartseo\General\SmartseoEventHandler', 'onBeforeRestartBuffer');
        $eventManager->unRegisterEventHandler('main', 'OnAfterEpilog', self::MODULE_ID,
          '\Aspro\Smartseo\General\SmartseoEventHandler', 'onAfterEpilog');
        $eventManager->unRegisterEventHandler('iblock', 'OnTemplateGetFunctionClass', self::MODULE_ID,
          '\Aspro\Smartseo\General\SmartseoEventHandler', 'onTemplateGetFunctionClass');
        $eventManager->unRegisterEventHandler('search', 'OnReindex', self::MODULE_ID,
          '\Aspro\Smartseo\General\SmartseoEventHandler', 'onSearchReindex');
        $eventManager->unRegisterEventHandler('search', 'OnAfterIndexAdd', self::MODULE_ID,
          '\Aspro\Smartseo\General\SmartseoEventHandler', 'onSearchAfterIndexAdd');
        $eventManager->unRegisterEventHandler('search', 'BeforeIndex', self::MODULE_ID,
          '\Aspro\Smartseo\General\SmartseoEventHandler', 'onSearchBeforeIndex');
        $eventManager->unRegisterEventHandler('seo', '\\Bitrix\\Seo\\Sitemap::OnAfterUpdate', self::MODULE_ID,
          '\Aspro\Smartseo\General\SmartseoEventHandler', 'onSitemapAfterUpdate');
        $eventManager->unRegisterEventHandler(self::MODULE_ID, 'onAfterUpdateUrlEngine', self::MODULE_ID,
          '\Aspro\Smartseo\General\SmartseoEventHandler', 'onAfterUpdateUrlEngine');
    }

}
