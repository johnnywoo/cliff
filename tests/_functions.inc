<?

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

// let's not affect autoload, we don't have too much files here
require_once __DIR__.'/../lib/Cliff.php';
require_once __DIR__.'/../lib/Exception.php';
require_once __DIR__.'/../lib/Exception/ParseError.php';
require_once __DIR__.'/../lib/Config.php';
require_once __DIR__.'/../lib/Parser.php';
require_once __DIR__.'/../lib/Tokenizer.php';
require_once __DIR__.'/../lib/Usage.php';
require_once __DIR__.'/../lib/Completion.php';

use cliff\Completion;

function draw_parser($parser, $short_options_with_values = '')
{
	if(!($parser instanceof cliff\Parser))
	{
		$args = $parser;
		if(is_string($args))
			$args = ($args == '') ? array() : explode(' ', $args);

		$parser = new cliff\Parser($args);
	}

	while($row = $parser->read($short_options_with_values))
	{
		$s = array();
		foreach($row as $k=>$v)
		{
			$v = var_export($v, true);

			if($k == 'type')
			{
				if($v == cliff\Parser::TYPE_PARAM)
					$v = 'param';
				if($v == cliff\Parser::TYPE_OPTION)
					$v = 'option';
			}
			$s[] = "$k: " . $v;
		}
		echo join(' ', $s)."\n";
	}
	echo "---\n";
}

function draw_tokenizer($string, $stop_at_offset = -1)
{
	// pipes are used so we don't have to add extra escaping madness,
	// but heading/trailing whitespace is still visible
	echo "> |$string|\n";
	$tk = new cliff\Tokenizer($string);
	while($arg = $tk->read($stop_at_offset))
	{
		echo 'arg |'.$arg->arg.'|  word |'.$arg->word."|\n";
	}
	echo "---\n";
}

function draw_completon($config, $command, $pos = null)
{
	if(is_null($pos))
		$pos = strlen($command);

	ob_start();
	Completion::action_complete($config, array($command, $pos, " \t\n\"'><=;|&(:"));
	$c = ob_get_clean();
	echo str_replace("\n", "|\n", $c);
	echo "---\n";
}