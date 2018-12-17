<?php

namespace Logic\Simplifier;

include '../src/LogicSimplifier/expr.php';
include '../src/LogicSimplifier/parser.php';
include '../src/LogicSimplifier/perm.php';
include '../src/LogicSimplifier/simplifier.php';


function parse_test()
{
    $_test_parse = function($expr)
    {
        try {
            echo sprintf('%s -> %s', str_pad($expr, 16), parse($expr)).PHP_EOL;
        } catch (\Exception $e) {
            echo sprintf('%s -> %s', str_pad($expr, 16), 'Invalid syntax').PHP_EOL;
        }
    };

    // $_test_parse('a&b');
    // $_test_parse('a|~b');
    // $_test_parse('(~a & b) |c');
    // $_test_parse('(a & (b) |c)');
    // $_test_parse('a & (b |c)');
    // $_test_parse('a & (~b |c');
    // $_test_parse('a) & (b |c');
    // $_test_parse('(a > b) |c');
    // $_test_parse('a > (b |~c)');
    // $_test_parse('abc & def');
    // $_test_parse('abc & def *');

    echo "\n";

    $expr = parse('a & (b) & 1');
    print($expr).PHP_EOL;

    $str = [];
    foreach ($expr->extract_vars() as $value) {
        $str[] = "'{$value}'";
    }
    $str = "{".implode(',', $str)."}";
    print($str).PHP_EOL;
    var_dump($expr->eval([
        'a' =>  true,
        'b' =>  false
    ]));
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


function SimplificationTable_test()
{
    // echo(simplify('~a&b&~c&~d | a&~b&~c&~d | a&~b&~c&d | a&~b&c&~d | ' . 
                   // 'a&~b&c&d | a&b&~c&~d | a&b&c&~d | a&b&c&d')).PHP_EOL;
    // print(simplify('~a&~b&~c | ~a&~b&c | ~a&b&~c | ~a&~b&c | a&b&~c | a&b&c')).PHP_EOL;
    // print(simplify('a | ~a')).PHP_EOL;
    // print(simplify('a & ~a')).PHP_EOL;
    // print(simplify('~a&b&~c&~d | a&~b&~c&d | a&~b&~c&~d')).PHP_EOL;
    // print(simplify('valid')).PHP_EOL;

    print(simplify('~(中国|~美国|~日本|~韩国) & (手机|电脑)')).PHP_EOL;
    // print(simplify('(a|~b|~c|~d) & (e|f)')).PHP_EOL;

    // print(simplify('invalid?')).PHP_EOL;
}



// perm_test();
// parse_test();
// SimplificationTable_test();