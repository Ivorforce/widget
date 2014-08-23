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
	 * @return string
	 */
	public function render($view, $parameters)
	{
		return json_encode($parameters);
	}

}