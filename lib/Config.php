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
require_once __DIR__.'/Config/Param.php';
require_once __DIR__.'/Config/Option.php';

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
 * General props: name, is_array, is_required, validator, callback, default, visibility.
 * Flag/option props: flag_value.
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
 *  * name         Name for $_REQUEST, if you don't like the default one (which is 'name' for --name
 *                 and 'n' for -n). For params, the name is set in param()'s first arg, but you can
 *                 nevertheless override it by setting name in $props.
 *
 *  * is_array     If true, the arg is allowed to be set multiple times, and all values are
 *                 accumulated in an array.
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
 *  * validator    A regexp or callback (which is called with reference to value as first argument
 *                 and should return bool); validator will be called immediately after the value is
 *                 parsed from script arguments, so $_REQUEST will not be filled yet.
 *                 For array options/params the callback will be called for each element of the array.
 *
 *                 WARNING: the validation callback receives a reference to value as its argument!
 *                 Even if your callback is defined as function($value){} with no reference,
 *                 $value is still a reference there and changing it will affect $_REQUEST.
 *
 *                 Also keep in mind that validation callback will be called for bash completion,
 *                 so it should not output anything or exit the script. For that, use regular callback.
 *
 *  * callback     Will be called immediately after the value is parsed from script arguments
 *                 (after validation callback if there is one), so $_REQUEST will not be filled yet.
 *                 For array options/params the callback will be called for each element of the array.
 *                 The callback receives parsed value as its argument.
 *
 *  * completion   Array of possible values or a callback returning such array.
 *                 Use a closure if you need to pass a class method as callback!
 *                 The callback receives the word being completed in first argument.
 *
 *  * default      Value for $_REQUEST if the arg was not set in script arguments.
 *                 Defaults to NULL for options/params (or an empty array if is_array is set to TRUE)
 *                 and FALSE for flags.
 *
 *  * visibility   Defines places where the item will be visible.
 *                 Bitmask of following constants:
 *                  * Config::V_USAGE
 *                  * Config::V_HELP
 *                  * Config::V_COMPLETION (for options this only affects completion of the option name)
 *                  * Config::V_REQUEST
 *                 Defaults to all of them.
 *
 *  * use_for_commands  Use a param for detection of commands.
 *                      A config should only have one such param.
 *
 * (preferences for flags and options)
 *
 *  * flag_value   Value for $_REQUEST if the option/flag is present in script arguments
 *                 without a value (--x, but not --x=1). If flag_value is not set or set to NULL
 *                 for an option, that option will require a value, causing an error without it.
 *                 Defaults to TRUE for flags.
 */
class Config
{
	const V_NONE       = 0;
	const V_USAGE      = 1;
	const V_HELP       = 2;
	const V_COMPLETION = 4;
	const V_REQUEST    = 8;
	const V_ALL        = 15;

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

	/**
	 * Sets a script name for usage/help
	 *
	 * @param string $script_name
	 * @return Config self
	 */
	public function script_name($script_name)
	{
		$this->script_name = $script_name;
		return $this;
	}

	/**
	 * If true, unknown options and flags will not be treated as parse errors
	 *
	 * @param bool $state
	 * @return Config self
	 */
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
	 * @throws Exception
	 * @return Config self
	 */
	public function param($name, $props = array())
	{
		if($this->command_param_name != '')
			throw new Exception('Extra params are not allowed after a command detection one');

		if(!is_array($props))
			$props = array($props);

		if(!isset($props['name']))
			$props['name'] = $name;

		if(!empty($props['use_for_commands']))
		{
			if($this->command_param_name != '')
				throw new Exception('There must be only one command detection param; you should specify your own param BEFORE adding commands');

			$this->command_param_name = $props['name'];
		}

		$this->items[] = new Config_Param($props);

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
		if(!is_array($props))
			$props = array($props);

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
		if(!is_array($props))
			$props = array($props);

		if(!isset($props['flag_value']))
			$props['flag_value'] = true;

		if(!isset($props['default']))
			$props['default'] = false;

		$props['force_flag_value'] = true;

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
		if(!is_array($props))
			$props = array($props);

		if(!isset($props['aliases']))
			$props['aliases'] = $name;

		$this->items[] = new Config_Option($props);

		return $this;
	}

	/**
	 * Registers an option which can be set many times
	 *
	 * This behaves exactly as option() with is_array = true.
	 * @see Config::option()
	 *
	 * @param string $name
	 * @param array  $props
	 * @return Config self
	 */
	public function many_options($name, $props = array())
	{
		if(!is_array($props))
			$props = array($props);

		$props['is_array'] = true;
		return $this->option($name, $props);
	}

