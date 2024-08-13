<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!IsModuleInstalled("search")) {
	ShowError(GetMessage("CC_BST_MODULE_NOT_INSTALLED"));
	return;
}
//include_once("CsearchCustom.php");

if(!isset($arParams["PAGE"]) || strlen($arParams["PAGE"])<=0)
	$arParams["PAGE"] = "#SITE_DIR#search/index.php";

$arResult["CATEGORIES"] = array();

$query = ltrim($_POST["q"]);

if(!function_exists("switcher_ru"))
{
	function switcher_ru($value)
	{
		$converter = array(
			'f' => 'а',	',' => 'б',	'd' => 'в',	'u' => 'г',	'l' => 'д',	't' => 'е',	'`' => 'ё',
			';' => 'ж',	'p' => 'з',	'b' => 'и',	'q' => 'й',	'r' => 'к',	'k' => 'л',	'v' => 'м',
			'y' => 'н',	'j' => 'о',	'g' => 'п',	'h' => 'р',	'c' => 'с',	'n' => 'т',	'e' => 'у',
			'a' => 'ф',	'[' => 'х',	'w' => 'ц',	'x' => 'ч',	'i' => 'ш',	'o' => 'щ',	'm' => 'ь',
			's' => 'ы',	']' => 'ъ',	"'" => "э",	'.' => 'ю',	'z' => 'я',					
	 
			'F' => 'А',	'<' => 'Б',	'D' => 'В',	'U' => 'Г',	'L' => 'Д',	'T' => 'Е',	'~' => 'Ё',
			':' => 'Ж',	'P' => 'З',	'B' => 'И',	'Q' => 'Й',	'R' => 'К',	'K' => 'Л',	'V' => 'М',
			'Y' => 'Н',	'J' => 'О',	'G' => 'П',	'H' => 'Р',	'C' => 'С',	'N' => 'Т',	'E' => 'У',
			'A' => 'Ф',	'{' => 'Х',	'W' => 'Ц',	'X' => 'Ч',	'I' => 'Ш',	'O' => 'Щ',	'M' => 'Ь',
			'S' => 'Ы',	'}' => 'Ъ',	'"' => 'Э',	'>' => 'Ю',	'Z' => 'Я',					
	 
			'@' => '"',	'#' => '№',	'$' => ';',	'^' => ':',	'&' => '?',	'/' => '.',	'?' => ',',
		);
	 
		$value = strtr($value, $converter);
		return $value;
	}
}
if(!function_exists("switcher_en"))
{
	function switcher_en($value)
	{
		$converter = array(
			'а' => 'f',	'б' => ',',	'в' => 'd',	'г' => 'u',	'д' => 'l',	'е' => 't',	'ё' => '`',
			'ж' => ';',	'з' => 'p',	'и' => 'b',	'й' => 'q',	'к' => 'r',	'л' => 'k',	'м' => 'v',
			'н' => 'y',	'о' => 'j',	'п' => 'g',	'р' => 'h',	'с' => 'c',	'т' => 'n',	'у' => 'e',
			'ф' => 'a',	'х' => '[',	'ц' => 'w',	'ч' => 'x',	'ш' => 'i',	'щ' => 'o',	'ь' => 'm',
			'ы' => 's',	'ъ' => ']',	'э' => "'",	'ю' => '.',	'я' => 'z',
	 
			'А' => 'F',	'Б' => '<',	'В' => 'D',	'Г' => 'U',	'Д' => 'L',	'Е' => 'T',	'Ё' => '~',
			'Ж' => ':',	'З' => 'P',	'И' => 'B',	'Й' => 'Q',	'К' => 'R',	'Л' => 'K',	'М' => 'V',
			'Н' => 'Y',	'О' => 'J',	'П' => 'G',	'Р' => 'H',	'С' => 'C',	'Т' => 'N',	'У' => 'E',
			'Ф' => 'A',	'Х' => '{',	'Ц' => 'W',	'Ч' => 'X',	'Ш' => 'I',	'Щ' => 'O',	'Ь' => 'M',
			'Ы' => 'S',	'Ъ' => '}',	'Э' => '"',	'Ю' => '>',	'Я' => 'Z',
			
			'"' => '@',	'№' => '#',	';' => '$',	':' => '^',	'?' => '&',	'.' => '/',	',' => '?',
		);
	 
		$value = strtr($value, $converter);
		return $value;
	}
}

