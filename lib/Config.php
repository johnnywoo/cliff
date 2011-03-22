<?php

namespace cliff;

/**
 * A config for CLI options and params
 *
 * Config options:
 * ->flag(names, props)         # <script> --verbose
 * ->option(names, props)       # <script> --db=...
 * ->many_options(names, props) # <script> --exclude file --exclude file2
 * ->param(name, props)         # <script> file
 * ->many_params(name, props)   # <script> file file2 file3
 * ->desc(text)
 * ->allow_unknown_options(state)
 *
 * General props: is_array, is_required, validator, callback.
 * Flag/option props: default, if_absent, name.
 *
 *
 * ELEMENT PROPERTIES in $props:
 *
 * Every entry here can have certain properties, which are provided to config in form of an array.
 * First element with numeric key is treated as a title (first line) and description. Blank lines
 * are preserved, single line breaks are not.
 *
 * Non-numeric keys may be:
 *
 * (common preferences for flags, options and params)
 *
 *  * is_array:  If true, the arg is allowed to be set multiple times, and all values are
 *               accumulated in an array.
 *
 *  * is_required  The entry must be set in script arguments at least once.
 *                 Defaults to TRUE for params and FALSE for flags/options.
 *
 *                 Note that param validation is possessive: if a param is valid, it grabs the arg
 *                 and does not let go. If you have two params:
 *                   <script> <a> <b>
 *                 configured to <a> not required and <b> required (with same validation or without it)
 *                 it will not work as expected: <a> will grab the arg and <b> is required.
 *
 *  * validator: A regexp or callback (which is called with reference to value as first argument
 *               and should return bool); validator will be called immediately after the value is
 *               parsed from script arguments, so $_REQUEST will not be filled yet.
 *               For array options/params the callback will be called for each element of the array.
 *               WARNING: the callback receives a reference to value as its argument!
 *               Even if your callback is defined as function($value){} with no reference,
 *               $value is still a reference there and changing it will affect $_REQUEST.
 *
 *  * callback:  Will be called if the entity is found in script arguments (after all arguments
 *               are parsed and $_REQUEST is filled); this callback does not receive any arguments.
 *               For array options/params the callback will be called for each element of the array.
 *
 * (preferences for flags and options)
 *
 *  * default    Value for $_REQUEST if the option/flag is present in script arguments
 *               without a value (--x, but not --x=1). If default is not set or set NULL
 *               for an option, that option will require a value, causing an error without it.
 *               Defaults to TRUE for flags.
 *
 *  * if_absent  Value for $_REQUEST if the option/flag was not set in script arguments.
 *               Defaults to NULL for options and FALSE for flags.
 *
 *  * name:      Name for $_REQUEST, if you don't like the default one
 *               (which is 'name' for --name and 'n' for -n).
 *
 *  * visibility: Defines places where the option/flag will be visible.
 *                Bitmask of following constants:
 *                 * Config::V_USAGE
 *                 * Config::V_HELP
 *                 * Config::V_COMPLETION
 *                Defaults to all of them.
 */
class Config
{
	const V_USAGE      = 1;
	const V_HELP       = 2;
	const V_COMPLETION = 4;
	const V_ALL        = 7;

	/**
	 * Sets a description for the program
	 *
	 * First line of description is used as a summary for short usage.
	 *
	 * @param string $description
	 * @return Config self
	 */
	public function desc($description)
	{
		$this->description = $description;
		return $this;
	}

	public function allow_unknown_options($state = true)
	{
		$this->allow_unknown_options = $state;
		return $this;
	}

	/**
	 * Registers a param
	 *
	 * A param is a non-option positional argument.
	 *
	 * See Config class docblock about $props.
	 *
	 * If is_array prop is set to true, Cliff will grab each argument that passes validation
	 * (this means all of them if there is no validator given). If there is no valid param,
	 * an error will happen.
	 *
	 * @param string $name
	 * @param array $props
	 * @return Config self
	 */
	public function param($name, $props = array())
	{
		if(isset($props['name'])) // fool-proof
			$name = $props['name'];

		if(isset($this->params[$name]) || isset($this->options[$name]))
			throw new Exception('Config error: duplicate name '.$name);

		if(!array_key_exists('is_required', $props))
			$props['is_required'] = true;

		$this->params[$name] = $props;

		return $this;
	}

