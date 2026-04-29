<?php
/**
 *
 * Multiavatar extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Vinny
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace vinny\multiavatar\tests\service;

require_once __DIR__ . '/../../service/generator.php';

class generator_test extends \PHPUnit\Framework\TestCase
{
	protected function setUp(): void
	{
		parent::setUp();

		set_error_handler(function ($severity, $message, $file, $line) {
			if (error_reporting() & $severity)
			{
				throw new \ErrorException($message, 0, $severity, $file, $line);
			}

			return false;
		});
	}

	protected function tearDown(): void
	{
		restore_error_handler();

		parent::tearDown();
	}

	public function test_multiavatar_library_is_available()
	{
		$generator = new \vinny\multiavatar\service\generator($this->get_phpbb_root_path());

		$this->assertTrue($generator->is_available());
		$this->assertTrue(class_exists('\Multiavatar'));
	}

	public function test_generate_returns_svg()
	{
		$generator = new \vinny\multiavatar\service\generator($this->get_phpbb_root_path());
		$svg = $generator->generate('compatibility-test');

		$this->assertStringStartsWith('<svg', $svg);
		$this->assertStringContainsString('</svg>', $svg);
	}

	private function get_phpbb_root_path()
	{
		return realpath(__DIR__ . '/../../../../..') . DIRECTORY_SEPARATOR;
	}
}
