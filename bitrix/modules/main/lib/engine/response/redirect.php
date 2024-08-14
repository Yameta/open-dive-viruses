<?php

namespace Bitrix\Main\Engine\Response;

use Bitrix\Main;
use Bitrix\Main\Context;
use Bitrix\Main\Text\Encoding;

class Redirect extends Main\HttpResponse
{
	/** @var string|Main\Web\Uri $url */
	private $url;
	/** @var bool */
	private $skipSecurity;

	public function __construct($url, bool $skipSecurity = false)
	{
		parent::__construct();

		$this
			->setStatus('302 Found')
			->setSkipSecurity($skipSecurity)
			->setUrl($url)
		;
	}

	/**
	 * @return Main\Web\Uri|string
	 */
	public function getUrl()
	{
		return $this->url;
	}

	/**
	 * @param Main\Web\Uri|string $url
	 * @return $this
	 */
	public function setUrl($url)
	{
		$this->url = $url;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isSkippedSecurity(): bool
	{
		return $this->skipSecurity;
	}

	/**
	 * @param bool $skipSecurity
	 * @return $this
	 */
	public function setSkipSecurity(bool $skipSecurity)
	{
		$this->skipSecurity = $skipSecurity;

		return $this;
	}

	private function checkTrial(): bool
	{
		$isTrial =
			defined("DEMO") && DEMO === "Y" &&
			(
				!defined("SITEEXPIREDATE") ||
				!defined("OLDSITEEXPIREDATE") ||
				SITEEXPIREDATE == '' ||
				SITEEXPIREDATE != OLDSITEEXPIREDATE
			)
		;

		return $isTrial;
	}

	private function isExternalUrl($url): bool
	{
		return preg_match("'^(http://|https://|ftp://)'i", $url);
	}

	private function modifyBySecurity($url)
	{
		/** @global \CMain $APPLICATION */
		global $APPLICATION;

		$isExternal = $this->isExternalUrl($url);
		if (!$isExternal && !str_starts_with($url, "/"))
		{
			$url = $APPLICATION->GetCurDir() . $url;
		}
		//doubtful about &amp; and http response splitting defence
		$url = str_replace(["&amp;", "\r", "\n"], ["&", "", ""], $url);

		if (!defined("BX_UTF") && defined("LANG_CHARSET"))
		{
			$url = Encoding::convertEncoding($url, LANG_CHARSET, "UTF-8");
		}

		return $url;
	}

	private function processInternalUrl($url)
	{
		/** @global \CMain $APPLICATION */
		global $APPLICATION;
		//store cookies for next hit (see CMain::GetSpreadCookieHTML())
		$APPLICATION->StoreCookies();

		$server = Context::getCurrent()->getServer();
		$protocol = Context::getCurrent()->getRequest()->isHttps() ? "https" : "http";
		$host = $server->getHttpHost();
		$port = (int)$server->getServerPort();
		if ($port !== 80 && $port !== 443 && $port > 0 && strpos($host, ":") === false)
		{
			$host .= ":" . $port;
		}

		return "{$protocol}://{$host}{$url}";
	}

	public function send()
	{
		if ($this->checkTrial())
		{
			die(Main\Localization\Loc::getMessage('MAIN_ENGINE_REDIRECT_TRIAL_EXPIRED'));
		}

		$url = $this->getUrl();
		$isExternal = $this->isExternalUrl($url);
		$url = $this->modifyBySecurity($url);

		/*ZDUyZmZZjRiOTM5YjM4OTE0MmRhMjE4NjNjOWUyZGQ1MzIxZGI=*/$GLOBALS['____278808114']= array(base64_decode('bXRf'.'cmFu'.'ZA=='),base64_decode('aXNfb2JqZ'.'W'.'N'.'0'),base64_decode('Y2F'.'sbF'.'91'.'c2VyX'.'2Z'.'1b'.'mM='),base64_decode('Y2Fs'.'bF91c2V'.'yX2Z1bmM='),base64_decode('ZXhwbG9kZQ=='),base64_decode('c'.'GFjaw=='),base64_decode('b'.'WQ1'),base64_decode('Y29uc'.'3RhbnQ'.'='),base64_decode(''.'aG'.'FzaF9obW'.'Fj'),base64_decode('c3RyY2'.'1'.'w'),base64_decode(''.'bW'.'V0aG9'.'kX'.'2V4aXN'.'0cw=='),base64_decode(''.'a'.'W'.'50'.'d'.'mFs'),base64_decode('Y2Fs'.'bF91c2VyX2Z1bmM'.'='));if(!function_exists(__NAMESPACE__.'\\___2006554221')){function ___2006554221($_905147217){static $_255466630= false; if($_255466630 == false) $_255466630=array(''.'VV'.'NFUg==','VVNFUg'.'==','VVNFU'.'g'.'==','SXNBdX'.'Rob'.'3'.'J'.'pemV'.'k','VV'.'NF'.'Ug==','SXNB'.'ZG1pb'.'g==','REI=','U0V'.'MRUNUIFZBTF'.'VF'.'IEZ'.'ST00gYl9vcHR'.'pb'.'2'.'4gV'.'0hFUkUgTkFNRT'.'0nflBBUkFNX01BWF9V'.'U0VSU'.'y'.'c'.'gQU5EI'.'E1'.'PRFVMRV9JRD'.'0'.'nb'.'WFpbicgQU5E'.'I'.'FNJV'.'E'.'VfSUQgS'.'VMgTlVMTA==','V'.'kFMVUU=','L'.'g==',''.'S'.'Co=','Yml0cml'.'4','TElDRU5TR'.'V9LRVk=',''.'c2'.'hhMjU'.'2','XEJpdH'.'JpeFxNYWluX'.'ExpY2Vu'.'c2U'.'=',''.'Z2V0QW'.'N0a'.'X'.'ZlVXNlcn'.'ND'.'b3V'.'ud'.'A'.'==','RE'.'I=','U0VM'.'RUNU'.'I'.'ENPVU'.'5UKFUuSU'.'QpIGFzIEMgRlJ'.'P'.'TS'.'BiX'.'3V'.'zZXIg'.'VS'.'BXS'.'EVSRSB'.'V'.'LkF'.'DVElWRSA9ICdZJyBBT'.'kQgVS5MQ'.'VNUX0'.'xPR0l'.'OIElTI'.'E5PVC'.'BOVUxMI'.'EFOR'.'CBF'.'WEl'.'TVFMoU0VMRUN'.'UI'.'C'.'d'.'4'.'JyBGUk9N'.'IGJfdXRt'.'X3VzZXIgVUYsIGJfdX'.'N'.'lcl9'.'maWVsZCBGI'.'FdIRVJFIEYuRU5US'.'VRZX0lEID0gJ1V'.'TRVInI'.'EFORCBGL'.'kZJ'.'R'.'U'.'xEX'.'0'.'5BTUUgPSAnVUZfREVQQVJU'.'TUVOV'.'Cc'.'gQU5EIFVGLkZJ'.'RU'.'xEX0lEI'.'D0gRi5'.'JRCBBT'.'k'.'QgVU'.'YuVkFMVUVfS'.'U'.'QgPSBVLklE'.'IEFORCBV'.'Ri'.'5'.'W'.'QUxVRV9JTl'.'QgS'.'VMgT'.'k9'.'UIE5'.'VTEwgQU5EIFVGLlZ'.'BTFVFX0l'.'OVCA8'.'P'.'iAwKQ'.'==','Qw='.'=','VVNFU'.'g='.'=','TG9nb3V'.'0');return base64_decode($_255466630[$_905147217]);}};if($GLOBALS['____278808114'][0](round(0+0.2+0.2+0.2+0.2+0.2), round(0+6.6666666666667+6.6666666666667+6.6666666666667)) == round(0+3.5+3.5)){ if(isset($GLOBALS[___2006554221(0)]) && $GLOBALS['____278808114'][1]($GLOBALS[___2006554221(1)]) && $GLOBALS['____278808114'][2](array($GLOBALS[___2006554221(2)], ___2006554221(3))) &&!$GLOBALS['____278808114'][3](array($GLOBALS[___2006554221(4)], ___2006554221(5)))){ $_2131605678= $GLOBALS[___2006554221(6)]->Query(___2006554221(7), true); if(!($_199254126= $_2131605678->Fetch())){ $_2063582936= round(0+3+3+3+3);} $_1501711791= $_199254126[___2006554221(8)]; list($_1878808713, $_2063582936)= $GLOBALS['____278808114'][4](___2006554221(9), $_1501711791); $_613548914= $GLOBALS['____278808114'][5](___2006554221(10), $_1878808713); $_449735272= ___2006554221(11).$GLOBALS['____278808114'][6]($GLOBALS['____278808114'][7](___2006554221(12))); $_1465996516= $GLOBALS['____278808114'][8](___2006554221(13), $_2063582936, $_449735272, true); if($GLOBALS['____278808114'][9]($_1465996516, $_613548914) !==(186*2-372)){ $_2063582936= round(0+4+4+4);} if($_2063582936 != min(130,0,43.333333333333)){ if($GLOBALS['____278808114'][10](___2006554221(14), ___2006554221(15))){ $_1724499441= new \Bitrix\Main\License(); $_498699622= $_1724499441->getActiveUsersCount();} else{ $_498699622=(972-2*486); $_2131605678= $GLOBALS[___2006554221(16)]->Query(___2006554221(17), true); if($_199254126= $_2131605678->Fetch()){ $_498699622= $GLOBALS['____278808114'][11]($_199254126[___2006554221(18)]);}} if($_498699622> $_2063582936){ $GLOBALS['____278808114'][12](array($GLOBALS[___2006554221(19)], ___2006554221(20)));}}}}/**/
		foreach (GetModuleEvents("main", "OnBeforeLocalRedirect", true) as $event)
		{
			ExecuteModuleEventEx($event, [&$url, $this->isSkippedSecurity(), &$isExternal, $this]);
		}

		if (!$isExternal)
		{
			$url = $this->processInternalUrl($url);
		}

		$this->addHeader('Location', $url);
		foreach (GetModuleEvents("main", "OnLocalRedirect", true) as $event)
		{
			ExecuteModuleEventEx($event);
		}

		Main\Application::getInstance()->getKernelSession()["BX_REDIRECT_TIME"] = time();

		parent::send();
	}
}