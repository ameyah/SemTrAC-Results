<?php
/**
 * Created by PhpStorm.
 * User: Ameya
 * Date: 7/14/2016
 * Time: 22:42
 */

function utf8ize($d) {
    if (is_array($d)) {
        foreach ($d as $k => $v) {
            $d[$k] = utf8ize($v);
        }
    } else if (is_string ($d)) {
        return utf8_encode($d);
    }
    return $d;
}

function generate_grammar_arr($grammar_text) {
    preg_match_all('/\([a-z]+[0-9]*\)/i', $grammar_text, $grammar_arr);
    for($i = 0; $i < count($grammar_arr); $i++) {
        $grammar_arr[$i] = preg_replace("/[^a-zA-Z0-9]+/", "", $grammar_arr[$i]);
    }
    return $grammar_arr;
}