<?php
/******************************************************************************/
/* Script to add TrueType or Type1 fonts to FPDF                              */
/*                                                                            */
/* author: Y. SUGERE                                                          */
/* version: 1.0                                                               */
/* date: 2003-04-28                                                           */
/* required files: addfont.php, addfontt1.php, addfontttf.php                 */
/* other necessary software: pfm2afm, ttf2pt1, fpdf                           */
/* For more information, see readme.txt                                       */
/*                                                                            */
/* This file processes Type1 fonts                                            */
/******************************************************************************/

require('makefont.php');

function EncodingList()
{
	// list all available encodings
	$d=dir('.');
	while($f=$d->read())
	{
		if(preg_match('/([a-z0-9_-]+)\\.map$/i',$f,$res))
			$enc[]=$res[1];
	}
	$d->close();
	sort($enc);
	echo '<SELECT NAME="enc">';
	foreach($enc as $e)
		printf('<OPTION %s>%s</OPTION>',$e=='cp1252' ? 'SELECTED': '',$e);
	echo '</SELECT>';
}

if(isset($HTTP_POST_FILES['pfb'])){
	// get font file
	$tmp=$HTTP_POST_FILES['pfb']['tmp_name'];
	$pfb=$HTTP_POST_FILES['pfb']['name'];
	$a=explode('.',$pfb);
	if(strtolower($a[1])!='pfb')
		die('File is not a .pfb');
	if(!move_uploaded_file($tmp,$pfb))
		die('Error in upload');
	$fontname=$HTTP_POST_VARS['fontname'];
	if(empty($fontname))
		$fontname=$a[0];
	// get font metric file
	$tmp=$HTTP_POST_FILES['fm']['tmp_name'];
	$fm=$HTTP_POST_FILES['fm']['name'];
	$a=explode('.',$fm);
	$fm_type=strtolower($a[1]);
	if($fm_type!='pfm' and $fm_type!='afm')
		die('File is not .pfm nor .afm');
	$fm="$fontname.$fm_type";
	if(!move_uploaded_file($tmp,$fm))
		die('Error in upload');
	if($fm_type=='pfm')
	{
		// PFM->AFM conversion
		system("pfm2afm.exe -a $fm $fontname.afm");
		unlink($fm);
		$fm="$fontname.afm";
	}
	// MakeFont call
	MakeFont($pfb,$fm,$HTTP_POST_VARS['enc']);
	copy("$fontname.php","../$fontname.php");
	unlink("$fontname.php");
	if(file_exists("$fontname.z"))
	{
		copy("$fontname.z","../$fontname.z");
		unlink("$fontname.z");
	}
	else
		copy($pfb,"../$pfb");
	unlink($fm);
	unlink($pfb);
	echo "<script language='javascript'>alert('Font processed');\n";
	echo "window.location.href='addfont.php';</script>";
}
?>
<!doctype html public "-//W3C//DTD HTML 4.0//EN">
<html>
<head>
	<title>Font upload</title>
</head>
<body>
<form action="addfontt1.php" method="post" enctype="multipart/form-data">
<table border="0" cellspacing="5" cellpadding="5" width="300">
	<tr>
		<th align="left" colspan="2">
			Choose the .pfb file:
		</th>
	</tr>
	<tr>
		<td align="left" colspan="2">
			<input type="file" name="pfb">
		</td>
	</tr>
	<tr>
		<th align="left" colspan="2">
			Choose the .pfm or .afm file:
		</th>
	</tr>
	<tr>
		<td align="left" colspan="2">
			<input type="file" name="fm">
		</td>
	</tr>
	<tr>
		<td align="left">
			Font name:
		</td>
		<td align="left">
			<input type="text" name="fontname">
		</td>
	</tr>
	<tr>
		<td align="left">
			Font encoding:
		</td>
		<td align="left">
			<?php EncodingList(); ?>
		</td>
	</tr>
	<tr>
		<td align="center">
			<input type="reset" name="btnSub" value="Clear">
		</td>
		<td align="center">
			<input type="submit" name="btnSub" value="Send">
		</td>
	</tr>
</table>
</form>
</body>
</html>
