<?php 
	require_once __DIR__.'/private/config.php';
	require_once __DIR__.'/private/facebookInit.php';
	require_once __DIR__.'/private/facebookDatabase.php';
	require_once __DIR__.'/private/database.php';
	$requestUrl='';
	if (isset($_GET['request_ids']) )
	{
		//explode the requests and take the first one if multiple request were send
		$requestId = explode("," , $_GET['request_ids']);
		$_SESSION['request'] = $requestId[0];
	}
	if (isset($_GET['tok']) )
	{
		$token = $_GET['tok'];
		$_SESSION['requestMail'] = $token;
	}
?>
<script>
   window.top.location.href = '<?php echo MY_APP_URL;?>';
</script>
