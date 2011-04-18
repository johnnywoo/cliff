<?

// let's not affect autoload, we don't have too much files here
require_once __DIR__.'/../lib/Cliff.php';
require_once __DIR__.'/../lib/Exception.php';
require_once __DIR__.'/../lib/Exception/ParseError.php';
require_once __DIR__.'/../lib/Config.php';
require_once __DIR__.'/../lib/Parser.php';
require_once __DIR__.'/../lib/Tokenizer.php';
require_once __DIR__.'/../lib/Usage.php';
require_once __DIR__.'/../lib/Completion.php';

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