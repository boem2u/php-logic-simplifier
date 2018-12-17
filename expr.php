<?php

namespace Logic\Simplifier;

/**
 * 数组的值去重
 */
function array_value_union(... $arr_list)
{
    foreach ($arr_list as &$arr) {
        $arr = array_values($arr);
    }
    $data = array_unique(array_merge(... $arr_list));
    return $data;
}

class InvalidOperatorException extends \Exception
{}

class VariableNotFoundException extends \Exception
{}

class ValueError extends \Exception
{}

class Expression
{
    public function eval($varmap)
    {
        throw new Exception("必须实现该方法", 3);
    }

    public function extract_vars()
    {
        return [];
    }

    public function generate_positives()
    {
        $varlist = $this->extract_vars();
        foreach (Permutation::generate_values($varlist) as $p) {
            if ($this->eval($p->values())) {
                yield $p;
            }
        }
    }
}

class Negation extends Expression
{
    function __construct($expr)
    {
        $this->expr = $expr;
    }

    public function eval($varmap)
    {
        return !($this->expr->eval($varmap));
    }

    public function extract_vars()
    {
        return $this->expr->extract_vars();
    }

    public function __toString()
    {
        return '~'.strval($this->expr);
    }
}

class Operator extends Expression
{
    private $_oppri = [
        '=' => 0,
        '>' => 1,
        '|' => 2,
        '^' => 3,
        '&' => 4
    ];
    
    function __construct($left, $op, $right)
    {
        $this->left = $left;
        $this->op   = $op;
        $this->pri  = $this->_oppri[$op];
        $this->right= $right;
    }

    public function eval($varmap)
    {
        $obj = $this;
        $p = function() use ($obj, $varmap) {
            return $obj->left->eval($varmap);
        };
        $q = function() use ($obj, $varmap) {
            return $obj->right->eval($varmap);
        };

        if ($this->op == '>') {
            return !($p() and !$q());
        } elseif ($this->op == '&') {
            return ($p() and $q());
        } elseif ($this->op == '|') {
            return ($p() or $q());
        } elseif ($this->op == '^') {
            return ($p() != $q());
        } elseif ($this->op == '=') {
            return ($p() == $q());
        } else {
            throw new VariableNotFoundException("无效操作符", 2);
        }
    }

    public function extract_vars()
    {
        $extracted_left  = $this->left->extract_vars();
        $extracted_right = $this->right->extract_vars();

        return array_value_union($extracted_left, $extracted_right);
    }

    public function __toString()
    {
        $ret = '';
        if (property_exists($this->left, 'pri') and $this->left->pri < $this->pri ) {
            $ret .= '('.strval($this->left).')';
        } else {
            $ret .= strval($this->left);
        }

        $ret .= ' ' . $this->op . ' ';

        if (property_exists($this->right, 'pri') and $this->right->pri < $this->pri ) {
            $ret .= '('.strval($this->right).')';
        } else {
            $ret .= strval($this->right);
        }

        return $ret;
    }
}

class Variable extends Expression
{
    
    function __construct($name)
    {
        $this->name = $name;
    }

    public function eval($varmap)
    {
        if (array_key_exists($this->name, $varmap)) {
            return $varmap[$this->name];
        }
        throw new VariableNotFoundException("变量未找到", 2);
    }

    public function extract_vars()
    {
        return [$this->name];
    }

    public function __toString()
    {
        return $this->name;
    }
}

class FalseVal extends Expression
{
    public function eval($varmap)
    {
        return false;
    }

    public function extract_vars()
    {
        return [];
    }

    public function __toString()
    {
        return '0';
    }
}

class TrueVal extends Expression
{
    public function eval($varmap)
    {
        return true;
    }

    public function extract_vars()
    {
        return [];
    }

    public function __toString()
    {
        return '1';
    }
}