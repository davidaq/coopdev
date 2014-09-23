<?php
if(!user())
    redirect('/login');
die(tpl('settings'));
