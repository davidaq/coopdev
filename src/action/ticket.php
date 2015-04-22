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
        $data = json_decode(data_read($dataF), true);
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
        if(isset($_POST['status'])) {
            $data['status'] = $_POST['status'];
        }
    }
    if(isset($_POST['tag'])) {
        $data['tag'] = array();
        foreach(preg_split('/\s+/', $_POST['tag']) as $t) {
            if(!$t) continue;
            $t = strtolower($t);
            $data['tag'][] = $t;
            data_save('ticket/tag/' . base64_encode($t), '');
        }
    }
    data_save($dataF, json_encode($data));
    sync_end();
    header('location: ticket?id=' . $ticketID);
    die();
}
if(isset($_GET['list'])) {
    $list = data_list('ticket', 't_');
    $list = array_reverse($list);
    $max = $_GET['list'] * 1;
    if(!$max) {
        $max = 0x7ffffffffffff;
    }
    $c = 0;
    $ret = array();
    foreach($list as $k=>&$v) {
        $v *= 1;
        if($v >= $max)
            continue;
        $item = json_decode(data_read($k), true);
        $item['content']= $item['content'][0];
        $item['id'] = $v;
        $ret[] = $item;
        $c++;
        if($c >= 10) {
            break;
        }
    }
    die(json_encode($ret));
} elseif(isset($_POST['rmtag'])) {
    data_remove('ticket/tag/' . base64_encode($_POST['rmtag']));
    die();
} elseif(isset($_GET['id'])) {
    if(!data_exists('ticket/t_' . $_GET['id'])) {
        die(tpl('404', array('hehe'=>'123')));
    }
    $tags = data_list('ticket/tag');
    foreach($tags as &$v) {
        $v = base64_decode($v);
    }
    $item = json_decode(data_read('ticket/t_' . $_GET['id']), true);
    $item['id'] = $_GET['id'];
    die(tpl('show-ticket', array('tags' => $tags, 'ticket'=> $item)));
} else {
    $tags = data_list('ticket/tag');
    foreach($tags as &$v) {
        $v = base64_decode($v);
    }
    die(tpl('ticket', array('tags' => $tags)));
}

