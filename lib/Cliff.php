<?php

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
					$cmp = new Completion($config);
					foreach($cmp->complete($_ENV['COMP_LINE'], $_ENV['COMP_POINT']) as $opt)
					{
						echo "$opt\n";
					}
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
						$fname = realpath($_SERVER['PHP_SELF']);
						// if the file has a shebang, we assume it can execute itself
						if(is_readable($fname) && file_get_contents($fname, 0, null, 0, 2) == '#!')
						{
							$alias_cmd    = $fname;
							$complete_cmd = escapeshellarg($fname);
						}
						else
						{
							$alias_cmd    = 'php '.escapeshellarg($fname);
							$complete_cmd = $alias_cmd;
						}
						echo 'alias '.escapeshellarg($alias).'='.escapeshellarg($alias_cmd)."\n";
						echo 'complete -o bashdefault -o default -C '.escapeshellarg($complete_cmd.' --cliff-complete--').' '.escapeshellarg($alias)."\n";
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