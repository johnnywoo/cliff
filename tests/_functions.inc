<?

require_once __DIR__ . '/../lib/Cliff.php';

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
