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
require_once __DIR__.'/Exception/ParseError.php';
require_once __DIR__.'/Config.php';
require_once __DIR__.'/Parser.php';
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
		{
			$config = self::config()
				->allow_unknown_options()
				->many_params('args', array(
					'is_required' => false,
				));
		}

		// set exception handler
		set_exception_handler(function(\Exception $e) use($config) {

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
				$usage = new Usage($config);

				if(!$skip_error_message)
					echo "\n";
				fwrite(STDERR, $usage->make());
			}

			exit(Cliff::$error_exit_code);
		});

		static::add_default_options($config);

		// run
		$config->load_from_parser(new Parser());
		foreach($config->get_request() as $k=>$v)
		{
			$_REQUEST[$k] = $v;
		}
	}

	protected static function add_default_options(Config $config)
	{
		if(!isset($config->options['cliff-complete--']))
		{
			// completion handler for bash
			$config->flag('--cliff-complete--', array(
				'visibility' => Config::V_NONE,
				'callback' => function() use($config) {
					Completion::action_complete($config);
					exit;
				},
			));

			if(!isset($config->options['cliff-bash-profile']))
			{
				// completion handler for bash
				$config->option('--cliff-bash-profile', array(
					'Generate alias and completion commands for bash profile',
					'visibility' => Config::V_HELP,
					'callback' => function($alias) {
						Completion::action_bash_profile($alias);
						exit;
					},
				));
			}
		}

		if(!isset($config->options['help']))
		{
			$config->flag('--help', array(
				'Show descriptions of options and params',
				'visibility' => Config::V_ALL - Config::V_REQUEST,
				'callback' => function() use($config) {
					$usage = new Usage($config);
					$usage->long_descriptions = true;
					echo $usage->make();
					exit;
				},
			));
		}
	}
}