<?php

namespace cliff;

// let's not affect autoload, we don't have too much files here
require_once __DIR__.'/Exception.php';
require_once __DIR__.'/Exception/ParseError.php';
require_once __DIR__.'/Config.php';
require_once __DIR__.'/Parser.php';
require_once __DIR__.'/Usage.php';

class Cliff
{
	/**
	 * This exit code will be used if an uncaught exception occurs
	 */
	public static $error_exit_code = 1;

	/**
	 * A shortcut for `new Config` for easy chaining
	 */
	public static function config()
	{
		return new Config();
	}

	public static function run(Config $config)
	{
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
		$config->run_callbacks();
	}

	protected static function add_default_options(Config $config)
	{
		if(!isset($config->options['help']))
		{
			$config->option('--help', true, array(
				'Show descriptions of options and params',
				'validator' => function() use($config) {
					$usage = new Usage($config);
					$usage->long_descriptions = true;
					echo $usage->make();
					exit;
				},
			));

		}
	}
}