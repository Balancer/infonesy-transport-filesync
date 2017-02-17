<?php

namespace Infonesy\Transport;

class ObjectExporter
{
	var $dir;

	function __construct($dir)
	{
		$this->dir = $dir;
	}

	function export($object)
	{
		$data = [
			'class_name' => $object->class_name(),
			'data' => $object->data,
		];

		$file = trim(preg_replace('![^\w\-]+!', '.', $object->class_name().'-'.$object->id()), '.').'.json';

		\B2\Files::put_lock("{$this->dir}/$file", \json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
	}
}
