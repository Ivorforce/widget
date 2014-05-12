<?php require_once '../vendor/autoload.php';

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
	$format = $request->param('format', 'html');
	$widget = $request->param('theme', 'widget');

	$project = $curse->project($identifier);

	if ( ! $project)
	{
		$response->code(404);
		return $app->$format('error', [
			'code' => '404',
			'error' => 'Project Not Found',
			'message' => "{$identifier} cannot be found on curse.com"
		]);
	}

	$latest = key(array_slice($project['versions'], 0, 1));
	$version = $request->param('version', $latest);
	if (empty($version)) $version = $latest;

	return $app->$format($widget, array_merge(
		$project,
		['version' => $version]
	));
});

$router->respond('GET', '/', function ($request, $response, $service, $app)
{
	return $app->html('docs');
});

$router->dispatch();