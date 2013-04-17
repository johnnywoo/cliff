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

namespace cliff;

require_once __DIR__.'/Config.php';
require_once __DIR__.'/Config/Option.php';

class Usage
{
	/** @var Config */
	private $config;

	public $width = 100;

	public $max_term_length    = 20;
	public $term_padding_left  = 2;
	public $term_padding_right = 2;

	public $max_options_listed = 5;

	public $is_help_mode = false;

	public function __construct(Config $config)
	{
		$this->config = $config;
	}

	public function make()
	{
		$desc = '';
		if($this->is_help_mode && $this->config->description != '')
		{
			$desc = $this->format_description($this->config->description);
			$desc = "\n".$this->wrap($desc)."\n";
		}

		return 'Usage: ' . $this->make_usage() . "\n" . $desc
			. $this->make_options_block()
			. $this->make_params_block()
			. $this->make_commands_block();
	}

	public function make_usage($script_name = null)
	{
		if(is_null($script_name))
			$script_name = $this->config->script_name;

		$usage = $script_name;

		/** @var $options Config_Option[] */
		$options = $this->get_items_by_visibility(__NAMESPACE__.'\Config_Option');
		if(count($options))
		{
			$aliases = array();
			$short_options = '';
			foreach($options as $option)
			{
				foreach($this->makeup_option_aliases($option) as $alias)
				{
					if(substr($alias, 0, 2) != '--' && !$option->needs_value())
						$short_options .= substr($alias, 1);
					else
						$aliases[] = $alias;
				}
			}
			if($short_options)
				$usage .= ' [-' . $short_options . ']';
			$usage .= count($aliases) > $this->max_options_listed ? ' [options]' : ' [' . join('] [', $aliases) . ']';
		}

		/** @var $param Config_Param */
		foreach($this->get_items_by_visibility(__NAMESPACE__.'\Config_Param') as $param)
		{
			$usage .= ' <'.$param->name.'>';
		}

		return $usage;
	}

	private function make_options_block()
	{
		$lines = array();
		/** @var $options Config_Option[] */
		$options = $this->get_items_by_visibility(__NAMESPACE__.'\Config_Option');
		foreach($options as $option)
		{
			$term = join(', ', $this->makeup_option_aliases($option));
			$title = '';
			if($option->description != '')
				$title = $this->format_description($option->description);

			$lines[] = array($term, $title);
		}
		return $this->make_definition_list($lines, $this->is_help_mode ? 'OPTIONS' : '');
	}

	private function make_params_block()
	{
		$lines = array();
		/** @var $param Config_Param */
		foreach($this->get_items_by_visibility(__NAMESPACE__.'\Config_Param') as $param)
		{
			$title = $this->format_description($param->description);
			$lines[] = array('<'.$param->name.'>', $title);
		}
		if(count($lines) == 1 && $param->use_for_commands)
			$lines = array();
		return $this->make_definition_list($lines, 'PARAMETERS');
	}

	private function make_commands_block()
	{
		$lines = array();
		foreach($this->config->get_branches() as $config)
		{
			$usage = new Usage($config);
			$title = $this->format_description($config->description);
			$lines[] = array($usage->make_usage(), $title);
		}
		return $this->make_definition_list($lines, 'COMMANDS');
	}

	private function make_definition_list($lines, $title)
	{
		if(empty($lines))
			return '';

		// finding out max term length
		$max_length = 0;
		foreach($lines as $row)
		{
			$l = strlen($row[0]);
			if($l > $this->max_term_length)
				$l = 0;
			$max_length = max($max_length, $l);
		}
		$max_length = min($max_length, $this->max_term_length);

		$column_offset = $this->term_padding_left + $this->term_padding_right;
		if(!$this->is_help_mode)
			$column_offset += $max_length;

		$text = "\n";
		if($title != '')
			$text .= "$title\n";
		foreach($lines as $row)
		{
			$line = $this->wrap($row[0], $this->term_padding_left, true);
			if(($row[1] != '' && strlen($row[0]) > $max_length) || $this->is_help_mode)
			{
				// long desc/long param summary: make an indent on the next line
				$line .= "\n".str_repeat(' ', $column_offset);
			}
			else if($row[1] != '') // do not make trailing spaces
			{
				// short desc: add spaces to form descriptions into a column
				$line = str_pad($line, $column_offset, ' ');
			}

			$line .= $this->wrap($row[1], $column_offset);

			if($this->is_help_mode)
				$text .= "\n";

			$text .= $line."\n";
		}
		return $text;
	}

	private function wrap($text, $padding_left = 0, $pad_first_line = false)
	{
		$width = $this->width - $padding_left;
		$out = '';
		$padding = str_repeat(' ', $padding_left);
		foreach(explode("\n", wordwrap($text, $width, "\n", true)) as $i=>$line)
		{
			if($i || $pad_first_line)
				$out .= $padding;
			$out .= $line."\n";
		}
		$out = substr($out, 0, -1);
		// removing trailing spaces
		$out = preg_replace('/[ \t]+(?=\n|$)/', '', $out);
		return $out;
	}

	private function makeup_option_aliases(Config_Option $option)
	{
		$aliases = $option->aliases;
		if($option->needs_value())
		{
			foreach($aliases as &$v)
			{
				$v .= (substr($v, 0, 2) == '--') ? '=...' : ' ...';
			}
		}

		// moving short names up
		usort($aliases, array('static', 'option_alias_cmp'));

		return $aliases;
	}

	private static function option_alias_cmp($a, $b) {
		$as = (substr($a, 0, 2) == '--');
		$bs = (substr($b, 0, 2) == '--');
		if($as != $bs)
			return $as ? 1 : -1;
		return strcmp($a, $b);
	}

	private function format_description($text)
	{
		// removing meaningless indent
		$text = static::unindent($text);

		if(!$this->is_help_mode)
			list($text) = explode("\n", $text, 2);

		return $text;
	}

	private function get_items_by_visibility($class)
	{
		$visibility = $this->is_help_mode ? Config::V_HELP : Config::V_USAGE;

		$list = array();

		foreach($this->config->get_items() as $option)
		{
			if($option instanceof $class && $option->visibility & $visibility)
				$list[] = $option;
		}

		return $list;
	}

	/**
	 * Strips extra indentation from a string
	 *
	 * unindent() finds a common indent in your string and strips it away,
	 * so you can write descriptions for config items without having to
	 * break indentation. It also trims the string.
	 *
	 * @param string $desc
	 * @return string
	 */
	public static function unindent($desc)
	{
		$indent = '';
		$indent_length = 0;
		$lines = explode("\n", $desc);
		foreach($lines as $i=>$line)
		{
			if($line == '')
				continue;

			// we grab the first non-empty string prefix, except for the first line
			if($indent == '' && trim($line) != '' && preg_match('/^[\t ]+/', $line, $m))
			{
				// if first line does not have indent, we ignore it and look for the next line
				if(!strlen($m[0]) && $i == 0)
					continue;

				$indent = $m[0];
				$indent_length = strlen($indent);
			}

			if(substr($line, 0, $indent_length) == $indent)
				$lines[$i] = substr($line, $indent_length);
		}

		return trim(join("\n", $lines));
	}
}
