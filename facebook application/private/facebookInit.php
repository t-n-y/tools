<?php
    require_once __DIR__.'/libs/facebook.php';
    require_once __DIR__.'/config.php';

    $config = array();
    $config['appId'] =  MY_APP_ID;
    $config['secret'] = MY_APP_SECRET;
    
    //facebook object
    $facebook = new Facebook($config);
    
    