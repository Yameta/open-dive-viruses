<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc; 
use Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__); 
global $APPLICATION;

if (!Loader::includeModule("scoder.collections"))
	return;

// проверку значений фильтра для удобства вынесем в отдельную функцию
function CheckFilter()
{
	global $FilterArr, $lAdmin;
	foreach ($FilterArr as $f) global $$f;

	return count($lAdmin->arFilterErrors)==0; // если ошибки есть, вернем false;
}

// получим права доступа текущего пользовател¤ на модуль
$saleModulePermissions = $APPLICATION->GetGroupRight('scoder.collections');
$readOnly = ($saleModulePermissions < 'W');
// если нет прав - отправим к форме авторизации с сообщением об ошибке
if ($saleModulePermissions < "R")
	$APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));

$sTableID = "scoder_collections_collections"; // ID таблицы
$oSort = new CAdminSorting($sTableID, "ID", "DESC"); 		//объект сортировки
$lAdmin = new CAdminList($sTableID, $oSort); 				//основной объект списка
if (!$readOnly && ($listID = $lAdmin->GroupAction()))
{
	$action = $_REQUEST['action'];
	if (!empty($_REQUEST['action_button']))
		$action = $_REQUEST['action_button'];
	
	$listID = array_filter($listID);
	
	if (!empty($listID))
	{
		switch ($action)
		{
			case 'delete':
				foreach ($listID as &$eventID)
				{
					CScoderCollectionsApi::Delete($eventID);
				}
				unset($eventID);
				
				break;
		}
	}
	unset($discountList, $action, $listID);
}

// опишем элементы фильтра
$FilterArr = Array(
	"find",
	"find_type_field",
);

// инициализируем фильтр
$lAdmin->InitFilter($FilterArr);

$arFilter = Array();

$res = CIBlock::GetList(
	Array("SORT" => "ASC"), 
	Array(), 
	false
);
while($ar_res = $res->Fetch())
{
	$ar_iblocks_info[$ar_res["ID"]] = $ar_res;
}

// если все значени¤ фильтра корректны, обработаем его
if (CheckFilter())
{
	if (strlen($find) > 0 && strlen($find_type_field) > 0) {
		switch ($find_type_field) {
			case 'ID':
				$arFilter['ID'] = $find;
				break;
			default:
		}
	}
	
	if (strlen($find_type)>0 && $find_type!="NOT_REF") {
		$arFilter["TYPE"] = Trim($find_type);
	}
	
	// get rid of empty filter fields
	foreach ($arFilter as $key => $value) {
		if (is_array($value)) {
			foreach ($value as $key2 => $value2) {
				if (strlen(trim($value2)) == 0) {
					unset($value[$key2]);
				}
			}
			if (count($value) <= 0) {
				unset($arFilter[$key]);
			}
		} else {
			if (strlen(trim($value)) == 0) {
				unset($arFilter[$key]);
			}
		}
	}
}

$arSort = Array();
$arSortBy = explode('|', $by);
foreach ($arSortBy as $sBy) {
	$arSort[$sBy] = $order;
}

$rsData = CScoderCollectionsApi::GetList(
	$arSort,
	$arFilter,
	false,
	false,
	Array()
);

// преобразуем список в экземпл¤р класса CAdminResult
$rsData = new CAdminResult($rsData, $sTableID);

// аналогично CDBResult инициализируем постраничную навигацию.
$rsData->NavStart();

// отправим вывод переключател¤ страниц в основной объект $lAdmin
$lAdmin->NavText($rsData->GetNavPrint(Loc::getMessage("SCODER_COLLECTIONS_NAV")));

$arAdminListHeaders = array(
	array(
		"id"    =>"ID",
		"content"  =>"ID",
		"sort"    => "ID",
		"align"    =>"right",
		"default"  =>true,
	),
	array(
		"id"    =>"SECTION_ID",
		"content"  => Loc::getMessage("SCODER_COLLECTIONS_SECTION_ID"),
		"sort"    => "SECTION_ID",
		"align"    =>"left",
		"default"  =>true,
	),
	array(
		"id"    =>"IBLOCK_ID",
		"content"  => Loc::getMessage("SCODER_COLLECTIONS_IBLOCK_ID"),
		"sort"    => "IBLOCK_ID",
		"align"    =>"left",
		"default"  =>true,
	),	
	array(
		"id"    =>"TYPE_ID",
		"content"  =>Loc::getMessage("SCODER_COLLECTIONS_TYPE_ID"),
		"sort"    => "TYPE_ID",
		"align"    =>"left",
		"default"  =>true,
	),
	array(
		"id"    =>"CHECK_PARENT",
		"content"  =>Loc::getMessage("SCODER_COLLECTIONS_CHECK_PARENT"),
		"sort"    => "CHECK_PARENT",
		"align"    =>"left",
		"default"  =>true,
	),
	array(
		"id"    =>"CATALOG_AVAILABLE",
		"content"  => Loc::getMessage("SCODER_COLLECTIONS_CATALOG_AVAILABLE"),
		"sort"    => "CATALOG_AVAILABLE",
		"align"    =>"left",
		"default"  =>true,
	),
	array(
		"id"    =>"IS_SECTION_ACTIVE_UPDATE",
		"content"  =>Loc::getMessage("SCODER_COLLECTIONS_IS_SECTION_ACTIVE_UPDATE"),
		"sort"    => "IS_SECTION_ACTIVE_UPDATE",
		"align"    =>"left",
		"default"  =>true,
	),
);

