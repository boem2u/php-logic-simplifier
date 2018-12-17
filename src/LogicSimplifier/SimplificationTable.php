<?php

namespace Logic\Simplifier;

use Logic\Simplifier\Expression\Operator;

function array_value_union(... $arr_list)
{
    foreach ($arr_list as &$arr) {
        $arr = array_values($arr);
    }
    $data = array_unique(array_merge(... $arr_list));
    return $data;
}

class SimplificationTable
{
    function __construct($grouped)
    {
        $this->_stages = [$grouped];
    }

    public function measure($res)
    {
        $ret = 0;
        foreach ($res as $perm) {
            $ret += count($perm->get_reduced());
        }
        return $ret;
    }

    public function next_stage()
    {
        $stage    = end($this->_stages);
        $newstage = [];

        foreach ($stage as $gid => &$perms) {

            if (!array_key_exists($gid+1, $stage)) {
                continue;
            }

            foreach ($perms as &$perm) {
                foreach ($stage[$gid+1] as &$perm2) {

                    $reduced = $perm->reduce($perm2);
                    if ($reduced != null) {
                        $newstage[$gid][] = $reduced;
                        $perm->processed  = true;
                        $perm2->processed = true;
                    }
                }
            }
        }

        foreach ($newstage as $gid => &$perm3) {
            $perm3 = array_unique($perm3);
        }

        if ($newstage) {
            $this->_stages[] = $newstage;
            return true;
        }

        return false;
    }

    public function fill_stages()
    {
        while ($this->next_stage()) {
            continue;
        }
    }

    public function each_permutation($handler)
    {
        foreach ($this->_stages as $stage) {
            foreach ($stage as $perms) {
                foreach ($perms as $perm) {
                    $handler($perm);
                }
            }
        }
    }

    public function results()
    {
        $ret = [];

        $handler = function($perm) use (&$ret){
            if (!property_exists($perm, 'processed')) {
                $ret[] = $perm;
            }
        };

        $this->each_permutation($handler);
        return $ret;
    }

    public function grouped_results()
    {
        $grouped = [];
        foreach ($this->results() as $reduced) {
            foreach ($reduced->_perms as $f) {
                $f = serialize($f);
                if (!array_key_exists($f, $grouped)) {
                    $grouped[$f] = [];
                }
                $grouped[$f][] = $reduced;
            }
        }

        return $grouped;
    }

    public function _exclude_reduced($grouped, $to_exclude)
    {
        $ret = $grouped;

        foreach ($grouped as $perm => $reduced) {
            if (empty($reduced) or (in_array($to_exclude, $reduced))) {
                unset($ret[$perm]);
            }
        }

        return $ret;
    }

    public function _extract_essentials($grouped)
    {
        $min_degree = null;
        $min_reduced = null;
        foreach ($grouped as $reduced) {
            if ($min_degree == null or (count($reduced) < $min_degree)) {
                $min_degree  = count($reduced);
                $min_reduced = $reduced;
            }
        }

        if ($min_reduced == null) {
            return [];
        }

        $ret = [];

        foreach ($min_reduced as $essential) {
            $ret[serialize($essential)] = $this->_exclude_reduced($grouped, $essential);
        }

        return $ret;
    }

    public function _minimal_results_for($grouped)
    {
        $min_res = null;

        $list = $this->_extract_essentials($grouped);
        foreach ($list as $essential => $extracted) {
            $essential = unserialize($essential);
            $res = $this->_minimal_results_for($extracted);
            $res[] = $essential;
            if ($min_res == null or ($this->measure($res) < $this->measure($min_res))) {
                $min_res = $res;
            }
        }

        if ($min_res != null) {
            return $min_res;
        }

        return [];
    }

    public function minimal_results()
    {
        return $this->_minimal_results_for($this->grouped_results());
    }

    public function __toString()
    {
        $ret = 'SimplificationTable(\n';
        foreach ($this->_stages as $stage) {
            $ret .= '== Stage\n';
            foreach ($stage as $gid => $perms) {
                $ret .= strval($gid) + ': ' + $perms->__repr__() + '\n';
            }
        }
        $ret .= ')';
        return $ret;
    }

    public static function for_expr($expr)
    {
        $positives = $expr->generate_positives();
        return static::group_values($positives);
    }

    public static function group_values($values)
    {
        $ret = [];
        foreach ($values as $val) {
            $reduced = ReducedPermutation::from_permutation($val);
            $ret[$val->count_positives()][] = $reduced;
        }
        return new SimplificationTable($ret);
    }

    public static function simplify_expr($expr)
    {
        $tbl = SimplificationTable::for_expr($expr);
        $tbl->fill_stages();
        $minimal_results = $tbl->minimal_results();

        $simplified = null;
        foreach ($minimal_results as $reduced) {
            $expr = $reduced->get_reduced()->to_expr();
            if ($simplified == null) {
                $simplified = $expr;
            } else {
                $simplified = new Operator($simplified, '|', $expr);
            }
        }

        return ($simplified != null) ? $simplified : new FalseVal();
    }

    public static function simplify($s)
    {
        $str = '';
        $len = mb_strlen($s);
        for ($i=0; $i < $len; $i++) { 
            $ch = mb_substr($s, $i, 1);
            if (ord($ch) != 32) {
                $str .= $ch;
            }
        }
        $data = (new Parser($str))->parse();
        return strval(static::simplify_expr($data));
    }
}




function SimplificationTable_test()
{
    // echo(simplify('~a&b&~c&~d | a&~b&~c&~d | a&~b&~c&d | a&~b&c&~d | ' . 
                   // 'a&~b&c&d | a&b&~c&~d | a&b&c&~d | a&b&c&d')).PHP_EOL;
    // print(simplify('~a&~b&~c | ~a&~b&c | ~a&b&~c | ~a&~b&c | a&b&~c | a&b&c')).PHP_EOL;
    // print(simplify('a | ~a')).PHP_EOL;
    // print(simplify('a & ~a')).PHP_EOL;
    // print(simplify('~a&b&~c&~d | a&~b&~c&d | a&~b&~c&~d')).PHP_EOL;
    // print(simplify('valid')).PHP_EOL;

    $str = SimplificationTable::simplify('~(中国|~美国|~日本|~韩国) & (手机|电脑)');
    print($str).PHP_EOL;
    // print(simplify('(a|~b|~c|~d) & (e|f)')).PHP_EOL;

    // print(simplify('invalid?')).PHP_EOL;
}
