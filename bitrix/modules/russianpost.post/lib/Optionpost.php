<?
namespace Russianpost\Post;

use Bitrix\Sale\Delivery;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Sale;
use Bitrix\Main\Loader;


class Optionpost
{
	private static $MODULE_ID = 'russianpost.post';

	public static function get($option,$noRemake = true, $site_id = '')
	{
		$self = \COption::GetOptionString(self::$MODULE_ID,$option,self::getDefault($option), $site_id);
		if($self && $noRemake) {
			$handlingType = self::getHandling($option);
			switch ($handlingType) {
				case 'serialize' :
					$self = unserialize($self);
					break;
				case 'json'      :
					$self = json_decode($self,true);
					break;
			}
		}

		return $self;
	}

	public static function getDefault($option)
	{
		$opt = self::collection();
		if(array_key_exists($option,$opt))
			return $opt[$option]['default'];
		return false;
	}

	public static function getHandling($option)
	{
		$opt = self::collection();
		if(array_key_exists($option,$opt) && array_key_exists('handling',$opt[$option]))
			return $opt[$option]['handling'];
		return false;
	}

	static function placeHint($code, $site_id){?>
        <div id="pop-<?=$code?>-<?=$site_id?>" class="b-popup" style="display: none; ">
            <div class="pop-text"><?=GetMessage("RUSSIANPOST_HELPER_".$code)?></div>
            <div class="close" onclick="$(this).closest('.b-popup').hide();"></div>
        </div>
	<?}

	public static function collection()
	{
		$arOptions = array(
			// orderProps
			'location' => array(
				'group'   => 'orderProps',
				'hasHint' => 'N',
				'default' => 'LOCATION',
				'type'    => "special"
			),
			'name' => array(
				'group'   => 'orderProps',
				'hasHint' => 'N',
				'default' => 'FIO',
				'type'    => "text"
			),
			'fName' => array(
				'group'   => 'orderProps',
				'hasHint' => 'N',
				'default' => 'FIRSTNAME',
				'type'    => "text"
			),
			'sName' => array(
				'group'   => 'orderProps',
				'hasHint' => 'N',
				'default' => 'SECONDNAME',
				'type'    => "text"
			),
			'mName' => array(
				'group'   => 'orderProps',
				'hasHint' => 'N',
				'default' => 'MIDDLENAME',
				'type'    => "text"
			),
			'email' => array(
				'group'   => 'orderProps',
				'hasHint' => 'N',
				'default' => 'EMAIL',
				'type'    => "text"
			),
			'phone' => array(
				'group'   => 'orderProps',
				'hasHint' => 'N',
				'default' => 'PHONE',
				'type'    => "text"
			),
			'zip' => array(
				'group'   => 'orderProps',
				'hasHint' => 'N',
				'default' => 'ZIP',
				'type'    => "text"
			),
			'address' => array(
				'group'   => 'orderProps',
				'hasHint' => 'Y',
				'default' => 'ADDRESS',
				'type'    => "text"
			),
			'street' => array(
				'group'   => 'orderProps',
				'hasHint' => 'N',
				'default' => 'STREET',
				'type'    => "text"
			),
			'house' => array(
				'group'   => 'orderProps',
				'hasHint' => 'N',
				'default' => 'HOUSE',
				'type'    => "text"
			),
			'flat' => array(
				'group'   => 'orderProps',
				'hasHint' => 'N',
				'default' => 'FLAT',
				'type'    => "text"
			),
			'extendName' => array(
				'group'   => 'orderProps',
				'hasHint' => 'N',
				'default' => 'N',
				'type'    => "checkbox"
			),
		);
		return $arOptions;
	}

