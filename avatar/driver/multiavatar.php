<?php
/**
 *
 * Multiavatar extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Vinny
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace vinny\multiavatar\avatar\driver;

use phpbb\avatar\driver\driver;
use phpbb\cache\driver\driver_interface as cache_driver_interface;
use phpbb\config\config;
use phpbb\controller\helper;
use phpbb\path_helper;
use vinny\multiavatar\service\generator;

class multiavatar extends driver
{
	const DEFAULT_SIZE = 96;
	const MAX_SEED_LENGTH = 80;

	/** @var helper */
	protected $controller_helper;

	/** @var generator */
	protected $generator;

	/**
	 * @param config                 $config            phpBB configuration
	 * @param \FastImageSize\FastImageSize $imagesize  FastImageSize class
	 * @param string                 $phpbb_root_path   phpBB root path
	 * @param string                 $php_ext           PHP extension
	 * @param path_helper            $path_helper       phpBB path helper
	 * @param cache_driver_interface $cache             Cache driver
	 * @param helper                 $controller_helper Controller helper
	 * @param generator              $generator         Multiavatar generator
	 */
	public function __construct(config $config, \FastImageSize\FastImageSize $imagesize, $phpbb_root_path, $php_ext, path_helper $path_helper, cache_driver_interface $cache, helper $controller_helper, generator $generator)
	{
		parent::__construct($config, $imagesize, $phpbb_root_path, $php_ext, $path_helper, $cache);

		$this->controller_helper = $controller_helper;
		$this->generator = $generator;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_config_name()
	{
		return 'multiavatar';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_data($row)
	{
		$seed = $this->normalise_seed($row['avatar']);
		$size = $this->normalise_size($row['avatar_width'], $row['avatar_height']);

		if ($seed === '')
		{
			return array(
				'src' => '',
				'width' => 0,
				'height' => 0,
			);
		}

		return array(
			'src' => $this->controller_helper->route('vinny_multiavatar_avatar', array(
				'token' => $this->encode_seed($seed),
				'size' => $size,
			)),
			'width' => $size,
			'height' => $size,
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function prepare_form($request, $template, $user, $row, &$error)
	{
		$user->add_lang_ext('vinny/multiavatar', 'common');

		$seed = $this->get_form_seed($request, $user, $row);
		$size = $this->get_configured_size();

		$template->assign_vars(array(
			'AVATAR_MULTIAVATAR_SEED' => $seed,
			'AVATAR_MULTIAVATAR_WIDTH' => $size,
			'AVATAR_MULTIAVATAR_HEIGHT' => $size,
			'AVATAR_MULTIAVATAR_PREVIEW' => ($seed !== '') ? $this->controller_helper->route('vinny_multiavatar_avatar', array(
				'token' => $this->encode_seed($seed),
				'size' => $size,
			)) : '',
			'AVATAR_MULTIAVATAR_PREVIEW_PATTERN' => $this->controller_helper->route('vinny_multiavatar_avatar', array(
				'token' => 'MULTIAVATAR_TOKEN',
				'size' => $size,
			)),
			'AVATAR_MULTIAVATAR_TOKEN_PLACEHOLDER' => 'MULTIAVATAR_TOKEN',
		));

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function process_form($request, $template, $user, $row, &$error)
	{
		if (!$this->generator->is_available())
		{
			$error[] = 'MULTIAVATAR_LIBRARY_MISSING';
			return false;
		}

		$seed = $this->normalise_seed($request->variable('avatar_multiavatar_seed', '', true));

		if ($seed === '')
		{
			$seed = $this->get_default_seed($user, $row);
		}

		if (strlen($seed) > self::MAX_SEED_LENGTH)
		{
			$error[] = array('MULTIAVATAR_SEED_TOO_LONG', self::MAX_SEED_LENGTH);
			return false;
		}

		$size = $this->get_configured_size();

		if ($this->config['avatar_max_width'] && $size > $this->config['avatar_max_width'])
		{
			$error[] = array('MULTIAVATAR_WRONG_SIZE', $this->config['avatar_min_width'], $this->config['avatar_min_height'], $this->config['avatar_max_width'], $this->config['avatar_max_height'], $size, $size);
			return false;
		}

		if ($this->config['avatar_max_height'] && $size > $this->config['avatar_max_height'])
		{
			$error[] = array('MULTIAVATAR_WRONG_SIZE', $this->config['avatar_min_width'], $this->config['avatar_min_height'], $this->config['avatar_max_width'], $this->config['avatar_max_height'], $size, $size);
			return false;
		}

		if ($this->config['avatar_min_width'] && $size < $this->config['avatar_min_width'])
		{
			$error[] = array('MULTIAVATAR_WRONG_SIZE', $this->config['avatar_min_width'], $this->config['avatar_min_height'], $this->config['avatar_max_width'], $this->config['avatar_max_height'], $size, $size);
			return false;
		}

		if ($this->config['avatar_min_height'] && $size < $this->config['avatar_min_height'])
		{
			$error[] = array('MULTIAVATAR_WRONG_SIZE', $this->config['avatar_min_width'], $this->config['avatar_min_height'], $this->config['avatar_max_width'], $this->config['avatar_max_height'], $size, $size);
			return false;
		}

		return array(
			'avatar' => $seed,
			'avatar_width' => $size,
			'avatar_height' => $size,
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_template_name()
	{
		return '@vinny_multiavatar/ucp_avatar_options_multiavatar.html';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_acp_template_name()
	{
		return '@vinny_multiavatar/acp_avatar_options_multiavatar.html';
	}

	/**
	 * {@inheritdoc}
	 */
	public function prepare_form_acp($user)
	{
		$user->add_lang_ext('vinny/multiavatar', 'common');

		return array();
	}

	protected function get_form_seed($request, $user, $row)
	{
		$seed = $request->variable('avatar_multiavatar_seed', '', true);

		if ($seed !== '')
		{
			return $this->normalise_seed($seed);
		}

		if ($row['avatar_type'] === $this->get_name() && $row['avatar'] !== '')
		{
			return $this->normalise_seed($row['avatar']);
		}

		return $this->get_default_seed($user, $row);
	}

	protected function get_default_seed($user, $row = array())
	{
		$user_id = isset($row['id']) ? (int) $row['id'] : (isset($user->data['user_id']) ? (int) $user->data['user_id'] : 0);
		$username = isset($row['username_clean']) ? $row['username_clean'] : (isset($user->data['username_clean']) ? $user->data['username_clean'] : '');

		return $this->normalise_seed($username . '-' . $user_id);
	}

	protected function normalise_seed($seed)
	{
		$seed = trim((string) $seed);
		$seed = preg_replace('#[\x00-\x1F\x7F]+#', ' ', $seed);
		$seed = preg_replace('#\s+#u', ' ', $seed);

		return trim($seed);
	}

	protected function normalise_size($width, $height)
	{
		$width = (int) $width;
		$height = (int) $height;

		if ($width <= 0 || $height <= 0)
		{
			$max_width = (int) $this->config['avatar_max_width'];
			$max_height = (int) $this->config['avatar_max_height'];
			$size = min($max_width ?: self::DEFAULT_SIZE, $max_height ?: self::DEFAULT_SIZE);

			return $size > 0 ? $size : self::DEFAULT_SIZE;
		}

		return min($width, $height);
	}

	protected function get_configured_size()
	{
		$max_width = (int) $this->config['avatar_max_width'];
		$max_height = (int) $this->config['avatar_max_height'];
		$min_width = (int) $this->config['avatar_min_width'];
		$min_height = (int) $this->config['avatar_min_height'];

		$size = min($max_width ?: self::DEFAULT_SIZE, $max_height ?: self::DEFAULT_SIZE);
		$size = max($size, $min_width, $min_height);

		return $size > 0 ? $size : self::DEFAULT_SIZE;
	}

	protected function encode_seed($seed)
	{
		return rtrim(strtr(base64_encode($seed), '+/', '-_'), '=');
	}
}
