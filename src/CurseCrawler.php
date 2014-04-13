<?php namespace Widget;

use Symfony\Component\DomCrawler\Crawler;
use Carbon\Carbon;

class CurseCrawler extends Crawler {

	/**
	 * Strip out everything but numbers from the text of the node
	 *
	 * @return int
	 */
	public function number()
	{
		return (int) preg_replace('/\D/', '', $this->getNode(0)->nodeValue);
	}

	/**
	 * Strip the key from the text of the node
	 * eg: "License: MIT" becomes "MIT"
	 *
	 * @return string
	 */
	public function value()
	{
		return trim(explode(':', $this->getNode(0)->nodeValue)[1]);
	}

	public function attrAsTime($key)
	{
		$timestamp = $this->attr($key);
		return $this->timestampToISO8601($timestamp);
	}

	public function exists()
	{
		return (count($this)) ? true : false;
	}

	private function timestampToISO8601($timestamp)
	{
		return Carbon::createFromTimestamp($timestamp)->toISO8601String();
	}

}