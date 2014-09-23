<?php
if(!user())
    redirect('/login');
if(isset($_GET['action'])) {
    if($_GET['action'] == 'basic') {
        if($_FILES['avatar']['size'] > 0) {
            import('model/imgutil.php');
        }
        global $_USER;
        $user = $_USER;
        unset($user['avatar']);
        unset($user['id']);
        $user['name'] = $_POST['realname'];
        if($user['verified']) {
            $user['title'] = $_POST['title'];
        }
        data_save('user/' . user('id'). '/info', json_encode($user));
        redirect('/settings');
    }
}
die(tpl('settings'));

