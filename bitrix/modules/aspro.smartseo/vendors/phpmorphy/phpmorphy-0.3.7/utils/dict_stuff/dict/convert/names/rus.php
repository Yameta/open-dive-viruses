<?php
class ConstNames_Grammems_Rus {
	public
		 $rPlural     = 0,
		 $rSingular   = 1,

		 $rNominativ  = 2,
		 $rGenitiv    = 3,
		 $rDativ      = 4,
		 $rAccusativ  = 5,
		 $rInstrumentalis = 6,
		 $rLocativ    = 7,
		 $rVocativ    = 8,

		 $rMasculinum = 9,
		 $rFeminum    = 10,
		 $rNeutrum    = 11,
		 $rMascFem    = 12,


		 $rPresentTense = 13,
		 $rFutureTense = 14,
		 $rPastTense = 15,

		 $rFirstPerson = 16,
		 $rSecondPerson = 17,
		 $rThirdPerson = 18,

		 $rImperative = 19,

		 $rAnimative = 20,
		 $rNonAnimative = 21,

		 $rComparative = 22,

		 $rPerfective = 23,
		 $rNonPerfective = 24,

		 $rNonTransitive = 25,
		 $rTransitive = 26,

		 $rActiveVoice = 27,
		 $rPassiveVoice = 28,


		 $rIndeclinable = 29,
		 $rInitialism = 30,

		 $rPatronymic = 31,

		 $rToponym = 32,
		 $rOrganisation = 33,

		 $rQualitative = 34,
		 $rDeFactoSingTantum = 35,

		 $rInterrogative = 36,
		 $rDemonstrative = 37,

		 $rName	    = 38,
		 $rSurName	= 39,
		 $rImpersonal = 40,
		 $rSlang	= 41,
		 $rMisprint = 42,
		 $rColloquial = 43,
		 $rPossessive = 44,
		 $rArchaism = 45,
		 $rSecondCase = 46,
		 $rPoetry = 47,
		 $rProfession = 48,
		 $rSuperlative = 49,
		 $rPositive = 50;
}

class ConstNames_Poses_Rus {
	public
		$rNOUN  = 0, 
		$rADJ_FULL = 1, 
		$rVERB = 2, 
		$rPRONOUN = 3, 
		$rPRONOUN_P = 4, 
		$rPRONOUN_PREDK = 5,
		$rNUMERAL  = 6, 
		$rNUMERAL_P = 7, 
		$rADV = 8, 
		$rPREDK  = 9, 
		$rPREP = 10,
		$rPOSL = 11,
		$rCONJ = 12,
		$rINTERJ = 13,
		$rINP = 14,
		$rPHRASE = 15,
		$rPARTICLE = 16,
		$rADJ_SHORT = 17,
		$rPARTICIPLE = 18,
		$rADVERB_PARTICIPLE = 19,
		$rPARTICIPLE_SHORT = 20,
		$rINFINITIVE = 21;
}

class ConstNames_Rus extends ConstNames_Base {
	protected $poses = array(
		"РЎ",  // 0
		"Рџ", // 1
		"Р“", // 2
		"РњРЎ", // 3
		"РњРЎ-Рџ", // 4
		"РњРЎ-РџР Р•Р”Рљ", // 5
		"Р§РРЎР›", // 6
		"Р§РРЎР›-Рџ", // 7
		"Рќ", // 8
		"РџР Р•Р”Рљ", //9 
		"РџР Р•Р”Р›", // 10
		"РџРћРЎР›", // 11
		"РЎРћР®Р—", // 12
		"РњР•Р–Р”", // 13
		"Р’Р’РћР”Рќ",// 14
		"Р¤Р РђР—", // 15
		"Р§РђРЎРў", // 16
		"РљР _РџР РР›",  // 17
		"РџР РР§РђРЎРўРР•", //18
		"Р”Р•Р•РџР РР§РђРЎРўРР•", //19
		"РљР _РџР РР§РђРЎРўРР•", // 20
		"РРќР¤РРќРРўРР’"  //21
	);
	
	protected $grammems = array(
		// 0..1
	   	"РјРЅ","РµРґ",
		// 2..8
		"РёРј","СЂРґ","РґС‚","РІРЅ","С‚РІ","РїСЂ","Р·РІ",
		// СЂРѕРґ 9-12
		"РјСЂ","Р¶СЂ","СЃСЂ","РјСЂ-Р¶СЂ",
		// 13..15
		"РЅСЃС‚","Р±СѓРґ","РїСЂС€",
		// 16..18
		"1Р»","2Р»","3Р»",	
		// 19
		"РїРІР»",
		// 20..21
		"РѕРґ","РЅРѕ",	
		// 22
		"СЃСЂР°РІРЅ",
		// 23..24
		"СЃРІ","РЅСЃ",	
		// 25..26
		"РЅРї","РїРµ",
		// 27..28
		"РґСЃС‚","СЃС‚СЂ",
		// 29-31
		"0", "Р°Р±Р±СЂ", "РѕС‚С‡",
		// 32-33
		"Р»РѕРє", "РѕСЂРі",
		// 34-35
		"РєР°С‡", "РґС„СЃС‚",
		// 36-37 (РЅР°СЂРµС‡РёСЏ)
		"РІРѕРїСЂ", "СѓРєР°Р·Р°С‚",
		// 38..39
		"РёРјСЏ","С„Р°Рј",
		// 40
		"Р±РµР·Р»",
		// 41,42
		"Р¶Р°СЂРі", "РѕРїС‡",
		// 43,44,45
		"СЂР°Р·Рі", "РїСЂРёС‚СЏР¶", "Р°СЂС…",
		// РґР»СЏ РІС‚РѕСЂРѕРіРѕ СЂРѕРґРёС‚РµР»СЊРЅРѕРіРѕ Рё РІС‚РѕСЂРѕРіРѕ РїСЂРµРґР»РѕР¶РЅРѕРіРѕ
		"2",
		"РїРѕСЌС‚", "РїСЂРѕС„",
		"РїСЂРµРІ", "РїРѕР»РѕР¶"
	);
	
	function getPartsOfSpeech() {
		return $this->combineObjAndArray(new ConstNames_Poses_Rus(), $this->poses);
	}
	
	function getGrammems() {
		return $this->combineObjAndArray(new ConstNames_Grammems_Rus(), $this->grammems);
	}
}
