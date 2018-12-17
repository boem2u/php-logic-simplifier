<?php

namespace Logic\Simplifier;

use Logic\Simplifier\Expression\Negation;
use Logic\Simplifier\Expression\TrueVal;
use Logic\Simplifier\Expression\Variable;
use Logic\Simplifier\Expression\Operator;

class Permutation
{
    function __construct($val)
    {
        $this->_val = $val;
    }

    public function append($val)
    {
        $val = array_merge($this->_val, $val);
        return new Permutation($val);
    }

    public function count_positives()
    {
         $count = 0;

         foreach ($this->_val as $v) {
             if ($v) {
                 $count += 1;
             }
         }
         return $count;
    }

    public function keys()
    {
        return array_keys($this->_val);
    }

    public function values()
    {
        return $this->_val;
    }

    public function value($key)
    {
        return $this->_val[$key];
    }

    public function to_conj()
    {
        $ret = '';
        ksort($this->_val);
        foreach ($this->_val as $k => $v) {
            if ($v === true) {
                $ret .= ' & ' . $k;
            }
            if ($v === false) {
                $ret .= ' & ~' . $k;
            }
        }
        return ($ret === '') ? '1' : substr($ret, 3);
    }

    public function to_expr()
    {
        $ret = null;
        ksort($this->_val);
        foreach ($this->_val as $k => $v) {
            if ($v === null) continue;

            if ($v === true) $expr = new Variable($k);
            else $expr = new Negation(new Variable($k));

            if ($ret === null) $ret = $expr;
            else $ret = new Operator($ret, '&', $expr);
        }
        return ($ret !== null) ? $ret : new TrueVal();
    }

    /**
     * 可以作为array并集依据
     */
    public function __toString()
    {
        $ret = '';
        ksort($this->_val);
        foreach ($this->_val as $k => $v) {
            if ($v === true) {
                $ret .= $k;
            }
            if ($v === false) {
                $ret .= '!'.$k;
            }
        }
        return $ret;
    }

    public function __repr__()
    {
        return strval($this);
    }

    public function __eq__($p)
    {
        return property_exists($this, '_val') and ($this->_val == $p->_val);
    }

    public function __hash__()
    {
        return md5(serialize($this->_val));
    }

    public static function empty()
    {
        return new static([]);
    }

    public static function generate_values($varlist)
    {
        if (empty($varlist)) {
            yield static::empty();
        } else {
            foreach (static::generate_values(array_slice($varlist, 1)) as $p) {
                yield $p->append([$varlist[0] => true]);
                yield $p->append([$varlist[0] => false]);
            }    
        }
    }
}