	/**
	 * Registers a param which can be given many times
	 *
	 * This behaves exactly as param() with is_array = true.
	 * @see Config::param()
	 *
	 * @param string $name
	 * @param array $props
	 * @return Config self
	 */
	public function many_params($name, $props = array())
	{
		$props['is_array'] = true;
		return $this->param($name, $props);
	}

	/**
	 * Registers a flag (--x or -x)
	 *
	 * Flag is a no-value boolean kind of option, so everything works the same
	 * way (see option() below).
	 *
	 * $_REQUEST will have TRUE if the flag is set in script arguments and
	 * FALSE otherwise. Flags silently ignore attempts to give them values.
	 *
	 * See Config class docblock about $props.
	 *
	 * @param string $name
	 * @param array  $props
	 * @return Config self
	 */
	public function flag($name, $props = array())
	{
		$props['default']   = true;
		$props['if_absent'] = false;
		$props['_force_default_value'] = true;
		return $this->option($name, $props);
	}

	/**
	 * Registers an option (--x=1 or -x1)
	 *
	 * Name is a space-separated list of aliases, like this:
	 * '--help -h? --omgwtf'
	 * First alias with leading dashes stripped will be used as primary name.
	 * You can also specify name in $props (useful for single-letter options).
	 *
	 * '-h?' in the example above defines two single-letter aliases: -h and -?.
	 *
	 * Options should be specified before first param (you can get over this
	 * by using branches). There is also a special argument '--' (two dashes),
	 * which separates options from arguments. Anything after -- or first non-
	 * option argument is treated as a param.
	 *
	 * Options themselves are not positional, that is, they do not need to be
	 * specified in script aruments in order they are declared (as opposed to
	 * params, which are positional).
	 *
	 * See Config class docblock about $props.
	 *
	 * @param string $name
	 * @param array  $props
	 * @return Config self
	 */
	public function option($name, $props = array())
	{
		$names = preg_split('/\s+/', $name, -1, PREG_SPLIT_NO_EMPTY);
		if(empty($names))
			throw new Exception('Config error: wrong option name definition "'.$name.'"');

		$main_name = empty($props['name']) ? ltrim(reset($names), '-') : $props['name'];

		if(isset($this->params[$main_name]) || isset($this->options[$main_name]))
			throw new Exception('Config error: duplicate name '.$main_name);

		// props
		if(!isset($props['default']))
			$props['default'] = null;
		if(!isset($props['if_absent']))
			$props['if_absent'] = null;
		if(!array_key_exists('visibility', $props))
			$props['visibility'] = self::V_ALL;
		$props['name'] = $main_name;
		$props['_first_alias'] = reset($names);

		// registering name aliases
		foreach($names as $n)
		{
			if(preg_match('/^-[^-]/', $n))
			{
				// -abc is three aliases, not one
				for($i = 1; $i < strlen($n); $i++)
				{
					$letter = substr($n, $i, 1);
					$alias = '-'.$letter;
					$this->add_option_alias($main_name, $alias);
					if(is_null($props['default']))
						$this->short_options_with_values .= $letter;
				}
			}
			else if(preg_match('/^--\S/', $n))
			{
				$this->add_option_alias($main_name, $n);
			}
			else
			{
				throw new Exception('Config error: wrong option name "'.$n.'"');
			}
		}

		$this->options[$main_name] = $props;
		$this->option_values[$main_name] = $props['if_absent'];
		if(!empty($props['is_required']))
			$this->required_options[] = $main_name;

		return $this;
	}

