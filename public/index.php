<?php require_once '../vendor/autoload.php';

$router = new Klein\Klein();

header('Access-Control-Allow-Origin: *');

$router->respond('GET', '/[*:project].[json:format]?', function ($request, $response, $service, $app)
{
	$curse = new Widget\Curse(new Widget\CurseCrawler, new Widget\MemcacheCache(new Memcache));
	$project = $curse->project($request->param('project'));

	if ( ! $project) return 404;
	if ($request->param('format') == 'json') return $response->json($project);

	$latest = key(array_slice($project['versions'], 0, 1));
	$version = $request->param('version', $latest);
	if (empty($version)) $version = $latest;

	$widget = $request->param('theme', 'widget');

	return (new Twig_Environment(new Twig_Loader_Filesystem('../html')))
		->render("{$widget}.html", array_merge(
			$project,
			['version' => $version]
		));
});

$router->respond('GET', '/', function ($request, $response)
{
	return (new Twig_Environment(new Twig_Loader_Filesystem('../html')))
		->render('docs.html');
});

$router->dispatch();