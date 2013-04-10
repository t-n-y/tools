<?php
$data = array (
		'name' => '',
		'firstname' => '',
		'email' => '',
		'message' => '');
$errorMsg = array (
		'name' => '', 
		'FName' => '', 
		'mail' => '', 
		'message' => '');
$confirmMail='';
$to = "g.leclercq@iesanetwork.com";
$subject = "Message de mon portfolio";
$mailheaders = "From: Portfolio \n";
$mailheaders .= "Reply-To: ".$data['email'];
if (isset($_POST['contact']))
{
	$data=$_POST['contact'];
	$cleanData=array_map('strip_tags', $data);
	$error=false;
	if (empty($data['name']))
	{
		$error=true;
		$errorMsg['name']='Remplir le nom';
	}
	if (empty($data['firstname']))
	{
		$error=true;
		$errorMsg['FName']='Remplir le prénom';
	}
	if (empty($data['email']))
	{
		$error=true;
		$errorMsg['mail']='Remplir l\'email';
	}
	if (empty($data['message']))
	{
		$error=true;
		$errorMsg['message']='Ecrire un message';
	}
	if (!$error)
	{
		$msg="Message de ".$cleanData['firstname']." ".$cleanData['name']."\n Adresse e-mail : ".$cleanData['email']."\n Message :".$cleanData['message'];
		//mail($to, $subject, $msg);
		$confirmMail = '<h3>Message envoyé !</h3>';
	}
}

?>


<?php echo $confirmMail;?>
<FORM method="POST" action="index.php#fourth">
	<p><?php echo $errorMsg['name'];?></p><input type="text" name="contact[name]" value="<?php echo $data['name'];?>" placeholder="Entrez votre nom" onfocus="if (this.placeholder=='Entrez votre nom') this.placeholder='';" onBlur="if (this.placeholder=='') this.placeholder='Entrez votre nom';"> 
	<p><?php echo $errorMsg['FName'];?></p><input type="text" name="contact[firstname]" value="<?php echo $data['firstname'];?>" placeholder="Entrez votre prénom" onfocus="if (this.placeholder=='Entrez votre prénom') this.placeholder='';" onBlur="if (this.placeholder=='') this.placeholder='Entrez votre prénom';"> 
	<p><?php echo $errorMsg['mail'];?></p><input type="email" name="contact[email]" value="<?php  echo $data['email'];?>" placeholder="Votre.mail@exemple.com" onfocus="if (this.placeholder=='Votre.mail@exemple.com') this.placeholder='';" onBlur="if (this.placeholder=='') this.placeholder='Votre.mail@exemple.com';"> 
	<p><?php echo $errorMsg['message'];?></p><textarea rows="5" cols="30" name ="contact[message]"  placeholder="Entrez votre message ..." onfocus="if (this.placeholder=='Entrez votre message ...') this.placeholder='';" onBlur="if (this.placeholder=='') this.placeholder='Entrez votre message ...';"><?php echo $data['message'];?></textarea> 
	<input type="submit" name="validation" value="Valider" class="valid">
</FORM>