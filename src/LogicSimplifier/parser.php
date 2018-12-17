<?php

namespace Logic\Simplifier;

class ParseException extends \Exception
{}


class Parser
{
    private $_ops = [
        ['='],
        ['>'],
        ['|'],
        ['^'],
        ['&'],
    ];

    function __construct($data)
    {
        $this->_data = $data;
        $this->_pos  = 0;
    }

    public function parse()
    {
        $parsed = $this->_parseexpr();

        if ($this->_pos != mb_strlen($this->_data)) {
            throw new ParseException("解析错误:".mb_substr($this->_data, $this->_pos, 1), 1);
        }

        return $parsed;
    }

    public function _parseexpr()
    {
        return $this->_parsepri(0);
    }

    public function _parsepri($pri)
    {
        if (count($this->_ops) <= $pri) {
            return $this->_parseneg();
        }

        $expr = $this->_parsepri($pri + 1);

        while (!$this->_end()) {
            if (!(in_array($this->_look(), $this->_ops[$pri]))) {
                break;
            }

            $op    = $this->_next();
            $right = $this->_parsepri($pri + 1);
            $expr  = new Operator($expr, $op, $right);
        }
        return $expr;
    }

    public function _parseneg()
    {
        if ($this->_look() == '~') {
            $this->_next();
            return new Negation($this->_parseterm());
        } else {
            return $this->_parseterm();
        }
    }

    public function _parseterm()
    {
        $lk = $this->_look();
        if ($lk == '(') {
            $this->_next();
            $expr = $this->_parseexpr();

            if ($this->_next() != ')') {
                throw new ParseException("期望以 ) 结束", 1);
            }

            return $expr;
        } elseif ($lk == '0') {
            $this->_next();

            return new FalseVal();
        } elseif ($lk == '1') {
            $this->_next();

            return new TrueVal();
        } else {

            // 参数名称限定
            $isalpha = function($char)
            {
                if (in_array($char, ['(', ')', '=', '>', '|', '^', '&'])) {
                    return false;
                }
                return true;
            };

            $var = '';
            while ((!$this->_end()) and $isalpha($this->_look())) {
                $var .= $this->_next();
            }


            if (mb_strlen($var) == 0) {
                throw new ParseException("预期参数名称", 1);
            }

            return new Variable($var);
        }
    }

    public function _look()
    {
        return mb_substr($this->_data, $this->_pos, 1);
    }

    public function _next()
    {
        $char = mb_substr($this->_data, $this->_pos, 1);
        $this->_pos += 1;
        return $char;
    }

    public function _end()
    {
         return $this->_pos >= mb_strlen($this->_data);
    }
}

function parse($data)
{
    return (new Parser($data))->parse();
}
