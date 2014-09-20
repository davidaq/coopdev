<?php
if(isset($_SESSION[USER_SESSION])) unset($_SESSION[USER_SESSION]);
redirect(BASE);
