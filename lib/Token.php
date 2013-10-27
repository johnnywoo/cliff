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

class Token
{
	/**
	 * The arg in raw form, as it was written in the command
	 * @var string
	 */
	public $arg = '';

	/**
	 * The word that is parsed out of the arg
	 * @var string
	 */
	public $word = '';

	public function __construct($arg, $word)
	{
		$this->arg  = $arg;
		$this->word = $word;
	}

	/**
	 * Retrieves a part of the arg according to given wordbreaks
	 *
	 * Completion engine (at least in bash) uses different wordbreaks than the shell.
	 * In shell 'a:b' is (usually) one word, while compwords are 'a' and 'b' here.
	 * To make correct prefix for completion results (e.g. '//domain' instead of 'http://domain'),
	 * we need to split the arg the way completion wants it.
	 *
	 * Google 'bash IFS' and 'COMP_WORDBREAKS' for the rest of the lore.
	 *
	 * @param string $wordbreaks
	 * @return string
	 */
	public function get_arg_tail($wordbreaks = " \t\n")
	{
		$wb = preg_quote($wordbreaks);
		if(preg_match('{['.$wb.']([^'.$wb.']*)$}', $this->arg, $m))
			return $m[1];

		return $this->arg;
	}
}