	public static function toOptions($helpMakros = false)
	{
		if(!$helpMakros)
			$helpMakros = "<a href='#' class='PropHint' onclick='return showPopup(\"pop-#CODE#\", this);'></a>";

		$arOptions = array();
		foreach(self::collection() as $optCode => $optVal){
			if(!array_key_exists('group',$optVal) || !$optVal['group'])
				continue;

			if (!array_key_exists($optVal['group'], $arOptions))
				$arOptions[$optVal['group']] = array();

			$name = ($optVal['hasHint'] == 'Y') ? " ".str_replace('#CODE#',$optCode,$helpMakros) : '';

			if(!$helpMakros)
			    $arDescription = array($optCode,self::getMessage("OPT_{$optCode}").$name,$optVal['default'],is_array($optVal['type']) ? $optVal['type'] : array($optVal['type']));
			else
				$arDescription = array($optCode,self::getMessage("OPT_{$optCode}"),$optVal['default'],is_array($optVal['type']) ? $optVal['type'] : array($optVal['type']));

			if($optVal['type'] === 'selectbox'){
				$arDescription []= self::getSelectVals($optCode);
			}

			$arOptions[$optVal['group']][] = $arDescription;
		}

		return $arOptions;
	}

	public static function getSelectVals($code)
	{
		$arVals = false;

		return $arVals;
	}

	static function getMessage($code,$forseUTF=false)
	{
		$mess = GetMessage('RUSSIANPOST_'.$code);
		if($forseUTF){
			$mess = self::zajsonit($mess);
		}
		return $mess;
	}

	static function zajsonit($handle){
		if(LANG_CHARSET !== 'UTF-8'){
			if(is_array($handle))
				foreach($handle as $key => $val){
					unset($handle[$key]);
					$key=self::zajsonit($key);
					$handle[$key]=self::zajsonit($val);
				}
			else
				$handle=$GLOBALS['APPLICATION']->ConvertCharset($handle,LANG_CHARSET,'UTF-8');
		}
		return $handle;
	}

