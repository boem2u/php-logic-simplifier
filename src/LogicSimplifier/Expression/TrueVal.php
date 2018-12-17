<?php

namespace Logic\Simplifier\Expression;

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