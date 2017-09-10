<?php

namespace Infonesy\Transport;

class ObjectExporter
{
	var $dir;

	function __construct($dir)
	{
		$this->dir = $dir;
	}

	function export($object, $file = NULL)
	{
//		$data = [
//			'class_name' => $object->class_name(),
//			'data' => $object->data,
//		];

		$data = $object->infonesy_data();

		if(!$file)
			$file = $object->infonesy_uuid().'.json';

		\B2\Files::put_lock("{$this->dir}/$file", \json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
	}

	function export_md($object, $file = NULL)
	{
		$container = $object->infonesy_container();
		$user = $object->infonesy_user();
		$node = $object->infonesy_node();

		$ct = $object->create_time();
		$mt = $object->modify_time();
		if(!$mt)
			$mt = $ct;

		$data = [
			'Title' => $object->title(),
			'UUID' => $object->infonesy_uuid(),
//			'Node' => $object->infonesy_node_uuid(),
//			'TopicUUID' : ru.balancer.tt-rss.digest.201304
			'Author' => [
				'Title'		=> object_property($user, 'title'),
				'EmailMD5'	=> object_property($user, 'email_md5'),
				'UUID'		=> object_property($user, 'infonesy_uuid')],
			'Date' => date('r', $ct),
			'Type' => $object->infonesy_type(),
		];

		if($mt != $ct)
			$data['Modify'] = date('r', $mt);

		if(!$file)
			$file = $object->infonesy_uuid().'.md';

		$yaml = \Symfony\Component\Yaml\Yaml::dump(array_filter($data));

		$file = "{$this->dir}/$file";
		\B2\Files::put_lock($file, "---\n"
			. trim($yaml)
			. "\n---\n\n"
			. ($object->title() ? '# '.$object->title()."\n\n" : '')
			. $object->source()
		);

		touch($file, $mt);
		return $file;
	}
}
