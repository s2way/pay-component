<?php

namespace PayComponent\Component;

class Validator {

    private $validationErrors = array();

    public function validate($listRules, $data) {

        // Varre lista dos camposs
        foreach ($listRules as $field => $rules) {
            // Varre lista das regras de cada campo
            foreach ($rules as $methodName => $rule) {
                // Valida se foi passado parametros para a validação
                $params = array_key_exists('params', $rule) ? $rule['params'] : null;
                // Valida a regra
                if (!$this->$methodName($data["$field"], $params, $data)) {
                    // Verifica se existe uma regra quebrada para o campo no array de erros
                    if (!array_key_exists($field, $this->validationErrors)){
                        // echo '======';
                        // echo $field;
                        // echo $rule['message'];
                        // echo '======';
                        $this->setError($field, $rule['message']);
                    }else{
                        break;
                    }
                }
            }
        }
        return $this->validationErrors ? false : true;
    }

    public function setError($field, $errorMessage){
        $this->validationErrors[$field] = $errorMessage;
    }

    public function getValidationErrors(){
        return $this->validationErrors;
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
        $regex = "/^((http|https)?):\/\/([a-zA-Z0-9_-]+)(\.[a-zA-Z0-9_-]+)+(\:\d{4}|)(\/[a-zA-Z0-9_-]+)*\/?$/i";
        return $this->_check($check, $regex);
    }

    /**
     * Caso forma de pagamento for debito, valida apenas visa ou master
     */
    public function paymentTypeIssuer($check, $params, $data) {
        if($check === 'debito') {
            $validIssuersForDebit = array('mastercard', 'visa');
            if(!array_key_exists('issuer', $data) or !$this->inList($data['issuer'], $validIssuersForDebit)) {
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
 * Checks whether the length of a string is greater or equal to a minimal length.
 *
 * @param string $check The string to test
 * @param integer $min The minimal string length
 * @return boolean Success
 */
    public function minLength($check, $min, $data) {
        return strlen($check) >= $min;
    }

/**
 * Checks whether the length of a string is smaller or equal to a maximal length..
 *
 * @param string $check The string to test
 * @param integer $max The maximal string length
 * @return boolean Success
 */
    public function maxLength($check, $max, $data) {
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
 * Reset internal variables for another validation run.
 *
 * @return void
 */
    protected function _reset() {
        $this->errors = array();
    }

}
