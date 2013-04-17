<?php
    require_once __DIR__.'/private/config.php';
    require_once __DIR__.'/private/facebookDatabase.php';
    require_once __DIR__.'/private/database.php';
    require_once __DIR__.'/private/facebookInit.php';
    
    $MailValue = '';
    $user_id = $facebook->getUser();
    $idParticipation = '';
    $redirect = '';
    $facebook_id = $user_id;
    $userData = useFbApi ($facebook,$user_id);
    $name = $userData['name'];
    
    if (isset($_SESSION['request']))
    {
        //Get the request and give some points to the sender
        $req = $_SESSION['request'];
        $senderFromReq = selectSenderIdFromRequest($req);
        if ($senderFromReq !== false)
        {
            $idParticipation = $_SESSION['idParticipation'];
            inserInClassement($senderFromReq['senderId'], $idParticipation);
            unset($_SESSION['request']);
        }
    }
    if (isset($_SESSION['requestMail']))
    {
        //Get the mail request and give some points to the sender
        $req = $_SESSION['requestMail'];
        $senderFromReq = selectSenderIdFromMailRequest($req);
        if ($senderFromReq !== false)
        {
            $idParticipation = $_SESSION['idParticipation'];
            inserInClassement($senderFromReq['senderId'], $idParticipation);
            unset($_SESSION['requestMail']);
        }
    }
    //Get all the player, sort by score desc
    $rankBonus = fetchRankingWithBonus($facebook);
    
    if (isset($_POST['user_email']))
    {
        
        $facebook_id = $_POST['facebook_id'];
        $mail = htmlspecialchars($_POST['user_email']);
        // validate email
        $validMail = filter_var($mail, FILTER_VALIDATE_EMAIL);
        $validFilterMail = filter_var($validMail, FILTER_SANITIZE_EMAIL);
        //verify if mail is valid
        if ($validFilterMail == $mail)
        {
            // unique token
            $tok = md5(uniqid(rand(), true));
            //add request infos in database
            $addMail = AddMailInvitationIn ($user_id, $name, $validMail, $tok);

            if ($addMail == true)
            {
                //send mail
                $to      = $validMail;
                $subject = 'Invitation de la part de '.$name;
				$message = '
					<html>
						<head>
							<title>Concours zelieux</title>
						</head>
						<body>
							<table>
								<tr>
									<td>Bonjour, viens jouer et tente de gagner un cadeau !</td>
								</tr>
								<tr>
									<td>Clique sur ce lien pour jouer :</td>
								</tr>
								<tr>
									<td><a href="https://apps.facebook.com/appzelieux?tok='.$tok.'">https://apps.facebook.com/appzelieux?tok='.$tok.'</a></td>
								</tr>
							</table>
						</body>
					</html>
					';
                $headers  = 'MIME-Version: 1.0' . "\r\n";
				$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
				$headers .= 'From: zelieux.com <no-reply@zelieux.com>';

                mail($to, $subject, $message, $headers);
            }
            
        }
        else
        {
            $MailValue = $mail;
            $errorMsg = 'Entrez une adresse e-mail valide';
        }
    } 
?>
<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="https://www.facebook.com/2008/fbml">
  <head>
      <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
      <link type="text/css" rel="stylesheet" href="css/style.css">
  </head>
  <body>
    <div id="fb-root"></div>
    <script src="https://connect.facebook.net/fr_FR/all.js"></script>
    <!-- Invite facebook friend button, made in css in style.css -->
    <p>
    <input type="button"
      onclick="sendRequestViaMultiFriendSelector(); return false;"
      value="Inviter mes amis"/>
    </p>
	<script>
      FB.init({
        appId  : '<?php echo MY_APP_ID;?>',
        frictionlessRequests: true
      });

      function sendRequestViaMultiFriendSelector() {
        FB.ui({method: 'apprequests',
          message: 'Concours Zelieux'
        }, requestCallback);
      }
      
      function requestCallback(response) {
          // Handle callback here
          console.log(response);
          $.ajax({
              type: "POST",
              url: "addInvite.php",
              dataType: "JSON",
              data: {
                  request_id: response.request, to : response.to},
                  cache: false,
          }).done(function( inviteResponse ) {
              //addInvite.php give a response back
              if (inviteResponse.success == true ){
            	  document.getElementById('shadowing').style.display='block';
            	  document.getElementById('box').style.display='block';
              }
          });
      }
    </script>
    <p>classement</p>
    <table border="0" cellpadding="0" cellspacing="0">
    <?php foreach($rankBonus as $row): ?>
    <tr>
        <td><?php echo $row['name']['name'];?></td>
        <td><?php echo $row['score'];?></td>
    </tr>
    <?php endforeach; ?>
    </table>
	<!-- pop in after invite facebook friend -->
    <div id="shadowing"></div>
    <div id="box">
    	<div id="boxheader">
           <span id="boxclose" onclick="document.getElementById('box').style.display='none'; document.getElementById('shadowing').style.display='none'"> </span>
    	</div>
    	<div id="boxcontent">
            <p>Merci de votre participation</p> 
    	</div>
    </div>
    <!-- invite friend by mail button -->
    <input type="button" onclick='sendMail(); return false;' value="inviter vos amis par mail"/>
	<!-- pop in to send mail -->
	<div id="shadowingMail"></div>
    <div id="boxMail">
    	<div id="boxheaderMail">
           <span id="boxcloseMail" onclick="document.getElementById('boxMail').style.display='none'; document.getElementById('shadowingMail').style.display='none'"> </span>
    	</div>
    	<div id="boxcontentMail">
            <form action="invite.php" method="POST">
                <label for="inputMail">Adresse e-mail :</label>
                <input type="email" name="user_email" id="inputMail" value="<?php echo $MailValue;?>">
                <input type="hidden" name="facebook_id" value="<?php echo $facebook_id;?>">
                <input type="submit" value="valider">
            </form> 
    	</div>
    </div>
    <script>
    function sendMail()
    {
    	document.getElementById('shadowingMail').style.display='block';
        document.getElementById('boxMail').style.display='block';
    }
    </script>
	<!-- Post to wall button -->
    <input type="button" onclick='postToFeed(); return false;' value="Publier sur mon mur"/>
    <p id='msg'></p>
    <script> 
      function postToFeed() {
        // calling the API ...
        var obj = {
          method: 'feed',
          redirect_uri: '<?php echo MY_APP_URL;?>',
          link: '<?php echo MY_APP_URL;?>',
          picture: '<?php echo FEED_PICTURE;?>',
          name: '<?php echo FEED_NAME;?>',
          caption: '<?php echo FEED_CAPTION;?>',
          description: '<?php echo FEED_DESCRIPTION;?>'
        };
        function callback(response) {
          document.getElementById('msg').innerHTML = "Post ID: " + response['post_id'];
        }
        FB.ui(obj, callback);
      }
    </script>
  </body>
</html>