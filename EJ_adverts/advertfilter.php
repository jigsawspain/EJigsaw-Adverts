<?php 
session_start();
if ($_POST['key'] != $_SESSION['key'] or $_POST['key']=="")
{
	echo "<p class=\"EJ_user_error\"><strong>AUTHORISATION ERROR</strong>: Unable to verify key!</p>";
	echo "<p>{$_SESSION['key']}::{$_POST['key']}</p>";
} else
{
	$EJ_initPage ='ajax';
	require('../../init.inc.php');
	$locfind = "SELECT locName FROM {$EJ_mysql->prefix}module_EJ_adverts_locs WHERE locID = SUBSTRING_INDEX(SUBSTR(EJ_advertLoc,2),')',1)";
	$query="SELECT SQL_CALC_FOUND_ROWS *, (SELECT catName FROM {$EJ_mysql->prefix}module_EJ_adverts_cats WHERE catId = EJ_advertCat) as catName, ($locfind) as locName FROM {$EJ_mysql->prefix}module_EJ_adverts WHERE EJ_advertHidden = 0";
	if (!empty($_POST['text']))
	{
		$query .= " AND (EJ_advertTitle LIKE '%{$_POST['text']}%' OR EJ_advertText LIKE '%{$_POST['text']}%')";
	}
	if (!empty($_POST['cat']))
	{
		$query .= " AND EJ_advertCat = ".$_POST['cat'];
	}
	if (!empty($_POST['attributes']))
	{
		$advertatts = explode(":",$_POST['attributes']);
		$skip = 1;
		$query .= " AND (";
		$i=0;
		foreach ($advertatts as $att)
		{
			if ($i != 0)
				$query .= " OR ";
			else
				$i=1;
			$query .= "EJ_advertAttributes LIKE '%($att)%'";
		}
		$query .= ")";
	}
	if (!empty($_POST['locations']))
	{
		$advertlocs = explode(":",$_POST['locations']);
		$skip = 1;
		$query .= " AND (";
		$i=0;
		foreach ($advertlocs as $loc)
		{
			if ($i != 0)
				$query .= " OR ";
			else
				$i=1;
			$query .= "EJ_advertLoc LIKE '%($loc)%'";
		}
		$query .= ")";
	}
	$query .= " ORDER BY ";
	switch ($_POST['order'])
	{
		case 'TitleA':
			$query .= "EJ_advertTitle ASC";
		break;
		case 'TitleD':
			$query .= "EJ_advertTitle DESC";
		break;
		default:
			$query .= "EJ_advertTitle ASC";
		break;
	}
	/*
	if (!empty($_POST['limit']))
	{
		$startlimit = (($_POST['page']-1)*$_POST['limit']);
		$limit = $_POST['limit'];
	} else
	{
		$startlimit = 0;
		$limit = 10;
	}
	*/
	$limit = 10;
	$startlimit = (($_POST['page']-1)*$limit);
	$query .= " LIMIT ".$startlimit.",".$limit;
	$EJ_mysql->query($query);
	if ($EJ_mysql->numRows() == 0)
		{
			$content .= '<p style="text-align: center;"><strong>No Adverts Found!<br/>';
			if ($_POST['page']!=1)
			{
				$content .= '</strong><br/>Try page 1 by clicking above</p>';
			}
			else
			{
				$content .= 'Please try a broader search filter.</strong></p>';
			}
		} else
		{
			while($advert = $EJ_mysql->getRow())
			{
				if (strrpos($advert['EJ_advertLoc'],"(")!=0)
				{
					$advert['locName'] = "Multiple Locations";
				}
				if (!empty($advert['EJ_advertImages']) and file_exists(dirname(__FILE__)."/images/".$advert['EJ_advertId']."/".$advert['EJ_advertImages']))
				{
					$image = "<img class=\"EJ_advertResult_img\" src=\"{$EJ_settings['instloc']}modules/EJ_adverts/image.php/{$advert['EJ_advertImages']}?image={$EJ_settings['instloc']}modules/EJ_adverts/images/{$advert['EJ_advertId']}/{$advert['EJ_advertImages']}&amp;height=100&amp;width=100\" alt=\"{$EJ_advertTitle}\"/>";
				} else
				{
					$image = "<img class=\"EJ_advertResult_img\" src=\"{$EJ_settings['instloc']}modules/EJ_adverts/image.php/noimage.png?image={$EJ_settings['instloc']}modules/EJ_adverts/images/noimage.png&amp;height=100&amp;width=100\" alt=\"{$advert['EJ_advertTitle']}\"/>";
				}
				$content .= "<div class=\"EJ_advertResult\" id=\"{$advert['EJ_advertId']}\"><div class=\"EJ_advertResult_header\"><a href=\"?module=EJ_adverts&action=show_advert&adId={$advert['EJ_advertId']}\">{$advert['EJ_advertTitle']}</a></div><div class=\"EJ_advertResult_left\"><a href=\"?module=EJ_adverts&action=show_advert&adId={$advert['EJ_advertId']}\">$image</a>".substr($advert['EJ_advertText'],0,150)."... <a href=\"?module=EJ_adverts&action=show_advert&adId={$advert['EJ_advertId']}\">more</a></div><div class=\"EJ_advertResult_right\">{$advert['locName']}<br/>{$advert['catName']}<br/>{$advert['EJ_advert']}</div><div style=\"clear: left;\"></div></div>";
			}
		}
	$EJ_mysql->query("SELECT FOUND_ROWS() as results");
	$rows = $EJ_mysql->getRow();
	for ($i=1; $i<=ceil($rows['results']/$limit); $i++)
	{
		if ($_POST['page'] == $i)
		{
			$selected = "<strong>";
			$endselected = "</strong> | ";
		}
		else
		{
			$selected = "<a href=\"javascript: setPage($i,'{$_SESSION['key']}','{$EJ_settings['instloc']}')\">";
			$endselected = "</a> | ";
		}
		$pages .= $selected.$i.$endselected;
	}
	$pages = substr($pages,0,-3);
	if ($pages=="")
			$pages .= " <strong>1</strong>";
	$pages .= "</div>";
	$pages1 = "<div id=\"pages\">Page ".$pages;
	$pages2 = "<div id=\"pages\" style=\"margin-top: 0;\">Page ".$pages;
	/*
	$sorts = array('TitleD'=>'Title (Z-A)', 'PosterA'=>'Posted By (A-Z)', 'PosterD'=>'Posted By (Z-A)', 'ExpiryA'=>'Expiring Soonest', 'ExpiryD'=>'Expiring Latest');
	$sort = "<div style=\"float:left;\"><strong>Sort By:</strong> <select name=\"order\" id=\"order\" onchange=\"updateFilter('{$_SESSION['key']}')\">";
	$sort .= "<option value=\"TitleA\" selected=\"selected\">Title (A-Z)</option>";
	foreach ($sorts as $sortitem => $sortname)
	{
		if ($_POST['order']==$sortitem)
			$selected=" selected=\"selected\"";
		else
			$selected="";
		$sort .= "<option value=\"$sortitem\"$selected>$sortname</option>";
	}
	$sort .= "</select></div><div style=\"clear:both;\"></div></div>";
	*/
	$content = $pages1.$sort.$content.$pages2.":::".$rows['results'];
	echo $content;
}
?>