	/**
	 * Registers a subcommand config
	 *
	 * If the branch detection param has given value, the rest of options will be
	 * parsed with the subcommand config.
	 *
	 * If there's no param with use_for_commands yet, first call to command() will add
	 * a standard one with name 'command'. Validator, completion and description will be
	 * supplied into the param if the corresponding field is not filled yet.
	 *
	 * @param string $param_value
	 * @param Config $config
	 * @return Config self
	 */
	public function command($param_value, self $config)
	{
		if($this->command_param_name == '')
			$this->param('command', array('use_for_commands' => true));

		Cliff::add_default_options($config);
		$config->script_name($param_value);

		$aliases = preg_split('/\s+/', $param_value, -1, PREG_SPLIT_NO_EMPTY);
		$name = reset($aliases);

		$this->branches[$name] = $config;
		foreach($aliases as $alias)
		{
			$this->branch_aliases[$alias] = $name;
		}

		return $this;
	}








	//
	// MACHINERY
	//

	public $description = '';
	public $allow_unknown_options = '';
	public $script_name = '';

	/**
	 * @var Config_Item[]
	 */
	private $items;

	private $command_param_name = '';

	/**
	 * @var Config[]
	 */
	private $branches = array();
	private $branch_aliases = array();

	public function get_items()
	{
		return $this->items;
	}

	public function get_script_name()
	{
		return $this->script_name;
	}

	/**
	 * Returns list of options in the config
	 *
	 * Options can be distinguished by alias, so the returned array
	 * has aliases as keys. Remember that one option can have multiple
	 * aliases, and therefore may appear in the list may times!
	 *
	 * @return Config_Option[]
	 */
	public function get_options()
	{
		$list = array();
		/** @var $item Config_Option */
		foreach($this->items as $item)
		{
			if(!($item instanceof Config_Option))
				continue;

			foreach($item->aliases as $alias)
			{
				$list[$alias] = $item;
			}
		}
		return $list;
	}

	/**
	 * Returns list of params in the config
	 *
	 * Params are strictly positional, so the returned list nas numeric keys.
	 *
	 * @return Config_Param[]
	 */
	public function get_params()
	{
		$list = array();
		foreach($this->items as $item)
		{
			if($item instanceof Config_Param)
			{
				if($this->command_param_name == $item->name)
					$this->prepare_command_item($item);

				$list[] = $item;
			}
		}
		return $list;
	}

	/**
	 * Prepares the command detection item
	 *
	 * In theory it's possible to have options/flags as command detectors,
	 * so we'll do it the general way where possible even though we only support
	 * detection via params.
	 *
	 * @param Config_Item $param
	 */
	private function prepare_command_item(Config_Item $param)
	{
		$branches = $this->branches;
		$aliases  = $this->branch_aliases;

		if(empty($param->completion))
			$param->completion = array_keys($aliases);

		$old_validator = $param->validator;
		$param->validator = function(&$val) use($aliases, $old_validator, $param) {
			/** @var $param Config_Param */
			if(!Config_Item::run_validator($old_validator, $val, 'Config error: invalid validator for '.$param->name))
				return false;

			if(isset($aliases[$val]))
			{
				$val = $aliases[$val];
				return true;
			}

			return false;
		};

		if(empty($param->description))
		{
			$param->description = "Available commands: ".join(', ', array_keys($branches))."\n";
			foreach($branches as $name => $config)
			{
				$usage = new Usage($config);
				$param->description .= "\n".$usage->make_usage($name);
			}
			$param->description .= "\n\nUse `<subcommand> --help` to learn more.";
		}
	}

	public function get_short_options_with_values()
	{
		$str = '';

		/** @var $item Config_Option */
		foreach($this->items as $item)
		{
			if(!($item instanceof Config_Option))
				continue;

			if(!$item->needs_value())
				continue;

			$str .= join('', $item->get_short_alias_letters());
		}

		return $str;
	}

	/**
	 * Returns branch name if it exists
	 *
	 * @param string $alias
	 * @return string
	 */
	public function get_branch_name($alias)
	{
		return isset($this->branch_aliases[$alias]) ? $this->branch_aliases[$alias] : null;
	}

	/**
	 * Returns branch config if it exists
	 *
	 * @param string $name  proper name, not an alias!
	 * @return Config|null
	 */
	public function get_branch($name)
	{
		return isset($this->branches[$name]) ? $this->branches[$name] : null;
	}
}
