<?php
namespace Widget\Http;

use Twig_Environment;
use Twig_Loader_Filesystem;

class TwigEngine extends Engine implements EngineInterface {

	/**
	 * @param $loader
	 * @param $assets
	 */
	public function __construct($loader, $assets)
	{
		$this->twig = new Twig_Environment($loader);
		$this->assets = $assets;
	}

	/**
	 * Render a template
	 *
	 * @param $template
	 * @param $parameters
	 * @param $meta
	 * @return string
	 */
	public function render($template, $parameters, $meta)
	{
		$parameters = array_merge($parameters, ['assets' => $this->assets, 'meta' => $meta]);
		return $this->twig->render("{$template}.html", $parameters);
	}
}