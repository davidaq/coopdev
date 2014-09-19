<?php
/*******************************************
 * Examine the current location of the site
 *******************************************/
function __getBase() {
    $self = '';
    if(isset($_SERVER['PHP_SELF'])) {
        $self = $_SERVER['PHP_SELF'];
    } elseif(isset($_SERVER['SCRIPT_NAME'])) {
        $self = $_SERVER['SCRIPT_NAME'];
    } elseif(isset($_SERVER['CONTEXT_DOCUMENT_ROOT'])) {
        $self = str_replace($_SERVER['CONTEXT_DOCUMENT_ROOT'], '', __FILE__);
    }
    $p = strrpos(str_replace('\\', '/', __FILE__), '/');
    $fname = substr(__FILE__, $p + 1);
    $base = str_replace($fname, '', $self);
    return $base;
}
function __getUri() {
    $ret = preg_replace('/^' . preg_quote(BASE, '/') . '/', '', $_SERVER['REQUEST_URI']);
    if(!isset($ret{0}))
        $ret = 'index.php';
    return $ret;
}
define('BASE', __getBase());
define('URI', __getUri());
/*************************
 * Simple template engine
 *************************/
function tpl($path, $data=array()) {
    $path = "src/view/$path.html";
    if(file_exists($path)) {
        return file_get_contents($path);
    } else {
        return " ERROR: $path not found ";
    }
}
/***************
 * Import logic
 ***************/
function import($_PATH) {
    $_PATH = "src/action/$_PATH";
    if(file_exists($_PATH)) {
        include $_PATH;
    } else {
        die(tpl('404'));
    }
}
/***************************
 * Begin URI Specific logic
 ***************************/
import(URI);
