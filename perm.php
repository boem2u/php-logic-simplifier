<?php

namespace Logic\Simplifier;

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


class ReducedPermutation
{
    
    function __construct($perms, $reduced)
    {
        sort($perms);
        $this->_perms   = $perms;
        $this->_reduced = $reduced;

        if (!($reduced instanceof Permutation)) {
            throw new ValueError("错误的处理请求!", 1);
        }
    }

    public function get_reduced()
    {
        return $this->_reduced;
    }

    public function reduce($p)
    {
        $self_keys = $this->_reduced->keys();
        $p_keys    = $p->_reduced->keys();

        $keys = array_value_union($self_keys, $p_keys);
        $ret  = [];
        $reduced = false;

        foreach ($keys as $key) {
            $val1 = $p->_reduced->value($key);
            $val2 = $this->_reduced->value($key);

            if ($val1 === $val2) {
                $ret[$key] = $val1;
            } elseif ($reduced) {
                return null;
            } else {
                $reduced   = true;
                $ret[$key] = null;
            }
        }

        $perms = array_value_union($this->_perms, $p->_perms);
        return new ReducedPermutation($perms, new Permutation($ret));
    }

    public function __toString()
    {
        $perms_str = array_reduce($this->_perms ?? [], function($str, $p){
            return $str .= strval($p).',';
        });
        $perms_str = "{".trim($perms_str, ',')."}";

        return 'ReducedPermutation(from=' . $perms_str . ', to=' . strval($this->_reduced) .')';
    }

    public function __repr__()
    {
        return strval($this);
    }

    public function __eq__($p)
    {
        return ($p instanceof ReducedPermutation)
            && ($this->_perms === $p->_perms)
            && ($this->_reduced === $p->_reduced);
    }

    public function __hash__()
    {
        return md5(serialize($this->_reduced)).md5(serialize($this->_perms));
    }

    public static function from_permutation($prem)
    {
        return new ReducedPermutation([$prem], $prem);
    }
}


function perm_test()
{
    $list = Permutation::generate_values(['a', 'b', 'c', 'd']);
    foreach ($list as $val) {
        print($val);
        echo "\n";
    }
    
    # reducing
    $a = ReducedPermutation::from_permutation(new Permutation([ 'a' => true, 'b' => false ]));
    $b = ReducedPermutation::from_permutation(new Permutation([ 'a' => false, 'b' => false ]));
    echo($a->reduce($b)).PHP_EOL;
    $a = ReducedPermutation::from_permutation(new Permutation([ 'a' => true, 'b' => false ]));
    $b = ReducedPermutation::from_permutation(new Permutation([ 'a' => false, 'b' => true ]));
    echo($a->reduce($b)).PHP_EOL;
    $a = ReducedPermutation::from_permutation(new Permutation([ 'a' => true, 'b' => null ]));
    $b = ReducedPermutation::from_permutation(new Permutation([ 'a' => false, 'b' => null ]));
    echo($a->reduce($b)).PHP_EOL;
    $a = ReducedPermutation::from_permutation(new Permutation([ 'a' => true, 'b' => false ]));
    $b = ReducedPermutation::from_permutation(new Permutation([ 'a' => true, 'b' => null ]));
    echo($a->reduce($b)).PHP_EOL;
}
