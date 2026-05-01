<?php
/**
 *
 * Multiavatar extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Vinny
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace vinny\multiavatar\tests\avatar\driver;

$phpbb_root_path = realpath(__DIR__ . '/../../../../../..') . DIRECTORY_SEPARATOR;

require_once $phpbb_root_path . 'phpbb/avatar/driver/driver_interface.php';
require_once $phpbb_root_path . 'phpbb/avatar/driver/driver.php';
require_once $phpbb_root_path . 'phpbb/cache/driver/driver_interface.php';
require_once $phpbb_root_path . 'phpbb/cache/driver/base.php';
require_once $phpbb_root_path . 'phpbb/cache/driver/dummy.php';
require_once $phpbb_root_path . 'phpbb/config/config.php';
require_once $phpbb_root_path . 'phpbb/controller/helper.php';
require_once $phpbb_root_path . 'phpbb/path_helper.php';
require_once __DIR__ . '/../../../service/generator.php';
require_once __DIR__ . '/../../../avatar/driver/multiavatar.php';

class multiavatar_test extends \PHPUnit\Framework\TestCase
{
	public function test_driver_metadata()
	{
		$driver = $this->get_driver();

		$this->assertSame('multiavatar', $driver->get_config_name());
		$this->assertSame('@vinny_multiavatar/ucp_avatar_options_multiavatar.html', $driver->get_template_name());
		$this->assertSame('@vinny_multiavatar/acp_avatar_options_multiavatar.html', $driver->get_acp_template_name());
	}

	public function test_driver_templates_exist()
	{
		$extension_root = realpath(__DIR__ . '/../../..');

		$this->assertFileExists($extension_root . '/styles/all/template/ucp_avatar_options_multiavatar.html');
		$this->assertFileExists($extension_root . '/adm/style/acp_avatar_options_multiavatar.html');
	}

	public function test_get_data_returns_route_and_dimensions()
	{
		$driver = $this->get_driver();
		$data = $driver->get_data(array(
			'avatar' => 'robot-58',
			'avatar_width' => 90,
			'avatar_height' => 90,
		));

		$this->assertSame('/multiavatar/avatar/cm9ib3QtNTg/90.svg', $data['src']);
		$this->assertSame(90, $data['width']);
		$this->assertSame(90, $data['height']);
	}

	public function test_process_form_uses_row_for_default_seed()
	{
		$driver = $this->get_driver();
		$error = array();

		$result = $driver->process_form(
			new request_stub(array('avatar_multiavatar_seed' => '')),
			null,
			new user_stub(array('user_id' => 2, 'username_clean' => 'vinny')),
			array(
				'id' => 58,
				'username_clean' => 'robot',
				'avatar_type' => '',
				'avatar' => '',
			),
			$error
		);

		$this->assertSame(array(), $error);
		$this->assertSame('robot-58', $result['avatar']);
		$this->assertSame(90, $result['avatar_width']);
		$this->assertSame(90, $result['avatar_height']);
	}

	public function test_process_form_rejects_seed_that_is_too_long()
	{
		$driver = $this->get_driver();
		$error = array();

		$result = $driver->process_form(
			new request_stub(array('avatar_multiavatar_seed' => str_repeat('a', 81))),
			null,
			new user_stub(array('user_id' => 2, 'username_clean' => 'vinny')),
			array(
				'id' => 58,
				'username_clean' => 'robot',
				'avatar_type' => '',
				'avatar' => '',
			),
			$error
		);

		$this->assertFalse($result);
		$this->assertSame(array(array('MULTIAVATAR_SEED_TOO_LONG', 80)), $error);
	}

	public function test_process_form_uses_configured_avatar_size()
	{
		$driver = $this->get_driver(array(
			'avatar_min_width' => 80,
			'avatar_min_height' => 80,
			'avatar_max_width' => 128,
			'avatar_max_height' => 96,
		));
		$error = array();

		$result = $driver->process_form(
			new request_stub(array('avatar_multiavatar_seed' => 'robot-seed')),
			null,
			new user_stub(array('user_id' => 2, 'username_clean' => 'vinny')),
			array(
				'id' => 58,
				'username_clean' => 'robot',
				'avatar_type' => '',
				'avatar' => '',
			),
			$error
		);

		$this->assertSame(array(), $error);
		$this->assertSame('robot-seed', $result['avatar']);
		$this->assertSame(96, $result['avatar_width']);
		$this->assertSame(96, $result['avatar_height']);
	}

	private function get_driver(array $config_values = array())
	{
		$config = new \phpbb\config\config(array_merge(array(
			'avatar_min_width' => 0,
			'avatar_min_height' => 0,
			'avatar_max_width' => 90,
			'avatar_max_height' => 90,
		), $config_values));

		$driver = new \vinny\multiavatar\avatar\driver\multiavatar(
			$config,
			new \FastImageSize\FastImageSize(),
			$this->get_phpbb_root_path(),
			'php',
			new path_helper_stub(),
			new \phpbb\cache\driver\dummy(),
			new controller_helper_stub(),
			new \vinny\multiavatar\service\generator($this->get_phpbb_root_path())
		);
		$driver->set_name('avatar.driver.multiavatar');

		return $driver;
	}

	private function get_phpbb_root_path()
	{
		return realpath(__DIR__ . '/../../../../../..') . DIRECTORY_SEPARATOR;
	}
}

class controller_helper_stub extends \phpbb\controller\helper
{
	public function __construct()
	{
	}

	public function route($route, array $params = array(), $is_amp = true, $session_id = false, $reference_type = 1)
	{
		return '/multiavatar/avatar/' . $params['token'] . '/' . $params['size'] . '.svg';
	}
}

class path_helper_stub extends \phpbb\path_helper
{
	public function __construct()
	{
	}
}

class request_stub
{
	private $values;

	public function __construct(array $values)
	{
		$this->values = $values;
	}

	public function variable($name, $default, $multibyte = false)
	{
		return isset($this->values[$name]) ? $this->values[$name] : $default;
	}
}

class user_stub
{
	public $data;

	public function __construct(array $data)
	{
		$this->data = $data;
	}

	public function add_lang_ext($extension, $lang_set)
	{
	}
}
