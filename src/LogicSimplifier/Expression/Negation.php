<?php

namespace Logic\Simplifier\Expression;

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