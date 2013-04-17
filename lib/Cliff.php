<?php

/*

DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.

Cliff: a CLI framework for PHP.
Copyright 2011 Aleksandr Galkin.

This file is part of Cliff.

Cliff is free software: you can redistribute it and/or modify
it under the terms of the GNU Lesser General Public License
as published by the Free Software Foundation, either version 3
of the License, or (at your option) any later version.

Cliff is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the GNU Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public
License along with Cliff. If not, see <http://www.gnu.org/licenses/>.

*/

namespace cliff;

// let's not affect autoload, we don't have too much files here
require_once __DIR__.'/Config.php';
require_once __DIR__.'/Request.php';
require_once __DIR__.'/Parser.php';
require_once __DIR__.'/Exception/ParseError.php';
require_once __DIR__.'/Usage.php';
require_once __DIR__.'/Completion.php';

class Cliff
{
	/**
	 * This exit code will be used if an uncaught exception occurs
	 */
	public static $error_exit_code = 1;

	/**
	 * A shortcut for `new Config` for easy chaining
	 * @return Config
	 */
	public static function config()
	{
		return new Config();
	}

	public static function run(Config $config = null)
	{
		if(is_null($config))
			$config = static::get_default_config();

		if($config->script_name == '')
			$config->script_name = basename($_SERVER['argv'][0]);

		$request = new Request();

		static::set_exception_handler($request);
		static::add_default_options($config, true);

		// run
		$request->load($config, new Parser(), $_REQUEST);
		$request->validate();
	}

	protected static function get_default_config()
	{
		return static::config()
			->allow_unknown_options()
			->many_params('args', array(
				'is_required' => false,
			));
	}

	protected static function set_exception_handler(Request $request)
	{
		set_exception_handler(function(\Exception $e) use($request) {

			/** @var $request Request */

			// do not show error about required param when there is no args (there will be usage)
			$skip_error_message = false;
			if($e instanceof Exception_ParseError)
			{
				if($e->getCode() == Exception_ParseError::E_NO_PARAM && count($_SERVER['argv']) == 1)
					$skip_error_message = true;
			}
			if(!$skip_error_message)
				fwrite(STDERR, $e->getMessage()."\n");

			if($e instanceof Exception_ParseError)
			{
				// show usage
				$usage = new Usage($request->get_innermost_branch_config());

				if(!$skip_error_message)
					echo "\n";
				fwrite(STDERR, $usage->make());
			}

			exit(Cliff::$error_exit_code);
		});
	}

	public static function add_default_options(Config $config, $is_top_config = false)
	{
		if($is_top_config)
		{
			// completion handler for bash
			$config->flag('--cliff-complete--', array(
				'visibility' => Config::V_NONE,
				'callback' => function() use($config) {
					Completion::action_complete($config);
					exit;
				},
			));

			// completion handler for bash
			$config->option('--cliff-bash-profile', array(
				'Generate alias and completion commands for bash profile

				To enable bash completion, you should place the following in your '.Completion::guess_bash_profile_name().' (all on one line):

				'.Completion::make_profile_command($config->is_custom_script_name ? $config->script_name : 'your_alias'),

				'visibility' => Config::V_HELP,
				'callback' => function($alias) {
					Completion::action_bash_profile($alias);
					exit;
				},
			));
		}

		$config->flag('--help', array(
			'Show descriptions of options and params',
			'visibility' => Config::V_ALL & ~Config::V_REQUEST, // everywhere except in $_REQUEST
			'callback' => function() use($config) {
				$usage = new Usage($config);
				$usage->is_help_mode = true;
				echo $usage->make();
				exit;
			},
		));
	}
}
