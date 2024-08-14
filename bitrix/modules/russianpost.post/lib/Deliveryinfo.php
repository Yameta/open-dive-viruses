<?
namespace Russianpost\Post;

use Bitrix\Sale\Delivery\Restrictions;
use Bitrix\Main\Localization\Loc;
class Deliveryinfo
{
	private static $MODULE_ID = 'russianpost.post';


	public static function showDeliveryInfo()
	{
		$result = \Bitrix\Sale\Delivery\Services\Table::getList(array(
			'filter' => array(),
		));
		$arPostDeliverys = array();
		while($delivery=$result->fetch())
		{
			if(strpos($delivery['CLASS_NAME'], '\Sale\Handlers\Delivery\RussianpostProfile') !== false
				|| strpos($delivery['CLASS_NAME'], '\Sale\Handlers\Delivery\RussianpostHandler') !== false)
			{
			   // lo($delivery);
				$res = \Bitrix\Sale\Internals\ServiceRestrictionTable::getList(array(
					'filter' => array(
						'=SERVICE_ID' => $delivery['ID'],
						'=SERVICE_TYPE' => Restrictions\Manager::SERVICE_TYPE_SHIPMENT
					),
					'select' => array('ID', 'CLASS_NAME', 'SORT', 'PARAMS'),
					'order' => array('SORT' => 'ASC', 'ID' => 'DESC')
				));
				$data = $res->fetchAll();
				$dbRes = new \CDBResult;
				$dbRes->InitFromArray($data);
				$arRestriction = array();
				while ($record = $dbRes->Fetch())
                {
	                if(empty($record['CLASS_NAME']) || !class_exists($record['CLASS_NAME']))
		                continue;

	                if(!is_subclass_of($record['CLASS_NAME'], 'Bitrix\Sale\Services\Base\Restriction'))
		                continue;

	                if(strlen($record['CLASS_NAME']) > 0)
	                {
		                $restrictionClassNamesUsed[] = $record['CLASS_NAME'];

		                if(is_callable($record['CLASS_NAME'].'::getClassTitle'))
			                $className = $record['CLASS_NAME']::getClassTitle();
		                else
			                $className = $record['CLASS_NAME'];
	                }
	                else
		                $className = "";

	                $record['CLASS_NAME_TITLE'] = $className;

	                if(!$record["PARAMS"])
		                $record["PARAMS"] = array();

	                $paramsStructure = $record['CLASS_NAME']::getParamsStructure($delivery['ID']);
	                $record["PARAMS"] = $record['CLASS_NAME']::prepareParamsValues($record["PARAMS"], $delivery['ID']);

                    $arRestriction[] = $record;
                }
                $delivery['RESTRICTION'] = $arRestriction;

				if(intval($delivery['PARENT_ID']) > 0)
				{
					$arPostDeliverys[$delivery['PARENT_ID']]['PROFILES'][] = $delivery;
				}
				else
				{
					$arPostDeliverys[$delivery['ID']]['DELIVERY'] = $delivery;
				}

			}
		}

		if(empty($arPostDeliverys))
		{
			?>
			<p style="color: red;"><b><?=Loc::getMessage("RUSSIANPOST_ERROR_DELIVERY_INSTALL")?></b></p>
			<?
		}
		else
		{
		   // lo($arPostDeliverys);
			?>
            <div class=":table-grid :five">
                <div class=":table-cell"><b><?=Loc::getMessage("RUSSIANPOST_HD_DELIVERY")?></b></div>
                <div class=":table-cell"><b><?=Loc::getMessage("RUSSIANPOST_HD_PROFILE")?></b></div>
                <div class=":table-cell"><b>ID</b></div>
                <div class=":table-cell"><b><?=Loc::getMessage("RUSSIANPOST_HD_ACTIVE")?></b></div>
                <div class=":table-cell"><b><?=Loc::getMessage("RUSSIANPOST_HD_DER")?></b></div>
            <?foreach ($arPostDeliverys as $deliveryId=>$arDelivery):?>
                <div class=":table-cell"><?=$arDelivery['DELIVERY']['NAME'];?></div>
                <div class=":table-cell">&nbsp;</div>
                <div class=":table-cell"><?=$deliveryId;?></div>
                <div class=":table-cell"><?=$arDelivery['DELIVERY']['ACTIVE'];?></div>
                <div class=":table-cell"><?if(!empty($arDelivery['RESTRICTION'])):?>
		                <?foreach ($arDelivery['RESTRICTION'] as $arRestriction):?>
			                <?=$arRestriction['CLASS_NAME_TITLE'];?><br>
		                <?endforeach;?>
	                <?endif;?></div>
                <?if(!empty($arDelivery['PROFILES'])):?>
                <?foreach ($arDelivery['PROFILES'] as $arProfile):?>
                        <div class=":table-cell">&nbsp;</div>
                        <div class=":table-cell"><?=$arProfile['NAME'];?></div>
                        <div class=":table-cell"><?=$arProfile['ID'];?></div>
                        <div class=":table-cell"><?=$arProfile['ACTIVE'];?></div>
                        <div class=":table-cell"><?if(!empty($arProfile['RESTRICTION'])):?>
		                        <?foreach ($arProfile['RESTRICTION'] as $arRestriction):?>
			                        <?=$arRestriction['CLASS_NAME_TITLE'];?><br>
		                        <?endforeach;?>
	                        <?endif;?></div>
                <?endforeach;?>
                <?endif;?>
            <?endforeach;?>
            </div>
            <?
		}
	}


