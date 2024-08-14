<?php

namespace Aspro\Smartseo\Condition\Entities\Iblock;

use Aspro\Smartseo,
	Bitrix\Main\Localization\Loc;

class Helper
{
	/** = (equal) **/
	const LOGIC_EQ = 'Equal';
	/** != (not equal)  */
	const LOGIC_NOT_EQ = 'Not';
	/** > (great) */
	const LOGIC_GR = 'Great';
	/** < (less) */
	const LOGIC_LS = 'Less';
	/** >= (great or equal) */
	const LOGIC_EGR = 'EqGr';
	/** <= (less or equal) */
	const LOGIC_ELS = 'EqLs';
	/** contain */
	const LOGIC_CONT = 'Contain';
	/** not contain */
	const LOGIC_NOT_CONT = 'NotCont';
	/** AND */
	const LOGIC_AND = 'AND';
	/** OR */
	const LOGIC_OR = 'OR';

	static public function appendSelect(&$query, $field, $alias = '')
	{
		$query->addSelect($field, $alias);
	}

	static public function appendOrderBy(&$query, $field)
	{
		$query->addOrder($field);
	}

	static public function appendGroupBy(&$query, $field)
	{
		$query->addGroup($field);
	}

	static public function appendWhereNumberProperty(&$queryWhere, $property, $logics, $alias = '')
	{
		$whereRangeNumber = \Bitrix\Main\Entity\Query::filter();

		$egrValue = 0;
		$elsValue = 0;

		foreach ($logics as $logic) {
			if($logic['OPERATOR'] == self::LOGIC_EGR) {
				$egrValue = is_array($logic['VALUE']) ? min($logic['VALUE']) : $logic['VALUE'];
			} elseif($logic['OPERATOR'] == self::LOGIC_ELS) {
				$elsValue = is_array($logic['VALUE']) ? max($logic['VALUE']) : $logic['VALUE'];
			}

			self::appendPropertyCondition($whereRangeNumber, $property, $logic, $alias);
		}

		if($egrValue > $elsValue) {
			$whereRangeNumber->logic('or');
		}

		$queryWhere->where($whereRangeNumber);
	}

	static public function appendWhereDefaultProperty(&$queryWhere, $propertyId, $logics, $alias = '')
	{
		foreach ($logics as $logic) {
			self::appendPropertyCondition($queryWhere, $propertyId, $logic, $alias);
		}
	}

	static public function appendWherePrice(&$queryWhere, $catalogGroupId, $logics, $alias = '')
	{
		$whereRangeNumber = \Bitrix\Main\Entity\Query::filter();

		$egrValue = 0;
		$elsValue = 0;

		foreach ($logics as $logic) {
			if($logic['OPERATOR'] == self::LOGIC_EGR) {
				$egrValue = is_array($logic['VALUE']) ? min($logic['VALUE']) : $logic['VALUE'];
			} elseif($logic['OPERATOR'] == self::LOGIC_ELS) {
				$elsValue = is_array($logic['VALUE']) ? max($logic['VALUE']) : $logic['VALUE'];
			}

			self::appendPriceCondition($whereRangeNumber, $catalogGroupId, $logic, $alias);
		}

		if($egrValue > $elsValue) {
			$whereRangeNumber->logic('or');
		}

		$queryWhere->where($whereRangeNumber);
	}

	static public function appendWhereSectionMargin(&$queryWhere, array $sectionMargins, $isIncludeSubsection = true, $alias = 'section')
	{
		if(!$sectionMargins) {
			return;
		}

		$whereMargin = \Bitrix\Main\Entity\Query::filter();
		$whereMargin->logic('or');

		foreach ($sectionMargins as $margin) {
			$whereMargin->where(
				\Bitrix\Main\Entity\Query::filter()->where([
					[$alias . '.IBLOCK_SECTION.LEFT_MARGIN', $isIncludeSubsection ? '>=' : '=', $margin['LEFT_MARGIN']],
					[$alias . '.IBLOCK_SECTION.RIGHT_MARGIN', $isIncludeSubsection ? '<=' : '=', $margin['RIGHT_MARGIN']],
				])
			);
		}

		$queryWhere->where($whereMargin);

		$whereActive = \Bitrix\Main\Entity\Query::filter();
		$whereActive->where($alias . '.IBLOCK_SECTION.ACTIVE', 'Y');
		$whereActive->where($alias . '.IBLOCK_SECTION.GLOBAL_ACTIVE', 'Y');

		if ($whereActive->hasConditions()) {
			$queryWhere->where($whereActive);
		}
	}

