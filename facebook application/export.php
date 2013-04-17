<?php
    header('Content-Type: text/csv;');
    header('Content-Disposition: attachement; filename="Classement concours facebook.csv"');
    require_once __DIR__.'/private/config.php';
    require_once __DIR__.'/private/facebookDatabase.php';
    require_once __DIR__.'/private/database.php';
    require_once __DIR__.'/private/facebookInit.php';
    
    $rank = fetchRanking();
    ?>"Nom";"Adresse email";"Score"<?php 
    foreach($rank as $row)
    {
        $fbid = $row['facebookUserId'];
        $score = $row['score'];
        $mail = $row['mail'];
        $name = useFbApi ($facebook,$fbid);
        if ($score > 1)
        {
            $bonus=1;
        }
        elseif ($score > 3)
        {
            $bonus=4;
        }
        else
        {
            $bonus=0;
        }
        $total = $score+$bonus;
        $fbName = $name['name'];
        echo "\n".'"'.utf8_decode($fbName).'";"'.$mail.'";"'.$total.'"';
    }
?>
