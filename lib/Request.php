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

require_once __DIR__ . '/Config.php';
require_once __DIR__ . '/Parser.php';
require_once __DIR__ . '/Config/Item.php';
require_once __DIR__ . '/Config/Option.php';
require_once __DIR__ . '/Config/Param.php';
require_once __DIR__ . '/Exception/ParseError.php';

class Request
{
	/** @var Config */
	public $config;

	public $incomplete_mode   = false;
	public $disable_callbacks = false;

	/** @var Config_Option[] */
	public $options = array();
	/** @var Config_Param[] */
	public $params_stack  = array();

	protected $registered_items = array();

	public function load(Config $config, Parser $parser)
	{
		$this->config = $config;

		$short_options_with_values = $this->config->get_short_options_with_values();

		$this->registered_items = array();
		$this->options       = $this->config->get_options();
		$this->params_stack  = $this->config->get_params();

		foreach($this->config->get_items() as $item)
		{
			$this->set_request_val($item);
		}

		while($item = $parser->read($short_options_with_values))
		{
			if($item['type'] == Parser::TYPE_OPTION)
				$this->register_option($this->options, $item);

			if($item['type'] == Parser::TYPE_PARAM)
				$this->register_param($this->params_stack, $item);
		}
	}

	protected function set_request_val(Config_Item $item, $value = null)
	{
		if(!($item->visibility & Config::V_REQUEST))
			return;

		$name = $item->name;
		$is_array = $item->is_array;

		if(func_num_args() == 1)
		{
			// initialization mode
			$value = $item->if_absent;
			// absent value should not be wrapped in array
			$is_array = false;
		}
		else
		{
			// actual value was registered
			$this->registered_items[] = $item;
		}

		if($is_array)
		{
			if(!isset($_REQUEST[$name]) || !is_array($_REQUEST[$name]))
				$_REQUEST[$name] = array();

			$_REQUEST[$name][] = $value;
		}
		else
		{
			$_REQUEST[$name] = $value;
		}
	}

	protected function is_registered(Config_Item $item)
	{
		return in_array($item, $this->registered_items);
	}

	protected function register_option($options, $item)
	{
		if(isset($options[$item['name']]))
		{
			$option = $options[$item['name']]; // item.name is actually an alias (--smth)
		}
		else
		{
			if(!$this->config->allow_unknown_options)
				throw new Exception_ParseError('Unknown option '.$item['name'], Exception_ParseError::E_WRONG_OPTION);

			$option = new Config_Option(array(
				'aliases' => $item['name'],
				'default' => is_null($item['value']) ? true : null, // unknown flags get TRUE
			));
		}

		if($option->needs_value() && is_null($item['value']))
			throw new Exception_ParseError('No value for '.$item['name'], Exception_ParseError::E_NO_OPTION_VALUE);

		$value = (is_null($item['value']) || $option->force_default_value) ? $option->default : $item['value'];

		if(!$option->validate($value))
			throw new Exception_ParseError('Incorrect value for '.$item['name'], Exception_ParseError::E_NO_OPTION_VALUE);

		$this->set_request_val($option, $value);

		if(!$this->disable_callbacks)
			$option->run_callback($value);

		return $option;
	}

	protected function register_param(&$params, $item)
	{
		if(empty($params))
			throw new Exception_ParseError('Too many arguments', Exception_ParseError::E_TOO_MANY_PARAMS);

		/** @var $param Config_Param */
		$param = reset($params);

		$value = $item['value'];

		$is_valid = $param->validate($value);

		if(!$is_valid && !$param->is_required)
		{
			// okay, it is not valid, but the param is not required,
			// so we just continue to the next param
			array_shift($params);
			return $this->register_param($params, $item);
		}

		if($param->is_array)
		{
			if(!$is_valid)
			{
				// array needs one or more values
				if(!$this->is_registered($param))
					throw new Exception_ParseError('Unexpected "'. $value .'" in place of '.$param->name, Exception_ParseError::E_WRONG_PARAM);

				// okay, now it is not valid, but there is something in the array
				// so we just continue to the next param
				array_shift($params);
				return $this->register_param($params, $item);
			}

			$this->set_request_val($param, $value);
		}
		else
		{
			if(!$is_valid)
				throw new Exception_ParseError('Unexpected "'. $value .'" in place of '.$param->name, Exception_ParseError::E_WRONG_PARAM);

			$this->set_request_val($param, $value);
			array_shift($params);
		}

		if(!$this->disable_callbacks)
			$param->run_callback($value);

		return $param;
	}


	/**
	 * Validates the request state after it's been loaded
	 */
	public function validate()
	{
		// allowed leftower params:
		// non-required params
		// required array params with non-empty arrays
		foreach($this->params_stack as $param)
		{
			if(!$param->is_required)
				continue;

			if($param->is_array && $this->is_registered($param))
				continue;

			throw new Exception_ParseError('Need more arguments', Exception_ParseError::E_NO_PARAM);
		}

		$list = array();
		foreach($this->options as $option)
		{
			if(!$option->is_required || $this->is_registered($option))
				continue;

			$list[] = reset($option->aliases);
		}
		if(!empty($list))
			throw new Exception_ParseError('Need value'.(count($list)>1?'s':'').' for '.join(', ', $list), Exception_ParseError::E_NO_OPTION);
	}

	/**
	 * @return Config_Param[]
	 */
	public function get_allowed_params()
	{
		if(empty($this->params_stack))
			return array();

		$params = array();
		reset($this->params_stack);
		do
		{
			// we need all non-required params and the first required
			/** @var $p Config_Param */
			$p = current($this->params_stack);
			$params[] = $p;
		}
		while(next($this->params_stack) && !$p->is_required);

		return $params;
	}
}