$lAdmin->AddHeaders($arAdminListHeaders);
$arItems = $sections = array();
while($arRes = $rsData->NavNext(true, "f_"))
{
	$sections[] = $arRes["SECTION_ID"];
	$arItems[] = $arRes;
}
if (count($sections)>0)
{
	$rsSect = CIBlockSection::GetList(array('left_margin' => 'asc'),array("ID"=>$sections),false,array("ID","IBLOCK_ID","NAME","ACTIVE","IBLOCK_SECTION_ID"));
	while ($arSect = $rsSect->GetNext())
	{
		$arSections[$arSect["ID"]] = $arSect;
	}
}

foreach ($arItems as $arRes):
	
	$row =& $lAdmin->AddRow($f_ID, $arRes);
	$url = "/bitrix/admin/iblock_section_edit.php?IBLOCK_ID=".$arRes["IBLOCK_ID"]."&type=".$ar_iblocks_info[$arRes["IBLOCK_ID"]]["IBLOCK_TYPE_ID"]."&ID=".$arRes["SECTION_ID"]."&lang=".LANGUAGE_ID."&find_section_section=".$arSections[$arRes["SECTION_ID"]]["IBLOCK_SECTION_ID"]."&from=iblock_section_admin";
	$link = "<a href='".$url."' target='_blank'>".$arRes["SECTION_ID"];
	if (!$readOnly)
		$section = $arSections[$arRes["SECTION_ID"]]["NAME"] . " [".$link."]</a>";
	else
		$section = $arSections[$arRes["SECTION_ID"]]["NAME"] . " [".$arRes["SECTION_ID"]."]</a>";
	$row->AddViewField("SECTION_ID", $section);
	$iblock = $ar_iblocks_info[$arRes["IBLOCK_ID"]]["NAME"] . " [".$ar_iblocks_info[$arRes["IBLOCK_ID"]]["ID"]."]";
	$row->AddViewField("IBLOCK_ID", $iblock);
	$row->AddViewField("TYPE_ID", Loc::getMessage("SCODER_COLLECTIONS_TYPE_ID_TEXT_".($arRes["TYPE_ID"]=="F"?"F":"D")));				//Учитывать родительские разделы
	$row->AddViewField("CHECK_PARENT", Loc::getMessage("SCODER_COLLECTIONS_ACTIVE_TEXT_".($arRes["CHECK_PARENT"]=="Y"?"Y":"N")));				//Учитывать родительские разделы
	$row->AddViewField("IS_SECTION_ACTIVE_UPDATE", Loc::getMessage("SCODER_COLLECTIONS_ACTIVE_TEXT_".$arRes["IS_SECTION_ACTIVE_UPDATE"]));		//Не менять активность коллекции
	$row->AddViewField("CATALOG_AVAILABLE", Loc::getMessage("SCODER_COLLECTIONS_ACTIVE_TEXT_".($arRes["CATALOG_AVAILABLE"]=="Y"?"Y":"N")));		//Учитывать доступность товаров / Только доступные
	
	if (!$readOnly)
	{
		$actions = array();
		
		$actions[] = array(
			'ICON' => 'edit',
			'TEXT' => Loc::getMessage('SCODER_COLLECTIONS_CONTEXT_EDIT'),
			'ACTION' => "location.href='".$url."'",
			'DEFAULT' => true
		);
		$actions[] = array('SEPARATOR' => true);
		$actions[] = array(
			'ICON' =>'delete',
			'TEXT' => Loc::getMessage('SCODER_COLLECTIONS_CONTEXT_DELETE'),
			'ACTION' => "if (confirm('".Loc::getMessage('SCODER_COLLECTIONS_DELETE_CONFIRM')."')) ".$lAdmin->ActionDoGroup($arRes['ID'], 'delete')
		);
		$row->AddActions($actions);
	}
	
endforeach;

// резюме таблицы
$lAdmin->AddFooter(
	array(
		array("title"=> Loc::getMessage("SCODER_COLLECTIONS_CNT_TOTAL") .  ":", "value"=>$rsData->SelectedRowsCount()), // кол-во элементов
		array("counter"=>true, "title"=> Loc::getMessage("SCODER_COLLECTIONS_CNT_SELECTED") . ":", "value"=>"0"), // счетчик выбранных элементов
	)
);

$aContext = array();
// и прикрепим его к списку
$lAdmin->AddAdminContextMenu($aContext);

// альтернативный вывод
$lAdmin->CheckListMode();

// установим заголовок страницы
$APPLICATION->SetTitle(Loc::getMessage("SCODER_COLLECTIONS_PAGE_TITLE"));

// не забудем разделить подготовку данных и вывод
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$arFindFields = Array(
	"ID",
);
// создадим объект фильтра
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	$arFindFields
);
?>
<form name="find_form" method="get" action="<?echo $APPLICATION->GetCurPage();?>">
	<?$oFilter->Begin();?>
		<tr>
		  <td><b><?=Loc::getMessage("SCODER_COLLECTIONS_FIND")?>:</b></td>
		  <td>
			<input type="text" size="25" name="find" value="<?echo htmlspecialchars($find)?>" title="">
			<?
				$arr = array(
					"reference" => array(
						"ID",
					),
					"reference_id" => array(
						"ID",
					)
				);
			echo SelectBoxFromArray("find_type_field", $arr, $find_type_field, "", "");
			?>
		  </td>
		</tr>
	<?
	$oFilter->Buttons(array("table_id"=>$sTableID,"url"=>$APPLICATION->GetCurPage(),"form"=>"find_form"));
	$oFilter->End();
	?>

</form>

<?$lAdmin->DisplayList();		// выведем таблицу списка элементов?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>