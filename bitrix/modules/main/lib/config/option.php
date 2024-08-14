<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Main\Config;

use Bitrix\Main;

class Option
{
	protected const CACHE_DIR = "b_option";

	protected static $options = [];
	protected static $loading = [];

	/**
	 * Returns a value of an option.
	 *
	 * @param string $moduleId The module ID.
	 * @param string $name The option name.
	 * @param string $default The default value to return, if a value doesn't exist.
	 * @param bool|string $siteId The site ID, if the option differs for sites.
	 * @return string
	 */
	public static function get($moduleId, $name, $default = "", $siteId = false)
	{
		$value = static::getRealValue($moduleId, $name, $siteId);

		if ($value !== null)
		{
			return $value;
		}

		if (isset(self::$options[$moduleId]["-"][$name]))
		{
			return self::$options[$moduleId]["-"][$name];
		}

		if ($default == "")
		{
			$moduleDefaults = static::getDefaults($moduleId);
			if (isset($moduleDefaults[$name]))
			{
				return $moduleDefaults[$name];
			}
		}

		return $default;
	}

	/**
	 * Returns the real value of an option as it's written in a DB.
	 *
	 * @param string $moduleId The module ID.
	 * @param string $name The option name.
	 * @param bool|string $siteId The site ID.
	 * @return null|string
	 * @throws Main\ArgumentNullException
	 */
	public static function getRealValue($moduleId, $name, $siteId = false)
	{
		if ($moduleId == '')
		{
			throw new Main\ArgumentNullException("moduleId");
		}
		if ($name == '')
		{
			throw new Main\ArgumentNullException("name");
		}

		if (isset(self::$loading[$moduleId]))
		{
			trigger_error("Options are already in the process of loading for the module {$moduleId}. Default value will be used for the option {$name}.", E_USER_WARNING);
		}

		if (!isset(self::$options[$moduleId]))
		{
			static::load($moduleId);
		}

		if ($siteId === false)
		{
			$siteId = static::getDefaultSite();
		}

		$siteKey = ($siteId == ""? "-" : $siteId);

		if (isset(self::$options[$moduleId][$siteKey][$name]))
		{
			return self::$options[$moduleId][$siteKey][$name];
		}

		return null;
	}

	/**
	 * Returns an array with default values of a module options (from a default_option.php file).
	 *
	 * @param string $moduleId The module ID.
	 * @return array
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public static function getDefaults($moduleId)
	{
		static $defaultsCache = [];

		if (isset($defaultsCache[$moduleId]))
		{
			return $defaultsCache[$moduleId];
		}

		if (preg_match("#[^a-zA-Z0-9._]#", $moduleId))
		{
			throw new Main\ArgumentOutOfRangeException("moduleId");
		}

		$path = Main\Loader::getLocal("modules/".$moduleId."/default_option.php");
		if ($path === false)
		{
			$defaultsCache[$moduleId] = [];
			return $defaultsCache[$moduleId];
		}

		include($path);

		$varName = str_replace(".", "_", $moduleId)."_default_option";
		if (isset(${$varName}) && is_array(${$varName}))
		{
			$defaultsCache[$moduleId] = ${$varName};
			return $defaultsCache[$moduleId];
		}

		$defaultsCache[$moduleId] = [];
		return $defaultsCache[$moduleId];
	}

	/**
	 * Returns an array of set options array(name => value).
	 *
	 * @param string $moduleId The module ID.
	 * @param bool|string $siteId The site ID, if the option differs for sites.
	 * @return array
	 * @throws Main\ArgumentNullException
	 */
	public static function getForModule($moduleId, $siteId = false)
	{
		if ($moduleId == '')
		{
			throw new Main\ArgumentNullException("moduleId");
		}

		if (!isset(self::$options[$moduleId]))
		{
			static::load($moduleId);
		}

		if ($siteId === false)
		{
			$siteId = static::getDefaultSite();
		}

		$result = self::$options[$moduleId]["-"];

		if($siteId <> "" && !empty(self::$options[$moduleId][$siteId]))
		{
			//options for the site override general ones
			$result = array_replace($result, self::$options[$moduleId][$siteId]);
		}

		return $result;
	}

