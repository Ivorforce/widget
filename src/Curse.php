<?php
namespace Widget;

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
	 * Check if the project identifier is valid
	 *
	 * @param string $identifier
	 * @return bool
	 */
	public function isValid($identifier)
	{
		if (preg_match('%^.+/.+/.+$%', $identifier) || preg_match('%project/[0-9]+$%', $identifier))
		{
			return true;
		}

		return false;
	}

	/**
	 * Take a project key and return the properties
	 *
	 * @param string $identifier
	 * @return array
	 */
	public function project($identifier)
	{
		if ( ! $this->cache->has($identifier))
		{
			$html =  $this->fetch($identifier);
			$project = $this->parse($html);

			$this->cache->set($identifier, $project, $this->expiry);
		}

		return $this->cache->get($identifier);
	}

	/**
	 * Parse curse.com HTML for project properties
	 *
	 * @param string $html
	 * @return array
	 */
	public function parse($html)
	{
		$this->crawler->add($html);

		// Return null if this isn't a content page
		if ( ! $this->crawler->filter('ul.details-list .game')->exists())
		{
			return null;
		}

		$properties = [
			'title' => $this->crawler->filter('meta[property="og:title"]')->attr('content'),
			'game' => $this->crawler->filter('ul.details-list .game')->text(),
			'category' => $this->crawler->filter('#breadcrumbs-wrapper ul.breadcrumbs li a')->eq(2)->text(),
			'url' => $this->crawler->filter('meta[property="og:url"]')->attr('content'),
			'thumbnail' => $this->crawler->filter('meta[property="og:image"]')->attr('content'),
			'authors' => $this->crawler->filter('ul.authors li a')->each(function ($node, $i) { return $node->text(); }),
			'downloads' => [
				'monthly' => $this->crawler->filter('ul.details-list .average-downloads')->number(),
				'total' => $this->crawler->filter('ul.details-list .downloads')->number()
			],
			'favorites' => $this->crawler->filter('ul.details-list .favorited')->number(),
			'likes'=> $this->crawler->filter('li.grats span.project-rater')->number(),
			'updated_at' => $this->crawler->filter('ul.details-list .updated .standard-date')->eq(0)->attrAsTime('data-epoch'),
			'created_at' => $this->crawler->filter('ul.details-list .updated .standard-date')->eq(1)->attrAsTime('data-epoch'),
			'project_url' => $this->crawler->filter('ul.details-list .curseforge a')->attr('href'),
			'release_type' => $this->crawler->filter('ul.details-list .release')->value(),
			'license' => $this->crawler->filter('ul.details-list .license')->value(),
			'files' => $this->crawler->filter('table.project-file-listing tr')->eachWithoutNull(function ($node, $i)
			{
				if ($i === 0) return; // skip the table heading

				return [
					'id' => (int) $node->filter('td a')->eq(0)->finalUrlSegment('href'),
					'url' => 'http://curse.com' . $node->filter('td a')->eq(0)->attr('href'),
					'name' => $node->filter('td a')->eq(0)->text(),
					'type' => strtolower($node->filter('td')->eq(1)->text()),
					'version' => $node->filter('td')->eq(2)->text(),
					'downloads' => $node->filter('td')->eq(3)->number(),
					'created_at' => $node->filter('td .standard-date')->attrAsTime('data-epoch')
				];
			})
		];

		return $properties;
	}

	/**
	 * Fetch a project from curse.com
	 *
	 * @param string $project
	 * @return string
	 */
	public function fetch($project)
	{
		return $this->curl('http://www.curse.com/' . $project);
	}

	/**
	 * Perform CURL request
	 *
	 * @param string $url
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