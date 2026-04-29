<?php
/**
 *
 * Multiavatar extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Vinny
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace vinny\multiavatar\controller;

use Symfony\Component\HttpFoundation\Response;
use vinny\multiavatar\service\generator;

class avatar
{
	const MAX_SEED_LENGTH = 80;
	const MAX_TOKEN_LENGTH = 108;

	/** @var generator */
	protected $generator;

	/**
	 * @param generator $generator Multiavatar generator service
	 */
	public function __construct(generator $generator)
	{
		$this->generator = $generator;
	}

	/**
	 * @param string $token Encoded seed
	 * @param int    $size  Avatar size
	 * @return Response
	 */
	public function display($token, $size)
	{
		if (!$this->is_valid_token($token))
		{
			return new Response('', 404);
		}

		$seed = $this->decode_seed($token);
		$size = max(1, min(512, (int) $size));

		if (!$this->is_valid_seed($seed) || !$this->generator->is_available())
		{
			return new Response('', 404);
		}

		try
		{
			$svg = $this->generator->generate($seed);
		}
		catch (\RuntimeException $e)
		{
			return new Response('', 500);
		}

		$response = new Response($svg, 200, array(
			'Content-Type' => 'image/svg+xml; charset=UTF-8',
			'Cache-Control' => 'public, max-age=86400',
			'X-Content-Type-Options' => 'nosniff',
		));
		$response->setEtag(sha1($seed . ':' . $size));

		return $response;
	}

	protected function is_valid_token($token)
	{
		return $token !== '' && strlen($token) <= self::MAX_TOKEN_LENGTH && preg_match('#^[A-Za-z0-9_-]+$#', $token);
	}

	protected function is_valid_seed($seed)
	{
		return $seed !== '' && strlen($seed) <= self::MAX_SEED_LENGTH && !preg_match('#[\x00-\x1F\x7F]#', $seed);
	}

	protected function decode_seed($token)
	{
		$token = strtr($token, '-_', '+/');
		$padding = strlen($token) % 4;

		if ($padding)
		{
			$token .= str_repeat('=', 4 - $padding);
		}

		$seed = base64_decode($token, true);

		return ($seed === false) ? '' : $seed;
	}
}
