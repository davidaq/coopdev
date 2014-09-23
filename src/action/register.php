<?php
if(posted('username', 'password', 'retype')) {
    function chkRegister() {
        if(!preg_match('/^[0-9a-zA-Z_]+$/', $_POST['username'])) {
            return LANG('Username must only contain English alphabets, underscore and numbers');
        }
        $u = $_POST['username'];
        if(data_exists("user/$u/pwd")) {
            return LANG('User exists');
        }
        if(!isset($_POST['password']{2})) {
            return LANG('Password must be at least 3 charaters long');
        }
        if($_POST['password'] != $_POST['retype'])
            return LANG('Password retype doesn\'t match');
        $p = password($_POST['password']);
        data_save("user/$u/pwd", $p);
        $_SESSION[USER_SESSION] = $u;
    }
    $result = chkRegister();
    if($result)
        die(tpl('register', array('errormsg'=>$result)));
    else
        redirect('/');
} else {
    die(tpl('register'));
}