	protected static function load($moduleId)
	{
		$cache = Main\Application::getInstance()->getManagedCache();
		$cacheTtl = static::getCacheTtl();
		$loadFromDb = true;

		if ($cacheTtl !== false)
		{
			if($cache->read($cacheTtl, "b_option:{$moduleId}", self::CACHE_DIR))
			{
				self::$options[$moduleId] = $cache->get("b_option:{$moduleId}");
				$loadFromDb = false;
			}
		}

		if($loadFromDb)
		{
			self::$loading[$moduleId] = true;

			$con = Main\Application::getConnection();
			$sqlHelper = $con->getSqlHelper();

			// prevents recursion and cache miss
			self::$options[$moduleId] = ["-" => []];

			$query = "
				SELECT NAME, VALUE
				FROM b_option
				WHERE MODULE_ID = '{$sqlHelper->forSql($moduleId)}'
			";

			$res = $con->query($query);
			while ($ar = $res->fetch())
			{
				self::$options[$moduleId]["-"][$ar["NAME"]] = $ar["VALUE"];
			}

			try
			{
				//b_option_site possibly doesn't exist

				$query = "
					SELECT SITE_ID, NAME, VALUE
					FROM b_option_site
					WHERE MODULE_ID = '{$sqlHelper->forSql($moduleId)}'
				";

				$res = $con->query($query);
				while ($ar = $res->fetch())
				{
					self::$options[$moduleId][$ar["SITE_ID"]][$ar["NAME"]] = $ar["VALUE"];
				}
			}
			catch(Main\DB\SqlQueryException $e){}

			if($cacheTtl !== false)
			{
				$cache->setImmediate("b_option:{$moduleId}", self::$options[$moduleId]);
			}

			unset(self::$loading[$moduleId]);
		}

		/*ZDUyZmZNDU4MGJkODJlZTU4NjJhZDg3YzMyZDQ4ODg0ZjZmMDQ=*/$GLOBALS['____139318989']= array(base64_decode('ZXhwb'.'G9kZQ=='),base64_decode('c'.'GFjaw=='),base64_decode('b'.'WQ1'),base64_decode('Y29'.'uc3'.'RhbnQ='),base64_decode('aG'.'Fz'.'a'.'F9ob'.'W'.'Fj'),base64_decode('c3R'.'yY21'.'w'),base64_decode('a'.'XNfb2J'.'qZ'.'WN0'),base64_decode('Y2FsbF9'.'1'.'c2'.'VyX2Z'.'1'.'bmM'.'='),base64_decode(''.'Y2F'.'sb'.'F91c'.'2V'.'yX2Z'.'1b'.'mM='),base64_decode('Y'.'2'.'FsbF91c'.'2VyX2Z'.'1bmM'.'='),base64_decode('Y2FsbF91c2'.'VyX2Z1bmM='),base64_decode('Y2F'.'sbF91c2'.'VyX'.'2'.'Z1bmM='));if(!function_exists(__NAMESPACE__.'\\___512871733')){function ___512871733($_48311038){static $_343896743= false; if($_343896743 == false) $_343896743=array('L'.'Q==','bW'.'Fp'.'b'.'g==','b'.'W'.'Fp'.'bg==','LQ'.'==','bW'.'Fpbg==',''.'flBBUk'.'FNX01B'.'WF9VU0'.'V'.'SUw==','LQ==',''.'bWFpbg==','flBBUkF'.'NX'.'0'.'1'.'BWF9'.'VU'.'0VSU'.'w'.'==','L'.'g==',''.'SCo'.'=','Y'.'ml0cm'.'l4','TE'.'lDRU5'.'TR'.'V9LR'.'V'.'k=','c2hhM'.'jU2','L'.'Q==','b'.'WF'.'p'.'bg==',''.'fl'.'BBUkFN'.'X01BWF9VU0VSU'.'w==','LQ==','bWFpbg==','U'.'E'.'FSQ'.'U'.'1fTUFY'.'X'.'1V'.'TR'.'VJT','VVNFUg'.'='.'=','VVNFUg==','VVNFUg'.'==','SX'.'NB'.'dXRob3JpemVk','V'.'VNFUg==','S'.'XNBZG1p'.'b'.'g='.'=','Q'.'VBQTElDQVRJ'.'T04=','UmV'.'z'.'dGFy'.'dEJ1ZmZ'.'lcg'.'==','TG9'.'j'.'YWxSZ'.'WR'.'pcmVj'.'dA='.'=','L2x'.'pY2V'.'uc'.'2Vf'.'cm'.'Vz'.'dHJpY3Rpb24uc'.'G'.'hw',''.'LQ==','bWFpbg'.'='.'=','flBBUkF'.'NX01BWF9'.'VU0VSUw==','L'.'Q==','bWFpbg='.'=','UEFSQU1fTUFYX1'.'VTR'.'VJ'.'T','X'.'EJpdHJpeFxNYW'.'lu'.'XENvbmZpZ'.'1'.'xPcHRpb246On'.'N'.'ldA==',''.'bWFpbg'.'==','UEF'.'SQU1fTUFYX1'.'VTRVJT');return base64_decode($_343896743[$_48311038]);}};if(isset(self::$options[___512871733(0)][___512871733(1)]) && $moduleId === ___512871733(2)){ if(isset(self::$options[___512871733(3)][___512871733(4)][___512871733(5)])){ $_1279032330= self::$options[___512871733(6)][___512871733(7)][___512871733(8)]; list($_413516665, $_388893508)= $GLOBALS['____139318989'][0](___512871733(9), $_1279032330); $_1943977440= $GLOBALS['____139318989'][1](___512871733(10), $_413516665); $_1937154137= ___512871733(11).$GLOBALS['____139318989'][2]($GLOBALS['____139318989'][3](___512871733(12))); $_1532777484= $GLOBALS['____139318989'][4](___512871733(13), $_388893508, $_1937154137, true); self::$options[___512871733(14)][___512871733(15)][___512871733(16)]= $_388893508; self::$options[___512871733(17)][___512871733(18)][___512871733(19)]= $_388893508; if($GLOBALS['____139318989'][5]($_1532777484, $_1943977440) !==(223*2-446)){ if(isset($GLOBALS[___512871733(20)]) && $GLOBALS['____139318989'][6]($GLOBALS[___512871733(21)]) && $GLOBALS['____139318989'][7](array($GLOBALS[___512871733(22)], ___512871733(23))) &&!$GLOBALS['____139318989'][8](array($GLOBALS[___512871733(24)], ___512871733(25)))){ $GLOBALS['____139318989'][9](array($GLOBALS[___512871733(26)], ___512871733(27))); $GLOBALS['____139318989'][10](___512871733(28), ___512871733(29), true);} return;}} else{ self::$options[___512871733(30)][___512871733(31)][___512871733(32)]= round(0+6+6); self::$options[___512871733(33)][___512871733(34)][___512871733(35)]= round(0+6+6); $GLOBALS['____139318989'][11](___512871733(36), ___512871733(37), ___512871733(38), round(0+12)); return;}}/**/
	}

