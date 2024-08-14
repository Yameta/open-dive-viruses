<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2023 Bitrix
 */

use Bitrix\Main;
use Bitrix\Main\Session\Legacy\HealerEarlySessionStart;

require_once(__DIR__."/start.php");

$application = Main\HttpApplication::getInstance();
$application->initializeExtendedKernel([
	"get" => $_GET,
	"post" => $_POST,
	"files" => $_FILES,
	"cookie" => $_COOKIE,
	"server" => $_SERVER,
	"env" => $_ENV
]);

if (class_exists('\Dev\Main\Migrator\ModuleUpdater'))
{
	\Dev\Main\Migrator\ModuleUpdater::checkUpdates('main', __DIR__);
}

if (defined('SITE_ID'))
{
	define('LANG', SITE_ID);
}

$context = $application->getContext();
$context->initializeCulture(defined('LANG') ? LANG : null, defined('LANGUAGE_ID') ? LANGUAGE_ID : null);

// needs to be after culture initialization
$application->start();

// constants for compatibility
$culture = $context->getCulture();
define('SITE_CHARSET', $culture->getCharset());
define('FORMAT_DATE', $culture->getFormatDate());
define('FORMAT_DATETIME', $culture->getFormatDatetime());
define('LANG_CHARSET', SITE_CHARSET);

$site = $context->getSiteObject();
if (!defined('LANG'))
{
	define('LANG', ($site ? $site->getLid() : $context->getLanguage()));
}
define('SITE_DIR', ($site ? $site->getDir() : ''));
if (!defined('SITE_SERVER_NAME'))
{
	define('SITE_SERVER_NAME', ($site ? $site->getServerName() : ''));
}
define('LANG_DIR', SITE_DIR);

if (!defined('LANGUAGE_ID'))
{
	define('LANGUAGE_ID', $context->getLanguage());
}
define('LANG_ADMIN_LID', LANGUAGE_ID);

if (!defined('SITE_ID'))
{
	define('SITE_ID', LANG);
}

/** @global $lang */
$lang = $context->getLanguage();

//define global application object
$GLOBALS["APPLICATION"] = new CMain;

if (!defined("POST_FORM_ACTION_URI"))
{
	define("POST_FORM_ACTION_URI", htmlspecialcharsbx(GetRequestUri()));
}

$GLOBALS["MESS"] = [];
$GLOBALS["ALL_LANG_FILES"] = [];
IncludeModuleLangFile(__DIR__."/tools.php");
IncludeModuleLangFile(__FILE__);

error_reporting(COption::GetOptionInt("main", "error_reporting", E_COMPILE_ERROR | E_ERROR | E_CORE_ERROR | E_PARSE) & ~E_STRICT & ~E_DEPRECATED & ~E_WARNING & ~E_NOTICE);

if (!defined("BX_COMP_MANAGED_CACHE") && COption::GetOptionString("main", "component_managed_cache_on", "Y") <> "N")
{
	define("BX_COMP_MANAGED_CACHE", true);
}

// global functions
require_once(__DIR__."/filter_tools.php");

