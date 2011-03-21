<?

namespace cliff;

class Usage
{
	/** @var Config */
	private $config;

	public $width = 100;

	public $max_term_length    = 20;
	public $term_padding_left  = 2;
	public $term_padding_right = 2;

	public $max_options_listed = 5;

	public $long_descriptions = false;

	public function __construct(Config $config)
	{
		$this->config = $config;
	}

	public function make()
	{
		$desc = '';
		if($this->config->description != '')
		{
			$desc = $this->reduce_description($this->config->description);
			$desc = "\n".$this->wrap($desc)."\n";
		}

		return $this->make_usage() . "\n" . $desc . $this->make_options_block() . $this->make_params_block();
	}

	public function make_usage($script_name = null)
	{
		if(is_null($script_name))
			$script_name = basename($_SERVER['argv'][0]);

		$usage = 'Usage: '.$script_name;

		$options = $this->config->get_options_for_usage();
		if(count($options))
		{
			$aliases = array();
			$short_options = '';
			foreach($options as $option)
			{
				$option = $this->makeup_option_aliases($option);
				foreach($option['aliases'] as $alias)
				{
					$needs_value = is_null($option['default']);
					if(substr($alias, 0, 2) != '--' && !$needs_value)
						$short_options .= substr($alias, 1);
					else
						$aliases[] = $alias;
				}
			}
			if($short_options)
				$usage .= ' [-' . $short_options . ']';
			$usage .= count($aliases) > $this->max_options_listed ? ' [options]' : ' [' . join('] [', $aliases) . ']';
		}

		foreach($this->config->params as $name=>$param)
		{
			$usage .= ' <'.$name.'>';
		}

		return $usage;
	}

	public function make_options_block()
	{
		$lines = array();
		foreach($this->config->get_options_for_usage() as $option)
		{
			$option = $this->makeup_option_aliases($option);
			$term = join(', ', $option['aliases']);
			$title = '';
			if(!empty($option[0]))
				$title = $this->reduce_description($option[0]);

			$lines[] = array($term, $title);
		}
		return $this->make_definition_list($lines, 'OPTIONS');
	}

	public function make_params_block()
	{
		$lines = array();
		foreach($this->config->params as $name=>$param)
		{
			$title = '';
			if(!empty($param[0]))
				$title = $this->reduce_description($param[0]);

			$lines[] = array($name, $title);
		}
		return $this->make_definition_list($lines, 'PARAMETERS');
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
		if(!$this->long_descriptions)
			$column_offset += $max_length;

		$text = "\n$title\n";
		foreach($lines as $row)
		{
			$line = $this->wrap($row[0], $this->term_padding_left, true);
			if(strlen($row[0]) > $max_length || $this->long_descriptions)
			{
				$line .= "\n".str_repeat(' ', $column_offset);
			}
			else
			{
				$line = str_pad($line, $column_offset, ' ');
			}

			$line .= $this->wrap($row[1], $column_offset);

			if($this->long_descriptions)
				$text .= "\n";

			$text .= $line."\n";
		}
		return $text;
	}

	private function wrap($text, $padding_left = 0, $pad_first_line = false)
	{
		// formatting it a little
		// 1. removing
		$text = preg_replace("/(\S)[ \t]*\n[ \t]*([^\n])/", '$1 $2', $text);

		$width = $this->width - $padding_left;
		$out = '';
		$padding = str_repeat(' ', $padding_left);
		foreach(explode("\n", wordwrap($text, $width, "\n", true)) as $i=>$line)
		{
			if($i || $pad_first_line)
				$out .= $padding;
			$out .= $line."\n";
		}
		return substr($out, 0, -1);
	}

	private function makeup_option_aliases($option)
	{
		if(is_null($option['default']))
		{
			foreach($option['aliases'] as $k=>$v)
			{
				$option['aliases'][$k] .= (substr($v, 0, 2) == '--') ? '=...' : ' ...';
			}
		}

		// moving short names up
		usort($option['aliases'], array('static', 'option_alias_cmp'));

		return $option;
	}

	private static function option_alias_cmp($a, $b) {
		$as = (substr($a, 0, 2) == '--');
		$bs = (substr($b, 0, 2) == '--');
		if($as != $bs)
			return $as ? 1 : -1;
		return strcmp($a, $b);
	}

	private function reduce_description($text)
	{
		if(!$this->long_descriptions)
			list($text) = explode("\n", $text, 2);

		return $text;
	}
}