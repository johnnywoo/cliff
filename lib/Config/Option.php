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

require_once __DIR__ . '/Item.php';
require_once __DIR__ . '/../Exception.php';

class Config_Option extends Config_Item
{
	public $flag_value = null;

	public $force_flag_value = false;

	public $aliases = array();

	public function __construct(array $props = array())
	{
		if(isset($props['aliases']) && !is_array($props['aliases']))
			$props['aliases'] = static::make_aliases_list($props['aliases']);

		if(!isset($props['name']))
			$props['name'] = static::make_default_name($props);

		parent::__construct($props);
	}

	public function needs_value()
	{
		return is_null($this->flag_value);
	}

	public function get_short_alias_letters()
	{
		$list = array();
		foreach($this->aliases as $alias)
		{
			if(strlen($alias) == 2)
				$list[] = substr($alias, 1);
		}
		return $list;
	}



	public static function make_default_name($props)
	{
		if(empty($props['aliases']))
			throw new Exception('Flag/option needs at least one alias');

		return ltrim(reset($props['aliases']), '-');
	}

	public static function make_aliases_list($str)
	{
		$list = array();
		foreach(preg_split('/\s+/', $str, -1, PREG_SPLIT_NO_EMPTY) as $alias)
		{
			$len = strlen($alias);

			$c1 = substr($alias, 0, 1);
			$c2 = substr($alias, 1, 1);

			// --long-option => --long-option
			if($len > 2 && $c1 == '-' && $c2 == '-')
			{
				$list[] = $alias;
				continue;
			}

			// -short => -s -h -o -r -t
			if($len > 1 && $c1 == '-' && $c2 != '-')
			{
				for($i = 1; $i < $len; $i++)
				{
					$list[] = '-' . substr($alias, $i, 1);
				}
				continue;
			}

			throw new Exception('Config error: bad option alias '.$alias);
		}
		return $list;
	}
}
