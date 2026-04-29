<?php
/**
 *
 * Multiavatar extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Vinny
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace vinny\multiavatar\migrations;

class install extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return array('\phpbb\db\migration\data\v330\v330');
	}

	public function effectively_installed()
	{
		return isset($this->config['allow_avatar_multiavatar']);
	}

	public function update_data()
	{
		return array(
			array('config.add', array('allow_avatar_multiavatar', 0)),
		);
	}
}
