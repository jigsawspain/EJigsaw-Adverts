<?php
session_start();
if ($_SESSION['key'] != $_POST['key'] or $_POST['key']=="")
{
	echo "<p class=\"EJ_user_error\"><strong>AUTHORISATION ERROR</strong>: Unable to verify key!</p>";
} else
{
	$EJ_initPage ='ajax';
	require('../../init.inc.php');
	$query = "UPDATE {$EJ_mysql->prefix}module_EJ_adverts SET EJ_advertTitle='".urldecode($_POST['title'])."', EJ_advertTag='".urldecode($_POST['tag'])."', EJ_advertText='".str_replace(array("\n", "<br>", "<br/>"),"<br />",(urldecode($_POST['desc'])))."', EJ_advertCat = ".$_POST['cat'].", EJ_advertImages='".$_POST['image']."', EJ_advertLoc = '".$_POST['locs']."', EJ_advertAttributes = '".$_POST['atts']."', EJ_advertAddress1 = '".$_POST['address1']."', EJ_advertAddress2 = '".$_POST['address2']."', EJ_advertAddress3 = '".$_POST['address3']."', EJ_advertAddress4 = '".$_POST['address4']."', EJ_advertAddress5 = '".$_POST['address5']."', EJ_advertPhone = '".$_POST['phone']."', EJ_advertWebsite = '".$_POST['website']."', EJ_advertContact = '".$_POST['contact']."' WHERE EJ_advertId = ".$_POST['id']."";
	$EJ_mysql->query($query);
	echo "OK";
}
?>