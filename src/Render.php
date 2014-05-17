<?php
namespace Widget;

class Render {

	/**
	 * @var array
	 */
	protected $properties;

	/**
	 * @var array
	 */
	protected $files;

	/**
	 * @var array
	 */
	protected $versions;

	/**
	 * Assign the properties in preperation for rendering
	 *
	 * @param $properties
	 */
	public function __construct($properties)
	{
		$this->files = $this->keyFilesById($properties['files']);
		$this->versions = $this->organiseFilesIntoVersions($this->files);

		unset($properties['files']); // this is ugly :(

		$this->properties = $properties;
	}

	/**
	 * Render the properties in a consumable format
	 *
	 * @param string $version
	 * @return array
	 */
	public function render($version = 'release')
	{
		list($number, $type) = $this->expandStringVersion($version);
		$download = $this->pluckDownload($number, $type);

		return array_merge($this->properties, [
			'download' 	=> $download,
			'versions' 	=> $this->versions,
			'files' 	=> $this->files
		]);
	}

	/**
	 * Pluck a download from the file list using the provided version parameters
	 *
	 * @param string $number
	 * @param string $type
	 *
	 * @return array
	 */
	public function pluckDownload($number, $type = 'release')
	{
		$files = $this->files;

		// A numeric version is a file ID, eg: 123456
		if (is_numeric($number))
		{
			if (isset($files[$number])) return $files[$number];
		}

		// Remove any files that aren't from the specified version (eg: 1.7.2)
		if ($number && isset($this->versions[$number]))
		{
			$files = $this->versions[$number];
		}

		// If a file of $type exists in the files return that file
		if ($file = $this->pluckLatestFileOfType($type, $files))
		{
			return $file;
		}

		// No file meeting the parameters has been found, default to the newest
		// release unless the type is latest, in which case let the fallback handle
		if ($type != 'latest' && $file = $this->pluckLatestFileOfType('release', $files))
		{
			return $file;
		}

		// No release file exists, a hail mary... just any file! The latest file!
		return array_slice($this->files, 0, 1)[0];
	}

	/**
	 * Transform a numerical array of files into an associative array of files
	 * using the file id as the array key
	 *
	 * @param array $files
	 * @return array
	 */
	public function keyFilesById($files)
	{
		foreach ($files as $file)
		{
			$keyed_files[$file['id']] = $file;
		}

		return $keyed_files;
	}

	/**
	 * Sort files into a nested array using the file version (eg: 1.7.2)
	 *
	 * @param array $files
	 * @return array
	 */
	public function organiseFilesIntoVersions($files)
	{
		usort($files, function ($a, $b)
		{
			return ($a['created_at'] < $b['created_at']) ? 1 : -1;
		});

		foreach ($files as $file)
		{
			$versions[$file['version']][] = $file;
		}

		return $versions;
	}

	/**
	 * Expand a string (eg: 1.7.2, or 1.7.2/beta, or beta, or 123456) into an
	 * array containing $number (1.7.2) and $type (beta).
	 *
	 * @param string $version
	 * @return array
	 */
	public function expandStringVersion($version)
	{
		$properties = explode('/', $version);

		// if the version is numeric it's a file ID
		if (is_numeric($properties[0]))
		{
			return [$properties[0], null];
		}

		// if the version is only a type, eg: "release" or "beta"
		if (ctype_alpha($properties[0]))
		{
			return [null, $properties[0]];
		}

		// if the version is a number + a type, eg: "1.7.2/beta"
		if ( isset($properties[1]))
		{
			return [$properties[0], $properties[1]];
		}

		// if the version is just a number, eg: 1.7.2
		return [$properties[0], 'release'];
	}

	/**
	 * Pluck the most recent file from the array of the specified type, return
	 * null if no file meets the criteria
	 *
	 * @param string $type
	 * @param array $files
	 * @return array|null
	 */
	public function pluckLatestFileOfType($type, $files)
	{
		foreach ($files as $file)
		{
			if ($file['type'] == $type)
			{
				return $file;
			}
		}

		return null;
	}
}