/*ZDUyZmZMzYwNWRmOWZlMTE2NjM1YjU1MTBhZmRjODA0MmQzOTU=*/$GLOBALS['_____2066307101']= array(base64_decode('R2V0T'.'W9'.'kdWxlRXZ'.'l'.'bnRz'),base64_decode('RXhlY'.'3V0ZU1'.'vZ'.'HVsZUV2ZW'.'50RXg'.'='),base64_decode('V3Jp'.'dGVG'.'aW5hb'.'E'.'1l'.'c3'.'NhZ'.'2U='));$GLOBALS['____1225362135']= array(base64_decode('ZG'.'VmaW'.'5l'),base64_decode('Ym'.'FzZTY0X'.'2R'.'l'.'Y29'.'kZQ=='),base64_decode('d'.'W5zZXJ'.'p'.'YWxpem'.'U'.'='),base64_decode('aXNfY'.'XJy'.'YXk'.'='),base64_decode('aW5fYXJyYXk='),base64_decode('c2VyaWFsaXpl'),base64_decode('Ym'.'F'.'zZTY0X2VuY2'.'9kZQ'.'='.'='),base64_decode('bW'.'t0aW1l'),base64_decode('Z'.'G'.'F0'.'ZQ='.'='),base64_decode(''.'ZGF'.'0ZQ='.'='),base64_decode('c3Ry'.'bG'.'Vu'),base64_decode('bWt'.'0aW1l'),base64_decode(''.'ZGF0ZQ'.'='.'='),base64_decode('ZGF'.'0ZQ'.'=='),base64_decode(''.'bWV'.'0'.'aG9kX2V4aXN0cw='.'='),base64_decode('Y2FsbF91c2'.'VyX2'.'Z'.'1'.'bm'.'NfYXJ'.'yY'.'X'.'k='),base64_decode('c3Ryb'.'GVu'),base64_decode('c2'.'VyaWF'.'saXpl'),base64_decode('YmF'.'z'.'ZTY0X2VuY2'.'9k'.'ZQ=='),base64_decode('c3'.'RybGVu'),base64_decode('aXNfYXJyYXk='),base64_decode('c'.'2VyaWFsa'.'X'.'p'.'l'),base64_decode('Y'.'mFz'.'ZT'.'Y0X2Vu'.'Y'.'29kZQ=='),base64_decode('c2Vya'.'W'.'F'.'saXpl'),base64_decode('Y'.'mF'.'zZ'.'TY'.'0X2VuY29kZQ=='),base64_decode('a'.'XNfYXJyY'.'Xk='),base64_decode('aXNfYXJyYX'.'k='),base64_decode('a'.'W5fYXJyY'.'Xk'.'='),base64_decode('aW'.'5fYXJyYXk='),base64_decode('b'.'W'.'t0aW1l'),base64_decode('ZG'.'F0ZQ=='),base64_decode('ZGF0ZQ=='),base64_decode('ZGF0ZQ'.'=='),base64_decode('bWt0aW1l'),base64_decode('ZGF'.'0ZQ=='),base64_decode('ZGF0ZQ=='),base64_decode('aW5fY'.'XJyY'.'Xk='),base64_decode(''.'c2VyaWFsaX'.'pl'),base64_decode('YmFzZ'.'TY0X'.'2VuY29kZQ=='),base64_decode('aW50d'.'mFs'),base64_decode('dGlt'.'ZQ'.'=='),base64_decode('ZmlsZV'.'9'.'leGl'.'z'.'dHM='),base64_decode('c3R'.'y'.'X3JlcG'.'xh'.'Y2U='),base64_decode(''.'Y'.'2'.'xhc'.'3NfZXhp'.'c3Rz'),base64_decode('ZGVmaW5'.'l'),base64_decode('c3Ryc'.'mV2'),base64_decode(''.'c3'.'Ry'.'dG91cHB'.'lcg=='),base64_decode('c3ByaW50Z'.'g=='),base64_decode('c3ByaW50Zg'.'='.'='),base64_decode('c3Vic3'.'Ry'),base64_decode('c'.'3Ry'.'cmV2'),base64_decode('Ym'.'FzZ'.'T'.'Y0X2RlY29k'.'ZQ=='),base64_decode('c'.'3Vic3Ry'),base64_decode(''.'c3Ry'.'bGVu'),base64_decode('c'.'3RybGVu'),base64_decode('Y2hy'),base64_decode(''.'b3Jk'),base64_decode('b'.'3Jk'),base64_decode('bWt0aW1l'),base64_decode('aW50d'.'mFs'),base64_decode('aW'.'50'.'dmFs'),base64_decode('aW5'.'0dmFs'),base64_decode('a'.'3NvcnQ='),base64_decode('c3Vic3Ry'),base64_decode('aW1w'.'bG9kZQ=='),base64_decode('ZG'.'VmaW5lZA='.'='),base64_decode('YmFzZT'.'Y0X'.'2RlY29'.'kZQ=='),base64_decode(''.'Y'.'2'.'9uc'.'3Rh'.'bnQ='),base64_decode('c3R'.'ycmV2'),base64_decode('c'.'3Bya'.'W50Zg=='),base64_decode('c3Ryb'.'GV'.'u'),base64_decode('c3RybG'.'Vu'),base64_decode(''.'Y2hy'),base64_decode('b3J'.'k'),base64_decode('b3Jk'),base64_decode('bWt0aW'.'1l'),base64_decode('aW50dmFs'),base64_decode('aW50d'.'mFs'),base64_decode(''.'aW50dmFs'),base64_decode('c3Vic'.'3Ry'),base64_decode('c3Vic3R'.'y'),base64_decode('ZGVm'.'a'.'W'.'5l'.'ZA'.'=='),base64_decode(''.'c3Ry'.'cmV2'),base64_decode('c'.'3RydG91c'.'HBlcg=='),base64_decode('ZmlsZV'.'9l'.'eGlz'.'dHM'.'='),base64_decode('aW50dmF'.'s'),base64_decode('dG'.'lt'.'ZQ=='),base64_decode('bWt0aW1l'),base64_decode('bWt0aW1l'),base64_decode(''.'ZG'.'F'.'0Z'.'Q'.'=='),base64_decode(''.'ZGF0ZQ=='),base64_decode(''.'Z'.'GVmaW5l'),base64_decode(''.'ZGVmaW5l'));if(!function_exists(__NAMESPACE__.'\\___1780727293')){function ___1780727293($_901438284){static $_236071664= false; if($_236071664 == false) $_236071664=array('SU5U'.'UkFOR'.'VRfRURJVE'.'l'.'PTg'.'='.'=','WQ==',''.'bWFpbg'.'==','fmNwZl9t'.'YXBfd'.'mFsd'.'WU=','','','YWxsb3dlZF'.'9jb'.'G'.'F'.'zc2V'.'z',''.'ZQ==','Zg='.'=','ZQ==',''.'Rg==','W'.'A==',''.'Zg==',''.'bW'.'Fp'.'bg'.'==','f'.'mNwZl9'.'tYXBfdmF'.'s'.'dWU=','UG9ydGFs',''.'Rg='.'=','ZQ==','ZQ==','WA==',''.'R'.'g==','RA==','RA'.'==','b'.'Q='.'=','ZA='.'=','W'.'Q==',''.'Z'.'g'.'==','Zg==','Z'.'g==','Zg==','UG9ydGFs','R'.'g='.'=','ZQ'.'==',''.'ZQ==','WA==','Rg='.'=','RA==','R'.'A==','bQ==','ZA==','W'.'Q==',''.'bWFpbg==','T24=','U'.'2'.'V0dGluZ3'.'N'.'Da'.'GFuZ2U=','Zg='.'=',''.'Zg==','Z'.'g'.'='.'=','Zg'.'==','bWF'.'p'.'bg'.'==','fm'.'NwZl9tYXBf'.'dmFsdWU=','ZQ==','ZQ'.'==','RA'.'='.'=','ZQ==','ZQ==','Zg==','Zg='.'=','Zg==','ZQ==','bW'.'Fp'.'bg==','fm'.'Nw'.'Zl9'.'tY'.'XB'.'fdmFs'.'d'.'WU=','ZQ'.'==','Z'.'g==','Zg==','Zg==','Zg==','bW'.'Fpbg='.'=','fmNwZl9tY'.'XBfdmFsdWU=',''.'ZQ==','Zg==','UG9ydGFs','UG9y'.'dGFs','ZQ==','ZQ==','U'.'G9ydGFs','R'.'g='.'=','WA==','Rg==','RA='.'=','ZQ==','Z'.'Q==',''.'RA==','bQ==',''.'ZA==',''.'W'.'Q==','Z'.'Q==',''.'WA==','ZQ==','Rg==','ZQ'.'='.'=','RA'.'==','Zg==',''.'ZQ==','RA==','ZQ==',''.'bQ='.'=','ZA==','W'.'Q==','Z'.'g='.'=','Zg==',''.'Zg==',''.'Zg='.'=','Zg==','Z'.'g='.'=','Zg='.'=','Zg='.'=','bWFp'.'bg'.'==','f'.'m'.'NwZl9'.'tYXBfdmFsdWU=',''.'ZQ='.'=','ZQ='.'=',''.'UG'.'9ydGFs','Rg==',''.'WA='.'=','VFl'.'QRQ==',''.'REFURQ'.'==',''.'RkVBVFVSR'.'VM=','RV'.'hQSVJ'.'F'.'R'.'A==','V'.'FlQ'.'R'.'Q==','R'.'A==','VFJZX0RBWVNfQ09VTlQ=','RE'.'FURQ'.'==',''.'VFJZX0'.'R'.'BWVNfQ09'.'V'.'T'.'l'.'Q=','RV'.'hQ'.'S'.'VJF'.'R'.'A==','RkVBVFVSRV'.'M'.'=','Zg==','Zg==','RE9'.'DVU1FTl'.'R'.'fUk9PV'.'A'.'='.'=','L'.'2JpdHJpeC9tb2R1b'.'GVzLw'.'==','L2luc3Rhb'.'G'.'w'.'va'.'W5'.'k'.'ZXg'.'ucGhw','Lg==',''.'X'.'w==','c2VhcmNo','Tg==','','','QUNUSVZF',''.'WQ==','c'.'2'.'9'.'jaWFsbmV0d29yaw==',''.'YWxsb3dfZnJpZWxkcw='.'=','WQ==',''.'SUQ'.'=','c29ja'.'W'.'FsbmV0'.'d29'.'yaw==','YWxs'.'b'.'3d'.'fZn'.'Jp'.'ZWxkcw='.'=','SUQ=','c29'.'ja'.'WFsbmV0d29yaw'.'==','YWxsb'.'3'.'d'.'fZnJp'.'ZW'.'xk'.'c'.'w='.'=','Tg==','','','QUNUS'.'V'.'ZF','WQ==','c29'.'ja'.'WFsbmV'.'0d29ya'.'w==','YWxsb3dfb'.'Wlj'.'cm9'.'ib'.'G9'.'nX'.'3'.'Vz'.'ZXI=',''.'WQ==',''.'SU'.'Q=','c'.'29ja'.'W'.'FsbmV0'.'d29y'.'aw==','Y'.'Wxsb3'.'d'.'fbWl'.'jcm'.'9ibG9'.'nX3V'.'zZXI=','SUQ=','c29'.'j'.'a'.'W'.'FsbmV0d29ya'.'w'.'==','YW'.'xsb3'.'d'.'fbW'.'l'.'jcm'.'9i'.'bG9nX3V'.'zZXI=','c2'.'9jaWFsb'.'mV0d29yaw==',''.'YWxsb3dfbWljcm'.'9ibG9'.'nX2'.'dyb3'.'Vw','WQ==',''.'SUQ=','c29j'.'aWF'.'sb'.'mV0d'.'29ya'.'w==','YWxsb'.'3df'.'bWljcm9ibG9nX2dyb3Vw',''.'S'.'UQ'.'=','c29ja'.'W'.'Fsb'.'mV0'.'d29yaw'.'==','Y'.'Wxsb'.'3d'.'fbW'.'ljcm9'.'ib'.'G'.'9nX2'.'d'.'y'.'b3V'.'w','Tg='.'=','','','QUNUSVZF','WQ==','c29'.'jaWFs'.'bmV0d29yaw'.'==','YWxsb3dfZmlsZXNfdXNlc'.'g==','W'.'Q==','SUQ=',''.'c29j'.'aWFsb'.'m'.'V0d29'.'y'.'aw==','YWxsb3d'.'fZmlsZXNfdXNlcg==','SUQ=','c29j'.'aWF'.'sbmV'.'0d'.'29'.'yaw==','YWxsb3df'.'Z'.'m'.'lsZXNf'.'dXNlcg==',''.'Tg==','','',''.'QUN'.'U'.'SVZF','WQ'.'==','c29j'.'aWF'.'sbmV0d29yaw==','YW'.'xsb3df'.'Y'.'m'.'xvZ191c'.'2Vy','WQ==','SUQ=','c'.'29j'.'aWFsbmV0d29'.'y'.'aw==','YW'.'xsb3dfYmxvZ1'.'91c2V'.'y','SUQ=','c2'.'9jaWF'.'sbmV0d29yaw==','YWxsb3d'.'fYmxvZ191c2V'.'y','Tg='.'=','','',''.'QUNUSVZ'.'F','WQ==','c'.'29jaWFsb'.'mV0'.'d'.'29yaw='.'=','YW'.'xsb'.'3dfcG'.'h'.'vdG'.'9fdXNlcg==','WQ==','SUQ=',''.'c29jaWFs'.'bmV0'.'d29yaw==',''.'YWxs'.'b3dfcGhvdG9f'.'dXNlc'.'g==','SUQ=','c29j'.'aW'.'F'.'sbmV0d29y'.'aw==','YW'.'xs'.'b3dfcGhv'.'d'.'G9'.'f'.'d'.'XN'.'lc'.'g='.'=',''.'Tg='.'=','','',''.'QUNUSVZF','WQ==','c2'.'9'.'ja'.'WFs'.'bmV0'.'d29yaw'.'==','YW'.'xsb3df'.'Zm9ydW'.'1f'.'dX'.'Nlcg==','WQ==',''.'S'.'UQ=','c29j'.'aWFsbmV0d29yaw==',''.'YWxsb3dfZm9ydW1fdXNlcg==','SUQ=',''.'c29j'.'aWFsbmV0d29ya'.'w'.'==','Y'.'Wxsb3d'.'fZ'.'m9ydW1fdX'.'Nlcg='.'=','Tg==','','','QUNU'.'SV'.'Z'.'F','W'.'Q'.'='.'=','c29ja'.'WFsb'.'mV'.'0d'.'29ya'.'w==','YWx'.'sb3'.'dfdG'.'F'.'za3N'.'f'.'dX'.'N'.'lcg'.'==','WQ==','S'.'UQ'.'=','c29jaW'.'FsbmV0d2'.'9yaw==','Y'.'Wxsb3dfdG'.'Fza'.'3NfdXNlcg==','SU'.'Q=','c29ja'.'WFsbmV0d29yaw==','YWxsb3'.'dfdGF'.'za3N'.'f'.'dX'.'Nl'.'cg'.'='.'=','c29jaWFsbmV0d29y'.'a'.'w'.'==','YWxsb3'.'df'.'dGFza3NfZ3'.'Jv'.'dXA=','W'.'Q==','SU'.'Q=','c29'.'ja'.'WFs'.'bmV'.'0d29yaw==','YWxsb3dfd'.'GFza3N'.'fZ'.'3JvdXA=','SUQ'.'=','c2'.'9j'.'aWFsbmV0'.'d29yaw==','YWxs'.'b3df'.'dGFza3NfZ3JvdX'.'A'.'=','dGFza3M=','T'.'g='.'=','','','QU'.'NUSVZ'.'F','WQ==','c29jaWFsbmV0d29yaw==','YWxsb'.'3d'.'fY2'.'FsZW5'.'kYXJf'.'dX'.'Nlcg='.'=','WQ'.'==','SUQ=','c29jaWFsbmV0'.'d29yaw==','YWxsb3dfY2F'.'sZW'.'5k'.'Y'.'X'.'JfdX'.'Nl'.'cg='.'=','SUQ=','c29jaW'.'FsbmV0d2'.'9yaw='.'=','YW'.'xsb3df'.'Y2FsZW5kYX'.'Jf'.'dX'.'Nlcg==','c29jaWF'.'sbmV'.'0d2'.'9yaw'.'==','Y'.'Wxsb3dfY'.'2FsZW5kYXJfZ3Jv'.'dXA=','WQ==','SUQ=','c29jaW'.'Fs'.'bmV0d29yaw==','YWx'.'sb3'.'dfY2Fs'.'ZW5k'.'YXJ'.'fZ3Jv'.'dXA=','SU'.'Q=','c'.'29jaWFsbmV0d29yaw==','Y'.'W'.'xsb'.'3d'.'f'.'Y2FsZW5kY'.'XJfZ3Jvd'.'X'.'A=','QU'.'N'.'USVZF',''.'WQ==','Tg==','ZX'.'h'.'0cm'.'F'.'uZXQ=','aWJsb2Nr','T'.'2'.'5BZnRlcklCb'.'G9ja'.'0VsZW'.'1lbn'.'RVc'.'G'.'RhdGU=','aW5'.'0cmF'.'uZXQ=','Q'.'0ludHJhbmV0RXZlbn'.'RI'.'Y'.'W5'.'kbGVycw==',''.'U1BSZWdp'.'c3RlclV'.'wZGF0ZWRJdGVt','Q0ludHJhbmV0U'.'2'.'hhcmV'.'wb2lu'.'dDo6QWdlbnRMaXN0cy'.'gp'.'Ow==','aW'.'50'.'cmF'.'u'.'ZXQ=','Tg==','Q0'.'l'.'udHJhb'.'mV0U'.'2hhcmVwb2'.'ludDo6QW'.'dl'.'b'.'nRRdWV1ZSgpOw==','aW'.'50cmFuZX'.'Q=',''.'Tg==','Q0lu'.'dHJh'.'bmV0'.'U2hh'.'cmVw'.'b2lu'.'dDo6QW'.'dlbnR'.'VcGRhdGUoKTs=',''.'aW50cmFuZXQ=','Tg==','aWJsb2Nr','T25B'.'Zn'.'RlcklCbG9j'.'a'.'0VsZ'.'W1lbnRB'.'ZGQ'.'=',''.'aW50cmFuZXQ=','Q0ludHJhbmV0RXZlbnRIYW5kb'.'GVyc'.'w==','U1BS'.'ZWdpc'.'3Rl'.'c'.'lVwZGF0Z'.'WRJdGVt','aWJ'.'sb2Nr',''.'T25B'.'ZnRlck'.'lCbG9ja0'.'VsZW1l'.'b'.'nRVcGRhdGU=','aW50cmFuZXQ'.'=','Q'.'0ludHJhbmV0RXZ'.'l'.'bnR'.'IYW'.'5kb'.'GVyc'.'w'.'==','U1BSZWd'.'p'.'c3RlclVwZGF0ZW'.'RJ'.'dGV'.'t','Q0ludHJhbmV0'.'U'.'2hhcm'.'Vwb2ludD'.'o'.'6QWdl'.'bnRMaXN'.'0c'.'ygp'.'O'.'w='.'=','a'.'W50'.'cmFuZ'.'XQ=','Q0ludHJhbmV'.'0U2'.'hhcmV'.'wb2'.'l'.'u'.'dDo6QWdlbnRR'.'dWV'.'1Z'.'SgpOw'.'==',''.'aW'.'50cmFuZ'.'XQ=','Q0l'.'udHJhbm'.'V0U2hhc'.'mVw'.'b2l'.'udDo'.'6QWdlbnR'.'VcG'.'Rh'.'dG'.'Uo'.'KTs=','aW'.'50'.'cm'.'FuZX'.'Q=','Y3'.'Jt','bWFpbg==','T25CZWZvcmV'.'Qcm9s'.'b'.'2'.'c=','bWFpbg==','Q'.'1dpem'.'Fy'.'ZFNvbF'.'Bh'.'bmV'.'sS'.'W50cmFu'.'Z'.'XQ=','U2hvd'.'1'.'BhbmV'.'s',''.'L'.'21v'.'ZHV'.'sZX'.'Mv'.'aW50'.'cmFuZ'.'XQvcGFuZWxfYnV0dG9'.'u'.'L'.'nBocA'.'==',''.'ZX'.'hw'.'aXJlX21lc3My',''.'bm9pdG'.'lk'.'Z'.'V90aW1pbGVtaXQ=','WQ==','ZHJpb'.'l9wZXJnb2tj',''.'J'.'TAxMHMK','RUV'.'YUElS','bWFpbg='.'=','J'.'XMlcw==','YWRt','aGRy'.'b3'.'dzc'.'2E=',''.'YWRtaW4=','bW9kdWxlcw==','Z'.'G'.'Vm'.'aW5lLnBocA'.'==','bWFpbg==',''.'Yml0cml4','U'.'k'.'hTSVR'.'FR'.'V'.'g=','SDR1'.'Nj'.'d'.'maH'.'c4'.'N1ZoeX'.'Rvcw==','','dGhS','N0h'.'5cjEy'.'SH'.'d5MHJGc'.'g'.'==',''.'V'.'F9T'.'VEVBT'.'A==','aHR0cD'.'o'.'vL2JpdHJ'.'peHNvZnQuY29tL2Jp'.'dHJpeC9icy5waH'.'A=',''.'T0x'.'E','UElSRURBVEVT',''.'RE9DVU1FTl'.'RfUk9PVA==','Lw==','Lw==','VEVNU'.'E9SQVJZX0'.'NBQ'.'0'.'hF','VEVNUE9SQV'.'JZX0NBQ0hF','','T'.'05fT0Q=',''.'J'.'X'.'Mlc'.'w==',''.'X09VUl9CVVM=','U0lU',''.'RURBVEVN'.'QVB'.'F'.'U'.'g==','bm9pdGl'.'kZV90'.'aW1pbG'.'Vt'.'aX'.'Q=',''.'R'.'E'.'9DVU'.'1FTlR'.'f'.'U'.'k9PVA==','L2JpdHJ'.'peC8uY2'.'9'.'u'.'ZmlnLnBoc'.'A'.'==',''.'RE9DVU1'.'FT'.'lRfUk9'.'PV'.'A='.'=','L2'.'JpdHJpeC'.'8uY29uZ'.'mlnLnB'.'o'.'cA==','c2Fh'.'c'.'w==','ZGF'.'5c1'.'9h'.'Zn'.'Rlcl90cmlhbA==','c2Fhc'.'w==','Z'.'GF5c'.'19hZnRlc'.'l90cmlhbA==',''.'c2Fh'.'c'.'w==','d'.'HJpYWxfc3'.'RvcHBlZA==','','c2Fh'.'cw'.'==','dHJpYWxfc3R'.'vcHBlZA==','bQ='.'=','ZA==','W'.'Q'.'==',''.'U0NSSVBUX05BTUU=','L2JpdHJp'.'eC9jb3V'.'w'.'b25fYWN'.'0aXZhdGlvbi5waHA=',''.'U0NS'.'S'.'VB'.'UX05'.'BTUU=','L2'.'JpdHJpeC9'.'zZXJ2aW'.'Nlcy9'.'t'.'YWluL2F'.'qY'.'XgucGh'.'w','L2J'.'pdHJpeC'.'9'.'jb3Vwb25'.'f'.'YWN0a'.'XZhdG'.'l'.'vb'.'i5'.'wa'.'HA=','U2l'.'0ZUV'.'4'.'c'.'GlyZ'.'URhdGU=');return base64_decode($_236071664[$_901438284]);}};$GLOBALS['____1225362135'][0](___1780727293(0), ___1780727293(1));class CBXFeatures{ private static $_1727834294= 30; private static $_2044097028= array( "Portal" => array( "CompanyCalendar", "CompanyPhoto", "CompanyVideo", "CompanyCareer", "StaffChanges", "StaffAbsence", "CommonDocuments", "MeetingRoomBookingSystem", "Wiki", "Learning", "Vote", "WebLink", "Subscribe", "Friends", "PersonalFiles", "PersonalBlog", "PersonalPhoto", "PersonalForum", "Blog", "Forum", "Gallery", "Board", "MicroBlog", "WebMessenger",), "Communications" => array( "Tasks", "Calendar", "Workgroups", "Jabber", "VideoConference", "Extranet", "SMTP", "Requests", "DAV", "intranet_sharepoint", "timeman", "Idea", "Meeting", "EventList", "Salary", "XDImport",), "Enterprise" => array( "BizProc", "Lists", "Support", "Analytics", "crm", "Controller", "LdapUnlimitedUsers",), "Holding" => array( "Cluster", "MultiSites",),); private static $_502125079= null; private static $_9972145= null; private static function __546473818(){ if(self::$_502125079 === null){ self::$_502125079= array(); foreach(self::$_2044097028 as $_1824496298 => $_1793948906){ foreach($_1793948906 as $_196689401) self::$_502125079[$_196689401]= $_1824496298;}} if(self::$_9972145 === null){ self::$_9972145= array(); $_937671223= COption::GetOptionString(___1780727293(2), ___1780727293(3), ___1780727293(4)); if($_937671223 != ___1780727293(5)){ $_937671223= $GLOBALS['____1225362135'][1]($_937671223); $_937671223= $GLOBALS['____1225362135'][2]($_937671223,[___1780727293(6) => false]); if($GLOBALS['____1225362135'][3]($_937671223)){ self::$_9972145= $_937671223;}} if(empty(self::$_9972145)){ self::$_9972145= array(___1780727293(7) => array(), ___1780727293(8) => array());}}} public static function InitiateEditionsSettings($_1541402748){ self::__546473818(); $_303489442= array(); foreach(self::$_2044097028 as $_1824496298 => $_1793948906){ $_1728786920= $GLOBALS['____1225362135'][4]($_1824496298, $_1541402748); self::$_9972145[___1780727293(9)][$_1824496298]=($_1728786920? array(___1780727293(10)): array(___1780727293(11))); foreach($_1793948906 as $_196689401){ self::$_9972145[___1780727293(12)][$_196689401]= $_1728786920; if(!$_1728786920) $_303489442[]= array($_196689401, false);}} $_564540609= $GLOBALS['____1225362135'][5](self::$_9972145); $_564540609= $GLOBALS['____1225362135'][6]($_564540609); COption::SetOptionString(___1780727293(13), ___1780727293(14), $_564540609); foreach($_303489442 as $_176285513) self::__1919341810($_176285513[(974-2*487)], $_176285513[round(0+0.5+0.5)]);} public static function IsFeatureEnabled($_196689401){ if($_196689401 == '') return true; self::__546473818(); if(!isset(self::$_502125079[$_196689401])) return true; if(self::$_502125079[$_196689401] == ___1780727293(15)) $_1863415235= array(___1780727293(16)); elseif(isset(self::$_9972145[___1780727293(17)][self::$_502125079[$_196689401]])) $_1863415235= self::$_9972145[___1780727293(18)][self::$_502125079[$_196689401]]; else $_1863415235= array(___1780727293(19)); if($_1863415235[(880-2*440)] != ___1780727293(20) && $_1863415235[(752-2*376)] != ___1780727293(21)){ return false;} elseif($_1863415235[min(146,0,48.666666666667)] == ___1780727293(22)){ if($_1863415235[round(0+0.25+0.25+0.25+0.25)]< $GLOBALS['____1225362135'][7]((199*2-398),(1072/2-536), min(202,0,67.333333333333), Date(___1780727293(23)), $GLOBALS['____1225362135'][8](___1780727293(24))- self::$_1727834294, $GLOBALS['____1225362135'][9](___1780727293(25)))){ if(!isset($_1863415235[round(0+0.66666666666667+0.66666666666667+0.66666666666667)]) ||!$_1863415235[round(0+1+1)]) self::__813521745(self::$_502125079[$_196689401]); return false;}} return!isset(self::$_9972145[___1780727293(26)][$_196689401]) || self::$_9972145[___1780727293(27)][$_196689401];} public static function IsFeatureInstalled($_196689401){ if($GLOBALS['____1225362135'][10]($_196689401) <= 0) return true; self::__546473818(); return(isset(self::$_9972145[___1780727293(28)][$_196689401]) && self::$_9972145[___1780727293(29)][$_196689401]);} public static function IsFeatureEditable($_196689401){ if($_196689401 == '') return true; self::__546473818(); if(!isset(self::$_502125079[$_196689401])) return true; if(self::$_502125079[$_196689401] == ___1780727293(30)) $_1863415235= array(___1780727293(31)); elseif(isset(self::$_9972145[___1780727293(32)][self::$_502125079[$_196689401]])) $_1863415235= self::$_9972145[___1780727293(33)][self::$_502125079[$_196689401]]; else $_1863415235= array(___1780727293(34)); if($_1863415235[(207*2-414)] != ___1780727293(35) && $_1863415235[(1248/2-624)] != ___1780727293(36)){ return false;} elseif($_1863415235[(786-2*393)] == ___1780727293(37)){ if($_1863415235[round(0+1)]< $GLOBALS['____1225362135'][11]((183*2-366), min(148,0,49.333333333333),(249*2-498), Date(___1780727293(38)), $GLOBALS['____1225362135'][12](___1780727293(39))- self::$_1727834294, $GLOBALS['____1225362135'][13](___1780727293(40)))){ if(!isset($_1863415235[round(0+2)]) ||!$_1863415235[round(0+0.66666666666667+0.66666666666667+0.66666666666667)]) self::__813521745(self::$_502125079[$_196689401]); return false;}} return true;} private static function __1919341810($_196689401, $_1331235465){ if($GLOBALS['____1225362135'][14]("CBXFeatures", "On".$_196689401."SettingsChange")) $GLOBALS['____1225362135'][15](array("CBXFeatures", "On".$_196689401."SettingsChange"), array($_196689401, $_1331235465)); $_1116113318= $GLOBALS['_____2066307101'][0](___1780727293(41), ___1780727293(42).$_196689401.___1780727293(43)); while($_16340872= $_1116113318->Fetch()) $GLOBALS['_____2066307101'][1]($_16340872, array($_196689401, $_1331235465));} public static function SetFeatureEnabled($_196689401, $_1331235465= true, $_1388133273= true){ if($GLOBALS['____1225362135'][16]($_196689401) <= 0) return; if(!self::IsFeatureEditable($_196689401)) $_1331235465= false; $_1331235465= (bool)$_1331235465; self::__546473818(); $_1792605359=(!isset(self::$_9972145[___1780727293(44)][$_196689401]) && $_1331235465 || isset(self::$_9972145[___1780727293(45)][$_196689401]) && $_1331235465 != self::$_9972145[___1780727293(46)][$_196689401]); self::$_9972145[___1780727293(47)][$_196689401]= $_1331235465; $_564540609= $GLOBALS['____1225362135'][17](self::$_9972145); $_564540609= $GLOBALS['____1225362135'][18]($_564540609); COption::SetOptionString(___1780727293(48), ___1780727293(49), $_564540609); if($_1792605359 && $_1388133273) self::__1919341810($_196689401, $_1331235465);} private static function __813521745($_1824496298){ if($GLOBALS['____1225362135'][19]($_1824496298) <= 0 || $_1824496298 == "Portal") return; self::__546473818(); if(!isset(self::$_9972145[___1780727293(50)][$_1824496298]) || self::$_9972145[___1780727293(51)][$_1824496298][(130*2-260)] != ___1780727293(52)) return; if(isset(self::$_9972145[___1780727293(53)][$_1824496298][round(0+1+1)]) && self::$_9972145[___1780727293(54)][$_1824496298][round(0+0.4+0.4+0.4+0.4+0.4)]) return; $_303489442= array(); if(isset(self::$_2044097028[$_1824496298]) && $GLOBALS['____1225362135'][20](self::$_2044097028[$_1824496298])){ foreach(self::$_2044097028[$_1824496298] as $_196689401){ if(isset(self::$_9972145[___1780727293(55)][$_196689401]) && self::$_9972145[___1780727293(56)][$_196689401]){ self::$_9972145[___1780727293(57)][$_196689401]= false; $_303489442[]= array($_196689401, false);}} self::$_9972145[___1780727293(58)][$_1824496298][round(0+0.4+0.4+0.4+0.4+0.4)]= true;} $_564540609= $GLOBALS['____1225362135'][21](self::$_9972145); $_564540609= $GLOBALS['____1225362135'][22]($_564540609); COption::SetOptionString(___1780727293(59), ___1780727293(60), $_564540609); foreach($_303489442 as $_176285513) self::__1919341810($_176285513[min(80,0,26.666666666667)], $_176285513[round(0+0.2+0.2+0.2+0.2+0.2)]);} public static function ModifyFeaturesSettings($_1541402748, $_1793948906){ self::__546473818(); foreach($_1541402748 as $_1824496298 => $_1495667057) self::$_9972145[___1780727293(61)][$_1824496298]= $_1495667057; $_303489442= array(); foreach($_1793948906 as $_196689401 => $_1331235465){ if(!isset(self::$_9972145[___1780727293(62)][$_196689401]) && $_1331235465 || isset(self::$_9972145[___1780727293(63)][$_196689401]) && $_1331235465 != self::$_9972145[___1780727293(64)][$_196689401]) $_303489442[]= array($_196689401, $_1331235465); self::$_9972145[___1780727293(65)][$_196689401]= $_1331235465;} $_564540609= $GLOBALS['____1225362135'][23](self::$_9972145); $_564540609= $GLOBALS['____1225362135'][24]($_564540609); COption::SetOptionString(___1780727293(66), ___1780727293(67), $_564540609); self::$_9972145= false; foreach($_303489442 as $_176285513) self::__1919341810($_176285513[(1448/2-724)], $_176285513[round(0+0.5+0.5)]);} public static function SaveFeaturesSettings($_216744892, $_760097769){ self::__546473818(); $_1894988764= array(___1780727293(68) => array(), ___1780727293(69) => array()); if(!$GLOBALS['____1225362135'][25]($_216744892)) $_216744892= array(); if(!$GLOBALS['____1225362135'][26]($_760097769)) $_760097769= array(); if(!$GLOBALS['____1225362135'][27](___1780727293(70), $_216744892)) $_216744892[]= ___1780727293(71); foreach(self::$_2044097028 as $_1824496298 => $_1793948906){ if(isset(self::$_9972145[___1780727293(72)][$_1824496298])){ $_682556672= self::$_9972145[___1780727293(73)][$_1824496298];} else{ $_682556672=($_1824496298 == ___1780727293(74)? array(___1780727293(75)): array(___1780727293(76)));} if($_682556672[(164*2-328)] == ___1780727293(77) || $_682556672[(926-2*463)] == ___1780727293(78)){ $_1894988764[___1780727293(79)][$_1824496298]= $_682556672;} else{ if($GLOBALS['____1225362135'][28]($_1824496298, $_216744892)) $_1894988764[___1780727293(80)][$_1824496298]= array(___1780727293(81), $GLOBALS['____1225362135'][29]((181*2-362),(242*2-484),(806-2*403), $GLOBALS['____1225362135'][30](___1780727293(82)), $GLOBALS['____1225362135'][31](___1780727293(83)), $GLOBALS['____1225362135'][32](___1780727293(84)))); else $_1894988764[___1780727293(85)][$_1824496298]= array(___1780727293(86));}} $_303489442= array(); foreach(self::$_502125079 as $_196689401 => $_1824496298){ if($_1894988764[___1780727293(87)][$_1824496298][min(60,0,20)] != ___1780727293(88) && $_1894988764[___1780727293(89)][$_1824496298][(249*2-498)] != ___1780727293(90)){ $_1894988764[___1780727293(91)][$_196689401]= false;} else{ if($_1894988764[___1780727293(92)][$_1824496298][(834-2*417)] == ___1780727293(93) && $_1894988764[___1780727293(94)][$_1824496298][round(0+0.2+0.2+0.2+0.2+0.2)]< $GLOBALS['____1225362135'][33]((238*2-476),(146*2-292), min(136,0,45.333333333333), Date(___1780727293(95)), $GLOBALS['____1225362135'][34](___1780727293(96))- self::$_1727834294, $GLOBALS['____1225362135'][35](___1780727293(97)))) $_1894988764[___1780727293(98)][$_196689401]= false; else $_1894988764[___1780727293(99)][$_196689401]= $GLOBALS['____1225362135'][36]($_196689401, $_760097769); if(!isset(self::$_9972145[___1780727293(100)][$_196689401]) && $_1894988764[___1780727293(101)][$_196689401] || isset(self::$_9972145[___1780727293(102)][$_196689401]) && $_1894988764[___1780727293(103)][$_196689401] != self::$_9972145[___1780727293(104)][$_196689401]) $_303489442[]= array($_196689401, $_1894988764[___1780727293(105)][$_196689401]);}} $_564540609= $GLOBALS['____1225362135'][37]($_1894988764); $_564540609= $GLOBALS['____1225362135'][38]($_564540609); COption::SetOptionString(___1780727293(106), ___1780727293(107), $_564540609); self::$_9972145= false; foreach($_303489442 as $_176285513) self::__1919341810($_176285513[(127*2-254)], $_176285513[round(0+0.5+0.5)]);} public static function GetFeaturesList(){ self::__546473818(); $_1597122506= array(); foreach(self::$_2044097028 as $_1824496298 => $_1793948906){ if(isset(self::$_9972145[___1780727293(108)][$_1824496298])){ $_682556672= self::$_9972145[___1780727293(109)][$_1824496298];} else{ $_682556672=($_1824496298 == ___1780727293(110)? array(___1780727293(111)): array(___1780727293(112)));} $_1597122506[$_1824496298]= array( ___1780727293(113) => $_682556672[min(100,0,33.333333333333)], ___1780727293(114) => $_682556672[round(0+0.25+0.25+0.25+0.25)], ___1780727293(115) => array(),); $_1597122506[$_1824496298][___1780727293(116)]= false; if($_1597122506[$_1824496298][___1780727293(117)] == ___1780727293(118)){ $_1597122506[$_1824496298][___1780727293(119)]= $GLOBALS['____1225362135'][39](($GLOBALS['____1225362135'][40]()- $_1597122506[$_1824496298][___1780727293(120)])/ round(0+28800+28800+28800)); if($_1597122506[$_1824496298][___1780727293(121)]> self::$_1727834294) $_1597122506[$_1824496298][___1780727293(122)]= true;} foreach($_1793948906 as $_196689401) $_1597122506[$_1824496298][___1780727293(123)][$_196689401]=(!isset(self::$_9972145[___1780727293(124)][$_196689401]) || self::$_9972145[___1780727293(125)][$_196689401]);} return $_1597122506;} private static function __2042775600($_466223961, $_1479229){ if(IsModuleInstalled($_466223961) == $_1479229) return true; $_2119584795= $_SERVER[___1780727293(126)].___1780727293(127).$_466223961.___1780727293(128); if(!$GLOBALS['____1225362135'][41]($_2119584795)) return false; include_once($_2119584795); $_2111599038= $GLOBALS['____1225362135'][42](___1780727293(129), ___1780727293(130), $_466223961); if(!$GLOBALS['____1225362135'][43]($_2111599038)) return false; $_1327412416= new $_2111599038; if($_1479229){ if(!$_1327412416->InstallDB()) return false; $_1327412416->InstallEvents(); if(!$_1327412416->InstallFiles()) return false;} else{ if(CModule::IncludeModule(___1780727293(131))) CSearch::DeleteIndex($_466223961); UnRegisterModule($_466223961);} return true;} protected static function OnRequestsSettingsChange($_196689401, $_1331235465){ self::__2042775600("form", $_1331235465);} protected static function OnLearningSettingsChange($_196689401, $_1331235465){ self::__2042775600("learning", $_1331235465);} protected static function OnJabberSettingsChange($_196689401, $_1331235465){ self::__2042775600("xmpp", $_1331235465);} protected static function OnVideoConferenceSettingsChange($_196689401, $_1331235465){ self::__2042775600("video", $_1331235465);} protected static function OnBizProcSettingsChange($_196689401, $_1331235465){ self::__2042775600("bizprocdesigner", $_1331235465);} protected static function OnListsSettingsChange($_196689401, $_1331235465){ self::__2042775600("lists", $_1331235465);} protected static function OnWikiSettingsChange($_196689401, $_1331235465){ self::__2042775600("wiki", $_1331235465);} protected static function OnSupportSettingsChange($_196689401, $_1331235465){ self::__2042775600("support", $_1331235465);} protected static function OnControllerSettingsChange($_196689401, $_1331235465){ self::__2042775600("controller", $_1331235465);} protected static function OnAnalyticsSettingsChange($_196689401, $_1331235465){ self::__2042775600("statistic", $_1331235465);} protected static function OnVoteSettingsChange($_196689401, $_1331235465){ self::__2042775600("vote", $_1331235465);} protected static function OnFriendsSettingsChange($_196689401, $_1331235465){ if($_1331235465) $_1610795983= "Y"; else $_1610795983= ___1780727293(132); $_1470944025= CSite::GetList(___1780727293(133), ___1780727293(134), array(___1780727293(135) => ___1780727293(136))); while($_246137334= $_1470944025->Fetch()){ if(COption::GetOptionString(___1780727293(137), ___1780727293(138), ___1780727293(139), $_246137334[___1780727293(140)]) != $_1610795983){ COption::SetOptionString(___1780727293(141), ___1780727293(142), $_1610795983, false, $_246137334[___1780727293(143)]); COption::SetOptionString(___1780727293(144), ___1780727293(145), $_1610795983);}}} protected static function OnMicroBlogSettingsChange($_196689401, $_1331235465){ if($_1331235465) $_1610795983= "Y"; else $_1610795983= ___1780727293(146); $_1470944025= CSite::GetList(___1780727293(147), ___1780727293(148), array(___1780727293(149) => ___1780727293(150))); while($_246137334= $_1470944025->Fetch()){ if(COption::GetOptionString(___1780727293(151), ___1780727293(152), ___1780727293(153), $_246137334[___1780727293(154)]) != $_1610795983){ COption::SetOptionString(___1780727293(155), ___1780727293(156), $_1610795983, false, $_246137334[___1780727293(157)]); COption::SetOptionString(___1780727293(158), ___1780727293(159), $_1610795983);} if(COption::GetOptionString(___1780727293(160), ___1780727293(161), ___1780727293(162), $_246137334[___1780727293(163)]) != $_1610795983){ COption::SetOptionString(___1780727293(164), ___1780727293(165), $_1610795983, false, $_246137334[___1780727293(166)]); COption::SetOptionString(___1780727293(167), ___1780727293(168), $_1610795983);}}} protected static function OnPersonalFilesSettingsChange($_196689401, $_1331235465){ if($_1331235465) $_1610795983= "Y"; else $_1610795983= ___1780727293(169); $_1470944025= CSite::GetList(___1780727293(170), ___1780727293(171), array(___1780727293(172) => ___1780727293(173))); while($_246137334= $_1470944025->Fetch()){ if(COption::GetOptionString(___1780727293(174), ___1780727293(175), ___1780727293(176), $_246137334[___1780727293(177)]) != $_1610795983){ COption::SetOptionString(___1780727293(178), ___1780727293(179), $_1610795983, false, $_246137334[___1780727293(180)]); COption::SetOptionString(___1780727293(181), ___1780727293(182), $_1610795983);}}} protected static function OnPersonalBlogSettingsChange($_196689401, $_1331235465){ if($_1331235465) $_1610795983= "Y"; else $_1610795983= ___1780727293(183); $_1470944025= CSite::GetList(___1780727293(184), ___1780727293(185), array(___1780727293(186) => ___1780727293(187))); while($_246137334= $_1470944025->Fetch()){ if(COption::GetOptionString(___1780727293(188), ___1780727293(189), ___1780727293(190), $_246137334[___1780727293(191)]) != $_1610795983){ COption::SetOptionString(___1780727293(192), ___1780727293(193), $_1610795983, false, $_246137334[___1780727293(194)]); COption::SetOptionString(___1780727293(195), ___1780727293(196), $_1610795983);}}} protected static function OnPersonalPhotoSettingsChange($_196689401, $_1331235465){ if($_1331235465) $_1610795983= "Y"; else $_1610795983= ___1780727293(197); $_1470944025= CSite::GetList(___1780727293(198), ___1780727293(199), array(___1780727293(200) => ___1780727293(201))); while($_246137334= $_1470944025->Fetch()){ if(COption::GetOptionString(___1780727293(202), ___1780727293(203), ___1780727293(204), $_246137334[___1780727293(205)]) != $_1610795983){ COption::SetOptionString(___1780727293(206), ___1780727293(207), $_1610795983, false, $_246137334[___1780727293(208)]); COption::SetOptionString(___1780727293(209), ___1780727293(210), $_1610795983);}}} protected static function OnPersonalForumSettingsChange($_196689401, $_1331235465){ if($_1331235465) $_1610795983= "Y"; else $_1610795983= ___1780727293(211); $_1470944025= CSite::GetList(___1780727293(212), ___1780727293(213), array(___1780727293(214) => ___1780727293(215))); while($_246137334= $_1470944025->Fetch()){ if(COption::GetOptionString(___1780727293(216), ___1780727293(217), ___1780727293(218), $_246137334[___1780727293(219)]) != $_1610795983){ COption::SetOptionString(___1780727293(220), ___1780727293(221), $_1610795983, false, $_246137334[___1780727293(222)]); COption::SetOptionString(___1780727293(223), ___1780727293(224), $_1610795983);}}} protected static function OnTasksSettingsChange($_196689401, $_1331235465){ if($_1331235465) $_1610795983= "Y"; else $_1610795983= ___1780727293(225); $_1470944025= CSite::GetList(___1780727293(226), ___1780727293(227), array(___1780727293(228) => ___1780727293(229))); while($_246137334= $_1470944025->Fetch()){ if(COption::GetOptionString(___1780727293(230), ___1780727293(231), ___1780727293(232), $_246137334[___1780727293(233)]) != $_1610795983){ COption::SetOptionString(___1780727293(234), ___1780727293(235), $_1610795983, false, $_246137334[___1780727293(236)]); COption::SetOptionString(___1780727293(237), ___1780727293(238), $_1610795983);} if(COption::GetOptionString(___1780727293(239), ___1780727293(240), ___1780727293(241), $_246137334[___1780727293(242)]) != $_1610795983){ COption::SetOptionString(___1780727293(243), ___1780727293(244), $_1610795983, false, $_246137334[___1780727293(245)]); COption::SetOptionString(___1780727293(246), ___1780727293(247), $_1610795983);}} self::__2042775600(___1780727293(248), $_1331235465);} protected static function OnCalendarSettingsChange($_196689401, $_1331235465){ if($_1331235465) $_1610795983= "Y"; else $_1610795983= ___1780727293(249); $_1470944025= CSite::GetList(___1780727293(250), ___1780727293(251), array(___1780727293(252) => ___1780727293(253))); while($_246137334= $_1470944025->Fetch()){ if(COption::GetOptionString(___1780727293(254), ___1780727293(255), ___1780727293(256), $_246137334[___1780727293(257)]) != $_1610795983){ COption::SetOptionString(___1780727293(258), ___1780727293(259), $_1610795983, false, $_246137334[___1780727293(260)]); COption::SetOptionString(___1780727293(261), ___1780727293(262), $_1610795983);} if(COption::GetOptionString(___1780727293(263), ___1780727293(264), ___1780727293(265), $_246137334[___1780727293(266)]) != $_1610795983){ COption::SetOptionString(___1780727293(267), ___1780727293(268), $_1610795983, false, $_246137334[___1780727293(269)]); COption::SetOptionString(___1780727293(270), ___1780727293(271), $_1610795983);}}} protected static function OnSMTPSettingsChange($_196689401, $_1331235465){ self::__2042775600("mail", $_1331235465);} protected static function OnExtranetSettingsChange($_196689401, $_1331235465){ $_1615561484= COption::GetOptionString("extranet", "extranet_site", ""); if($_1615561484){ $_1833374948= new CSite; $_1833374948->Update($_1615561484, array(___1780727293(272) =>($_1331235465? ___1780727293(273): ___1780727293(274))));} self::__2042775600(___1780727293(275), $_1331235465);} protected static function OnDAVSettingsChange($_196689401, $_1331235465){ self::__2042775600("dav", $_1331235465);} protected static function OntimemanSettingsChange($_196689401, $_1331235465){ self::__2042775600("timeman", $_1331235465);} protected static function Onintranet_sharepointSettingsChange($_196689401, $_1331235465){ if($_1331235465){ RegisterModuleDependences("iblock", "OnAfterIBlockElementAdd", "intranet", "CIntranetEventHandlers", "SPRegisterUpdatedItem"); RegisterModuleDependences(___1780727293(276), ___1780727293(277), ___1780727293(278), ___1780727293(279), ___1780727293(280)); CAgent::AddAgent(___1780727293(281), ___1780727293(282), ___1780727293(283), round(0+500)); CAgent::AddAgent(___1780727293(284), ___1780727293(285), ___1780727293(286), round(0+75+75+75+75)); CAgent::AddAgent(___1780727293(287), ___1780727293(288), ___1780727293(289), round(0+1200+1200+1200));} else{ UnRegisterModuleDependences(___1780727293(290), ___1780727293(291), ___1780727293(292), ___1780727293(293), ___1780727293(294)); UnRegisterModuleDependences(___1780727293(295), ___1780727293(296), ___1780727293(297), ___1780727293(298), ___1780727293(299)); CAgent::RemoveAgent(___1780727293(300), ___1780727293(301)); CAgent::RemoveAgent(___1780727293(302), ___1780727293(303)); CAgent::RemoveAgent(___1780727293(304), ___1780727293(305));}} protected static function OncrmSettingsChange($_196689401, $_1331235465){ if($_1331235465) COption::SetOptionString("crm", "form_features", "Y"); self::__2042775600(___1780727293(306), $_1331235465);} protected static function OnClusterSettingsChange($_196689401, $_1331235465){ self::__2042775600("cluster", $_1331235465);} protected static function OnMultiSitesSettingsChange($_196689401, $_1331235465){ if($_1331235465) RegisterModuleDependences("main", "OnBeforeProlog", "main", "CWizardSolPanelIntranet", "ShowPanel", 100, "/modules/intranet/panel_button.php"); else UnRegisterModuleDependences(___1780727293(307), ___1780727293(308), ___1780727293(309), ___1780727293(310), ___1780727293(311), ___1780727293(312));} protected static function OnIdeaSettingsChange($_196689401, $_1331235465){ self::__2042775600("idea", $_1331235465);} protected static function OnMeetingSettingsChange($_196689401, $_1331235465){ self::__2042775600("meeting", $_1331235465);} protected static function OnXDImportSettingsChange($_196689401, $_1331235465){ self::__2042775600("xdimport", $_1331235465);}} $_1019866521= GetMessage(___1780727293(313));$_1513933650= round(0+3.75+3.75+3.75+3.75);$GLOBALS['____1225362135'][44]($GLOBALS['____1225362135'][45]($GLOBALS['____1225362135'][46](___1780727293(314))), ___1780727293(315));$_1715050668= round(0+0.2+0.2+0.2+0.2+0.2); $_1675514540= ___1780727293(316); unset($_968320932); $_2092126115= $GLOBALS['____1225362135'][47](___1780727293(317), ___1780727293(318)); $_968320932= \COption::GetOptionString(___1780727293(319), $GLOBALS['____1225362135'][48](___1780727293(320),___1780727293(321),$GLOBALS['____1225362135'][49]($_1675514540, round(0+0.5+0.5+0.5+0.5), round(0+0.8+0.8+0.8+0.8+0.8))).$GLOBALS['____1225362135'][50](___1780727293(322))); $_223152512= array(round(0+5.6666666666667+5.6666666666667+5.6666666666667) => ___1780727293(323), round(0+7) => ___1780727293(324), round(0+4.4+4.4+4.4+4.4+4.4) => ___1780727293(325), round(0+2.4+2.4+2.4+2.4+2.4) => ___1780727293(326), round(0+0.6+0.6+0.6+0.6+0.6) => ___1780727293(327)); $_2130906133= ___1780727293(328); while($_968320932){ $_1835143234= ___1780727293(329); $_1929035042= $GLOBALS['____1225362135'][51]($_968320932); $_476585903= ___1780727293(330); $_1835143234= $GLOBALS['____1225362135'][52](___1780727293(331).$_1835143234, min(102,0,34),-round(0+1.6666666666667+1.6666666666667+1.6666666666667)).___1780727293(332); $_722755378= $GLOBALS['____1225362135'][53]($_1835143234); $_1687817761= min(236,0,78.666666666667); for($_520076462=(980-2*490); $_520076462<$GLOBALS['____1225362135'][54]($_1929035042); $_520076462++){ $_476585903 .= $GLOBALS['____1225362135'][55]($GLOBALS['____1225362135'][56]($_1929035042[$_520076462])^ $GLOBALS['____1225362135'][57]($_1835143234[$_1687817761])); if($_1687817761==$_722755378-round(0+0.5+0.5)) $_1687817761=(1156/2-578); else $_1687817761= $_1687817761+ round(0+1);} $_1715050668= $GLOBALS['____1225362135'][58]((992-2*496), min(134,0,44.666666666667),(1432/2-716), $GLOBALS['____1225362135'][59]($_476585903[round(0+6)].$_476585903[round(0+1.5+1.5)]), $GLOBALS['____1225362135'][60]($_476585903[round(0+0.25+0.25+0.25+0.25)].$_476585903[round(0+3.5+3.5+3.5+3.5)]), $GLOBALS['____1225362135'][61]($_476585903[round(0+2.5+2.5+2.5+2.5)].$_476585903[round(0+9+9)].$_476585903[round(0+1.75+1.75+1.75+1.75)].$_476585903[round(0+4+4+4)])); unset($_1835143234); break;} $_1756165088= ___1780727293(333); $GLOBALS['____1225362135'][62]($_223152512); $_443185719= ___1780727293(334); $_2130906133= ___1780727293(335).$GLOBALS['____1225362135'][63]($_2130906133.___1780727293(336), round(0+1+1),-round(0+0.2+0.2+0.2+0.2+0.2));@include($_SERVER[___1780727293(337)].___1780727293(338).$GLOBALS['____1225362135'][64](___1780727293(339), $_223152512)); $_1841957715= round(0+0.66666666666667+0.66666666666667+0.66666666666667); while($GLOBALS['____1225362135'][65](___1780727293(340))){ $_870828703= $GLOBALS['____1225362135'][66]($GLOBALS['____1225362135'][67](___1780727293(341))); $_1183839966= ___1780727293(342); $_1756165088= $GLOBALS['____1225362135'][68](___1780727293(343)).$GLOBALS['____1225362135'][69](___1780727293(344),$_1756165088,___1780727293(345)); $_1379054995= $GLOBALS['____1225362135'][70]($_1756165088); $_1687817761= min(52,0,17.333333333333); for($_520076462=(155*2-310); $_520076462<$GLOBALS['____1225362135'][71]($_870828703); $_520076462++){ $_1183839966 .= $GLOBALS['____1225362135'][72]($GLOBALS['____1225362135'][73]($_870828703[$_520076462])^ $GLOBALS['____1225362135'][74]($_1756165088[$_1687817761])); if($_1687817761==$_1379054995-round(0+0.33333333333333+0.33333333333333+0.33333333333333)) $_1687817761= min(86,0,28.666666666667); else $_1687817761= $_1687817761+ round(0+1);} $_1841957715= $GLOBALS['____1225362135'][75]((178*2-356),(990-2*495), min(226,0,75.333333333333), $GLOBALS['____1225362135'][76]($_1183839966[round(0+1.2+1.2+1.2+1.2+1.2)].$_1183839966[round(0+8+8)]), $GLOBALS['____1225362135'][77]($_1183839966[round(0+1.8+1.8+1.8+1.8+1.8)].$_1183839966[round(0+0.5+0.5+0.5+0.5)]), $GLOBALS['____1225362135'][78]($_1183839966[round(0+6+6)].$_1183839966[round(0+3.5+3.5)].$_1183839966[round(0+14)].$_1183839966[round(0+1+1+1)])); unset($_1756165088); break;} $_2092126115= ___1780727293(346).$GLOBALS['____1225362135'][79]($GLOBALS['____1225362135'][80]($_2092126115, round(0+1.5+1.5),-round(0+0.25+0.25+0.25+0.25)).___1780727293(347), round(0+0.2+0.2+0.2+0.2+0.2),-round(0+1.6666666666667+1.6666666666667+1.6666666666667));while(!$GLOBALS['____1225362135'][81]($GLOBALS['____1225362135'][82]($GLOBALS['____1225362135'][83](___1780727293(348))))){function __f($_638677964){return $_638677964+__f($_638677964);}__f(round(0+0.33333333333333+0.33333333333333+0.33333333333333));};if($GLOBALS['____1225362135'][84]($_SERVER[___1780727293(349)].___1780727293(350))){ $bxProductConfig= array(); include($_SERVER[___1780727293(351)].___1780727293(352)); if(isset($bxProductConfig[___1780727293(353)][___1780727293(354)])){ $_1718227439= $GLOBALS['____1225362135'][85]($bxProductConfig[___1780727293(355)][___1780727293(356)]); if($_1718227439 >=(1256/2-628) && $_1718227439< round(0+3.75+3.75+3.75+3.75)) $_1513933650= $_1718227439;} if($bxProductConfig[___1780727293(357)][___1780727293(358)] <> ___1780727293(359)) $_1019866521= $bxProductConfig[___1780727293(360)][___1780727293(361)];}for($_520076462=(134*2-268),$_1029207360=($GLOBALS['____1225362135'][86]()< $GLOBALS['____1225362135'][87]((1484/2-742),(248*2-496),min(160,0,53.333333333333),round(0+5),round(0+0.25+0.25+0.25+0.25),round(0+403.6+403.6+403.6+403.6+403.6)) || $_1715050668 <= round(0+2+2+2+2+2)),$_841393717=($_1715050668< $GLOBALS['____1225362135'][88]((1284/2-642),(144*2-288),(138*2-276),Date(___1780727293(362)),$GLOBALS['____1225362135'][89](___1780727293(363))-$_1513933650,$GLOBALS['____1225362135'][90](___1780727293(364)))),$_1940613532=($_SERVER[___1780727293(365)]!==___1780727293(366)&&$_SERVER[___1780727293(367)]!==___1780727293(368)); $_520076462< round(0+10),($_1029207360 || $_841393717 || $_1715050668 != $_1841957715) && $_1940613532; $_520076462++,LocalRedirect(___1780727293(369)),exit,$GLOBALS['_____2066307101'][2]($_1019866521));$GLOBALS['____1225362135'][91]($_2130906133, $_1715050668); $GLOBALS['____1225362135'][92]($_2092126115, $_1841957715); $GLOBALS[___1780727293(370)]= OLDSITEEXPIREDATE;/**/			//Do not remove this

