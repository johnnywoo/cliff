<?

namespace cliff;

class Exception_ParseError extends Exception
{
	const E_NO_OPTION_VALUE = 1;
	const E_WRONG_OPTION    = 2;

	const E_NO_PARAM        = 3;
	const E_WRONG_PARAM     = 4;
	const E_TOO_MANY_PARAMS = 5;
}