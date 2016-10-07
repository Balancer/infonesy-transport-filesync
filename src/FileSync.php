<?php

namespace Infonesy\Transport;

class FileSync
{
	static function load_file($file)
	{
		$content = file_get_contents($file);

		if(preg_match('/\.bts$/', $file))
			return NULL;

		if(preg_match('/\.json$/', $file))
			return array_merge(['File' => $file], json_decode($content, true));

		if(preg_match("/^---\n(.+?)\n---\n+(.*)$/s", trim($content), $m))
		{
			list($foo, $yaml, $src) = $m;

			$data = \Symfony\Component\Yaml\Yaml::parse($yaml);
			$data['Text'] = $src;
			$data['File'] = $file;

			return $data;
		}

		throw new Exception("Oops [$file]: ".$content);
	}
}
