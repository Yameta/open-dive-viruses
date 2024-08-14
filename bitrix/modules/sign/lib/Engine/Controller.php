<?php

namespace Bitrix\Sign\Engine;

use Bitrix\Main;
use ReflectionClass;
use ReflectionMethod;
use Bitrix\Sign\Attribute;
use Bitrix\Sign\Engine\ActionFilter;
use Bitrix\Intranet;

class Controller extends \Bitrix\Main\Engine\Controller
{
	/**
	 * Returns default pre-filters for action.
	 * @return array
	 */
	protected function getDefaultPreFilters()
	{
		return
			[
				new Main\Engine\ActionFilter\ContentType([Main\Engine\ActionFilter\ContentType::JSON]),
				new Main\Engine\ActionFilter\Authentication(),
				new Main\Engine\ActionFilter\HttpMethod(
					[Main\Engine\ActionFilter\HttpMethod::METHOD_GET, Main\Engine\ActionFilter\HttpMethod::METHOD_POST]
				),
				new Intranet\ActionFilter\IntranetUser()
			];
	}

	public function configureActions(): array
	{
		$config = [];
		$class = new ReflectionClass($this);
		$parent = $class->getParentClass();
		foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method)
		{
			$name = $method->getName();
			if (
				mb_substr($name, -6) !== 'Action'
				|| $method->isConstructor()
				|| !$method->isPublic()
				|| $method->isStatic()
				|| !$method->isUserDefined()
				|| $parent->hasMethod($name)
			)
			{
				continue;
			}

			$name = mb_substr($name, 0, -6);
			$prefilters = [];
			foreach ($method->getAttributes() as $attr)
			{
				$attr = $attr->newInstance();
				if ($attr instanceof Attribute\ActionAccess)
				{
					$prefilters[] = new ActionFilter\AccessCheck($attr->permission);
				}
			}

			if (!$prefilters)
			{
				continue;
			}

			$config[$name] = [
				'+prefilters' => $prefilters,
			];
		}

		return $config;
	}
}