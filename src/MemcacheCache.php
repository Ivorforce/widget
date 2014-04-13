<?php namespace Widget;

use Memcache;

/**
 * @uses CacheInterface
 */
class MemcacheCache implements CacheInterface {

	/**
	 * Memcache object
	 *
	 * @var Memcache
	 * @access protected
	 */
	protected $memcache;

	/**
	 * Constructor
	 *
	 * Create a new Cache object using Memcache as the underlying system
	 *
	 * @access public
	 */
	public function __construct(Memcache $memcache)
	{
		$this->memcache = $memcache;
		$this->memcache->connect('localhost', 11211);
	}

	/**
	 * Set $key in cache with $data for $seconds
	 *
	 * @param string $key
	 * @param mixed $data
	 * @param int $seconds
	 * @access public
	 * @return boolean
	 */
	public function set($key, $data, $seconds)
	{
		return $this->memcache->set($key, $data, false, $seconds);
	}

	/**
	 * Check if $key exists in the cache
	 *
	 * @param string $key
	 * @access public
	 * @return boolean
	 */
	public function has($key)
	{
		if ( ! $this->get($key))
		{
			return false;
		}

		return true;
	}

	/**
	 * Get the data from the cache for $key
	 *
	 * @param string $key
	 * @access public
	 * @return mixed
	 */
	public function get($key)
	{
		return $this->memcache->get($key);
	}

	/**
	 * Delete the key from the cache
	 *
	 * @param string $key
	 * @access public
	 * @return boolean
	 */
	public function delete($key)
	{
		return $this->memcache->delete($key);
	}

}