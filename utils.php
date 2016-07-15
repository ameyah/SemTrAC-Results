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