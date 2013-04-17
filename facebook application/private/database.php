<?php
    require_once __DIR__.'/config.php';
	require_once __DIR__.'/facebookInit.php';
	require_once __DIR__.'/facebookDatabase.php';
    
    //BDD
    function getConnexion(){
            $bdd = new PDO('mysql:host='.DATABASE_HOST.';dbname='.DATABASE_TABLE.'', ''.DATABASE_USER.'', ''.DATABASE_PASS.'');
            $bdd->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); 
            $bdd->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND, "SET NAME'utf8'");
            
            return $bdd;
        }

    //clean data before insert in DB
    function cleanData ($data){
        $stripData = strip_tags($data);
        $trimData = trim($stripData);
        return $trimData;
    }
   
    //select sender id where request is request number 1 in the url
    function selectSenderIdFromRequest($requestId)
    {
        $bdd = getConnexion();
        $sth = $bdd->prepare('SELECT senderId FROM invitation WHERE requestId = '.$bdd->quote($requestId).'');
        $sth->execute();
        $result = $sth->fetch();
        return $result;
    }

    //test if the current user already played the game
    function testIfUserAsPlayed($user_id)
    {
        $bdd = getConnexion();
        $sth = $bdd->prepare("SELECT Id FROM participation WHERE facebookId = :user_id");
        $sth->bindParam(':user_id', $user_id);
        $sth->execute();
        $result = $sth->fetch();
        return $result;
    }
    
    //insert into DB, all the info about the request
    function insertRequestInDb($user_id, $requestId, $toId)
    {
        $bdd = getConnexion();
        $sth = $bdd->prepare('INSERT INTO invitation (requestId, toId, senderId) VALUES (:request, :item, :sender )');
        $sth->bindParam(':sender', cleanData($user_id));
        $sth->bindParam(':request', cleanData($requestId));
        
        foreach ($toId as $to){
            $sth->bindParam(':item', $to);
            $sth->execute();
        }
    }
    
    //insert into DB, the infos about the player : fb name, mail and user id
    function addParticipation($user_id, $facebook_name, $mail)
    {
        $bdd = getConnexion();
        $sth = $bdd->prepare('INSERT INTO participation (facebookId, facebookName, mail) VALUES (:id, :name, :mail )');
        $sth->bindParam(':id', cleanData($user_id));
        $sth->bindParam(':name', cleanData($facebook_name));
        $sth->bindParam(':mail', cleanData($mail));
        $sth->execute();
        return $bdd->lastInsertId();
    }
    
    //add points to sender when friend play from request
    function inserInClassement($senderId, $participationId) {
        $bdd = getConnexion();
        $sth = $bdd->prepare('INSERT INTO classement (fromUserId, participationId) VALUES (:from, :to)');
        $sth->bindParam(':from', cleanData($senderId));
        $sth->bindParam(':to', cleanData($participationId));
        $sth->execute();
    }
    
    //fetch name and points
    function fetchRanking ()
    {
        $bdd = getConnexion();
        $sth = $bdd->prepare('SELECT fromUserId AS facebookUserId, count(*) AS score, mail FROM classement GROUP BY fromUserId ORDER BY count(*) DESC');
        $sth->execute();
        $result = $sth->fetchAll();
        return $result;
    }
	
	function fetchRankingWithBonus ($facebook)
	{
		$scoreArray = array();
		$rank = fetchRanking ();
		foreach($rank as $row)
		{
			$fbid = $row['facebookUserId'];
			$score = $row['score'];
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
			$temp = array('name' => $name, 'score' => $score + $bonus);
			$scoreArray[] = $temp;
 		}
		return $scoreArray;
	}
    
    //automatic add in classement when add in participation
    function addParticipationInclassement($userId, $participationId, $mail)
    {
        $bdd = getConnexion();
        $sth = $bdd->prepare('INSERT INTO classement (fromUserId, participationId, mail) VALUES (:from, :to, :mail)');
        $sth->bindParam(':from', cleanData($userId));
        $sth->bindParam(':to', cleanData($participationId));
        $sth->bindParam(':mail', cleanData($mail));
        $sth->execute();
    }

    //add mail invitation in database
    function AddMailInvitationIn ($id, $name, $mail, $token)
    {
        $bdd = getConnexion();
        $sth = $bdd->prepare('INSERT INTO invitationmail (senderId, senderFbName, mail, token) VALUES (:id, :name, :mail, :token)');
        $sth->bindParam(':id', cleanData($id));
        $sth->bindParam(':name', cleanData($name));
        $sth->bindParam(':mail', cleanData($mail));
        $sth->bindParam(':token', cleanData($token));
        $sth->execute();
        return true;
    }
    
    //select sender from mail request
    function selectSenderIdFromMailRequest($token)
    {
        $bdd = getConnexion();
        $sth = $bdd->prepare('SELECT senderId FROM invitationmail WHERE token = "'.$token.'"');
        $sth->execute();
        $result = $sth->fetch();
		return $result;
    }