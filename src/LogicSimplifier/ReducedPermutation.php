<?php

namespace Logic\Simplifier;

use Logic\Simplifier\Exception;

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