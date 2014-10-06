<?php
/*******************
 * Define constants
 *******************/
function define_constants() {
    date_default_timezone_set('Asia/Chongqing');
    define('REQUEST_TIME', mstime());
    define('VERSION', file_get_contents('version'));
    define('USER_SESSION', 'USER_SESSION');
    define('BASE', __getBase());
    define('URI', __getUri());
}
/**********
 * Utility
 **********/
function iescape($val, $breaklines=false) {
    if($breaklines)
        return strtr($val, array(' '=>'&nbsp;','<'=>'&lt;','>'=>'&gt;',"\n"=>'<br/>'));
    return strtr($val, array(' '=>'&nbsp;','<'=>'&lt;','>'=>'&gt;'));
}
function redirect($url) {
    if($url{0} == '/')
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
function delTree($dir) { 
    $files = array_diff(scandir($dir), array('.','..')); 
    foreach ($files as $file) { 
        if(is_dir("$dir/$file")) 
            delTree("$dir/$file");
        else
            unlink("$dir/$file"); 
    } 
    return rmdir($dir); 
}
function delEmptyTree($dir) {
    $files = array_diff(scandir($dir), array('.','..')); 
    foreach ($files as $file) { 
        if(is_dir("$dir/$file")) 
            delEmptyTree("$dir/$file");
    } 
    return @rmdir($dir); 
}
function data_remove($file) {
    if(file_exists("data/$file.php")) {
        unlink("data/$file.php");
    } elseif(is_dir("data/$file")) {
        delTree("data/$file");
    }
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
        $oldmask = umask(0);
        @mkdir("data/$dir", 0777, true);
        umask($oldmask);
    }
    file_put_contents("data/$file.php", $c);
}
function data_list($dir, $prefix='') {
    if(file_exists("data/$dir")) {
        if($dir{strlen($dir) - 1} != '/')
            $dir .= '/';
        $list = scandir("data/$dir");
        $len = strlen($prefix);
        sort($list);
        $ret = array();
        foreach($list as $k=>$v) {
            if($v{0} === '.' || ($len > 0 && substr($v, 0, $len) !== $prefix)) {
                unset($list[$k]);
            } else {
                $ret["$dir$v"] = substr($v, $len);
            }
        }
        return $ret;
    }
    return array();
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
    flock($_LOCK_FP, LOCK_UN);
    fclose($_LOCK_FP);
    $_LOCK_FP = NULL;
}
function password($passwd) {
    return md5($passwd . CFG('secure-seed'));
}
function posted() {
    foreach(func_get_args() as $k) {
        if(!isset($_POST[$k]))
            return false;
    }
    return true;
}
function human_time($time) {
    $time *= 1;
    $d = time() - $time;
    if($d < 120) {
        return LANG('just now');
    } elseif ($d < 300) {
        return LANG('in 5 minutes');
    } elseif ($d < 3600) {
        return LANG('%% minutes before', ceil($d / 60));
    } elseif ($d < 15000) {
        return LANG('%% hours before', floor($d / 3600));
    } else {
        return date('y/m/d H:i', $time);
    }
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
function LANG($key, $replacement='') {
    global $_LANG;
    $v = CFG($key);
    if($v !== NULL)
        return $v;
    if($_LANG === NULL)
        $_LANG = parse_ini_file('language/' . CFG('language') . '.ini');
    if(isset($_LANG[$key]))
        $key = $_LANG[$key];
    $key = str_replace('%%', $replacement, $key);
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
        ob_start();
        evaluate($eval, $data);
        $ret = ob_get_contents();
        ob_end_clean();
        return $ret;
    } else {
        return " ERROR: $path not found ";
    }
}
/******************
 * user accounting
 ******************/
function getUser($u) {
    if(data_exists("user/$u/pwd")) {
        $id = $u;
        if(!data_exists("user/$u/info")) {
            $u = array(
                'name' => $u,
                'title' => LANG('Unidentified'),
                'verified' => false,
            );
        } else {
            $u = json_decode(data_read("user/$u/info"), true);
        }
        $u['id'] = $id;
        if(file_exists("data/user/$id/avatar.jpg")) {
            $u['avatar'] = BASE . "data/user/$id/avatar.jpg";
        } else {
            $u['avatar'] = BASE . 'res/images/default-avatar.jpg';
        }
        return $u;
    }
    return NULL;
}
function user($key=NULL) {
    global $_USER;
    if($_USER === NULL) {
        $_USER = 1;
        if(isset($_SESSION[USER_SESSION])) {
            $_USER = getUser($_SESSION[USER_SESSION]);
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
    $_PATH = "src/$_PATH";
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
if(get_magic_quotes_gpc()) {
    function stripslashes_deep($value) {
        $value = is_array($value) ?
                    array_map('stripslashes_deep', $value) :
                    stripslashes($value);
        return $value;
    }
    $_POST = array_map('stripslashes_deep', $_POST);
    $_GET = array_map('stripslashes_deep', $_GET);
    $_COOKIE = array_map('stripslashes_deep', $_COOKIE);
    $_REQUEST = array_map('stripslashes_deep', $_REQUEST);
}
function escape_tpl($value) {
    $value = is_array($value) ?
                array_map('escape_tpl', $value) :
                strtr($value, array('<%'=>'&lt;%', '%>'=>'%&gt;'));
    return $value;
}
$_POST = array_map('escape_tpl', $_POST);
$_GET = array_map('escape_tpl', $_GET);
$_COOKIE = array_map('escape_tpl', $_COOKIE);
$_REQUEST = array_map('escape_tpl', $_REQUEST);
import('action/' . URI);