require_once(__DIR__."/autoload.php");

// Component 2.0 template engines
$GLOBALS['arCustomTemplateEngines'] = [];

// User fields manager
$GLOBALS['USER_FIELD_MANAGER'] = new CUserTypeManager;

// todo: remove global
$GLOBALS['BX_MENU_CUSTOM'] = CMenuCustom::getInstance();

if (file_exists(($_fname = __DIR__."/classes/general/update_db_updater.php")))
{
	$US_HOST_PROCESS_MAIN = false;
	include($_fname);
}

if (file_exists(($_fname = $_SERVER["DOCUMENT_ROOT"]."/bitrix/init.php")))
{
	include_once($_fname);
}

if (($_fname = getLocalPath("php_interface/init.php", BX_PERSONAL_ROOT)) !== false)
{
	include_once($_SERVER["DOCUMENT_ROOT"].$_fname);
}

if (($_fname = getLocalPath("php_interface/".SITE_ID."/init.php", BX_PERSONAL_ROOT)) !== false)
{
	include_once($_SERVER["DOCUMENT_ROOT"].$_fname);
}

//global var, is used somewhere
$GLOBALS["sDocPath"] = $GLOBALS["APPLICATION"]->GetCurPage();

if ((!(defined("STATISTIC_ONLY") && STATISTIC_ONLY && mb_substr($GLOBALS["APPLICATION"]->GetCurPage(), 0, mb_strlen(BX_ROOT."/admin/")) != BX_ROOT."/admin/")) && COption::GetOptionString("main", "include_charset", "Y")=="Y" && LANG_CHARSET <> '')
{
	header("Content-Type: text/html; charset=".LANG_CHARSET);
}