	public static function showOrderOptions($site_id=''){
		global $arPayers;
		$arNomatterProps=array('street'=>true,'house'=>true,'flat'=>true);
		$arOptions = \Russianpost\Post\Optionpost::toOptions();
		foreach($arOptions['orderProps'] as $orderProp){
			if($orderProp[0] == 'extendName'){
				continue;
			}
			$value= self::get($orderProp[0],true, $site_id);
			if(!trim($value)){
				$showErr=true;
				if($orderProp[0]=='address'&& self::get('street',true, $site_id)){
					unset($arNomatterProps['street']);
					$showErr=false;
				}
				if($orderProp[0]=='address'&& self::get('house',true, $site_id)){
					unset($arNomatterProps['house']);
					$showErr=false;
				}
				if($orderProp[0]=='address'&& self::get('flat',true, $site_id)){
					unset($arNomatterProps['flat']);
					$showErr=false;
				}
			}
			else
				$showErr=false;

			$arError=array(
				'noPr'=>false,
				'unAct'=>false,
				'str'=>false,
                'html'=>false,
			);

			if(!array_key_exists($orderProp[0],$arNomatterProps)&&$value){
			    $arTmpPayers = $arPayers[$site_id];
				foreach($arTmpPayers as $payId =>$payerInfo)
					if($payerInfo['sel']){
						if($curProp=\CSaleOrderProps::GetList(array(),array('PERSON_TYPE_ID'=>$payId,'CODE'=>$value))->Fetch()){
							if($curProp['ACTIVE']!='Y')
								$arError['unAct'].="<br>".$payerInfo['NAME'];
						}
						else
							$arError['noPr'].="<br>".$payerInfo['NAME'];
					}
				if($arError['noPr']){
					$arError['str']=GetMessage('RUSSIANPOST_LABEL_noPr')." <a href='#' class='PropHint' onclick='return showPopup(\"pop-noPr_".$orderProp[0]."_".$site_id."\",$(this));'></a>";
					$arError['html'] = '<div id="pop-noPr_'.$orderProp[0].'_'.$site_id.'" class="b-popup" style="display: none; ">
						<div class="pop-text">'.GetMessage('RUSSIANPOST_LABEL_Sign_noPr').'<br><br>'.substr($arError['noPr'],4).'</div>	
                        <div class="close" onclick="$(this).closest(\'.b-popup\').hide();"></div>
					</div>';
				}
				if($arError['unAct']){
					$arError['str'].=GetMessage('RUSSIANPOST_LABEL_unAct')." <a href='#' class='PropHint' onclick='return showPopup(\"pop-noPr_".$orderProp[0]."_".$site_id."\",$(this));'></a>";
					$arError['html'] = '<div id="pop-unAct_'.$orderProp[0].'_'.$site_id.'" class="b-popup" style="display: none; ">
						<div class="pop-text">'.GetMessage('RUSSIANPOST_LABEL_Sign_unAct').'<br><br>'.substr($arError['unAct'],4).'</div>	
						<div class="close" onclick="$(this).closest(\'.b-popup\').hide();"></div>															
					</div>';
				}

				if($arError['str'])
					$showErr=true;
			}
			elseif(array_key_exists($orderProp[0],$arNomatterProps))
				$showErr=false;

			$styleTdStr = ($orderProp[0] == 'street')?'style="border-top: 1px solid #BCC2C4;"':'';
            self::placeHint($orderProp[0], $site_id);
			?>
			<div>
				<p><?=$orderProp[1]?></p>
				<?if($orderProp[0] != 'location'){?>
					<input type="text" size="" maxlength="255" value="<?=$value?>" name="<?=$orderProp[0]?>[<?=htmlspecialcharsbx($site_id)?>]">
				<?}else{
					global $locProps;
					// dont show "choose option"
					if($showErr && !$arError['str'])
						$showErr = false;
					// location will be chosen automatically from location-props
					if(count($locProps)==0){
						$showErr = true;
						$arError['str'] = GetMessage('RUSSIANPOST_LABEL_noLoc');
					}elseif(count($locProps)==1){
						$key = array_pop(array_keys($locProps));
						?>
						<input type='hidden' value="<?=$key?>" name="<?=$orderProp[0]?>[<?=htmlspecialcharsbx($site_id)?>]">
						<?=$locProps[$key]?> [<?=$key?>]
					<?}else{?>
						<select name="<?=$orderProp[0]?>[<?=htmlspecialcharsbx($site_id)?>]">
							<?foreach($locProps as $code => $name){?>
								<option value='<?=$code?>' <?=($value==$code)?"selected":""?>><?=$name." [".$code."]"?></option>
							<?}?>
						</select>
					<?}
				}?>
				&nbsp;&nbsp;<span class='errorText' <?if(!$showErr){?>style='display:none'<?}?>><?=($arError['str'])?$arError['str']:GetMessage('RUSSIANPOST_LABEL_shPr')?></span>
				<?if($orderProp[0] == 'name'){?>
					&nbsp;&nbsp;<a href="javascript:void(0)" onclick="splitName('<?=$site_id?>');"><?=GetMessage('RUSSIANPOST_LBL_turnOnExtendName')?></a>
					<input type='hidden' value="<?=(self::get('extendName', true, $site_id) == 'Y') ? 'Y' : 'N'?>" name="extendName[<?=htmlspecialcharsbx($site_id)?>]">
				<?}elseif($orderProp[0] == 'fName'){?>&nbsp;&nbsp;<a href="javascript:void(0)" onclick="implodeName('<?=$site_id?>')"><?=GetMessage('RUSSIANPOST_LBL_turnOffExtendName')?></a><?}?>
                <?
                    if($arError['html'])
                        echo $arError['html'];
                ?>
			</div>
		<?}
	}

	public static function set($option,$val,$doSerialise = false)
	{
		if($doSerialise){
			$val = serialize($val);
		}
		return \COption::SetOptionString(self::$MODULE_ID,$option,$val);
	}
}
?>