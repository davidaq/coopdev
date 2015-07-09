<?php
$data = array();
$data['list'] = array();
$list = &$data['list'];
foreach(data_list('user') as $k=>$v) {
    if(data_exists($k . '/info')) {
        $item = json_decode(data_read($k . '/info'), true);
        if(file_exists("data/user/$v/avatar.jpg")) {
            $item['avatar'] = BASE . "data/user/$v/avatar.jpg";
        } else {
            $item['avatar'] = BASE . 'res/images/default-avatar.jpg';
        }
        $item['o'] = iconv('utf-8', 'gbk', $item['name']);
        $list[] = $item;
    }
}
function sortFunc($a, $b) {
    return strcmp($a['o'], $b['o']);
}
usort($list, 'sortFunc');
die(tpl('team', $data));
