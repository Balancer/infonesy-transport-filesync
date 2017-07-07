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

	function export_md($object)
	{
		$container = $object->infonesy_container();
		$user = $object->infonesy_user();
		$node = $object->infonesy_node();

		$data = [
			'Title' => $object->title(),
			'UUID' => $object->infonesy_uuid(),
//			'Node' => $object->infonesy_node_uuid(),
//			'TopicUUID' : ru.balancer.tt-rss.digest.201304
			'Author' => [
				'Title'		=> $user->title(),
				'EmailMD5'	=> $user->email_md5(),
				'UUID'		=> $user->infonesy_uuid()],
			'Date' => date('r', $object->create_time()),
			'Type' => $object->infonesy_type(),
		];

		if($mt = $object->modify_time())
			$data['Modify'] = date('r', $mt);

//		$file = trim(preg_replace('![^\w\-]+!', '.', $object->class_name().'-'.$object->id()), '.').'.md';
		$file = $object->infonesy_uuid().'.md';

		$yaml = \Symfony\Component\Yaml\Yaml::dump($data);

		\B2\Files::put_lock("{$this->dir}/$file", "---\n"
			. trim($yaml)
			. "\n---\n\n"
			. '# '.$object->title()."\n\n"
			. $object->source()
		);
	}
}
