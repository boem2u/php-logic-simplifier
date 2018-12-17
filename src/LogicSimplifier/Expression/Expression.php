<?php

namespace Logic\Simplifier\Expression;

use Logic\Simplifier\Permutation;

class Expression
{
    public function eval($varmap)
    {
        throw new \Exception("必须实现该方法", 3);
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
