<?php

namespace Infonesy\Transport;

class FileSync extends \B2\Obj
{
	static function load_file($file)
	{
		// Ignore trmporary btsync files
		if(preg_match('/\.bts$/', $file))
			return NULL;

		$content = file_get_contents($file);

		// Return JSON immediately
		if(preg_match('/\.json$/', $file))
			return array_merge(['File' => $file], json_decode($content, true));

		// Decode Markdown with YAML metdata
		if(preg_match("/^---\n(.+?)\n---\n+(.*)$/s", trim($content), $m))
		{
			list($foo, $yaml, $src) = $m;

			$data = \Symfony\Component\Yaml\Yaml::parse($yaml);
			$data['Text'] = $src;
			$data['File'] = $file;

			return $data;
		}

		throw new \Exception("Oops [$file]: ".$content);
	}

	static function map_to_bors($data)
	{
		$object = new \B2\Obj(NULL);

		if($ct = strtotime(popval($data, 'Date')))
			$object->set_create_time($ct);

		if($mt = strtotime(popval($data, 'Modify')))
			$object->set_modify_time($mt);

		$text = popval($data, 'Text');

		if($title = defval($data, 'Title'))
			$text = preg_replace('/^#\s+'.preg_quote($title, '/').'\s*/', '', $text);

		$object->set_source($text);

		foreach($data as $key => $val)
		{
			if(is_array($val))
				$val = self::map_to_bors($val);

			$object->set(strtolower($key), $val);
		}

		return $object;
	}

	static function file_to_bors($file)
	{
		return self::map_to_bors(self::load_file($file));
	}

	function push($object)
	{
		$exporter = new ObjectExporter($this->id());
		$exporter->export($object);
	}

	function push_md($object)
	{
		$exporter = new ObjectExporter($this->id());
		$exporter->export_md($object);
	}
}
