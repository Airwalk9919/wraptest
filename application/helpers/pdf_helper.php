<?php
if (!function_exists('fmt_date')) {
  function fmt_date($s){
    if(!$s) return '';
    $t = strtotime($s);
    return $t ? date('d/m/Y', $t) : $s;
  }
}
