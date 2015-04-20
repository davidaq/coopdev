<?php
if(isset($_POST['ticket']) || isset($_POST['tag'])) {
    if(!user()) {
        header('location: login');
        die();
    }
    sync_begin();
    $ticketID = isset($_POST['id']) ? 1 * $_POST['id'] : time() . rand(10,99);
    $dataF = 'ticket/t_' . $ticketID;
    if(data_exists($dataF)) {
        $data = json_decode(data_read(), true);
    } else {
        $data = array(
            'user'    => user('id'),
            'type'    => $_POST['type'],
            'tag'     => array(),
            'status'  => 'pending',
            'content' => array()
        );
    }
    if(isset($_POST['ticket'])) {
        $data['content'][] = array(
            'user' => user('id'),
            'time' => time(),
            'text' => $_POST['ticket']
        );
    }
    if(isset($_POST['tag'])) {
        $data['tag'] = array();
        foreach(preg_split('/\s+/', $_POST['tag']) as $t) {
            if(!$t) 
                continue;
            $data['tag'][] = $t;
            data_write('ticket/tag/' . base64_encode($t), '');
        }
    }
    data_save($dataF, json_encode($data));
    sync_end();
    header('location: ticket');
    die();
}
if(isset($_GET['list'])) {
    $list = data_list('ticket', 't_');
    $list = array_reverse($list);
    $max = $_GET['list'] * 1;
    if(!$max) {
        $max = 0x7ffffffffffff;
    }
    $c = 10;
    $ret = array();
    foreach($list as $k=>&$v) {
        if($v * 1 > $max)
            continue;
        $ret[] = json_decode(data_read($k), true);
        $c++;
        if($c > 20) {
            break;
        }
    }
    die(json_encode($ret));
}
$tags = data_list('ticket/tag');
foreach($tags as &$v) {
    $v = base64_decode($v);
}
die(tpl('ticket', array('tags' => $tags)));
