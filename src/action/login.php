<?php
if(posted('username', 'password')) {
    function chkLogin() {
        $u = $_POST['username'];
        if(!data_exists("user/$u/pwd")) {
            return LANG('No such user');
        }
        $p = password($_POST['password']);
        if($p != data_read("user/$u/pwd")) {
            return LANG('Password wrong');
        }
        $_SESSION[USER_SESSION] = $u;
        return false;
    }
    $result = chkLogin();
    if($result)
        die(tpl('login', array('errormsg'=>$result)));
    else
        redirect($_POST['refer']);
} else {
    die(tpl('login'));
}
