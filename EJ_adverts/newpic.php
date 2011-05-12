<?php

/* File Build 0.2*/

if (!isset($_FILES['imagefind']) or empty($_FILES['imagefind']['name'])) {
	if (isset($_POST['save'])) {
		$message="ERROR: Upload error or no picture selected.";
	}
?>
<script src="EJ_adverts.js" language="javascript" type="text/javascript"></script>
<form name="imageform" id="imageform" method="post" enctype="multipart/form-data" action="newpic.php?adid=<?=$_REQUEST['adid']?>" style="font-size:12px;">
	<p><strong>Select a picture  to add</strong><br/>
	<div id="message" style="margin:0; padding:0;"><?=$message?></div>
		<input type="file" name="imagefind" id="imagefind"/>
		<br/>
		<input type="button" name="save" id="save" value="Add Picture" style="width:100%;" onclick="sendimage('<?=$_REQUEST['adid']?>')"/>
	</p>
</form>
<?php
} else {
	require('simpleimage.inc');
	$target_path = "images/{$_REQUEST['adid']}/";
	if (!is_dir(dirname(__FILE__)."/".$target_path))
		mkdir(dirname(__FILE__)."/".$target_path,0777,true);
	$target_path = $target_path . $_REQUEST['adid']."_".substr(time(),-4).substr($_FILES['imagefind']['name'],-4);
	if(move_uploaded_file($_FILES['imagefind']['tmp_name'], $target_path)) {
		print("<script src=\"EJ_adverts.js\" language=\"javascript\" type=\"text/javascript\"></script>");
		$image = new SimpleImage();
		$image->load($target_path);
		$width = $image->getWidth();
		echo basename( '<p>'.$_FILES['imagefind']['name']) . " has been uploaded!".'</p><input type="button" name="return" id="return" value="Return to Image Selection" onclick="document.location=\'changepic.php?id='.$_REQUEST['adid'].'\'"/>';
	} else{
		echo "There was an error uploading the file, please <a href=\"newpic.php?adid={$_REQUEST['adid']}\">try again<//a>!";
	}
}
?>