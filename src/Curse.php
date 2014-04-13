<?php namespace Widget;

class Curse {

	/**
	 * An instance of the crawler
	 *
	 * @var Symfony\Component\DomCrawler\Crawler $crawler
	 */
	protected $crawler;

	/**
	 * @var Cache $cache
	 */
	protected $cache;

	/**
	 * Expiry time (in seconds) of data in the cache
	 *
	 * @var Int $expiry
	 */
	protected $expiry = 3600;

	/**
	 * @param $crawler CurseCrawler
	 * @param $cache CacheInterface
	 */
	public function __construct($crawler, $cache)
	{
		$this->crawler = $crawler;
		$this->cache = $cache;
	}

	/**
	 * Take a project key and return the properties
	 *
	 * @param $project
	 * @return array
	 */
	public function project($project)
	{
		if ($this->cache->has($project))
		{
			return $this->cache->get($project);
		}

		$project_html =  $this->fetch($project);
		$properties = $this->parse($project_html);

		$this->cache->set($project, $properties, $this->expiry);

		return $properties;
	}

	/**
	 * Parse curse.com HTML for project properties
	 *
	 * @param $html
	 * @return array
	 */
	public function parse($html)
	{
		$this->crawler->add($html);

		// Are we looking at a mod page? If not, return nothing
		if ( ! $this->crawler->filter('ul.details-list .game')->exists())
		{
			return null;
		}

		$properties = [
			'title' => $this->crawler->filter('meta[property="og:title"]')->attr('content'),
			'game' => $this->crawler->filter('ul.details-list .game')->text(),
			'category' => $this->crawler->filter('#breadcrumbs-wrapper ul.breadcrumbs li a')->eq(2)->text(),
			'thumbnail' => $this->crawler->filter('meta[property="og:image"]')->attr('content'),
			'authors' => $this->crawler->filter('ul.authors li a')->each(function ($node, $i) { return $node->text(); }),
			'total_downloads' => $this->crawler->filter('ul.details-list .downloads')->number(),
			'monthly_downloads' => $this->crawler->filter('ul.details-list .average-downloads')->number(),
			'favorites' => $this->crawler->filter('ul.details-list .favorited')->number(),
			'likes'=> $this->crawler->filter('li.grats span.project-rater')->number(),
			'updated_at' => $this->crawler->filter('ul.details-list .updated .standard-date')->eq(0)->attrAsTime('data-epoch'),
			'created_at' => $this->crawler->filter('ul.details-list .updated .standard-date')->eq(1)->attrAsTime('data-epoch'),
			'project_url' => $this->crawler->filter('ul.details-list .curseforge a')->attr('href'),
			'release_type' => $this->crawler->filter('ul.details-list .release')->value(),
			'license' => $this->crawler->filter('ul.details-list .license')->value(),
		];

		$files = $this->crawler->filter('table.project-file-listing tr')->each(function ($node, $i)
		{
			if ($i == 0) return; // skip the table heading

			return [
				'url' => 'http://curse.com' . $node->filter('td a')->eq(0)->attr('href'),
				'name' => $node->filter('td a')->eq(0)->text(),
				'type' => $node->filter('td')->eq(1)->text(),
				'version' => $node->filter('td')->eq(2)->text(),
				'downloads' => $node->filter('td')->eq(3)->number(),
				'timestamp' => $node->filter('td .standard-date')->attrAsTime('data-epoch')
			];
		});

		// Remove the first entry from the array, the first is always null
		// because of the skipped table heading
		$properties['files'] = array_slice($files, 1);

		return $properties;
	}

	/**
	 * Fetch a project from curse.com
	 *
	 * @param $project
	 * @return string
	 */
	public function fetch($project)
	{
		return $this->curl('http://www.curse.com/' . $project);
	}

	/**
	 * Perform CURL request
	 *
	 * @param $url
	 * @return mixed
	 */
	protected function curl($url)
	{
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_USERAGENT, "widget -- sryan@curse.com");
		curl_setopt($curl, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_URL, $url);
		$response = curl_exec($curl);

		return $response;
	}

}