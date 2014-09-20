<?php
/*******************
 * Define constants
 *******************/
function define_constants() {
    define('REQUEST_TIME', mstime());
    define('VERSION', file_get_contents('version'));
    define('USER_SESSION', 'USER_SESSION');
    define('BASE', __getBase());
    define('URI', __getUri());
}
/**********
 * Utility
 **********/
function redirect($url) {
    if($url{0} != '/')
        $url = BASE . substr($url, 1);
    header('location: ' . $url);
    die();
}
function mstime() {
    return ceil(microtime(true) * 1000);
}
function data_exists($file) {
    return file_exists("data/$file.php");
}
function data_read($file) {
    if(data_exists($file)) {
        $c = file("data/$file.php");
        unset($c[0]);
        return implode('', $c);
    }
    return '';
}
function data_save($file, $c) {
    $c = "<?php die('Unauthorized access'); ?>\n$c";
    $dir = explode('/', $file);
    if(isset($dir[1])) {
        unset($dir[count($dir) - 1]);
        $dir = implode('/', $dir);
        mkdir("data/$dir");
    }
    mkdir($file);
    file_put_contents("data/$file.php", $c);
}
function sync_begin() {
    global $_LOCK_FP;
    if($_LOCK_FP) return;
    $_LOCK_FP = fopen('data/writelock', 'r+');
    flock($_LOCK_FP, LOCK_EX);
}
function sync_end() {
    global $_LOCK_FP;
    if(!$_LOCK_FP) return;
    flock($_LOCK_FP);
    fclose($_LOCK_FP);
    $_LOCK_FP = NULL;
}
function password($passwd) {
    return md5($passwd . '___');
}
function posted() {
    foreach(func_get_args() as $k) {
        if(!isset($_POST[$k]))
            return false;
    }
    return true;
}
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
            $c = $m[2];
            if($c{0} == '$') {
                $c = "isset($c)?$c:''";
            }
            if($m[1] == '-')
                $c = 'htmlentities(' . $c . ')';
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
/******************
 * user accounting
 ******************/
function user($key=NULL) {
    global $_USER;
    if($_USER === NULL) {
        $_USER = 1;
        if(isset($_SESSION[USER_SESSION])) {
            $u = $_SESSION[USER_SESSION];
            if(data_exists("user/$u/pwd")) {
                if(!data_exists("user/$u/info")) {
                    $u = array(
                        'name' => $u,
                        'title' => LANG('Unidentified'),
                    );
                } else {
                    $u = json_decode(data_read("user/$u/info"), true);
                }
                if(data_exists("user/$u/avatar.jpg")) {
                    $u['avatar'] = BASE . "data/user/$u/avatar.jpg";
                } else {
                    $u['avatar'] = BASE . 'res/images/default-avatar.jpg';
                }
                $_USER = $u;
            }
        }
    }
    if(!$key) {
        return is_array($_USER);
    } elseif(isset($_USER[$key])) {
        return $_USER[$key];
    }
    return NULL;
}
/**************
 * Import wrap
 **************/
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
define_constants();
session_start();
import(URI);
