<?php
/**
 *
 * Multiavatar extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Vinny
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace vinny\multiavatar\service;

class generator
{
	/** @var string */
	protected $phpbb_root_path;

	/**
	 * @param string $phpbb_root_path phpBB root path
	 */
	public function __construct($phpbb_root_path)
	{
		$this->phpbb_root_path = $phpbb_root_path;
	}

	/**
	 * @return bool
	 */
	public function is_available()
	{
		$this->load_library();

		return class_exists('\Multiavatar');
	}

	/**
	 * @param string $seed Avatar seed
	 * @return string SVG markup
	 */
	public function generate($seed)
	{
		$this->load_library();

		if (!class_exists('\Multiavatar'))
		{
			throw new \RuntimeException();
		}

		$multiavatar = new \Multiavatar();

		return $multiavatar($seed, null, null);
	}

	protected function load_library()
	{
		if (class_exists('\Multiavatar'))
		{
			return;
		}

		$autoload = $this->phpbb_root_path . 'ext/vinny/multiavatar/vendor/autoload.php';

		if (is_file($autoload))
		{
			require_once $autoload;
		}
	}
}
