<?php require_once '../vendor/autoload.php';

$router = new Klein\Klein();

$router->respond('GET', '/[*:project].[json:format]?', function ($request, $response, $service)
{
	$curse = new Widget\Curse(new Widget\CurseCrawler, new Widget\MemcacheCache(new Memcache));
	$project = $curse->project($request->param('project'));

	if ($request->param('format') == 'json')
	{
		if ( ! $project) return $response->code(404)->json(['error' => 'Project not found']);
		return $response->json($project);
	}
});

$router->dispatch();