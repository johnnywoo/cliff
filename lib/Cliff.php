<?php

namespace cliff;

require_once __DIR__.'/Exception.php';
require_once __DIR__.'/Config.php';
require_once __DIR__.'/Parser.php';
require_once __DIR__.'/Usage.php';

/**
 * Problems:
 *
 * how to define this?
 * script --force cmd --force
 * with our current scheme it requires two nested branches, because options cannot be present before params
 * maybe subcommand($name, $config) to complement branch($config)?
 *
 * Todo:
 * config should accumulate info inside, so we can distinguish --force global from --force local
 * by going $global_config->get('force') and ->isset()
 *
 * specifying error code somewhere (it may be 2 for diff)
 *
 * completion
 *
 * helpers:
 * color (like console_color)
 * read (char, string, password, stdin)
 * write (out, err, interface = skipped if not tty, table)
 */


class Cliff
{
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

			// do not show error about required param when there is no args
			$skip_error_message = false;
			if($e instanceof Exception)
			{
				if($e->getCode() == Exception::E_NO_PARAM && count($_SERVER['argv']) == 1)
					$skip_error_message = true;
			}
			if(!$skip_error_message)
				fwrite(STDERR, $e->getMessage()."\n\n");

			// show usage
			$usage = new Usage($config);
			fwrite(STDERR, $usage->make());

			exit(1);
		});

		self::add_default_options($config);

		// run
		$config->load_from_parser(new Parser());
		foreach($config->get_request() as $k=>$v)
		{
			$_REQUEST[$k] = $v;
		}
	}

	protected static function add_default_options(Config $config)
	{
		if(!isset($config->options['help']))
		{
			$config->option('--help', true, array(
				'Show descriptions of options and params',
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