<?php

namespace Bitrix\Sign\Util\Request;

class File
{
	/**
	 * Return flat request files data by array with shape like $_FILES
	 *
	 * @param array $requestFilesData
	 * @return array{name: ?string, type: ?string, tmp_name: ?string, error: int, size: ?int}[]
	 */
	public static function flatRequestFilesData(array $requestFilesData): array
	{
		$result = [];

		foreach ($requestFilesData as $requestFilesDataPerName)
		{
			if (!is_array($requestFilesDataPerName['name']))
			{
				$result[] = $requestFilesDataPerName;
				continue;
			}

			for (
				$filesPerNameIndex = 0, $maxIndex = count($requestFilesDataPerName['name']);
				$filesPerNameIndex < $maxIndex;
				$filesPerNameIndex++
			)
			{
				$result[] = [
					'name' => $requestFilesDataPerName['name'][$filesPerNameIndex],
					'type' => $requestFilesDataPerName['type'][$filesPerNameIndex],
					'tmp_name' => $requestFilesDataPerName['tmp_name'][$filesPerNameIndex],
					'error' => $requestFilesDataPerName['error'][$filesPerNameIndex],
					'size' => $requestFilesDataPerName['size'][$filesPerNameIndex],
				];
			}
		}

		return $result;
	}
}