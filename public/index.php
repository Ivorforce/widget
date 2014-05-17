<?php
require_once '../vendor/autoload.php';

$router = new Klein\Klein();

$router->respond(function ($request, $response, $service, $app)
{
	$app->html = function ($view, $parameters = []) use ($response) {
		$twig = new Twig_Environment(new Twig_Loader_Filesystem('../html'));
		return $twig->render("{$view}.html", $parameters);
	};

	$app->json = function ($view, $parameters) use ($response) {
		return $response->json($parameters);
	};
});

$router->respond('GET', '/[*:identifier].[json:format]?', function ($request, $response, $service, $app)
{
	$curse = new Widget\Curse(new Widget\CurseCrawler, new Widget\MemcacheCache(new Memcache));

	$identifier = $request->param('identifier');
	$version = $request->param('version', 'latest');
	$format = $request->param('format', 'html');
	$theme = $request->param('theme', 'default');

	if ( ! file_exists("../html/widgets/{$theme}.html")) $theme = 'default';

	$properties = $curse->project($identifier);

	if ( ! $properties)
	{
		$response->code(404);
		return $app->$format('error', [
			'code' => '404',
			'error' => 'Project Not Found',
			'message' => "{$identifier} cannot be found on curse.com"
		]);
	}

	$renderer = new Widget\Render($properties);
	$project = $renderer->render($version);

	return $app->$format("widgets/{$theme}", $project);
});

$router->respond('GET', '/', function ($request, $response, $service, $app)
{
	return $app->html('docs');
});

$router->dispatch();