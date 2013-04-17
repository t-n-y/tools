<?php
	require_once 'private/config.php';
	require_once 'phar://imagine.phar';
	use Imagine\Image\Box;
	use Imagine\Image\Point;
	//db connect
	function getConnexion(){
		$db = new PDO('mysql:host='.DB_HOST.';dbname='.DB_BASE.'', ''.DB_USER.'', ''.DB_PASS.'');
		return $db;
	}

	//check if text field is empty. if not, clean data
	function checkEmptyField($postText)
	{
		if (empty($postText)){
			$emptyName = true;
			$responseMsg = "Renseignez un nom de fichier";
			$cleanName = '';
		}
		else {
			$emptyName = false;
			$responseMsg = false;
			$cleanName = strip_tags($postText);
		}
		
		return array(
				'empty' => $emptyName,
				'response' => $responseMsg,
				'cleanData' => $cleanName);
	}

	//check all possible error
	function switchError($error)
	{
		switch ($error)
		{
			case UPLOAD_ERR_OK :
				$erreur = false;
				$responseMsg = "Pas d'erreur";
				break;
			case UPLOAD_ERR_INI_SIZE:
				$erreur = true;
				$responseMsg = 'Le fichier excède la taille supportée';
				break;
			case UPLOAD_ERR_PARTIAL:
				$erreur = true;
				$responseMsg = "Le fichier n'a été que partiellement téléchargé";
				break;
			case UPLOAD_ERR_NO_FILE:
				$erreur = true;
				$responseMsg = "Aucun fichier n'a été téléchargé";
				break;
			case UPLOAD_ERR_NO_TMP_DIR:
				$erreur = true;
				$responseMsg = "Un dossier temporaire est manquant";
				break;
			case UPLOAD_ERR_CANT_WRITE:
				$erreur = true;
				$responseMsg = "Échec de l'écriture du fichier sur le disque";
				break;
			case UPLOAD_ERR_EXTENSION:
				$erreur = true;
				$responseMsg = "Une extension PHP a arrêté l'envoi de fichier";
				break;
		}
		return array(
				'msg' => $responseMsg,
				'error' => $erreur);
	}

	//check if file exist, if not add name in db and upload picture in directory
	function checkIfFileExist($cleanName, $ext)
	{
		$tok = sha1(uniqid(rand(), true));
		if(file_exists('moveinto/'.$tok.'.'.$ext))
		{
			$response = 'le fichier existe deja';
			$nameValue= $cleanName;
		}
		else
		{	
			$db = getConnexion();
			$sth = $db->prepare('INSERT INTO uploadpics (hash, ext, name) VALUES (:hash, :ext, :name)');
			$sth->bindParam(':hash', $tok);
			$sth->bindParam(':ext', $ext);
			$sth->bindParam(':name', $cleanName);
			$sth->execute();
			move_uploaded_file($_FILES['myFile']['tmp_name'], PATH_IMG.$tok.'.'.$ext);
			
			$imagine = new Imagine\Gd\Imagine();
			$size = new Imagine\Image\Box(150,100);
			$image = $imagine->open(PATH_IMG.$tok.'.'.$ext)->thumbnail($size, 'inset')->save(PATH_IMG.'mini_'.$tok.'.'.$ext);
			
			$response = 'le fichier a bien été uploadé';
			$nameValue = false;
		}
		
		return array(
				'response' => $response,
				'value' => $nameValue);
	}
	
	//select pics to display
	function selectImage()
	{
		$db = getConnexion();
		$sth = $db->prepare('SELECT * FROM uploadpics');
		$sth->execute();
		$result = $sth->fetchAll();
		return $result;
	}