if(!empty($query) && $_REQUEST["ajax_call"] === "y" && (!isset($_REQUEST["INPUT_ID"]) || $_REQUEST["INPUT_ID"] == $arParams["INPUT_ID"]) && CModule::IncludeModule("search")) {
	CUtil::decodeURIComponent($query);



	$arResult["query"] = $query;
	$arResult["phrase"] = stemming_split($query, LANGUAGE_ID);

	$arParams["NUM_CATEGORIES"] = intval($arParams["NUM_CATEGORIES"]);
	if($arParams["NUM_CATEGORIES"] <= 0)
		$arParams["NUM_CATEGORIES"] = 1;

	$arParams["TOP_COUNT"] = intval($arParams["TOP_COUNT"]);
	if($arParams["TOP_COUNT"] <= 0)
		$arParams["TOP_COUNT"] = 5;

	if($arParams["ORDER"] == "date")
		$aSort = array("DATE_CHANGE" => "DESC", "CUSTOM_RANK" => "DESC", "RANK" => "DESC");
	else
		$aSort = array("CUSTOM_RANK" => "DESC", "RANK" => "DESC", "DATE_CHANGE" => "DESC");

	$arOthersFilter = array("LOGIC" => "OR");

	for($i = 0; $i < $arParams["NUM_CATEGORIES"]; $i++) {
		$category_title = trim($arParams["CATEGORY_".$i."_TITLE"]);
		if(empty($category_title)) {
			if(is_array($arParams["CATEGORY_".$i]))
				$category_title = implode(", ", $arParams["CATEGORY_".$i]);
			else
				$category_title = trim($arParams["CATEGORY_".$i]);
		}
		if(empty($category_title))
			continue;

		$arResult["CATEGORIES"][$i] = array(
			"TITLE" => htmlspecialcharsbx($category_title),
			"ITEMS" => array()
		);

		$exFILTER = array(
			0 => CSearchParameters::ConvertParamsToFilter($arParams, "CATEGORY_".$i),
		);
		$exFILTER[0]["LOGIC"] = "OR";
		//$exFILTER[0]["STEMMING"] = true;

		if($arParams["CHECK_DATES"] === "Y")
			$exFILTER["CHECK_DATES"] = "Y";

		$arOthersFilter[] = $exFILTER;

		$j = 0;
		$obTitle = new CSearch;
		//$str_query = $arResult["alt_query"] ? $arResult["alt_query"] : $arResult["query"];
		/*if($arResult["alt_query"])
			$str_query = "'".$arResult["alt_query"] ."' | '" . $arResult["query"]."'";
		else*/
		$str_query = "'".$arResult["query"]."'";
		 
		$obTitle->Search(array("QUERY" => $str_query), $aSort, $exFILTER);
		global $APPLICATION;
		$APPLICATION->restartBuffer();

		$obTitle->NavStart($arParams["TOP_COUNT"], false);
		$altsearch = true;
		while($ar = $obTitle->Fetch()) {			
			$j++;
			if($j > $arParams["TOP_COUNT"]) {
				$params = array("q" => $arResult["query"]);
				//$params = array("q" => $arResult["alt_query"]? $arResult["alt_query"]: $arResult["query"]);

				$url = CHTTP::urlAddParams(
					str_replace("#SITE_DIR#", SITE_DIR, $arParams["PAGE"])
					,$params
					,array("encode"=>true)
				).CSearchTitle::MakeFilterUrl("f", $exFILTER);

				$arResult["CATEGORIES"][$i]["ITEMS"][] = array(
					"NAME" => GetMessage("CC_BST_MORE"),
					"URL" => htmlspecialcharsex($url),
				);
				break;
			} else {
				$altsearch = false;
				$arResult["CATEGORIES"][$i]["ITEMS"][] = array(
					"NAME" => $ar["TITLE"],
					"URL" => htmlspecialcharsbx($ar["URL"]),
					"MODULE_ID" => $ar["MODULE_ID"],
					"PARAM1" => $ar["PARAM1"],
					"PARAM2" => $ar["PARAM2"],
					"ITEM_ID" => $ar["ITEM_ID"],
				);
			}
		}
		
		
		if($arParams["USE_LANGUAGE_GUESS"] !== "N" && $altsearch)
		{
			$lengQuery = switcher_ru($query);
			if($lengQuery != $query)
				$arResult["query"] = $lengQuery;
			else
				$arResult["query"] = switcher_en($query);

			$str_query = "'".$arResult["query"]."'";
			$obTitle->Search(array("QUERY" => $str_query), $aSort, $exFILTER);
			while($ar = $obTitle->Fetch()) {			
				$j++;
				if($j > $arParams["TOP_COUNT"]) {
					$params = array("q" => $arResult["query"]);

					$url = CHTTP::urlAddParams(
						str_replace("#SITE_DIR#", SITE_DIR, $arParams["PAGE"])
						,$params
						,array("encode"=>true)
					).CSearchTitle::MakeFilterUrl("f", $exFILTER);

					$arResult["CATEGORIES"][$i]["ITEMS"][] = array(
						"NAME" => GetMessage("CC_BST_MORE"),
						"URL" => htmlspecialcharsex($url),
					);
					break;
				} else {
					$altsearch = false;
					$arResult["CATEGORIES"][$i]["ITEMS"][] = array(
						"NAME" => $ar["TITLE"],
						"URL" => htmlspecialcharsbx($ar["URL"]),
						"MODULE_ID" => $ar["MODULE_ID"],
						"PARAM1" => $ar["PARAM1"],
						"PARAM2" => $ar["PARAM2"],
						"ITEM_ID" => $ar["ITEM_ID"],
					);
				}
			}		
			if($altsearch)
			{
				$lengQuery = switcher_en($query);
				if($lengQuery != $query)
					$arResult["query"] = $lengQuery;
				else
					$arResult["query"] = switcher_ru($query);

				$str_query = "'".$arResult["query"]."'";
				$obTitle->Search(array("QUERY" => $str_query), $aSort, $exFILTER);
				while($ar = $obTitle->Fetch()) {			
					$j++;
					if($j > $arParams["TOP_COUNT"]) {
						$params = array("q" => $arResult["query"]);

						$url = CHTTP::urlAddParams(
							str_replace("#SITE_DIR#", SITE_DIR, $arParams["PAGE"])
							,$params
							,array("encode"=>true)
						).CSearchTitle::MakeFilterUrl("f", $exFILTER);

						$arResult["CATEGORIES"][$i]["ITEMS"][] = array(
							"NAME" => GetMessage("CC_BST_MORE"),
							"URL" => htmlspecialcharsex($url),
						);
						break;
					} else {
						$altsearch = false;
						$arResult["CATEGORIES"][$i]["ITEMS"][] = array(
							"NAME" => $ar["TITLE"],
							"URL" => htmlspecialcharsbx($ar["URL"]),
							"MODULE_ID" => $ar["MODULE_ID"],
							"PARAM1" => $ar["PARAM1"],
							"PARAM2" => $ar["PARAM2"],
							"ITEM_ID" => $ar["ITEM_ID"],
						);
					}
				}		
			}
		}
		
		if(!$j) {
			unset($arResult["CATEGORIES"][$i]);
		}
	}

	if($arParams["SHOW_OTHERS"] === "Y") {
		$arResult["CATEGORIES"]["others"] = array(
			"TITLE" => htmlspecialcharsbx($arParams["CATEGORY_OTHERS_TITLE"]),
			"ITEMS" => array(),
		);

		$j = 0;
		$obTitle = new CSearch;
		$str_other_query = $arResult["alt_query"] ? $arResult["alt_query"] : $arResult["query"];
		$obTitle->Search(array("QUERY" => $str_other_query), $aSort, $arOthersFilter);
		
		while($ar = $obTitle->Fetch()) {			
			$j++;
			if($j > $arParams["TOP_COUNT"]) {
				//it's really hard to make it working
				break;
			} elseif(!empty($ar["NAME"])) {
				$arResult["CATEGORIES"]["others"]["ITEMS"][] = array(
					"NAME" => $ar["NAME"],
					"URL" => htmlspecialcharsbx($ar["URL"]),
					"MODULE_ID" => $ar["MODULE_ID"],
					"PARAM1" => $ar["PARAM1"],
					"PARAM2" => $ar["PARAM2"],
					"ITEM_ID" => $ar["ITEM_ID"],
				);
			}
		}		

		if(!$j) {
			unset($arResult["CATEGORIES"]["others"]);
		}
	}

	if(!empty($arResult["CATEGORIES"])) {
		$arResult["CATEGORIES"]["all"] = array(
			"TITLE" => "",
			"ITEMS" => array()
		);

		$params = array(
			"q" => $arResult["alt_query"]? $arResult["alt_query"]: $arResult["query"],
		);
		$url = CHTTP::urlAddParams(
			str_replace("#SITE_DIR#", SITE_DIR, $arParams["PAGE"])
			,$params
			,array("encode"=>true)
		);
		$arResult["CATEGORIES"]["all"]["ITEMS"][] = array(
			"NAME" => GetMessage("CC_BST_ALL_RESULTS"),
			"URL" => $url,
		);
		/*
		if($arResult["alt_query"] != "")
		{
			$params = array(
				"q" => $arResult["query"],
				"spell" => 1,
			);

			$url = CHTTP::urlAddParams(
				str_replace("#SITE_DIR#", SITE_DIR, $arParams["PAGE"])
				,$params
				,array("encode"=>true)
			);

			$arResult["CATEGORIES"]["all"]["ITEMS"][] = array(
				"NAME" => GetMessage("CC_BST_ALL_QUERY_PROMPT", array("#query#"=>$arResult["query"])),
				"URL" => htmlspecialcharsex($url),
			);
		}
		*/
	}
}

$arResult["FORM_ACTION"] = htmlspecialcharsbx(str_replace("#SITE_DIR#", SITE_DIR, $arParams["PAGE"]));

if($_REQUEST["ajax_call"] === "y" && (!isset($_REQUEST["INPUT_ID"]) || $_REQUEST["INPUT_ID"] == $arParams["INPUT_ID"])) {
	$APPLICATION->RestartBuffer();

	if(!empty($query))
		$this->IncludeComponentTemplate('ajax');
	require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_after.php");
	die();
} else {
	$APPLICATION->AddHeadScript($this->GetPath().'/script.js');
	CUtil::InitJSCore(array('ajax'));
	$this->IncludeComponentTemplate();
}?>