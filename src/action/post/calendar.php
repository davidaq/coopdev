<?php
if($_GET['action'] == 'add') {
    if(user() && user('verified') && posted('date', 'content')) {
        $dataitem = 'calendar/' . $_POST['date'];
        if(data_exists($dataitem))
            $o = json_decode(data_read($dataitem), true);
        else
            $o = array();
        $c = iescape($_POST['content']);
        $lines = explode("\n", $c);
        $t = $lines[0];
        unset($lines[0]);
        $c = implode('</br>', $lines);
        $o[] = array('title'=>$t, 'content'=>$c, 'user'=>user('id'));
        data_save($dataitem, json_encode($o));
    }
} elseif($_GET['action'] == 'delete') {
    if(user() && user('verified') && posted('date', 'key')) {
        $dataitem = 'calendar/' . $_POST['date'];
        if(data_exists($dataitem))
            $o = json_decode(data_read($dataitem), true);
        else
            $o = array();
        if(isset($o[$_POST['key']])) {
            unset($o[$_POST['key']]);
        }
        if(count($o) <= 0) {
            data_remove($dataitem);
        } else {
            data_save($dataitem, json_encode($o));
        }
    }
}
