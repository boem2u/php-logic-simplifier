<?php

namespace Logic\Simplifier\Expression;

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
