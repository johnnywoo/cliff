<?

namespace cliff;

class Exception_ParseError extends Exception
{
	const E_NO_OPTION       = 101;
	const E_WRONG_OPTION    = 102;
	const E_NO_OPTION_VALUE = 103;

	const E_NO_PARAM        = 201;
	const E_WRONG_PARAM     = 202;
	const E_TOO_MANY_PARAMS = 203;
}