if (COption::GetOptionString("main", "set_p3p_header", "Y")=="Y")
{
	header("P3P: policyref=\"/bitrix/p3p.xml\", CP=\"NON DSP COR CUR ADM DEV PSA PSD OUR UNR BUS UNI COM NAV INT DEM STA\"");
}

$license = $application->getLicense();
header("X-Powered-CMS: Bitrix Site Manager (" . ($license->isDemoKey() ? "DEMO" : $license->getPublicHashKey()) . ")");

if (COption::GetOptionString("main", "update_devsrv", "") == "Y")
{
	header("X-DevSrv-CMS: Bitrix");
}

//agents
if (COption::GetOptionString("main", "check_agents", "Y") == "Y")
{
	$application->addBackgroundJob(["CAgent", "CheckAgents"], [], \Bitrix\Main\Application::JOB_PRIORITY_LOW);
}

//send email events
if (COption::GetOptionString("main", "check_events", "Y") !== "N")
{
	$application->addBackgroundJob(['\Bitrix\Main\Mail\EventManager', 'checkEvents'], [], \Bitrix\Main\Application::JOB_PRIORITY_LOW-1);
}

$healerOfEarlySessionStart = new HealerEarlySessionStart();
$healerOfEarlySessionStart->process($application->getKernelSession());

