<?php
function last_post_id() {
    if(data_exists('status/id')) {
        $ret = data_read('status/id');
        $ret *= 1;
    } else {
        $ret = 1;
    }
    return $ret;
}
function post_status($content, $type='did') {
    if(!user())
        return;
    $data = array(
        'user' => user('id'),
        'date' => time(),
        'type' => $type,
        'content' => iescape($content),
    );
    $data = json_encode($data);
    sync_begin();
    $id = last_post_id() + 1;
    data_save("status/post_$id", $data);
    data_save('status/id', $id);
    sync_end();
}
function list_status($start=NULL, $limit=50) {
    if(!$start) $start = last_post_id();
    $id = last_post_id();
    $ret = array();
    while($limit > 0 && $id > 0) {
        if(data_exists("status/post_$id")) {
            $ret[] = json_decode(data_read("status/post_$id"), true);
        }
        $id--;
    }
    return $ret;
}
