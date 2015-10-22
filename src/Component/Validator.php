<?php

class Validator {

	private $error = null;

	public function validate($listRules, $data) {

		foreach ($listRules as $field => $rules) {
			foreach ($rules as $methodName => $rule) {

				$params = array_key_exists('params', $rule) ? $rule['params'] : null;

				if (!$this->$methodName($data["$field"], $params, $data)) {
					$this->setError($rule['message']);
					return;
				}
			}
		}

		return true;
	}

	public function setError($errorMessage){
		$this->error = $errorMessage;
	}

	public function getError(){
		return $this->error;
	}

	/**
	 * Caso tipo de pagamento for debito ou credito a vista, o número de parcelas deve ser obrigatoriamente 1
	 */
	public function installmentsPaymentType($check, $params, $data) {
		if($this->inList($data['payment_type'], $params) and $check != 1) {
			return false;
		}
		return true;
	}

	/**
	 * Valida o número máximo de parcelas
	 */
	public function installmentsMaxValue($check, $params, $data) {
		if($check > $params) {
			return false;
		}
		return true;
	}

	/**
	 * Valida se o security_code foi enviado, caso sec_code_status for 1
	 */
	public function securityCodeSecCodeStatus($check, $params, $data) {

		if ($data['sec_code_status'] == 1) {
			if (!$this->notEmpty($check)) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Valida se é um email válido
	 */
	public function url ($check, $params, $data){
		$regex = "/^((http|https)?):\/\/([a-zA-Z0-9_-]+)(\.[a-zA-Z0-9_-]+)+(\/[a-zA-Z0-9_-]+)*\/?$/i";
		return $this->_check($check, $regex);
	}

	/**
	 * Caso forma de pagamento for debito, valida apenas visa ou master 
	 */
	public function paymentTypeIssuer($check, $params, $data) {
		if($check === 'debito') {
			if(!$this->inList($data['issuer'], ['master', 'visa'])) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Caso o tipo de pagamento for por token, não poderá aceitar forma de pagamento debito
	 */
	public function tokenPaymentType($check, $params, $data) {
		if(array_key_exists('token', $data)) {
			if($data['payment_type'] === 'debito'){
				return false;
			}
		}
		return true;
	}



/**
 * Holds an array of errors messages set in this class.
 * These are used for debugging purposes
 *
 * @var array
 */
	public $errors = array();

/**
 * Checks that a string contains something other than whitespace
 *
 * Returns true if string contains something other than whitespace
 *
 * $check can be passed as an array:
 * array('check' => 'valueToCheck');
 *
 * @param string|array $check Value to check
 * @return boolean Success
 */
	public function notEmpty($check) {
		if (is_array($check)) {
			extract($this->_defaults($check));
		}

		if (empty($check) && $check != '0') {
			return false;
		}
		return $this->_check($check, '/[^\s]+/m');
	}

/**
 * Checks that a string contains only integer or letters
 *
 * Returns true if string contains only integer or letters
 *
 * $check can be passed as an array:
 * array('check' => 'valueToCheck');
 *
 * @param string|array $check Value to check
 * @return boolean Success
 */
	public function alphaNumeric($check) {
		if (is_array($check)) {
			extract($this->_defaults($check));
		}

		if (empty($check) && $check != '0') {
			return false;
		}
		return $this->_check($check, '/^[\p{Ll}\p{Lm}\p{Lo}\p{Lt}\p{Lu}\p{Nd}]+$/Du');
	}


	public function between($check, $params, $data) {
		return ($check >= $params['min'] && $check <= $params['max']);
	}

/**
 * Returns true if field is left blank -OR- only whitespace characters are present in its value
 * Whitespace characters include Space, Tab, Carriage Return, Newline
 *
 * $check can be passed as an array:
 * array('check' => 'valueToCheck');
 *
 * @param string|array $check Value to check
 * @return boolean Success
 */
	public function blank($check) {
		if (is_array($check)) {
			extract($this->_defaults($check));
		}
		return !$this->_check($check, '/[^\\s]/');
	}

/**
 * Validation of credit card numbers.
 * Returns true if $check is in the proper credit card format.
 *
 * @param string|array $check credit card number to validate
 * @param string|array $type 'all' may be passed as a sting, defaults to fast which checks format of most major credit cards
 *    if an array is used only the values of the array are checked.
 *    Example: array('amex', 'bankcard', 'maestro')
 * @param boolean $deep set to true this will check the Luhn algorithm of the credit card.
 * @param string $regex A custom regex can also be passed, this will be used instead of the defined regex values
 * @return boolean Success
 * @see Validation::luhn()
 */
	public function cc($check, $type = 'fast', $deep = false, $regex = null) {
		if (is_array($check)) {
			extract($this->_defaults($check));
		}

		$check = str_replace(array('-', ' '), '', $check);
		if (strlen($check) < 13) {
			return false;
		}

		if ($regex !== null) {
			if ($this->_check($check, $regex)) {
				return $this->luhn($check, $deep);
			}
		}
		$cards = array(
			'all' => array(
				'amex'		=> '/^3[4|7]\\d{13}$/',
				'bankcard'	=> '/^56(10\\d\\d|022[1-5])\\d{10}$/',
				'diners'	=> '/^(?:3(0[0-5]|[68]\\d)\\d{11})|(?:5[1-5]\\d{14})$/',
				'disc'		=> '/^(?:6011|650\\d)\\d{12}$/',
				'electron'	=> '/^(?:417500|4917\\d{2}|4913\\d{2})\\d{10}$/',
				'enroute'	=> '/^2(?:014|149)\\d{11}$/',
				'jcb'		=> '/^(3\\d{4}|2100|1800)\\d{11}$/',
				'maestro'	=> '/^(?:5020|6\\d{3})\\d{12}$/',
				'mc'		=> '/^5[1-5]\\d{14}$/',
				'solo'		=> '/^(6334[5-9][0-9]|6767[0-9]{2})\\d{10}(\\d{2,3})?$/',
				'switch'	=> '/^(?:49(03(0[2-9]|3[5-9])|11(0[1-2]|7[4-9]|8[1-2])|36[0-9]{2})\\d{10}(\\d{2,3})?)|(?:564182\\d{10}(\\d{2,3})?)|(6(3(33[0-4][0-9])|759[0-9]{2})\\d{10}(\\d{2,3})?)$/',
				'visa'		=> '/^4\\d{12}(\\d{3})?$/',
				'voyager'	=> '/^8699[0-9]{11}$/'
			),
			'fast' => '/^(?:4[0-9]{12}(?:[0-9]{3})?|5[1-5][0-9]{14}|6011[0-9]{12}|3(?:0[0-5]|[68][0-9])[0-9]{11}|3[47][0-9]{13})$/'
		);

		if (is_array($type)) {
			foreach ($type as $value) {
				$regex = $cards['all'][strtolower($value)];

				if ($this->_check($check, $regex)) {
					return $this->luhn($check, $deep);
				}
			}
		} elseif ($type === 'all') {
			foreach ($cards['all'] as $value) {
				$regex = $value;

				if ($this->_check($check, $regex)) {
					return $this->luhn($check, $deep);
				}
			}
		} else {
			$regex = $cards['fast'];

			if ($this->_check($check, $regex)) {
				return $this->luhn($check, $deep);
			}
		}
		return false;
	}

	public function greaterThan($check, $params, $data) {
		return $this->comparison($check, '>', $params);
	}



/**
 * Used to compare 2 numeric values.
 *
 * @param string|array $check1 if string is passed for a string must also be passed for $check2
 *    used as an array it must be passed as array('check1' => value, 'operator' => 'value', 'check2' -> value)
 * @param string $operator Can be either a word or operand
 *    is greater >, is less <, greater or equal >=
 *    less or equal <=, is less <, equal to ==, not equal !=
 * @param integer $check2 only needed if $check1 is a string
 * @return boolean Success
 */
	public function comparison($check1, $operator = null, $check2 = null) {
		if (is_array($check1)) {
			extract($check1, EXTR_OVERWRITE);
		}

		$operator = str_replace(array(' ', "\t", "\n", "\r", "\0", "\x0B"), '', strtolower($operator));

		switch ($operator) {
			case 'isgreater':
			case '>':
				if ($check1 > $check2) {
					return true;
				}
				break;
			case 'isless':
			case '<':
				if ($check1 < $check2) {
					return true;
				}
				break;
			case 'greaterorequal':
			case '>=':
				if ($check1 >= $check2) {
					return true;
				}
				break;
			case 'lessorequal':
			case '<=':
				if ($check1 <= $check2) {
					return true;
				}
				break;
			case 'equalto':
			case '==':
				if ($check1 == $check2) {
					return true;
				}
				break;
			case 'notequal':
			case '!=':
				if ($check1 != $check2) {
					return true;
				}
				break;
		}
		return false;
	}

/**
 * Date validation, determines if the string passed is a valid date.
 * keys that expect full month, day and year will validate leap years
 *
 * @param string $check a valid date string
 * @param string|array $format Use a string or an array of the keys below. Arrays should be passed as array('dmy', 'mdy', etc)
 * 	      Keys: dmy 27-12-2006 or 27-12-06 separators can be a space, period, dash, forward slash
 * 	            mdy 12-27-2006 or 12-27-06 separators can be a space, period, dash, forward slash
 * 	            ymd 2006-12-27 or 06-12-27 separators can be a space, period, dash, forward slash
 * 	            dMy 27 December 2006 or 27 Dec 2006
 * 	            Mdy December 27, 2006 or Dec 27, 2006 comma is optional
 * 	            My December 2006 or Dec 2006
 * 	            my 12/2006 separators can be a space, period, dash, forward slash
 * 	            ym 2006/12 separators can be a space, period, dash, forward slash
 * 	            y 2006 just the year without any separators
 * @param string $regex If a custom regular expression is used this is the only validation that will occur.
 * @return boolean Success
 */
	public function date($check, $format = 'ymd', $regex = null) {
		if ($regex !== null) {
			return $this->_check($check, $regex);
		}

		$regex['dmy'] = '%^(?:(?:31(\\/|-|\\.|\\x20)(?:0?[13578]|1[02]))\\1|(?:(?:29|30)(\\/|-|\\.|\\x20)(?:0?[1,3-9]|1[0-2])\\2))(?:(?:1[6-9]|[2-9]\\d)?\\d{2})$|^(?:29(\\/|-|\\.|\\x20)0?2\\3(?:(?:(?:1[6-9]|[2-9]\\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00))))$|^(?:0?[1-9]|1\\d|2[0-8])(\\/|-|\\.|\\x20)(?:(?:0?[1-9])|(?:1[0-2]))\\4(?:(?:1[6-9]|[2-9]\\d)?\\d{2})$%';
		$regex['mdy'] = '%^(?:(?:(?:0?[13578]|1[02])(\\/|-|\\.|\\x20)31)\\1|(?:(?:0?[13-9]|1[0-2])(\\/|-|\\.|\\x20)(?:29|30)\\2))(?:(?:1[6-9]|[2-9]\\d)?\\d{2})$|^(?:0?2(\\/|-|\\.|\\x20)29\\3(?:(?:(?:1[6-9]|[2-9]\\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00))))$|^(?:(?:0?[1-9])|(?:1[0-2]))(\\/|-|\\.|\\x20)(?:0?[1-9]|1\\d|2[0-8])\\4(?:(?:1[6-9]|[2-9]\\d)?\\d{2})$%';
		$regex['ymd'] = '%^(?:(?:(?:(?:(?:1[6-9]|[2-9]\\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00)))(\\/|-|\\.|\\x20)(?:0?2\\1(?:29)))|(?:(?:(?:1[6-9]|[2-9]\\d)?\\d{2})(\\/|-|\\.|\\x20)(?:(?:(?:0?[13578]|1[02])\\2(?:31))|(?:(?:0?[1,3-9]|1[0-2])\\2(29|30))|(?:(?:0?[1-9])|(?:1[0-2]))\\2(?:0?[1-9]|1\\d|2[0-8]))))$%';
		$regex['dMy'] = '/^((31(?!\\ (Feb(ruary)?|Apr(il)?|June?|(Sep(?=\\b|t)t?|Nov)(ember)?)))|((30|29)(?!\\ Feb(ruary)?))|(29(?=\\ Feb(ruary)?\\ (((1[6-9]|[2-9]\\d)(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00)))))|(0?[1-9])|1\\d|2[0-8])\\ (Jan(uary)?|Feb(ruary)?|Ma(r(ch)?|y)|Apr(il)?|Ju((ly?)|(ne?))|Aug(ust)?|Oct(ober)?|(Sep(?=\\b|t)t?|Nov|Dec)(ember)?)\\ ((1[6-9]|[2-9]\\d)\\d{2})$/';
		$regex['Mdy'] = '/^(?:(((Jan(uary)?|Ma(r(ch)?|y)|Jul(y)?|Aug(ust)?|Oct(ober)?|Dec(ember)?)\\ 31)|((Jan(uary)?|Ma(r(ch)?|y)|Apr(il)?|Ju((ly?)|(ne?))|Aug(ust)?|Oct(ober)?|(Sep)(tember)?|(Nov|Dec)(ember)?)\\ (0?[1-9]|([12]\\d)|30))|(Feb(ruary)?\\ (0?[1-9]|1\\d|2[0-8]|(29(?=,?\\ ((1[6-9]|[2-9]\\d)(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00)))))))\\,?\\ ((1[6-9]|[2-9]\\d)\\d{2}))$/';
		$regex['My'] = '%^(Jan(uary)?|Feb(ruary)?|Ma(r(ch)?|y)|Apr(il)?|Ju((ly?)|(ne?))|Aug(ust)?|Oct(ober)?|(Sep(?=\\b|t)t?|Nov|Dec)(ember)?)[ /]((1[6-9]|[2-9]\\d)\\d{2})$%';
		$regex['my'] = '%^((0[123456789]|10|11|12)([- /.])(([1][9][0-9][0-9])|([2][0-9][0-9][0-9])))$%';
		$regex['ym'] = '%^((([1][9][0-9][0-9])|([2][0-9][0-9][0-9]))([- /.])(0[123456789]|10|11|12))$%';
		$regex['y'] = '%^(([1][9][0-9][0-9])|([2][0-9][0-9][0-9]))$%';

		$format = (is_array($format)) ? array_values($format) : array($format);
		foreach ($format as $key) {
			if ($this->_check($check, $regex[$key]) === true) {
				return true;
			}
		}
		return false;
	}

/**
 * Validates a datetime value
 * All values matching the "date" core validation rule, and the "time" one will be valid
 *
 * @param string $check Value to check
 * @param string|array $dateFormat Format of the date part
 * Use a string or an array of the keys below. Arrays should be passed as array('dmy', 'mdy', etc)
 * ## Keys:
 *
 * - dmy 27-12-2006 or 27-12-06 separators can be a space, period, dash, forward slash
 * - mdy 12-27-2006 or 12-27-06 separators can be a space, period, dash, forward slash
 * - ymd 2006-12-27 or 06-12-27 separators can be a space, period, dash, forward slash
 * - dMy 27 December 2006 or 27 Dec 2006
 * - Mdy December 27, 2006 or Dec 27, 2006 comma is optional
 * - My December 2006 or Dec 2006
 * - my 12/2006 separators can be a space, period, dash, forward slash
 * @param string $regex Regex for the date part. If a custom regular expression is used this is the only validation that will occur.
 * @return boolean True if the value is valid, false otherwise
 * @see Validation::date
 * @see Validation::time
 */
	public function datetime($check, $dateFormat = 'ymd', $regex = null) {
		$valid = false;
		$parts = explode(' ', $check);
		if (!empty($parts) && count($parts) > 1) {
			$time = array_pop($parts);
			$date = implode(' ', $parts);
			$valid = $this->date($date, $dateFormat, $regex) && $this->time($time);
		}
		return $valid;
	}

/**
 * Time validation, determines if the string passed is a valid time.
 * Validates time as 24hr (HH:MM) or am/pm ([H]H:MM[a|p]m)
 * Does not allow/validate seconds.
 *
 * @param string $check a valid time string
 * @return boolean Success
 */
	public function time($check) {
		return $this->_check($check, '%^((0?[1-9]|1[012])(:[0-5]\d){0,2} ?([AP]M|[ap]m))$|^([01]\d|2[0-3])(:[0-5]\d){0,2}$%');
	}

/**
 * Boolean validation, determines if value passed is a boolean integer or true/false.
 *
 * @param string $check a valid boolean
 * @return boolean Success
 */
	public function boolean($check) {
		$booleanList = array(0, 1, '0', '1', true, false);
		return in_array($check, $booleanList, true);
	}

/**
 * Checks that a value is a valid decimal. Both the sign and exponent are optional.
 *
 * Valid Places:
 *
 * - null => Any number of decimal places, including none. The '.' is not required.
 * - true => Any number of decimal places greater than 0, or a float|double. The '.' is required.
 * - 1..N => Exactly that many number of decimal places. The '.' is required.
 *
 * @param float $check The value the test for decimal
 * @param integer $places
 * @param string $regex If a custom regular expression is used, this is the only validation that will occur.
 * @return boolean Success
 */
	public function decimal($check, $places = null, $regex = null) {
		if ($regex === null) {
			$lnum = '[0-9]+';
			$dnum = "[0-9]*[\.]{$lnum}";
			$sign = '[+-]?';
			$exp = "(?:[eE]{$sign}{$lnum})?";

			if ($places === null) {
				$regex = "/^{$sign}(?:{$lnum}|{$dnum}){$exp}$/";

			} elseif ($places === true) {
				if (is_float($check) && floor($check) === $check) {
					$check = sprintf("%.1f", $check);
				}
				$regex = "/^{$sign}{$dnum}{$exp}$/";

			} elseif (is_numeric($places)) {
				$places = '[0-9]{' . $places . '}';
				$dnum = "(?:[0-9]*[\.]{$places}|{$lnum}[\.]{$places})";
				$regex = "/^{$sign}{$dnum}{$exp}$/";
			}
		}
		return $this->_check($check, $regex);
	}

/**
 * Validates for an email address.
 *
 * Only uses getmxrr() checking for deep validation if PHP 5.3.0+ is used, or
 * any PHP version on a non-windows distribution
 *
 * @param string $check Value to check
 * @param boolean $deep Perform a deeper validation (if true), by also checking availability of host
 * @param string $regex Regex to use (if none it will use built in regex)
 * @return boolean Success
 */
	public function email($check, $deep = false, $regex = null) {
		if (is_array($check)) {
			extract($this->_defaults($check));
		}

		if ($regex === null) {
			$regex = '/^[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+)*@' . $this->$_pattern['hostname'] . '$/i';
		}
		$return = $this->_check($check, $regex);
		if ($deep === false || $deep === null) {
			return $return;
		}

		if ($return === true && preg_match('/@(' . $this->$_pattern['hostname'] . ')$/i', $check, $regs)) {
			if (function_exists('getmxrr') && getmxrr($regs[1], $mxhosts)) {
				return true;
			}
			if (function_exists('checkdnsrr') && checkdnsrr($regs[1], 'MX')) {
				return true;
			}
			return is_array(gethostbynamel($regs[1]));
		}
		return false;
	}

/**
 * Check that value is exactly $comparedTo.
 *
 * @param mixed $check Value to check
 * @param mixed $comparedTo Value to compare
 * @return boolean Success
 */
	public function equalTo($check, $comparedTo) {
		return ($check === $comparedTo);
	}

/**
 * Check that value has a valid file extension.
 *
 * @param string|array $check Value to check
 * @param array $extensions file extensions to allow. By default extensions are 'gif', 'jpeg', 'png', 'jpg'
 * @return boolean Success
 */
	public function extension($check, $extensions = array('gif', 'jpeg', 'png', 'jpg')) {
		if (is_array($check)) {
			return $this->extension(array_shift($check), $extensions);
		}
		$extension = strtolower(pathinfo($check, PATHINFO_EXTENSION));
		foreach ($extensions as $value) {
			if ($extension === strtolower($value)) {
				return true;
			}
		}
		return false;
	}

/**
 * Validation of an IP address.
 *
 * @param string $check The string to test.
 * @param string $type The IP Protocol version to validate against
 * @return boolean Success
 */
	public function ip($check, $type = 'both') {
		$type = strtolower($type);
		$flags = 0;
		if ($type === 'ipv4') {
			$flags = FILTER_FLAG_IPV4;
		}
		if ($type === 'ipv6') {
			$flags = FILTER_FLAG_IPV6;
		}
		return (boolean)filter_var($check, FILTER_VALIDATE_IP, array('flags' => $flags));
	}

/**
 * Checks whether the length of a string is greater or equal to a minimal length.
 *
 * @param string $check The string to test
 * @param integer $min The minimal string length
 * @return boolean Success
 */
	public function minLength($check, $min) {
		return strlen($check) >= $min;
	}

/**
 * Checks whether the length of a string is smaller or equal to a maximal length..
 *
 * @param string $check The string to test
 * @param integer $max The maximal string length
 * @return boolean Success
 */
	public function maxLength($check, $max) {
		return strlen($check) <= $max;
	}

	public function equalLength($check, $params, $data) {
		return strlen($check) == $params;
	}

	public function betweenLength($check, $params, $data) {
		$length = strlen($check);
		return ($length >= $params['min'] && $length <= $params['max']);
	}

/**
 * Checks that a value is a monetary amount.
 *
 * @param string $check Value to check
 * @param string $symbolPosition Where symbol is located (left/right)
 * @return boolean Success
 */
	public function money($check, $symbolPosition = 'left') {
		$money = '(?!0,?\d)(?:\d{1,3}(?:([, .])\d{3})?(?:\1\d{3})*|(?:\d+))((?!\1)[,.]\d{1,2})?';
		if ($symbolPosition === 'right') {
			$regex = '/^' . $money . '(?<!\x{00a2})\p{Sc}?$/u';
		} else {
			$regex = '/^(?!\x{00a2})\p{Sc}?' . $money . '$/u';
		}
		return $this->_check($check, $regex);
	}

/**
 * Validate a multiple select.
 *
 * Valid Options
 *
 * - in => provide a list of choices that selections must be made from
 * - max => maximum number of non-zero choices that can be made
 * - min => minimum number of non-zero choices that can be made
 *
 * @param array $check Value to check
 * @param array $options Options for the check.
 * @param boolean $strict Defaults to true, set to false to disable strict type check
 * @return boolean Success
 */
	public function multiple($check, $options = array(), $strict = true) {
		$defaults = array('in' => null, 'max' => null, 'min' => null);
		$options = array_merge($defaults, $options);
		$check = array_filter((array)$check);
		if (empty($check)) {
			return false;
		}
		if ($options['max'] && count($check) > $options['max']) {
			return false;
		}
		if ($options['min'] && count($check) < $options['min']) {
			return false;
		}
		if ($options['in'] && is_array($options['in'])) {
			foreach ($check as $val) {
				if (!in_array($val, $options['in'], $strict)) {
					return false;
				}
			}
		}
		return true;
	}

/**
 * Checks if a value is numeric.
 *
 * @param string $check Value to check
 * @return boolean Success
 */
	public function numeric($check) {
		return is_numeric($check);
	}

/**
 * Checks if a value is a natural number.
 *
 * @param string $check Value to check
 * @param boolean $allowZero Set true to allow zero, defaults to false
 * @return boolean Success
 * @see http://en.wikipedia.org/wiki/Natural_number
 */
	public function naturalNumber($check, $allowZero = false) {
		$regex = $allowZero ? '/^(?:0|[1-9][0-9]*)$/' : '/^[1-9][0-9]*$/';
		return $this->_check($check, $regex);
	}

/**
 * Check that a value is a valid phone number.
 *
 * @param string|array $check Value to check (string or array)
 * @param string $regex Regular expression to use
 * @param string $country Country code (defaults to 'all')
 * @return boolean Success
 */
	public function phone($check, $regex = null, $country = 'all') {
		if (is_array($check)) {
			extract($this->_defaults($check));
		}

		if ($regex === null) {
			switch ($country) {
				case 'us':
				case 'ca':
				case 'can': // deprecated three-letter-code
				case 'all':
					// includes all NANPA members.
					// see http://en.wikipedia.org/wiki/North_American_Numbering_Plan#List_of_NANPA_countries_and_territories
					$regex = '/^(?:(?:\+?1\s*(?:[.-]\s*)?)?';

					// Area code 555, X11 is not allowed.
					$areaCode = '(?![2-9]11)(?!555)([2-9][0-8][0-9])';
					$regex .= '(?:\(\s*' . $areaCode . '\s*\)|' . $areaCode . ')';
					$regex .= '\s*(?:[.-]\s*)?)';

					// Exchange and 555-XXXX numbers
					$regex .= '(?!(555(?:\s*(?:[.\-\s]\s*))(01([0-9][0-9])|1212)))';
					$regex .= '(?!(555(01([0-9][0-9])|1212)))';
					$regex .= '([2-9]1[02-9]|[2-9]1[02-9]|[2-9][02-9]1|[2-9][02-9]{2})\s*(?:[.-]\s*)';

					// Local number and extension
					$regex .= '?([0-9]{4})';
					$regex .= '(?:\s*(?:#|x\.?|ext\.?|extension)\s*(\d+))?$/';
				break;
			}
		}
		if (empty($regex)) {
			return $this->_pass('phone', $check, $country);
		}
		return $this->_check($check, $regex);
	}

/**
 * Checks that a given value is a valid postal code.
 *
 * @param string|array $check Value to check
 * @param string $regex Regular expression to use
 * @param string $country Country to use for formatting
 * @return boolean Success
 */
	public function postal($check, $regex = null, $country = 'us') {
		if (is_array($check)) {
			extract($this->_defaults($check));
		}

		if ($regex === null) {
			switch ($country) {
				case 'uk':
					$regex = '/\\A\\b[A-Z]{1,2}[0-9][A-Z0-9]? [0-9][ABD-HJLNP-UW-Z]{2}\\b\\z/i';
					break;
				case 'ca':
					$district = '[ABCEGHJKLMNPRSTVYX]';
					$letters = '[ABCEGHJKLMNPRSTVWXYZ]';
					$regex = "/\\A\\b{$district}[0-9]{$letters} [0-9]{$letters}[0-9]\\b\\z/i";
					break;
				case 'it':
				case 'de':
					$regex = '/^[0-9]{5}$/i';
					break;
				case 'be':
					$regex = '/^[1-9]{1}[0-9]{3}$/i';
					break;
				case 'us':
					$regex = '/\\A\\b[0-9]{5}(?:-[0-9]{4})?\\b\\z/i';
					break;
			}
		}
		if (empty($regex)) {
			return $this->_pass('postal', $check, $country);
		}
		return $this->_check($check, $regex);
	}

/**
 * Validate that a number is in specified range.
 * if $lower and $upper are not set, will return true if
 * $check is a legal finite on this platform
 *
 * @param string $check Value to check
 * @param integer $lower Lower limit
 * @param integer $upper Upper limit
 * @return boolean Success
 */
	public function range($check, $lower = null, $upper = null) {
		if (!is_numeric($check)) {
			return false;
		}
		if (isset($lower) && isset($upper)) {
			return ($check > $lower && $check < $upper);
		}
		return is_finite($check);
	}

/**
 * Checks that a value is a valid Social Security Number.
 *
 * @param string|array $check Value to check
 * @param string $regex Regular expression to use
 * @param string $country Country
 * @return boolean Success
 */
	public function ssn($check, $regex = null, $country = null) {
		if (is_array($check)) {
			extract($this->_defaults($check));
		}

		if ($regex === null) {
			switch ($country) {
				case 'dk':
					$regex = '/\\A\\b[0-9]{6}-[0-9]{4}\\b\\z/i';
					break;
				case 'nl':
					$regex = '/\\A\\b[0-9]{9}\\b\\z/i';
					break;
				case 'us':
					$regex = '/\\A\\b[0-9]{3}-[0-9]{2}-[0-9]{4}\\b\\z/i';
					break;
			}
		}
		if (empty($regex)) {
			return $this->_pass('ssn', $check, $country);
		}
		return $this->_check($check, $regex);
	}

/**
 * Checks that a value is a valid URL according to http://www.w3.org/Addressing/URL/url-spec.txt
 *
 * The regex checks for the following component parts:
 *
 * - a valid, optional, scheme
 * - a valid ip address OR
 *   a valid domain name as defined by section 2.3.1 of http://www.ietf.org/rfc/rfc1035.txt
 *   with an optional port number
 * - an optional valid path
 * - an optional query string (get parameters)
 * - an optional fragment (anchor tag)
 *
 * @param string $check Value to check
 * @param boolean $strict Require URL to be prefixed by a valid scheme (one of http(s)/ftp(s)/file/news/gopher)
 * @return boolean Success
 */
	// public function url($check, $strict = false) {
	// 	$this->_populateIp();
	// 	$validChars = '([' . preg_quote('!"$&\'()*+,-.@_:;=~[]') . '\/0-9a-z\p{L}\p{N}]|(%[0-9a-f]{2}))';
	// 	$regex = '/^(?:(?:https?|ftps?|sftp|file|news|gopher):\/\/)' . (!empty($strict) ? '' : '?') .
	// 		'(?:' . $this->$_pattern['IPv4'] . '|\[' . $this->$_pattern['IPv6'] . '\]|' . $this->$_pattern['hostname'] . ')(?::[1-9][0-9]{0,4})?' .
	// 		'(?:\/?|\/' . $validChars . '*)?' .
	// 		'(?:\?' . $validChars . '*)?' .
	// 		'(?:#' . $validChars . '*)?$/iu';
	// 	return $this->_check($check, $regex);
	// }

/**
 * Checks if a value is in a given list.
 *
 * @param string $check Value to check
 * @param array $list List to check against
 * @param boolean $strict Defaults to true, set to false to disable strict type check
 * @return boolean Success
 */
	public function inList($check, $list) {
		return in_array($check, $list);
	}

/**
 * Runs an user-defined validation.
 *
 * @param string|array $check value that will be validated in user-defined methods.
 * @param object $object class that holds validation method
 * @param string $method class method name for validation to run
 * @param array $args arguments to send to method
 * @return mixed user-defined class class method returns
 */
	public function userDefined($check, $object, $method, $args = null) {
		return call_user_func_array(array($object, $method), array($check, $args));
	}

/**
 * Checks that a value is a valid uuid - http://tools.ietf.org/html/rfc4122
 *
 * @param string $check Value to check
 * @return boolean Success
 */
	public function uuid($check) {
		$regex = '/^[a-fA-F0-9]{8}-[a-fA-F0-9]{4}-[1-5][a-fA-F0-9]{3}-[89aAbB][a-fA-F0-9]{3}-[a-fA-F0-9]{12}$/';
		return $this->_check($check, $regex);
	}

/**
 * Attempts to pass unhandled Validation locales to a class starting with $classPrefix
 * and ending with Validation. For example $classPrefix = 'nl', the class would be
 * `NlValidation`.
 *
 * @param string $method The method to call on the other class.
 * @param mixed $check The value to check or an array of parameters for the method to be called.
 * @param string $classPrefix The prefix for the class to do the validation.
 * @return mixed Return of Passed method, false on failure
 */
	protected function _pass($method, $check, $classPrefix) {
		$className = ucwords($classPrefix) . 'Validation';
		if (!class_exists($className)) {
			trigger_error(__d('cake_dev', 'Could not find %s class, unable to complete validation.', $className), E_USER_WARNING);
			return false;
		}
		if (!method_exists($className, $method)) {
			trigger_error(__d('cake_dev', 'Method %s does not exist on %s unable to complete validation.', $method, $className), E_USER_WARNING);
			return false;
		}
		$check = (array)$check;
		return call_user_func_array(array($className, $method), $check);
	}

/**
 * Runs a regular expression match.
 *
 * @param string $check Value to check against the $regex expression
 * @param string $regex Regular expression
 * @return boolean Success of match
 */
	protected function _check($check, $regex) {
		if (is_string($regex) && preg_match($regex, $check)) {
			return true;
		}
		return false;
	}

/**
 * Get the values to use when value sent to validation method is
 * an array.
 *
 * @param array $params Parameters sent to validation method
 * @return void
 */
	protected function _defaults($params) {
		$this->_reset();
		$defaults = array(
			'check' => null,
			'regex' => null,
			'country' => null,
			'deep' => false,
			'type' => null
		);
		$params = array_merge($defaults, $params);
		if ($params['country'] !== null) {
			$params['country'] = mb_strtolower($params['country']);
		}
		return $params;
	}

/**
 * Luhn algorithm
 *
 * @param string|array $check
 * @param boolean $deep
 * @return boolean Success
 * @see http://en.wikipedia.org/wiki/Luhn_algorithm
 */
	public function luhn($check, $deep = false) {
		if (is_array($check)) {
			extract($this->_defaults($check));
		}
		if ($deep !== true) {
			return true;
		}
		if ((int)$check === 0) {
			return false;
		}
		$sum = 0;
		$length = strlen($check);

		for ($position = 1 - ($length % 2); $position < $length; $position += 2) {
			$sum += $check[$position];
		}

		for ($position = ($length % 2); $position < $length; $position += 2) {
			$number = $check[$position] * 2;
			$sum += ($number < 10) ? $number : $number - 9;
		}

		return ($sum % 10 === 0);
	}

/**
 * Checks the mime type of a file
 *
 * @param string|array $check
 * @param array $mimeTypes to check for
 * @return boolean Success
 * @throws CakeException when mime type can not be determined.
 */
	public function mimeType($check, $mimeTypes = array()) {
		if (is_array($check) && isset($check['tmp_name'])) {
			$check = $check['tmp_name'];
		}

		$File = new File($check);
		$mime = $File->mime();

		if ($mime === false) {
			throw new CakeException(__d('cake_dev', 'Can not determine the mimetype.'));
		}
		return in_array($mime, $mimeTypes);
	}

/**
 * Checks the filesize
 *
 * @param string|array $check
 * @param integer|string $size Size in bytes or human readable string like '5MB'
 * @param string $operator See `Validation::comparison()`
 * @return boolean Success
 */
	public function fileSize($check, $operator = null, $size = null) {
		if (is_array($check) && isset($check['tmp_name'])) {
			$check = $check['tmp_name'];
		}

		if (is_string($size)) {
			$size = CakeNumber::fromReadableSize($size);
		}
		$filesize = filesize($check);

		return $this->comparison($filesize, $operator, $size);
	}

/**
 * Checking for upload errors
 *
 * @param string|array $check
 * @return boolean
 * @see http://www.php.net/manual/en/features.file-upload.errors.php
 */
	public function uploadError($check) {
		if (is_array($check) && isset($check['error'])) {
			$check = $check['error'];
		}

		return $check === UPLOAD_ERR_OK;
	}

/**
 * Lazily populate the IP address patterns used for validations
 *
 * @return void
 */
	protected function _populateIp() {
		if (!isset($this->$_pattern['IPv6'])) {
			$pattern = '((([0-9A-Fa-f]{1,4}:){7}(([0-9A-Fa-f]{1,4})|:))|(([0-9A-Fa-f]{1,4}:){6}';
			$pattern .= '(:|((25[0-5]|2[0-4]\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2})){3})';
			$pattern .= '|(:[0-9A-Fa-f]{1,4})))|(([0-9A-Fa-f]{1,4}:){5}((:((25[0-5]|2[0-4]\d|[01]?\d{1,2})';
			$pattern .= '(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2})){3})?)|((:[0-9A-Fa-f]{1,4}){1,2})))|(([0-9A-Fa-f]{1,4}:)';
			$pattern .= '{4}(:[0-9A-Fa-f]{1,4}){0,1}((:((25[0-5]|2[0-4]\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2}))';
			$pattern .= '{3})?)|((:[0-9A-Fa-f]{1,4}){1,2})))|(([0-9A-Fa-f]{1,4}:){3}(:[0-9A-Fa-f]{1,4}){0,2}';
			$pattern .= '((:((25[0-5]|2[0-4]\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2})){3})?)|';
			$pattern .= '((:[0-9A-Fa-f]{1,4}){1,2})))|(([0-9A-Fa-f]{1,4}:){2}(:[0-9A-Fa-f]{1,4}){0,3}';
			$pattern .= '((:((25[0-5]|2[0-4]\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2}))';
			$pattern .= '{3})?)|((:[0-9A-Fa-f]{1,4}){1,2})))|(([0-9A-Fa-f]{1,4}:)(:[0-9A-Fa-f]{1,4})';
			$pattern .= '{0,4}((:((25[0-5]|2[0-4]\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2})){3})?)';
			$pattern .= '|((:[0-9A-Fa-f]{1,4}){1,2})))|(:(:[0-9A-Fa-f]{1,4}){0,5}((:((25[0-5]|2[0-4]';
			$pattern .= '\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2})){3})?)|((:[0-9A-Fa-f]{1,4})';
			$pattern .= '{1,2})))|(((25[0-5]|2[0-4]\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2})){3})))(%.+)?';

			$this->$_pattern['IPv6'] = $pattern;
		}
		if (!isset($this->$_pattern['IPv4'])) {
			$pattern = '(?:(?:25[0-5]|2[0-4][0-9]|(?:(?:1[0-9])?|[1-9]?)[0-9])\.){3}(?:25[0-5]|2[0-4][0-9]|(?:(?:1[0-9])?|[1-9]?)[0-9])';
			$this->$_pattern['IPv4'] = $pattern;
		}
	}

/**
 * Reset internal variables for another validation run.
 *
 * @return void
 */
	protected function _reset() {
		$this->$errors = array();
	}

}
