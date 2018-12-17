<?php

namespace Logic\Simplifier\Expression;

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

        return \Logic\Simplifier\array_value_union($extracted_left, $extracted_right);
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