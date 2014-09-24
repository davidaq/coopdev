<?php
if(!user()) redirect('/login');

$errormsg = array('','');
if(isset($_GET['action'])) {
    if($_GET['action'] == 'basic') {
        $uid = user('id');
        if($_FILES['avatar']['size'] > 0) {
            import('model/imgutil.php');
            make_thumb($_FILES['avatar']['tmp_name'], "data/user/$uid/avatar.jpg", 90, 90);
        }
        global $_USER;
        $user = $_USER;
        unset($user['avatar']);
        unset($user['id']);
        $uname = trim($_POST['realname']);
        if(isset($uname{0}))
            $user['name'] = iescape($uname);
        $utitle = trim($_POST['title']);
        if($user['verified'] && isset($utitle{0})) {
            $user['title'] = iescape($utitle);
        }
        data_save("user/$uid/info", json_encode($user));
        redirect('/settings');
    } elseif($_GET['action'] == 'password') {
        function checkPwd() {
            if(!isset($_POST['password']{2})) {
                return LANG('Password must be at least 3 charaters long');
            }
            if($_POST['password'] !== $_POST['retype'])
                return LANG('Password retype doesn\'t match');
            $p = password($_POST['password']);
            $u = user('id');
            data_save("user/$u/pwd", $p);
            return false;
        }
        $errormsg[1] = checkPwd();
        if($errormsg[1] === false)
            redirect('/');
    } else {
        function hex2array($str) {
            $arr = array();
            for($i = 0; $i < 32; $i++) {
                $c = ord($str{$i});
                if($c > 90) $c -= 87;
                else $c -= 48;
                $arr[] = $c;
            }
            return $arr;
        }
        function harass($arr, $sand) {
            $sand = hex2array(md5($sand));
            $str = '';
            $map = '1234567890qwertyuiopasdfghjklzxcvbnmPOIUYTREWQLKJHGDASMNBVCXZ._-!';
            foreach($arr as $k=>$v) {
                $v *= ($sand[$k] & 3) + 1;
                $v += $sand[$k] >> 2;
                $str .= $map{$v};
            }
            return $str;
        }
        function user_verify_hash() {
            $arr = hex2array(md5(user('id')));
            $ret = array();
            for($i = 0; $i < 30; $i++) {
                $sed = ceil(time() / 60) + $i;
                $ret[] = harass($arr, CFG('secure-seed') . $sed);
            }
            for($i = 1; $i < 30; $i++) {
                $sed = ceil(time() / 60) - $i;
                $ret[] = harass($arr, CFG('secure-seed') . $sed);
            }
            return $ret;
        }
        if($_GET['action'] == 'verifysend') {
            if(isset($_SESSION['prev_verify_time'])) {
                $d = $_SESSION['prev_verify_time'] * 1;
                if(time() - $d < 60) {
                    echo tpl('header');
                    echo tpl('info', array(
                        'icon' => 'info-sign',
                        'title' => LANG('Action aborted'),
                        'content' => LANG('Verification too frequent')
                    ));
                    echo tpl('footer');
                    die();
                }
            }
            $_SESSION['prev_verify_time'] = time();
            $email = $_POST['email'] . CFG('verify email');
            $hash = user_verify_hash();
            $hash = $hash[0];
            $header = "From: do-not-reply@" . $_SERVER['HTTP_HOST'];
            $content = tpl('verifymail', array('hash'=>$hash));
            mail($email, LANG('Identity Verify'), $content, $header);
            echo tpl('header');
            echo tpl('info', array(
                'icon' => 'send',
                'title' => LANG('Verification sent'),
                'content' => LANG('Use the link sent to you to finish the verification')
            ));
            echo tpl('footer');
            die();
        } elseif($_GET['action'] == 'verify') {
            if(in_array($_GET['hash'], user_verify_hash())) {
                $message = LANG('Verify complete. You now have more permissions');
                global $_USER;
                $_USER['verified'] = true;
                $_USER['title'] = '-';
                $user = $_USER;
                unset($user['avatar']);
                unset($user['id']);
                $uid = user('id');
                data_save("user/$uid/info", json_encode($user));
                import('model/status.php');
                post_status(LANG('Verified identity'));
            } else {
                $message = LANG('Verification key is invalid or out of date');
            }
            echo tpl('header');
            echo tpl('info', array(
                'icon' => 'send',
                'title' => LANG('Identity Verify'),
                'content' => $message
            ));
            echo tpl('footer');
            die();
        }
    }
}
die(tpl('settings', array('errormsg'=>$errormsg)));
