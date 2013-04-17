<?php 
    require_once __DIR__.'/private/config.php';
    require_once __DIR__.'/private/facebookInit.php';
    require_once __DIR__.'/private/facebookDatabase.php';
    require_once __DIR__.'/private/database.php';

$resultSender = false;
$sender_profile = false;
$redirect = false;
$testliked = false;
$reveal = false;
$resultPlay = true;
$MailValue = false;
$errorMsg = false;
$requestUrl='';
$idParticipation = '';
//get user info
$user_id = $facebook->getUser();
//test if session request exist
if (isset($_SESSION['request']) )
	{
		$requestId = $_SESSION['request'];
		//Get the user who sent the request
		$resultSender = selectSenderIdFromRequest($requestId);
		if($resultSender !== false)
		{
			// Get facebook info from sender user
			$sender_profile = useFbApi ($facebook,$resultSender['senderId']);
		}
	}
if (isset($_SESSION['requestMail']) )
	{
		$token = $_SESSION['requestMail'];
		//Get the user who sent the mail
		$resultSender = selectSenderIdFromMailRequest($token);
		if($resultSender !== false)
		{
			// Get facebook info from sender user
			$sender_profile = useFbApi ($facebook,$resultSender['senderId']);
		}
	}
function parsePageSignedRequest() {
    if (isset($_REQUEST['signed_request'])) 
		{
			$encoded_sig = null;
			$payload = null;
			list($encoded_sig, $payload) = explode('.', $_REQUEST['signed_request'], 2);
			$sig = base64_decode(strtr($encoded_sig, '-_', '+/'));
			$data = json_decode(base64_decode(strtr($payload, '-_', '+/'), true));
			return $data;
		}
    return false;
}
// Test if current user like the page or not
if($signed_request = parsePageSignedRequest()) {
    $testliked = $signed_request->page->liked;
    if($signed_request->page->liked) {
       // echo "like ok";
        $_SESSION['hasLike']= true;
    } else {
       // echo "like pas ok";
        $_SESSION['hasLike']= false;
    }
}
if ($testliked == true || (isset($_SESSION['hasLike']) && $_SESSION['hasLike'] == true))
{
    //if user has accepted the application
    if ($user_id)
    {
        //Check if user has already played the game
        $resultPlay = testIfUserAsPlayed($user_id);
        //If the user haven't played yet, get his facebook info
        if($resultPlay == false)
        {
            $user_profile = $facebook->api('/me','GET');
            $facebook_name = $user_profile['name'];
            $MailValue = $user_profile['email'];
        }
        //else delete the request session to avoid multi insert un database 
        //when user come another time by cliking on the request, then redirect on invite page
        else{
            unset($_SESSION['request']);
            unset($_SESSION['requestMail']);
            header('location: invite.php');
            exit;
        }
        if (isset($_POST['facebook_name']))
        {
            $facebook_name = $_POST['facebook_name'];
            $mail = htmlspecialchars($_POST['user_email']);
            // validate email
            $validMail = filter_var($mail, FILTER_VALIDATE_EMAIL);
            $validMail = filter_var($validMail, FILTER_SANITIZE_EMAIL);
            //verify if mail is valid
            if ($validMail == $mail)
            {
                //insert gamer info in database then redirect on invite page
                $idParticipation = addParticipation($user_id, $facebook_name, $mail);
                $_SESSION['idParticipation'] = $idParticipation;
                addParticipationInclassement($user_id, $idParticipation, $validMail);
                header('location: invite.php');
                exit;
            }
            else
            {
                $MailValue = $mail;
                $errorMsg = 'Entrez une adresse e-mail valide';
            }
        }
    }
    //redirect user to accept the application
    else 
    {
        $params = array(
                'scope'=>'email',
                'redirect_uri' => MY_APP_URL
        );
        $loginUrl = $facebook->getLoginUrl($params);
        $redirect = '<script>top.location.href="'.$loginUrl.'";</script>';
    }   
}
?>
<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://ogp.me/ns/fb#">
<head>
  <meta charset="utf-8">
   <link type="text/css" rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php echo $redirect;?>
    <div id="fb-root"></div>
    <script>
    //initialise sdk
      window.fbAsyncInit = function() {
        // init the FB JS SDK
        FB.init({
          appId      : '<?php echo MY_APP_ID;?>', // App ID from the App Dashboard
          status     : true, // check the login status upon init?
          cookie     : true, // set sessions cookies to allow your server to access the session?
          xfbml      : true  // parse XFBML tags on this page?
        });
      };
      // Load the SDK's source Asynchronously
      // Note that the debug version is being actively developed and might 
      // contain some type checks that are overly strict. 
      // Please report such bugs using the bugs tool.
      (function(d, debug){
         var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
         if (d.getElementById(id)) {return;}
         js = d.createElement('script'); js.id = id; js.async = true;
         js.src = "//connect.facebook.net/fr_FR/all" + (debug ? "/debug" : "") + ".js";
         ref.parentNode.insertBefore(js, ref);
       }(document, /*debug*/ false));
    </script>
    <?php if ($testliked == true):?>
        
    <?php else:?>    
        <p>Vous devez aimer la page pour jouer</p>
    <?php endif;?>
    <?php if ($resultPlay == false):?>
        <form action="index.php" method="POST">
        <label for="inputMail">Adresse e-mail :</label>
        <input type="email" name="user_email" id="inputMail" value="<?php echo $MailValue;?>">
        <input type="hidden" name="facebook_name" value="<?php echo $facebook_name;?>">
        <input type="submit" value="valider">
    </form>
    <?php echo $errorMsg;?>
    <?php endif;?>
    <?php if ($resultSender !== false): ?>
        <img src="https://graph.facebook.com/<?php echo $resultSender['senderId'];?>/picture">
        <p><?php echo $sender_profile['name'];?> vous invite à jouer</p>
    <?php endif; ?>
</body>
</html>