<?php
/**
 *
 * Multiavatar extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Vinny
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace vinny\multiavatar\tests\controller;

require_once __DIR__ . '/../../service/generator.php';
require_once __DIR__ . '/../../controller/avatar.php';

class avatar_test extends \PHPUnit\Framework\TestCase
{
	public function test_valid_avatar_request_returns_svg_response()
	{
		$controller = new \vinny\multiavatar\controller\avatar(
			new \vinny\multiavatar\service\generator($this->get_phpbb_root_path())
		);
		$response = $controller->display($this->encode_seed('compatibility-test'), 90);

		$this->assertSame(200, $response->getStatusCode());
		$this->assertSame('image/svg+xml; charset=UTF-8', $response->headers->get('Content-Type'));
		$this->assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
		$this->assertStringStartsWith('<svg', $response->getContent());
	}

	public function test_invalid_avatar_token_returns_not_found_response()
	{
		$controller = new \vinny\multiavatar\controller\avatar(
			new \vinny\multiavatar\service\generator($this->get_phpbb_root_path())
		);
		$response = $controller->display('invalid!!!', 90);

		$this->assertSame(404, $response->getStatusCode());
		$this->assertSame('', $response->getContent());
	}

	public function test_oversized_avatar_token_returns_not_found_response()
	{
		$controller = new \vinny\multiavatar\controller\avatar(
			new \vinny\multiavatar\service\generator($this->get_phpbb_root_path())
		);
		$response = $controller->display(str_repeat('a', 109), 90);

		$this->assertSame(404, $response->getStatusCode());
		$this->assertSame('', $response->getContent());
	}

	private function encode_seed($seed)
	{
		return rtrim(strtr(base64_encode($seed), '+/', '-_'), '=');
	}

	private function get_phpbb_root_path()
	{
		return realpath(__DIR__ . '/../../../../..') . DIRECTORY_SEPARATOR;
	}
}
