<?php
if(user() && user('verified') && posted('content')) {
    data_save('announcement', $_POST['content']);
}
