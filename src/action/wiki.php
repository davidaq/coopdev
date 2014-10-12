<?php
$path = isset($_GET['p']) ? $_GET['p'] : '';
$path = preg_replace('/^(\/|\s)+|(\/|\s)+$/', '', $path);
while(isset($path{0}) && $path{0} == '/') {
    $path = substr($path, 1);
}
if(isset($path{0})) {
    $path = str_replace('.', '_', $path);
    $path = preg_split('/\s*\/+\s*/', $path);
    $qpath = $path;
    foreach($qpath as &$p) {
        $p = 'wiki_' . $p;
    }
    $query = implode('/', $qpath);
    $pquery = implode('/', $path);
} else {
    $path = array('');
    $query = '';
    $pquery = '';
}

/*****************
 * Save wiki page
 *****************/
if(isset($_GET['save']) && user('verified')) {
    data_save("wiki/$query/content", $_POST['content']);
    die();
}
/*******************
 * Delete wiki page
 *******************/
if(isset($_GET['delete']) && user('verified')) {
    data_remove("wiki/$query/content");
    data_remove("wiki/$query/attachments");
    foreach(scandir("data/wiki/$query") as $f) {
        if(substr($f, 0, 4) == 'att_') {
            $f = "data/wiki/$query/$f";
            if(!is_dir($f)) {
                unlink($f);
            }
        }
    }
    delEmptyTree("data/wiki/$query");
    redirect('/wiki?p=' . urlencode($pquery));
    die();
}
/********************
 * Delete attachment
 ********************/
if(isset($_GET['deleteatt']) && user('verified')) {
    $id = $_GET['deleteatt'];
    sync_begin();
    if(data_exists("wiki/$query/attachments")) {
        $odata = json_decode(data_read("wiki/$query/attachments"), true);
        if(isset($odata[$id])) {
            unset($odata[$id]);
            data_save("wiki/$query/attachments", json_encode($odata));
        }
    }
    sync_end();
    unlink("data/wiki/$query/att_$id");
    die();
}
/********************
 * Upload attachment
 ********************/
if(isset($_GET['upload']) && user('verified')) {
    $xdata = json_decode($_SERVER['HTTP_X_DATA'], true);
    $id = $_GET['upload'];
    sync_begin();
    if(data_exists("wiki/$query/attachments"))
        $odata = json_decode(data_read("wiki/$query/attachments"), true);
    else
        $odata = array();
    $odata[$id] = array(
        'name' => urldecode($xdata['name']),
        'size' => $xdata['size'],
    );
    data_save("wiki/$query/attachments", json_encode($odata));
    sync_end();
    file_put_contents("data/wiki/$query/att_$id", file_get_contents('php://input'));
    die();
}
/*****************
 * Get Attachment
 *****************/
if(isset($_GET['x'])) {
    $id = $_GET['x'];
    while($id{0} == '/')
        $id = substr($id, 1);
    $odata = json_decode(data_read("wiki/$query/attachments"), true);
    if(isset($odata[$id])) {
        $att = $odata[$id];
        $name = $att['name'];
        if(preg_match('/\.(png|jpe?g|gif|bmp)/i', $name)) {
            header('Content-type: image/png');
        } else {
            header('Content-type: application/zip');
            header('Content-disposition: attachment; filename=' . $name);
        }
    }
    readfile("data/wiki/$query/att_$id");
    die();
}
/*****************
 * Normal display
 *****************/
if(isset($_GET['edit']) && !user('verified')) {
    unset($_GET['edit']);
}
$title = $path[count($path) - 1];
if(!isset($title{0})) {
    $title = LANG('Wiki');
}
$childs = data_list("wiki/$query", 'wiki_');
foreach($childs as $k=>&$v) {
    $v = array(
        'path' => substr(str_replace('/wiki_', '/', $k), 5),
        'name' => $v,
        'child' => array(),
        'active' => false
    );
}
if(isset($qpath)) {
    if(isset($qpath[1])) {
        unset($qpath[count($qpath) - 1]);
        $parent = implode('/', $qpath);
    } else {
        $parent = '';
    }
    $index = data_list("wiki/$parent", 'wiki_');
    foreach($index as $k=>&$v) {
        $v = array(
            'path' => substr(str_replace('/wiki_', '/', $k), 5),
            'name' => $v,
            'child' => array(),
            'active' => false
        );
        if($v['path'] == $pquery) {
            $v['child'] = $childs;
            $v['active'] = true;
        }
    }
    if(isset($parent{0})) {
        $name = substr($qpath[count($qpath) - 1], 5);
        $index = array(array(
            'path' => substr(str_replace('/wiki_', '/', $parent), 5),
            'name' => $name,
            'child' => $index,
            'active' => false
        ));
    }
} else {
    $index = $childs;
}
$content = data_read("wiki/$query/content");
if(!user('verified'))
    $content = preg_replace('/\{\{\{.*?\}\}\}/s', '<span class="verified-only">' . LANG('Visible to verified user only') . '</span>', $content);
elseif(!isset($_GET['edit']))
    $content = preg_replace('/\{\{\{(.*?)\}\}\}/s', '$1', $content);
$data = array(
    'query' => $pquery,
    'title' => $title,
    'path' => $path,
    'index' => $index,
    'content' => $content,
    'isedit' => isset($_GET['edit']),
    'attachments' => json_decode(data_read("wiki/$query/attachments"), true)
);
die(tpl('wiki', $data));