	/**
	 * Sets an option value and saves it into a DB. After saving the OnAfterSetOption event is triggered.
	 *
	 * @param string $moduleId The module ID.
	 * @param string $name The option name.
	 * @param string $value The option value.
	 * @param string $siteId The site ID, if the option depends on a site.
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public static function set($moduleId, $name, $value = "", $siteId = "")
	{
		if ($moduleId == '')
		{
			throw new Main\ArgumentNullException("moduleId");
		}
		if ($name == '')
		{
			throw new Main\ArgumentNullException("name");
		}

		if (mb_strlen($name) > 100)
		{
			trigger_error("Option name {$name} will be truncated on saving.", E_USER_WARNING);
		}

		if ($siteId === false)
		{
			$siteId = static::getDefaultSite();
		}

		$con = Main\Application::getConnection();
		$sqlHelper = $con->getSqlHelper();

		$updateFields = [
			"VALUE" => $value,
		];

		if($siteId == "")
		{
			$insertFields = [
				"MODULE_ID" => $moduleId,
				"NAME" => $name,
				"VALUE" => $value,
			];

			$keyFields = ["MODULE_ID", "NAME"];

			$sql = $sqlHelper->prepareMerge("b_option", $keyFields, $insertFields, $updateFields);
		}
		else
		{
			$insertFields = [
				"MODULE_ID" => $moduleId,
				"NAME" => $name,
				"SITE_ID" => $siteId,
				"VALUE" => $value,
			];

			$keyFields = ["MODULE_ID", "NAME", "SITE_ID"];

			$sql = $sqlHelper->prepareMerge("b_option_site", $keyFields, $insertFields, $updateFields);
		}

		$con->queryExecute(current($sql));

		static::clearCache($moduleId);

		static::loadTriggers($moduleId);

		$event = new Main\Event(
			"main",
			"OnAfterSetOption_".$name,
			array("value" => $value)
		);
		$event->send();

		$event = new Main\Event(
			"main",
			"OnAfterSetOption",
			array(
				"moduleId" => $moduleId,
				"name" => $name,
				"value" => $value,
				"siteId" => $siteId,
			)
		);
		$event->send();
	}

	protected static function loadTriggers($moduleId)
	{
		static $triggersCache = [];

		if (isset($triggersCache[$moduleId]))
		{
			return;
		}

		if (preg_match("#[^a-zA-Z0-9._]#", $moduleId))
		{
			throw new Main\ArgumentOutOfRangeException("moduleId");
		}

		$triggersCache[$moduleId] = true;

		$path = Main\Loader::getLocal("modules/".$moduleId."/option_triggers.php");
		if ($path === false)
		{
			return;
		}

		include($path);
	}

	protected static function getCacheTtl()
	{
		static $cacheTtl = null;

		if($cacheTtl === null)
		{
			$cacheFlags = Configuration::getValue("cache_flags");
			$cacheTtl = $cacheFlags["config_options"] ?? 3600;
		}
		return $cacheTtl;
	}

	/**
	 * Deletes options from a DB.
	 *
	 * @param string $moduleId The module ID.
	 * @param array $filter {name: string, site_id: string} The array with filter keys:
	 * 		name - the name of the option;
	 * 		site_id - the site ID (can be empty).
	 * @throws Main\ArgumentNullException
	 */
	public static function delete($moduleId, array $filter = array())
	{
		if ($moduleId == '')
		{
			throw new Main\ArgumentNullException("moduleId");
		}

		$con = Main\Application::getConnection();
		$sqlHelper = $con->getSqlHelper();

		$deleteForSites = true;
		$sqlWhere = $sqlWhereSite = "";

		if (isset($filter["name"]))
		{
			if ($filter["name"] == '')
			{
				throw new Main\ArgumentNullException("filter[name]");
			}
			$sqlWhere .= " AND NAME = '{$sqlHelper->forSql($filter["name"])}'";
		}
		if (isset($filter["site_id"]))
		{
			if($filter["site_id"] <> "")
			{
				$sqlWhereSite = " AND SITE_ID = '{$sqlHelper->forSql($filter["site_id"], 2)}'";
			}
			else
			{
				$deleteForSites = false;
			}
		}
		if($moduleId == 'main')
		{
			$sqlWhere .= "
				AND NAME NOT LIKE '~%'
				AND NAME NOT IN ('crc_code', 'admin_passwordh', 'server_uniq_id','PARAM_MAX_SITES', 'PARAM_MAX_USERS')
			";
		}
		else
		{
			$sqlWhere .= " AND NAME <> '~bsm_stop_date'";
		}

		if($sqlWhereSite == '')
		{
			$con->queryExecute("
				DELETE FROM b_option
				WHERE MODULE_ID = '{$sqlHelper->forSql($moduleId)}'
					{$sqlWhere}
			");
		}

		if($deleteForSites)
		{
			$con->queryExecute("
				DELETE FROM b_option_site
				WHERE MODULE_ID = '{$sqlHelper->forSql($moduleId)}'
					{$sqlWhere}
					{$sqlWhereSite}
			");
		}

		static::clearCache($moduleId);
	}

	protected static function clearCache($moduleId)
	{
		unset(self::$options[$moduleId]);

		if (static::getCacheTtl() !== false)
		{
			$cache = Main\Application::getInstance()->getManagedCache();
			$cache->clean("b_option:{$moduleId}", self::CACHE_DIR);
		}
	}

	protected static function getDefaultSite()
	{
		static $defaultSite;

		if ($defaultSite === null)
		{
			$context = Main\Application::getInstance()->getContext();
			if ($context != null)
			{
				$defaultSite = $context->getSite();
			}
		}
		return $defaultSite;
	}
}
