<?php
if(!isset($_GET['action'])) die();
import('model/status.php');
if($_GET['action'] == 'delete') {
    remove_status($_POST['id'] * 1, true);
} elseif ($_GET['action'] == 'post') {
    if(user() && posted('content')) {
        if(trim($_POST['content'])) {
            post_status($_POST['content'], 'say');
        }
    }
} elseif ($_GET['action'] == 'fetch') {
    die(json_encode(list_status($_GET['start'] * 1, $_GET['limit'] * 1)));
} elseif ($_GET['action'] == 'querynew') {
    $id = $_GET['id'] * 1;
    if($id < last_post_id())
        die('new');
    die('none');
}