	/**
	 * Registers an option which can be set many times
	 *
	 * This behaves exactly as option() with is_array = true.
	 * @see Config::option()
	 *
	 * @param string $name
	 * @param mixed  $default
	 * @param array  $props
	 * @return Config self
	 */
	public function many_options($name, $default = null, $props = array())
	{
		$props['is_array'] = true;
		return $this->option($name, $default, $props);
	}

	/**
	 * Registers a branch in usage
	 *
	 * CLI command is nested with other commands:
	 * script --script_option subcommand --subcommand_option [...]
	 * So, the whole usage config becomes a tree of subcommands. This way, if your
	 * script has two subcommands, you will have a top-level config with two branches
	 * in it, one for each subcommand. A branch is just another Config object.
	 *
	 * @param Config $config
	 * @return Config self
	 */
	public function branch(Config $config)
	{
		$this->branches[] = $config;
		return $this;
	}





	//
	// MACHINERY
	//

	public $params = array();

	public $option_name_aliases       = array();
	public $options                   = array();
	public $required_options          = array();
	public $short_options_with_values = '';
	public $allow_unknown_options     = false;

	public $param_values  = array();
	public $option_values = array();

	/** @var Config[] */
	public $branches = array();

	public $description = '';

	protected $callbacks = array();


	protected function add_option_alias($name, $alias)
	{
		if(isset($this->option_name_aliases[$alias]))
			throw new Exception('Config error: trying to readd option '.$alias);

		$this->option_name_aliases[$alias] = $name;
	}

	public function load_from_parser(Parser $parser, $incomplete_mode = false)
	{
		$params_stack     = array_keys($this->params);
		$required_options = array_flip($this->required_options);

		$last_item = false;
		while($item = $parser->read($this->short_options_with_values))
		{
			if($item['type'] == Parser::TYPE_OPTION)
				$this->register_option($item, $required_options);

			if($item['type'] == Parser::TYPE_PARAM)
				$this->register_param($item, $params_stack);

			$last_item = $item;
		}

		if(!$incomplete_mode)
			$this->validate_command($params_stack, $required_options);

		return $last_item;
	}

	protected function validate_command($params_stack, $required_options)
	{
		// allowed leftower params:
		// non-required params
		// required array params with non-empty arrays
		foreach($params_stack as $name)
		{
			$param = $this->params[$name];

			if(empty($param['is_required']))
				continue;

			if(!empty($param['is_array']) && !empty($this->param_values[$name]))
				continue;

			throw new Exception_ParseError('Need more arguments', Exception_ParseError::E_NO_PARAM);
		}

		if(!empty($required_options))
		{
			$list = array();
			foreach($required_options as $name=>$foo)
			{
				$list[] = $this->options[$name]['_first_alias'];
			}
			throw new Exception_ParseError('Need value'.(count($list)>1?'s':'').' for '.join(', ', $list), Exception_ParseError::E_NO_OPTION);
		}
	}

	protected function register_option($item, &$required_options)
	{
		if(!isset($this->option_name_aliases[$item['name']]))
		{
			if(!$this->allow_unknown_options)
				throw new Exception_ParseError('Unknown option '.$item['name'], Exception_ParseError::E_WRONG_OPTION);

			$name = ltrim($item['name'], '-');
			$option = array(
				'default'      => is_null($item['value']) ? true : null, // unknown flags get TRUE
				'name'         => $name,
				'_first_alias' => $item['name'],
			);
		}
		else
		{
			$name = $this->option_name_aliases[$item['name']];
			$option = $this->options[$name];
		}

		if(is_null($option['default']) && is_null($item['value']))
			throw new Exception_ParseError('No value for '.$item['name'], Exception_ParseError::E_NO_OPTION_VALUE);

		$value = is_null($item['value']) ? $option['default'] : $item['value'];

		if(!$this->validate($value, $option))
			throw new Exception_ParseError('Incorrect value for '.$item['name'], Exception_ParseError::E_NO_OPTION_VALUE);

		$this->register_callback($option, $item['name']);

		if(!empty($option['is_array']))
			$this->option_values[$name][] = $value;
		else
		{
			$this->option_values[$name] = $value;
		}

		unset($required_options[$name]); // unset ignores undefined keys

		return $option;
	}

