<?php 
	require_once 'private/functions.php';
	require_once 'phar://imagine.phar';
	use Imagine\Image\Box;
	use Imagine\Image\Point;
	$dir = './moveinto';
	$nameValue='';
	$responseMsg = array(
			'error' => '',
			'up' => '',
			'empty' => '');
	
	if (isset($_POST['submit']))
	{
		$img = $_FILES['myFile'];
		$postText = $_POST['myText'];
		
		//check if text field is empty 
		$checkEmptyField = checkEmptyField($postText);
		$emptyName = $checkEmptyField['empty'];
		$responseMsg['empty'] = $checkEmptyField['response'];
		$cleanName = $checkEmptyField['cleanData'];
		
		//check errors in upload
		$imgError = $img['error'];
		$responseError = switchError($imgError);
		$responseMsg['error'] = $responseError['msg'];
		$error = $responseError['error'];
		
		if ($error == false && $emptyName == false)
		{
			//check file mime
			$ext = pathinfo($img['name'], PATHINFO_EXTENSION);
			$finfo = new finfo(FILEINFO_MIME);
			$filetype = $_FILES['myFile']['tmp_name'];
			$fileInfo = $finfo->file($filetype);

			switch ($fileInfo){
				case "image/jpeg; charset=binary":
				case "image/png; charset=binary":
				case "image/gif; charset=binary":
					
					//check if file exist. If not, upload it
					$ifFileExist = checkIfFileExist($cleanName, $ext);
					$nameValue = $ifFileExist['value'];
					$responseMsg['up'] = $ifFileExist['response'];
					break;
				default:
					$responseMsg['up'] = 'le format du fichier est incorrect';
					$nameValue= $cleanName;
			}
		}
		elseif ($error == true && $emptyName == false)
		{
			$nameValue= $cleanName;
		}
	}
	$selectImg = selectImage();
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<title>Upload</title>
</head>
<body>
	<p><?php echo $responseMsg['error'];?></p>
	<p><?php echo $responseMsg['up'];?></p>
	<p><?php echo $responseMsg['empty'];?></p>
	<form action="up.php" method="post" enctype="multipart/form-data">
		<input type="file" name="myFile"/>
		<input type="text" name="myText" value="<?php echo $nameValue;?>"/>
		<input type="submit" name="submit" value="ok"/>
	</form>
	
	<?php foreach ($selectImg as $row): ?>
		<p>titre de l'image : <?php echo $row['name'];?></p>
		<img src="<?php echo PATH_IMG.$row['hash'].'.'.$row['ext']; ?>" alt="<?php echo $row['name'];?>" width="200"/>
		<p>miniature :</p>
		<img src="<?php echo PATH_IMG.'mini_'.$row['hash'].'.'.$row['ext']; ?>" alt="<?php echo $row['name'];?>" />
	<?php endforeach;?>	
</body>
</html>