$kernelSession = $application->getKernelSession();
$kernelSession->start();
$application->getSessionLocalStorageManager()->setUniqueId($kernelSession->getId());

foreach (GetModuleEvents("main", "OnPageStart", true) as $arEvent)
{
	ExecuteModuleEventEx($arEvent);
}

//define global user object
$GLOBALS["USER"] = new CUser;

//session control from group policy
$arPolicy = $GLOBALS["USER"]->GetSecurityPolicy();
$currTime = time();
if (
	(
		//IP address changed
		$kernelSession['SESS_IP']
		&& $arPolicy["SESSION_IP_MASK"] <> ''
		&& (
			(ip2long($arPolicy["SESSION_IP_MASK"]) & ip2long($kernelSession['SESS_IP']))
			!=
			(ip2long($arPolicy["SESSION_IP_MASK"]) & ip2long($_SERVER['REMOTE_ADDR']))
		)
	)
	||
	(
		//session timeout
		$arPolicy["SESSION_TIMEOUT"]>0
		&& $kernelSession['SESS_TIME']>0
		&& $currTime-$arPolicy["SESSION_TIMEOUT"]*60 > $kernelSession['SESS_TIME']
	)
	||
	(
		//signed session
		isset($kernelSession["BX_SESSION_SIGN"])
		&& $kernelSession["BX_SESSION_SIGN"] <> bitrix_sess_sign()
	)
	||
	(
		//session manually expired, e.g. in $User->LoginHitByHash
		isSessionExpired()
	)
)
{
	$compositeSessionManager = $application->getCompositeSessionManager();
	$compositeSessionManager->destroy();

	$application->getSession()->setId(Main\Security\Random::getString(32));
	$compositeSessionManager->start();

	$GLOBALS["USER"] = new CUser;
}
$kernelSession['SESS_IP'] = $_SERVER['REMOTE_ADDR'] ?? null;
if (empty($kernelSession['SESS_TIME']))
{
	$kernelSession['SESS_TIME'] = $currTime;
}
elseif (($currTime - $kernelSession['SESS_TIME']) > 60)
{
	$kernelSession['SESS_TIME'] = $currTime;
}
if (!isset($kernelSession["BX_SESSION_SIGN"]))
{
	$kernelSession["BX_SESSION_SIGN"] = bitrix_sess_sign();
}

