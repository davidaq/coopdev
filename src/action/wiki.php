<?php
$path = isset($_GET['p']) ? $_GET['p'] : '';
while(isset($path{0}) && $path{0} == '/') {
    $path = substr($path, 1);
}
$query = $path;
$path = preg_split('/\s*\/+\s*/', $path);
$title = $path[count($path) - 1];
if(!isset($title{0})) {
    $title = LANG('Wiki');
}
$data = array(
    'query' => $query,
    'title' => $title,
    'path' => $path,
    'index' => array(),
    'content' => data_read('wiki/' . implode('/', $path)),
    'isedit' => isset($_GET['edit'])
);
die(tpl('wiki', $data));