	protected function register_param($item, &$params_stack)
	{
		if(empty($params_stack))
			throw new Exception_ParseError('Too many arguments', Exception_ParseError::E_TOO_MANY_PARAMS);

		$name = reset($params_stack);
		$param = $this->params[$name];

		$value = $item['value'];

		$is_valid = $this->validate($value, $param);

		if(!$is_valid && !$param['is_required'])
		{
			// okay, it is not valid, but the param is not required,
			// so we just continue to the next arg
			array_shift($params_stack);
			return $this->register_param($item, $params_stack);
		}

		if(!empty($param['is_array']))
		{
			if(!$is_valid)
			{
				// array needs one or more values
				if(empty($this->param_values[$name]))
					throw new Exception_ParseError('Unexpected "'. $value .'" in place of '.$name, Exception_ParseError::E_WRONG_PARAM);

				// okay, now it is not valid, but there is something in the array
				// so we just continue to the next arg
				array_shift($params_stack);
				return $this->register_param($item, $params_stack);
			}

			$this->param_values[$name][] = $value;
		}
		else
		{
			if(!$is_valid)
				throw new Exception_ParseError('Unexpected "'. $value .'" in place of '.$name, Exception_ParseError::E_WRONG_PARAM);

			$this->param_values[$name] = $value;
			array_shift($params_stack);
		}

		$this->register_callback($param, $name);

		return $param;
	}

	public function get_request()
	{
		return $this->param_values + $this->option_values;
	}

	protected function validate(&$value, $props)
	{
		if(isset($props['validator']))
		{
			$validator = $props['validator'];

			if(is_callable($validator))
				return call_user_func_array($validator, array(&$value));

			if(is_string($validator) && !preg_match($validator, $value))
				return false;
		}
		return true;
	}

	/**
	 * Registers a callback for later calling
	 *
	 * Callbacks are called after all arguments are parsed,
	 * so we need to accumulate them and then call later.
	 *
	 * @param array $props
	 * @param string $item_desc
	 */
	protected function register_callback($props, $item_desc = '')
	{
		if(!empty($props['callback']))
			$this->callbacks[] = array($props['callback'], $item_desc);
	}

	/**
	 * Runs appropriate callbacks for options and params
	 *
	 * We need to call this when $_REQUEST is already filled with values,
	 * so this call is done externally.
	 */
	public function run_callbacks()
	{
		foreach($this->callbacks as $row)
		{
			list($cb, $desc) = $row;
			if(!is_callable($cb))
				throw new Exception('Non-callable callback for '.$desc);

			call_user_func($cb);
		}
	}

	public function get_options_for_usage($visibility = self::V_ALL)
	{
		$options = $this->options;

		foreach($options as $k=>$option)
		{
			if(!($option['visibility'] & $visibility))
				unset($options[$k]);
		}

		foreach($this->option_name_aliases as $alias=>$name)
		{
			if(isset($options[$name]))
				$options[$name]['aliases'][] = $alias;
		}

		return $options;
	}

	public function complete($args, $current_arg)
	{
		$this->callbacks = array();
		array_pop($args); // last one is being completed
		$parser = new Parser($args);
		$this->load_from_parser($parser, true);

		$competions = array();
		if(substr($current_arg, 0, 1) == '-' && $parser->are_options_allowed())
		{
			foreach($this->option_name_aliases as $alias=>$name)
			{
				// ignore one-letter aliases
				if(strlen($alias) == 2)
					continue;

				$opt = $this->options[$name];
				if(!($opt['visibility'] & self::V_COMPLETION))
					continue;

				if(is_null($opt['default']))
					$alias .= '=';

				$competions[] = $alias;
			}
		}

		return $competions;
	}
}