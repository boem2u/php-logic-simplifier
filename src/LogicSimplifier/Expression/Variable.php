<?php

namespace Logic\Simplifier\Expression;

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
        throw new VariableNotFoundException('VariableNotFound', 2);
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