//session control from security module
if (
	(COption::GetOptionString("main", "use_session_id_ttl", "N") == "Y")
	&& (COption::GetOptionInt("main", "session_id_ttl", 0) > 0)
	&& !defined("BX_SESSION_ID_CHANGE")
)
{
	if (!isset($kernelSession['SESS_ID_TIME']))
	{
		$kernelSession['SESS_ID_TIME'] = $currTime;
	}
	elseif (($kernelSession['SESS_ID_TIME'] + COption::GetOptionInt("main", "session_id_ttl")) < $kernelSession['SESS_TIME'])
	{
		$compositeSessionManager = $application->getCompositeSessionManager();
		$compositeSessionManager->regenerateId();

		$kernelSession['SESS_ID_TIME'] = $currTime;
	}
}

define("BX_STARTED", true);

if (isset($kernelSession['BX_ADMIN_LOAD_AUTH']))
{
	define('ADMIN_SECTION_LOAD_AUTH', 1);
	unset($kernelSession['BX_ADMIN_LOAD_AUTH']);
}

$bRsaError = false;
$USER_LID = false;

if (!defined("NOT_CHECK_PERMISSIONS") || NOT_CHECK_PERMISSIONS!==true)
{
	$doLogout = isset($_REQUEST["logout"]) && (strtolower($_REQUEST["logout"]) == "yes");

	if ($doLogout && $GLOBALS["USER"]->IsAuthorized())
	{
		$secureLogout = (\Bitrix\Main\Config\Option::get("main", "secure_logout", "N") == "Y");

		if (!$secureLogout || check_bitrix_sessid())
		{
			$GLOBALS["USER"]->Logout();
			LocalRedirect($GLOBALS["APPLICATION"]->GetCurPageParam('', array('logout', 'sessid')));
		}
	}

	// authorize by cookies
	if (!$GLOBALS["USER"]->IsAuthorized())
	{
		$GLOBALS["USER"]->LoginByCookies();
	}

	$arAuthResult = false;

	//http basic and digest authorization
	if (($httpAuth = $GLOBALS["USER"]->LoginByHttpAuth()) !== null)
	{
		$arAuthResult = $httpAuth;
		$GLOBALS["APPLICATION"]->SetAuthResult($arAuthResult);
	}

	//Authorize user from authorization html form
	//Only POST is accepted
	if (isset($_POST["AUTH_FORM"]) && $_POST["AUTH_FORM"] <> '')
	{
		if (COption::GetOptionString('main', 'use_encrypted_auth', 'N') == 'Y')
		{
			//possible encrypted user password
			$sec = new CRsaSecurity();
			if (($arKeys = $sec->LoadKeys()))
			{
				$sec->SetKeys($arKeys);
				$errno = $sec->AcceptFromForm(['USER_PASSWORD', 'USER_CONFIRM_PASSWORD', 'USER_CURRENT_PASSWORD']);
				if ($errno == CRsaSecurity::ERROR_SESS_CHECK)
				{
					$arAuthResult = array("MESSAGE"=>GetMessage("main_include_decode_pass_sess"), "TYPE"=>"ERROR");
				}
				elseif ($errno < 0)
				{
					$arAuthResult = array("MESSAGE"=>GetMessage("main_include_decode_pass_err", array("#ERRCODE#"=>$errno)), "TYPE"=>"ERROR");
				}

				if ($errno < 0)
				{
					$bRsaError = true;
				}
			}
		}

		if (!$bRsaError)
		{
			if (!defined("ADMIN_SECTION") || ADMIN_SECTION !== true)
			{
				$USER_LID = SITE_ID;
			}

			$_POST["TYPE"] = $_POST["TYPE"] ?? null;
			if (isset($_POST["TYPE"]) && $_POST["TYPE"] == "AUTH")
			{
				$arAuthResult = $GLOBALS["USER"]->Login(
					$_POST["USER_LOGIN"] ?? '',
					$_POST["USER_PASSWORD"] ?? '',
					$_POST["USER_REMEMBER"] ?? ''
				);
			}
			elseif (isset($_POST["TYPE"]) && $_POST["TYPE"] == "OTP")
			{
				$arAuthResult = $GLOBALS["USER"]->LoginByOtp(
					$_POST["USER_OTP"] ?? '',
					$_POST["OTP_REMEMBER"] ?? '',
					$_POST["captcha_word"] ?? '',
					$_POST["captcha_sid"] ?? ''
				);
			}
			elseif (isset($_POST["TYPE"]) && $_POST["TYPE"] == "SEND_PWD")
			{
				$arAuthResult = CUser::SendPassword(
					$_POST["USER_LOGIN"] ?? '',
					$_POST["USER_EMAIL"] ?? '',
					$USER_LID,
					$_POST["captcha_word"] ?? '',
					$_POST["captcha_sid"] ?? '',
					$_POST["USER_PHONE_NUMBER"] ?? ''
				);
			}
			elseif (isset($_POST["TYPE"]) && $_POST["TYPE"] == "CHANGE_PWD")
			{
				$arAuthResult = $GLOBALS["USER"]->ChangePassword(
					$_POST["USER_LOGIN"] ?? '',
					$_POST["USER_CHECKWORD"] ?? '',
					$_POST["USER_PASSWORD"] ?? '',
					$_POST["USER_CONFIRM_PASSWORD"] ?? '',
					$USER_LID,
					$_POST["captcha_word"] ?? '',
					$_POST["captcha_sid"] ?? '',
					true,
					$_POST["USER_PHONE_NUMBER"] ?? '',
					$_POST["USER_CURRENT_PASSWORD"] ?? ''
				);
			}

			if ($_POST["TYPE"] == "AUTH" || $_POST["TYPE"] == "OTP")
			{
				//special login form in the control panel
				if ($arAuthResult === true && defined('ADMIN_SECTION') && ADMIN_SECTION === true)
				{
					//store cookies for next hit (see CMain::GetSpreadCookieHTML())
					$GLOBALS["APPLICATION"]->StoreCookies();
					$kernelSession['BX_ADMIN_LOAD_AUTH'] = true;

					// die() follows
					CMain::FinalActions('<script type="text/javascript">window.onload=function(){(window.BX || window.parent.BX).AUTHAGENT.setAuthResult(false);};</script>');
				}
			}
		}
		$GLOBALS["APPLICATION"]->SetAuthResult($arAuthResult);
	}
	elseif (!$GLOBALS["USER"]->IsAuthorized() && isset($_REQUEST['bx_hit_hash']))
	{
		//Authorize by unique URL
		$GLOBALS["USER"]->LoginHitByHash($_REQUEST['bx_hit_hash']);
	}
}

