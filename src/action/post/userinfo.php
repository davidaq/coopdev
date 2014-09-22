<?php
if(posted('ids')) {
    $ret = array();
    foreach($_POST['ids'] as $k) {
        $ret[$k] = getUser($k);
    }
    die(json_encode($ret));
}
