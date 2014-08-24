<?php
namespace Widget\Http;

Interface EngineInterface {

	/**
	 * @param $template
	 * @param $parameters
	 * @param $meta
	 * @return string
	 */
	public function render($template, $parameters, $meta);

}