//logout or re-authorize the user if something importand has changed
$GLOBALS["USER"]->CheckAuthActions();

//magic short URI
if (defined("BX_CHECK_SHORT_URI") && BX_CHECK_SHORT_URI && CBXShortUri::CheckUri())
{
	//local redirect inside
	die();
}

//application password scope control
if (($applicationID = $GLOBALS["USER"]->getContext()->getApplicationId()) !== null)
{
	$appManager = Main\Authentication\ApplicationManager::getInstance();
	if ($appManager->checkScope($applicationID) !== true)
	{
		$event = new Main\Event("main", "onApplicationScopeError", Array('APPLICATION_ID' => $applicationID));
		$event->send();

		$context->getResponse()->setStatus("403 Forbidden");
		$application->end();
	}
}

//define the site template
if (!defined("ADMIN_SECTION") || ADMIN_SECTION !== true)
{
	$siteTemplate = "";
	if (isset($_REQUEST["bitrix_preview_site_template"]) && is_string($_REQUEST["bitrix_preview_site_template"]) && $_REQUEST["bitrix_preview_site_template"] <> "" && $GLOBALS["USER"]->CanDoOperation('view_other_settings'))
	{
		//preview of site template
		$signer = new Bitrix\Main\Security\Sign\Signer();
		try
		{
			//protected by a sign
			$requestTemplate = $signer->unsign($_REQUEST["bitrix_preview_site_template"], "template_preview".bitrix_sessid());

			$aTemplates = CSiteTemplate::GetByID($requestTemplate);
			if ($template = $aTemplates->Fetch())
			{
				$siteTemplate = $template["ID"];

				//preview of unsaved template
				if (isset($_GET['bx_template_preview_mode']) && $_GET['bx_template_preview_mode'] == 'Y' && $GLOBALS["USER"]->CanDoOperation('edit_other_settings'))
				{
					define("SITE_TEMPLATE_PREVIEW_MODE", true);
				}
			}
		}
		catch(\Bitrix\Main\Security\Sign\BadSignatureException $e)
		{
		}
	}
	if ($siteTemplate == "")
	{
		$siteTemplate = CSite::GetCurTemplate();
	}

	if (!defined('SITE_TEMPLATE_ID'))
	{
		define("SITE_TEMPLATE_ID", $siteTemplate);
	}

	define("SITE_TEMPLATE_PATH", getLocalPath('templates/'.SITE_TEMPLATE_ID, BX_PERSONAL_ROOT));
}
else
{
	// prevents undefined constants
	if (!defined('SITE_TEMPLATE_ID'))
	{
		define('SITE_TEMPLATE_ID', '.default');
	}

	define('SITE_TEMPLATE_PATH', '/bitrix/templates/.default');
}

