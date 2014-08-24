<?php
namespace Widget\Http;

class JsonEngine extends Engine implements EngineInterface {

	/**
	 * @var array
	 */
	public $headers = [
		'Content-Type' => 'application/json'
	];

	/**
	 * @param $view
	 * @param $parameters
	 * @param $meta
	 * @return string
	 */
	public function render($view, $parameters, $meta)
	{
		return json_encode($parameters);
	}

}