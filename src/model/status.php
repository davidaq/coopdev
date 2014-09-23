<?php
function last_post_id() {
    if(data_exists('status/id')) {
        $ret = data_read('status/id');
        $ret *= 1;
    } else {
        $ret = 0;
    }
    return $ret;
}
function remove_status($id, $checkuser=false) {
    $dataitem = "status/post_$id";
    if(!data_exists($dataitem)) return;
    if($checkuser) {
        if(!user()) return;
        $data = json_decode(data_read($dataitem), true);
        if($data['user'] !== user('id')) {
            return;
        }
    }
    data_remove($dataitem);
    sync_begin();
    $lastidO = last_post_id();
    $lastid = $lastidO;
    while($lastid > 0 && !data_exists("status/post_$lastid"))
        $lastid--;
    if($lastidO != $lastid) {
        data_save('status/id', $lastid);
    }
    sync_end();
}
function post_status($content, $type='did') {
    if(!user())
        return;
    $data = array(
        'user' => user('id'),
        'date' => time(),
        'type' => $type,
        'content' => iescape($content, true),
    );
    $data = json_encode($data);
    sync_begin();
    $id = last_post_id() + 1;
    data_save('status/id', $id);
    sync_end();
    data_save("status/post_$id", $data);
}
function list_status($start=NULL, $limit=50) {
    $last = last_post_id();
    if($limit > 0) {
        if(!$start) $start = $last + 1;
        $step = -1;
    } else {
        $limit = -$limit;
        if(!$start) $start = $last - $limit;
        if($start < 0) $start = 0;
        $n = $last - $start;
        if($limit > $n)
            $limit = $n;
        $step = 1;
    }
    $start += $step;
    $ret = array();
    $id = $start;
    while($limit > 0 && $id > 0 && $id <= $last) {
        if(data_exists("status/post_$id")) {
            $item = json_decode(data_read("status/post_$id"), true); 
            $item['id'] = $id;
            $item['type_lang'] = LANG($item['type']);
            $item['human_time'] = human_time($item['date']);
            $ret[] = $item;
            $limit--;
        }
        $id += $step;
    }
    if($step > 0)
        $ret = array_reverse($ret);
    return $ret;
}

