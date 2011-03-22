<?

namespace cliff;

// let's not affect autoload, we don't have too much files here
require_once __DIR__.'/../Exception.php';

class Exception_ParseError extends Exception
{
	const E_NO_OPTION       = 101;
	const E_WRONG_OPTION    = 102;
	const E_NO_OPTION_VALUE = 103;

	const E_NO_PARAM        = 201;
	const E_WRONG_PARAM     = 202;
	const E_TOO_MANY_PARAMS = 203;
}