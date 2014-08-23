<?php
require_once '../vendor/autoload.php';

$router = new Klein\Klein();

$router->respond('GET', '/[*:identifier].[json:format]?', function ($request, $response, $service, $app)
{
	$curse = new Widget\Curse(new Widget\CurseCrawler, new Widget\MemcacheCache(new Memcache));

	$identifier = $request->param('identifier');
	$version 	= $request->param('version', 'latest');
	$theme 		= $request->param('theme', 'default');

	if ($request->param('format') === 'json')
	{
		$response->engine = new Widget\Http\JsonEngine();
	}

	if ( ! file_exists("../html/widgets/{$theme}.html"))
	{
		$theme = 'default';
	}

	$properties = $curse->project($identifier);

	if ($curse->getError())
	{
		return $response->render('error', $curse->getError());
	}

	return $response->render("widgets/{$theme}", $properties);
});

$router->respond('GET', '/', function ($request, $response, $service, $app)
{
	return $response->render('docs');
});

$response = new Widget\Http\Response(new Widget\Http\TwigEngine(
	new Twig_Loader_Filesystem(__DIR__ . '/../html'),
	json_decode(file_get_contents(__DIR__ . '/assets/assets.json'), true)
));
$router->dispatch(null, $response);