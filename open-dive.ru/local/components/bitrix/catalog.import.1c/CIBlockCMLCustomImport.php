<?
class CIBlockCMLCustomImport extends CIBlockCMLImport
{
	const IBLOCK_MANUF_ID = 45;
	private $manufs = [];
	/*function ImportElement($arXMLElement, &$counter, $bWF, $arParent){
		$id = parent::ImportElement($arXMLElement, $counter, $bWF, $arParent);
		$res = CIBlockElement::GetList(array(), array("ID" => $id), false, false, array("PROPERTY_CML2_MANUFACTURER"));
		if($propNanuf = $res->fetch())
		{
			$arPropNanuf = CIBlockPropertyEnum::GetByID($propNanuf["PROPERTY_CML2_MANUFACTURER_ENUM_ID"]);
			if(!array_key_exists($arPropNanuf["XML_ID"], $this->manufs))
			{
				$manufId = 0;
				$resManuf = CIBlockElement::GetList(array(), array("IBLOCK_ID" => self::IBLOCK_MANUF_ID, "XML_ID" => $arPropNanuf["XML_ID"]), false, false, array("ID"));
				if($elManuf = $resManuf->fetch())
				{
					$manufId = $elManuf["ID"];
				}
				else
				{
					$el = new CIBlockElement;
					$arParams = array("replace_space"=>"-","replace_other"=>"-");
					$code = Cutil::translit($propNanuf["PROPERTY_CML2_MANUFACTURER_VALUE"],"ru",$arParams);
					for($i=0;$i<20;$i++)
					{
						$arFields = array(
							"IBLOCK_ID" => self::IBLOCK_MANUF_ID,
							"CODE" => trim($code.($i>0?$i:"")),
							"NAME" => $propNanuf["PROPERTY_CML2_MANUFACTURER_VALUE"],
							"XML_ID" => $arPropNanuf["XML_ID"]
						);
						if($manufId = $el->Add($arFields))
							break;
					}
				}
				$this->manufs[$arPropNanuf["XML_ID"]] = $manufId;
			}
			else
			{
				$manufId = $this->manufs[$arPropNanuf["XML_ID"]];
			}
			if($manufId)
				CIBlockElement::SetPropertyValuesEx($arFields["ID"], false, array("MANUFACTURER" => $manufId));
		}	
	}*/
	function ImportSections(){
		return true;
	}
}