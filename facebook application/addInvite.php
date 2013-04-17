<?php
    require_once __DIR__.'/private/config.php';
    require_once __DIR__.'/private/database.php';
    require_once __DIR__.'/private/facebookInit.php';
    //get those info by the ajax request in invite.php
    $requestId = $_POST['request_id'];
    $toId = $_POST['to'];
    //get user info
    $user_id = $facebook->getUser();
    try {
        // Insert request info in database
        insertRequestInDb($user_id, $requestId, $toId);
    } 
	catch(PDOException $e) {
        echo $e->getMessage();
    }
    //return response to invite.php
    $data = array (
        'success' => true,
    );
    echo json_encode($data);