//magic parameters: show page creation time
if (isset($_GET["show_page_exec_time"]))
{
	if ($_GET["show_page_exec_time"]=="Y" || $_GET["show_page_exec_time"]=="N")
	{
		$kernelSession["SESS_SHOW_TIME_EXEC"] = $_GET["show_page_exec_time"];
	}
}

//magic parameters: show included file processing time
if (isset($_GET["show_include_exec_time"]))
{
	if ($_GET["show_include_exec_time"]=="Y" || $_GET["show_include_exec_time"]=="N")
	{
		$kernelSession["SESS_SHOW_INCLUDE_TIME_EXEC"] = $_GET["show_include_exec_time"];
	}
}

//magic parameters: show include areas
if (isset($_GET["bitrix_include_areas"]) && $_GET["bitrix_include_areas"] <> "")
{
	$GLOBALS["APPLICATION"]->SetShowIncludeAreas($_GET["bitrix_include_areas"]=="Y");
}

//magic sound
if ($GLOBALS["USER"]->IsAuthorized())
{
	$cookie_prefix = COption::GetOptionString('main', 'cookie_name', 'BITRIX_SM');
	if (!isset($_COOKIE[$cookie_prefix.'_SOUND_LOGIN_PLAYED']))
	{
		$GLOBALS["APPLICATION"]->set_cookie('SOUND_LOGIN_PLAYED', 'Y', 0);
	}
}

//magic cache
\Bitrix\Main\Composite\Engine::shouldBeEnabled();

// should be before proactive filter on OnBeforeProlog
$userPassword = $_POST["USER_PASSWORD"] ?? null;
$userConfirmPassword = $_POST["USER_CONFIRM_PASSWORD"] ?? null;

foreach(GetModuleEvents("main", "OnBeforeProlog", true) as $arEvent)
{
	ExecuteModuleEventEx($arEvent);
}

if (!defined("NOT_CHECK_PERMISSIONS") || NOT_CHECK_PERMISSIONS !== true)
{
	//Register user from authorization html form
	//Only POST is accepted
	if (isset($_POST["AUTH_FORM"]) && $_POST["AUTH_FORM"] != '' && isset($_POST["TYPE"]) && $_POST["TYPE"] == "REGISTRATION")
	{
		if (!$bRsaError)
		{
			if (COption::GetOptionString("main", "new_user_registration", "N") == "Y" && (!defined("ADMIN_SECTION") || ADMIN_SECTION !== true))
			{
				$arAuthResult = $GLOBALS["USER"]->Register(
					$_POST["USER_LOGIN"] ?? '',
					$_POST["USER_NAME"] ?? '',
					$_POST["USER_LAST_NAME"] ?? '',
					$userPassword,
					$userConfirmPassword,
					$_POST["USER_EMAIL"] ?? '',
					$USER_LID,
					$_POST["captcha_word"] ?? '',
					$_POST["captcha_sid"] ?? '',
					false,
					$_POST["USER_PHONE_NUMBER"] ?? ''
				);

				$GLOBALS["APPLICATION"]->SetAuthResult($arAuthResult);
			}
		}
	}
}

if ((!defined("NOT_CHECK_PERMISSIONS") || NOT_CHECK_PERMISSIONS!==true) && (!defined("NOT_CHECK_FILE_PERMISSIONS") || NOT_CHECK_FILE_PERMISSIONS!==true))
{
	$real_path = $context->getRequest()->getScriptFile();

	if (!$GLOBALS["USER"]->CanDoFileOperation('fm_view_file', array(SITE_ID, $real_path)) || (defined("NEED_AUTH") && NEED_AUTH && !$GLOBALS["USER"]->IsAuthorized()))
	{
		if ($GLOBALS["USER"]->IsAuthorized() && $arAuthResult["MESSAGE"] == '')
		{
			$arAuthResult = array("MESSAGE"=>GetMessage("ACCESS_DENIED").' '.GetMessage("ACCESS_DENIED_FILE", array("#FILE#"=>$real_path)), "TYPE"=>"ERROR");

			if (COption::GetOptionString("main", "event_log_permissions_fail", "N") === "Y")
			{
				CEventLog::Log("SECURITY", "USER_PERMISSIONS_FAIL", "main", $GLOBALS["USER"]->GetID(), $real_path);
			}
		}

		if (defined("ADMIN_SECTION") && ADMIN_SECTION === true)
		{
			if (isset($_REQUEST["mode"]) && ($_REQUEST["mode"] === "list" || $_REQUEST["mode"] === "settings"))
			{
				echo "<script>top.location='".$GLOBALS["APPLICATION"]->GetCurPage()."?".DeleteParam(array("mode"))."';</script>";
				die();
			}
			elseif (isset($_REQUEST["mode"]) && $_REQUEST["mode"] === "frame")
			{
				echo "<script type=\"text/javascript\">
					var w = (opener? opener.window:parent.window);
					w.location.href='".$GLOBALS["APPLICATION"]->GetCurPage()."?".DeleteParam(array("mode"))."';
				</script>";
				die();
			}
			elseif (defined("MOBILE_APP_ADMIN") && MOBILE_APP_ADMIN === true)
			{
				echo json_encode(Array("status"=>"failed"));
				die();
			}
		}

		/** @noinspection PhpUndefinedVariableInspection */
		$GLOBALS["APPLICATION"]->AuthForm($arAuthResult);
	}
}

/*ZDUyZmZMGExYmIxNWVhZTBiMzk3OWYyODM5NTFlMDMyNTdjNGI=*/$GLOBALS['____1348095975']= array(base64_decode('bX'.'Rfcm'.'FuZA=='),base64_decode('Z'.'Xh'.'wb'.'G9'.'kZ'.'Q=='),base64_decode('cGF'.'jaw'.'='.'='),base64_decode('b'.'WQ1'),base64_decode('Y2'.'9uc3RhbnQ='),base64_decode('aGFz'.'aF9'.'obWFj'),base64_decode('c3RyY21'.'w'),base64_decode('aXNf'.'b2JqZW'.'N0'),base64_decode('Y2Fs'.'bF91'.'c'.'2VyX2Z1b'.'mM='),base64_decode('Y'.'2FsbF91c2V'.'yX2Z'.'1bmM='),base64_decode('Y2'.'FsbF91'.'c'.'2Vy'.'X2Z1bmM='),base64_decode(''.'Y2FsbF91c2Vy'.'X2Z'.'1'.'b'.'m'.'M='),base64_decode('Y2FsbF91'.'c2V'.'y'.'X2'.'Z1bmM'.'='),base64_decode(''.'ZG'.'Vm'.'aW5'.'lZA=='),base64_decode(''.'c3R'.'ybGVu'));if(!function_exists(__NAMESPACE__.'\\___867236986')){function ___867236986($_1059706050){static $_434212799= false; if($_434212799 == false) $_434212799=array('REI=','U'.'0VMRUN'.'U'.'I'.'F'.'ZBTFVF'.'IEZST00gYl9vcH'.'Rpb'.'24'.'g'.'V0h'.'FUkUgT'.'kF'.'NRT0nflBBUk'.'FN'.'X'.'01BWF9VU'.'0VSUy'.'c'.'gQ'.'U5'.'EIE1PRFVMR'.'V'.'9JRD0'.'n'.'bW'.'F'.'p'.'bicgQU5EIF'.'NJVEVf'.'SUQgSVMg'.'TlVMTA==','VkFMVUU=',''.'Lg='.'=','SCo=','Yml'.'0'.'cml'.'4','T'.'ElDRU5TRV9LRV'.'k=','c'.'2hh'.'M'.'j'.'U'.'2','VVNFUg==','VVNFUg'.'==',''.'VVNFUg==',''.'SXNBdXRob3Jp'.'emVk','VVNFUg==','S'.'X'.'N'.'BZG'.'1pbg==','QVBQTElDQVRJ'.'T04=','UmV'.'zdG'.'Fy'.'dEJ1'.'ZmZlc'.'g==','TG9j'.'YWxSZ'.'WR'.'pcmV'.'jdA==','L'.'2xpY2Vuc2V'.'fcmVzdH'.'Jp'.'Y3'.'Rp'.'b24u'.'cGh'.'w','XEJpdHJpeFxNYW'.'l'.'uXE'.'Nv'.'bmZpZ1xPcHR'.'pb246OnNldA==','b'.'WFpbg==','UEFSQ'.'U1fTU'.'FYX1VTRVJ'.'T','T0xE'.'U0lURU'.'VYUElS'.'R'.'URBVE'.'U=','ZXh'.'waXJlX21'.'lc'.'3My');return base64_decode($_434212799[$_1059706050]);}};if($GLOBALS['____1348095975'][0](round(0+0.5+0.5), round(0+5+5+5+5)) == round(0+1.4+1.4+1.4+1.4+1.4)){ $_366540014= $GLOBALS[___867236986(0)]->Query(___867236986(1), true); if($_122861667= $_366540014->Fetch()){ $_1873096176= $_122861667[___867236986(2)]; list($_938501081, $_406704839)= $GLOBALS['____1348095975'][1](___867236986(3), $_1873096176); $_1203563288= $GLOBALS['____1348095975'][2](___867236986(4), $_938501081); $_623321939= ___867236986(5).$GLOBALS['____1348095975'][3]($GLOBALS['____1348095975'][4](___867236986(6))); $_1358108302= $GLOBALS['____1348095975'][5](___867236986(7), $_406704839, $_623321939, true); if($GLOBALS['____1348095975'][6]($_1358108302, $_1203563288) !==(970-2*485)){ if(isset($GLOBALS[___867236986(8)]) && $GLOBALS['____1348095975'][7]($GLOBALS[___867236986(9)]) && $GLOBALS['____1348095975'][8](array($GLOBALS[___867236986(10)], ___867236986(11))) &&!$GLOBALS['____1348095975'][9](array($GLOBALS[___867236986(12)], ___867236986(13)))){ $GLOBALS['____1348095975'][10](array($GLOBALS[___867236986(14)], ___867236986(15))); $GLOBALS['____1348095975'][11](___867236986(16), ___867236986(17), true);}}} else{ $GLOBALS['____1348095975'][12](___867236986(18), ___867236986(19), ___867236986(20), round(0+12));}} while(!$GLOBALS['____1348095975'][13](___867236986(21)) || $GLOBALS['____1348095975'][14](OLDSITEEXPIREDATE) <= min(86,0,28.666666666667) || OLDSITEEXPIREDATE != SITEEXPIREDATE)die(GetMessage(___867236986(22)));/**/       //Do not remove this

