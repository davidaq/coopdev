<?php
/**********
 * Utility
 **********/
function redirect($url) {
    if($url{0} == '/')
        $url = BASE . substr($url, 1);
    header('location: ' . $url);
    die();
}
function mstime() {
    return ceil(microtime(true) * 1000);
}
define('REQUEST_TIME', mstime());
/*******************************************
 * Examine the current location of the site
 *******************************************/
function __getBase() {
    return preg_replace('/index\.php$/', '', $_SERVER['SCRIPT_NAME']);
}
function __getUri() {
    $ret = preg_replace('/^' . preg_quote(BASE, '/') . '/', '', $_SERVER['REQUEST_URI']);
    if(!isset($ret{0}))
        return 'index.php';
    $ret = preg_replace('/\?.*$/', '', $ret);
    if(substr($ret, strlen($ret) - 4) != '.php')
        $ret .= '.php';
    return $ret;
}
define('BASE', __getBase());
define('URI', __getUri());
/**************
 * Load Config
 **************/
function CFG($key) {
    global $_CFG;
    if($_CFG === NULL) {
        if(file_exists('config.php')) {
            $_CFG = include 'src/config.default.php';
            $spec = include 'config.php';
            foreach($spec as $k=>$v) {
                $_CFG[$k] = $v;
            }
        } else
            $_CFG = include 'src/config.default.php';
        
    }
    if(isset($_CFG[$key])) {
        return $_CFG[$key];
    }
    return NULL;
}
/**************
 * Load Language
 **************/
function LANG($key) {
    global $_LANG;
    $v = CFG($key);
    if($v !== NULL)
        return $v;
    if($_LANG === NULL)
        $_LANG = parse_ini_file('language/' . CFG('language') . '.ini');
    if(isset($_LANG[$key]))
        return $_LANG[$key];
    return $key;
}
/*************************
 * Simple template engine
 *************************/
function evaluate($_EVAL, $ROOT) {
    eval($_EVAL);
}
function tpl($path, $data=array()) {
    $path = "src/view/$path.html";
    if(file_exists($path)) {
        $c =  file_get_contents($path);
        $c = preg_replace_callback('/\{\{\s*(.+?)\s*\}\}/', function($m) {
            return '<?php echo LANG(\'' . addslashes($m[1]) . '\');?>'; 
        }, $c);
        $c = preg_replace_callback('/\<%([\-\=])\s*(.+?)%\s*\>/s', function($m) {
            if($m[1] == '-')
                $c = 'htmlentities(' . $m[2] . ')';
            else 
                $c = $m[2];
            return '<?php echo ' . $c . ';?>'; 
        }, $c);
        $c = preg_replace('/\<%(.+?)%\>/s', '<?php $1; ?>', $c);
        $eval = '';
        if(is_array($data)) {
            foreach(array_keys($data) as $____) {
                $eval .= '$' . $____ . '=' . '$ROOT[\'' . $____ . '\'];';
            }
        }
        $eval .= '?>' . $c;
        return evaluate($eval, $data);
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
        die(tpl('404', array('hehe'=>'123')));
    }
}
/***************************
 * Begin URI Specific logic
 ***************************/
import(URI);
