<?php
$add = isset($_POST['url']) && trim($_POST['url']);
$delete = isset($_GET['delete']);
$edit = user('verified') && ($add || $delete);
if($edit) sync_begin();
$data = json_decode(data_read('chron'), true);
if(!$data || !is_array($data)) 
    $data = array();
if(isset($_GET['chron'])) {
    $t = time();
    header('Content-type:text/plain; charset=utf-8');
    foreach($data as $item) {
        if($item['last'] * 1 + $item['interval'] * 1 < $t) {
            echo $item['url'] . "\n";
        }
    }
    die();
}
if($edit) {
    if($add) {
        $ndata = array(
            'url' => trim($_POST['url']),
            'interval' => isset($_POST['interval']) ? $_POST['interval'] * 10 : 10,
            'last' => time()
        );
        if($ndata['interval'] < 10)
            $ndata['interval'] = 10;
        $data[time() . rand(100, 999)] = $ndata;
    } else if($delete) {
        unset($data[$_GET['delete']]);
    }
    data_save('chron', json_encode($data));
    sync_end();
    redirect('dev');
    die();
}
die(tpl('dev', array('chron' => $data)));
