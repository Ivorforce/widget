<?php
namespace Widget\Http;

Interface EngineInterface {

	/**
	 * @param $template
	 * @param $parameters
	 * @return string
	 */
	public function render($template, $parameters);

}