	public static function showPropsRestriction()
    {
	    global $arPayers;
	    $deliveryOptions = array();
	    $postDeliverys = array();
	    foreach(\Bitrix\Sale\Delivery\Services\Manager::getActiveList(true) as $deliveryId => $deliveryFields)
	    {
		    $name = $deliveryFields["NAME"]." [".$deliveryId."]";
		    $sites = \Bitrix\Sale\Delivery\Restrictions\Manager::getSitesByServiceId($deliveryId);

		    if(!empty($sites))
			    $name .= " (".implode(", ", $sites).")";
		    if(strpos($deliveryFields['CLASS_NAME'], '\Sale\Handlers\Delivery\RussianpostProfile') !== false)
            {
                $postDeliverys[$deliveryId] = $name;
            }

		    $deliveryOptions[$deliveryId] = $name;
	    }
	    //lo($deliveryOptions);
	    $arNomatterProps=array('street'=>true,'house'=>true,'flat'=>true);
	    $arOptions = \Russianpost\Post\Optionpost::toOptions(true);

	    foreach($arOptions['orderProps'] as $orderProp)
        {
	        if($orderProp[0] == 'extendName'){
		        continue;
	        }
	        if(!array_key_exists($orderProp[0],$arNomatterProps))
            {
	            ?>
                <div>
                    <p><b><?=$orderProp[1]?></b></p>
	            <?
                foreach($arPayers as $site_id=>$arTmpPayers)
                {
	                $value= \Russianpost\Post\Optionpost::get($orderProp[0], true, $site_id);
	                if(!trim($value)){
		                $showErr=true;
		                if($orderProp[0]=='address'&& \Russianpost\Post\Optionpost::get('street', true, $site_id)){
			                unset($arNomatterProps['street']);
		                }
		                if($orderProp[0]=='address'&& \Russianpost\Post\Optionpost::get('house', true, $site_id)){
			                unset($arNomatterProps['house']);
		                }
		                if($orderProp[0]=='address'&& \Russianpost\Post\Optionpost::get('flat', true, $site_id)){
			                unset($arNomatterProps['flat']);
		                }
	                }
	                if($value)
                    {
	                    foreach($arTmpPayers as $payId =>$payerInfo)
	                    {
		                    $relationTxt = "";
		                    if($payerInfo['sel']){
			                    if($curProp=\CSaleOrderProps::GetList(array(),array('PERSON_TYPE_ID'=>$payId,'CODE'=>$value))->Fetch()){

				                    $arRelationPayerInfo = \Bitrix\Sale\Internals\OrderPropsRelationTable::getList(array('filter' => array('PROPERTY_ID' => $curProp['ID'])))->fetchAll();
				                    if(empty($arRelationPayerInfo))
				                    {
					                    $relationTxt = "<br>".$payerInfo['NAME'].": ".Loc::getMessage("RUSSIANPOST_NO_LINK");
				                    }
				                    else
				                    {
					                    $bPostRelation = false;
					                    foreach ($arRelationPayerInfo as $arRelation)
					                    {
						                    if($arRelation['ENTITY_TYPE'] != 'D')
							                    continue;

						                    $relationTxt .= "<br>".$payerInfo['NAME'].": ".$deliveryOptions[$arRelation['ENTITY_ID']];
						                    if(isset($postDeliverys[$arRelation['ENTITY_ID']]))
							                    $bPostRelation = true;
					                    }
					                    if(!$bPostRelation)
						                    $relationTxt .= "<br> <b style='color: red'>".Loc::getMessage("RUSSIANPOST_ERROR_RESTR").'</b>';
				                    }

			                    }
		                    }
		                    ?>
                            <span><?=$relationTxt;?></span>
		                    <?
	                    }
                    }
                }
	            ?>
                </div>
	            <?
            }
        }
    }
}
?>