	static protected function appendPropertyCondition(&$queryWhere, $propertyId, $logic, $alias = '')
	{
		if ($alias) {
			$alias = $alias . '.';
		}

		$values = [];

		if($logic['VALUE']) {
			if(is_array($logic['VALUE'])) {
				$values = array_filter($logic['VALUE']);
			} else {
				$values = [$logic['VALUE']];
			}
		}

		switch ($logic['OPERATOR']) {
			case self::LOGIC_EQ :
				$queryFilter = \Bitrix\Main\Entity\Query::filter();
				//$queryFilter->where($alias . 'IBLOCK_PROPERTY_ID', '=', $propertyId);

				if($values) {
					$queryFilter->whereIn($alias . 'VALUE', $values);
				} else {
					$queryFilter->whereNotNull($alias . 'VALUE', $values);
				}

				break;
			case self::LOGIC_NOT_EQ :
				$queryFilter = \Bitrix\Main\Entity\Query::filter();

				if($values) {
					$queryFilter->whereNotIn($alias . 'VALUE', $values);
				} else {
					$queryFilter->whereNull($alias . 'VALUE');
				}

				break;
			case self::LOGIC_CONT :
				$queryFilterLike = \Bitrix\Main\Entity\Query::filter();
				$queryFilterLike->logic('OR');

				if($values) {
					foreach ($values as $_value) {
						$queryFilterLike->whereLike($alias . 'VALUE', '%' . $_value . '%');
					}
				} else {
					$queryFilterLike->whereNotNull($alias . 'VALUE');
				}

				$queryFilter = \Bitrix\Main\Entity\Query::filter();
				$queryFilter
					->where($queryFilterLike);

				break;

			case self::LOGIC_NOT_CONT :
				$queryFilterLike = \Bitrix\Main\Entity\Query::filter();
				$queryFilterLike->logic('OR');

				if($values) {
					foreach ($values as $_value) {
						$queryFilterLike->whereNotLike($alias . 'VALUE', '%' . $_value . '%');
					}
				} else {
					$queryFilterLike->whereNull($alias . 'VALUE');
				}

				$queryFilter = \Bitrix\Main\Entity\Query::filter();
				$queryFilter
					->where($queryFilterLike);

				break;

			case self::LOGIC_EGR :
				$queryFilter = \Bitrix\Main\Entity\Query::filter();
				$queryFilter
					->where($alias . 'VALUE_NUM', '>=', min($values));

				break;
			case self::LOGIC_ELS :
				$queryFilter = \Bitrix\Main\Entity\Query::filter();
				$queryFilter
					->where($alias . 'VALUE_NUM', '<=', max($values));

				break;
			default:
				$queryFilter = \Bitrix\Main\Entity\Query::filter();
				$queryFilter
					->where($alias . 'VALUE', 'in', $values);

				break;
		}

		if($queryFilter->hasConditions()) {
			$queryWhere->where($queryFilter);
		}
	}

	static protected function appendPriceCondition(&$queryWhere, $catalogGroupId, $logic, $alias = '')
	{
		if ($alias) {
			$alias = $alias . '.';
		}

		if(is_array($logic['VALUE'])) {
			$values = array_filter($logic['VALUE']);
		} else {
			$values = [$logic['VALUE']];
		}

		switch ($logic['OPERATOR']) {
			case self::LOGIC_EGR :
				$queryFilter = \Bitrix\Main\Entity\Query::filter();
				$queryFilter
					->where($alias . 'PRICE_SCALE', '>=', min($values));

				break;
			case self::LOGIC_ELS :
				$queryFilter = \Bitrix\Main\Entity\Query::filter();
				$queryFilter
					->where($alias . 'PRICE_SCALE', '<=', max($values));

				break;
		}

		$queryWhere->where($queryFilter);
	}
}
