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

require_once __DIR__ . '/../Config.php';
require_once __DIR__ . '/../Exception.php';

abstract class Config_Item
{
	public $name = '';
	public $description = '';

	/** @var string|callback */
	public $validator  = null; // regexp/callback
	/** @var callback */
	public $callback   = null;
	/** @var string[]|callback */
	public $completion = null;

	public $is_array    = false;
	public $is_required = false;

	public $if_absent = null;

	public $visibility = Config::V_ALL;

	public function __construct(array $props = array())
	{
		if(isset($props[0]))
			$this->description = $props[0];

		foreach($props as $k => $v)
		{
			if(property_exists($this, $k))
				$this->$k = $v;
		}

		if($this->is_array && !array_key_exists('if_absent', $props))
			$this->if_absent = array();
	}

	public function validate(&$value)
	{
		$validator = $this->validator;
		if(!is_null($validator))
		{
			if(is_callable($validator))
				return call_user_func_array($validator, array(&$value));

			if(is_string($validator) && !preg_match($validator, $value))
				return false;

			throw new Exception('Config error: invalid validator for '.$this->name);
		}
		return true;
	}

	public function run_callback()
	{
		if(!is_null($this->callback))
		{
			if(!is_callable($this->callback))
				throw new Exception('Config error: non-callable callback for '.$this->name);

			$args = func_get_args();
			call_user_func_array($this->callback, $args);
		}
	}

	public function complete($entered_value)
	{
		$cpt = $this->completion;

		if(!is_array($cpt) && is_callable($cpt))
			$cpt = call_user_func($cpt, $entered_value);

		if(is_array($cpt) || ($cpt instanceof Traversable))
			return $cpt;

		return array();
	}
}