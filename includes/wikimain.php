<?php

/*
 	ERFAN WIKI : a wiki with no database based on PrintWiki
 
    Authors: 
			Erfan Arabfakhri, Esfahan, Iran, <buttercupgreen@gmail.com>
			Amir Reza Rahbaran, Esfahan, Iran <amirrezarahbaran@gmail.com>
 
    Version:  0.1  (your constructive criticism is appreciated, please see our
    project page on http://sourceforge.net/projects/erfanwiki/
 
   Licence:  GNU General Public License

   This program is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.
 */

if ($masterID != "ERFANWIKI")
	{
	die("<big><big><big>ACCESS DENIED !");
	}

function erfanwiki_encode($mystring)
	{
	$mystring = base64_encode($mystring);
	$mystring = preg_replace("/\//","%x1", $mystring);
	return $mystring;
	}

function erfanwiki_decode($mystring)
	{
	$mystring = preg_replace("/%x1/","/", $mystring);
	$mystring = base64_decode($mystring);
	return $mystring;
	}
		
function doLogin($queryString, $showloginform) {
	global $lng;
	global $Config;
	global $displaypage;
	session_start();
	if ($_SESSION['logged-in'] != TRUE) {
		if ($_POST['login'] == "Login") {
			$filename = "./data/users/" . erfanwiki_encode($_POST['uid']);
			if (file_exists($filename) == ture)
				{
				$tempinfo = file($filename);
				$tempinfo = implode('',$tempinfo);
				$userinfo = explode(':',$tempinfo);
				if (md5($_POST['pwd']) == $userinfo[1] && $userinfo[5] == 1)
					{
					$_SESSION['logged-in'] = TRUE;
					$_SESSION['username'] = $_POST['uid'];
					$_SESSION['isadmin'] = FALSE;
					if ($userinfo[4] == 1)
						{
						$_SESSION['isadmin'] = TRUE;
						}
					} else {
						$_SESSION['logged-in'] = FALSE;
						}
				}
		if ($_SESSION['logged-in'] == FALSE)
			{
			putMessage("".
			"
			<p>
				".$lng['invalidpassword']."
			</p>
			<p>
				<ul class=button>
				<li class=button>
				<a href='".$_SERVER['PHP_SELF']."?$queryString' class='button'>
					".$lng['countinue']."
				</a>
				</li>
				</ul>
			</p>",
			$lng['messagecaption']);
			die();
			}
	  	} else {
			if ($showloginform == true ) {			
				putMessage("".
				"
				<form action='".$_SERVER['PHP_SELF']."?$queryString' method='post'>
				<table id=dialogtable >
					<tr>
						<td>
							".$lng['username']."
						</td>
						<td>
							<p><input type='text' name='uid'></p>
						</td>
					</tr>
					<tr>
						<td>
							".$lng['password']."
						</td>
						<td>
							<p><input type='password' name='pwd'></p>
						</td>
					</tr>
				</table>
				<br>
				<input type=hidden name=login value=Login>
				<input class=mybutton type=submit name=submit value='".$lng['login']."'>
				</form>",
				$lng['login']);
				}
	  	}
	}
} // end doLogin();

function doLogout($queryString) {
	global $lng;
	session_start();
	setcookie(session_name(), '', time()-42000, '/');
	session_destroy();
	putMessage("".
	"
	<p>
		".$lng['logoutsuccess']."
	</p>
	<p>
		<ul class=button>
		<li class=button>
		<a href='".$_SERVER['PHP_SELF']."?$queryString' class='button'>
			".$lng['countinue']."
		</a>
		</li>
		</ul>
	</p>",
	$lng['messagecaption']);
} // end doLogout()

function printWikiSyntax($title, $in) {
	global $lng;
	$align = "left";
	$mystyle = "style='margin-right: 30px;'";
	if ($lng['direction'] == "ltr")
		{
		$align = "right";
		$mystyle = "style='margin-left: 30px;'";
		}
	
        // Platform-independent newlines.
    $in = preg_replace("/(\r\n|\r)/", "\n", $in);
   		// Remove excess newlines.
    $in = preg_replace("/\n\n+/", "\n\n", $in);

		// Process nowiki marks
	preg_match('/\s<nowiki>(.*?)<\/nowiki>/s', $in, $matches);
	$in = preg_replace('/\s<nowiki>(.*?)<\/nowiki>/s','----',$in);
	$tempcontent = preg_replace('/<nowiki>|<\/nowiki>/', '', $matches[0]);

    	// Make paragraphs, including one at the end.
    $in = preg_replace('/\n?(.+?)(?:\n\s*\n|\z)/s', "<p>$1</p>\n", $in);
     	// Remove paragraphs if they contain only whitespace.
    $in = preg_replace('|<p>\s*?</p>|', '', $in);
		// Strong emphasis.
    $in = preg_replace("/'''(.*?)'''/", "<strong>$1</strong>", $in);
		// Emphasis.
    $in = preg_replace("/''(.*?)''/", "<em>$1</em>", $in);
		// Highlight text
    $in = preg_replace("/@@(.*?)@@/", "<span class=highlight>$1</span>", $in);    		
		// Internal Links
    $in = preg_replace("/\[\[(.*?)\]\]/", "<a href=\"".$_SERVER['PHP_SELF']."?title=$1\">$1</a>", $in);	
		// Image thumbnails with comments
    $in = preg_replace("/\[image:(.*?)\|(.*?)\]/", "<table id=image $mystyle align=".$align." border=1><tr><td><a class=imglink href='".$_SERVER['PHP_SELF']."?picture=$1&article=$title'><img border=1 src=./data/uploads/thumbs/$1></a><br>$2</td></tr></table>", $in);	
		// Image thumbnails
	$in = preg_replace("/\[image:(.*?)\]/", "<table id=image $mystyle align=".$align." border=1><tr><td><a class=imglink href='".$_SERVER['PHP_SELF']."?picture=$1&article=$title'><img border=1 src=./data/uploads/thumbs/$1></a></td></tr></table>", $in);
		// Media files with comment
    $in = preg_replace("/\[media:(.*?)\|(.*?)\]/", "<a href='./data/uploads/$1'>$2</a>", $in);	
		// Media files
	$in = preg_replace("/\[media:(.*?)\]/", "<a href='./data/uploads/$1'>$1</a>", $in);
		// External links
    $in = preg_replace("/\[(.*?)\]/", "<a href=\"$1\">$1</a>", $in);			
		// Horizontal lines
    $in = preg_replace("/==(.*?)==/", "<p style='text-indent: 0px'><strong>$1</strong><hr style='text-indent: 0px'></p>", $in);    	
		// Items
    $in = preg_replace("/\*(.*?)\n/", "<ul><li class=page>$1</li></ul>", $in);
		//Restore nowiki content
	$in = preg_replace('/----/',$tempcontent,$in);		
	return $in;
}

function smartsearch($keyword, $article) {
	global $lng;
	global $templateimagesdir;	
	global $displaypage;
	global $fnd_printtemplate;
	global $searchalign;

	//--- Reading filenames into array
	$handle=opendir("./data/pages");
	while ($file = readdir($handle))
	{
		if (!is_dir($file))
			{
			$myresult[]=$file;
			}
	}
	sort($myresult);
	closedir($handle);	
	
	//--- Searching array for keyword
	$isfound = false;
	$count = 0;
	$searchresult1 = "";
	foreach ($myresult as $myfiles) 
	{
		if (preg_match("/".$keyword."/i", erfanwiki_decode($myfiles)) == true)
		{
			$isfound = ture;
			$count = $count + 1;
			$searchresult1 = $searchresult1  . "<li style='list-style:none'>$count _ <a href='".$_SERVER['PHP_SELF']."?title=".erfanwiki_decode($myfiles)."'>".erfanwiki_decode($myfiles)."</a></li>";
			if ($count >= 20) break;
		}
	}
	if ($isfound == false)
		{
		$searchresult1 = "".
			"
			".$lng['noresult']."<br>
			".$lng['clicknewpage1']." <a href='".$_SERVER['PHP_SELF']."?title=".$keyword."'>$keyword</a>
			".$lng['clicknewpage2']." <a href='".$_SERVER['PHP_SELF']."?title=".$keyword."'>
			".$lng['clicknewpage3']."
			<br><br>
			";
		}

	$isfound = false;
	$count = 0;
	$searchresult2 = "";
	foreach ($myresult as $myfiles) 
	{
		$mycontent = implode("", file("./data/pages/".$myfiles));
		if (preg_match("/".$keyword."/i", $mycontent) == true)
		{
			$isfound = ture;
			$count = $count + 1;
			$searchresult2 = $searchresult2  . "<li style='list-style:none'>$count _ <a href='".$_SERVER['PHP_SELF']."?highlight=".$keyword."&title=".erfanwiki_decode($myfiles)."'>".erfanwiki_decode($myfiles)."</a></li>";
			if ($count >= 20) break;
		}
	}
	if ($isfound == false)
		{
		$searchresult2 = $lng['noresult'] . "<br><br>";
		}
	
	doLogin("title=$title",false);
	if ($_SESSION['logged-in'] == true) {
		$loginlogout = "<a class=tabs href='".$_SERVER['PHP_SELF']."?logout=1'>".$lng['tablogout']."</a>";
		if ($_SESSION['isadmin'] == true)
			{
			$adminpanel = "<li class=tabs><a class=tabs href='".$_SERVER['PHP_SELF']."?admin=1&article=$title'>".$lng['tabadmin']."</a></li>";
			}
		} else {
		$loginlogout = "<a class=tabs href='".$_SERVER['PHP_SELF']."?login=1'>".$lng['tablogin']."</a>";
		}
			
	$articleedit = "<a class=tabs href='".$_SERVER['PHP_SELF']."?title=$article'>".$lng['article']."</a>";
	
			print(getHtmlHead($lng['search'],"").
			"
			<body>
			<div align=center>
			<table id=mainbody border=0 cellpadding=0 cellspacing=0>
				<tr>
					<td>
					<ul class=tabs>
						<li class=tabs>
						<a class=tabs href='".$_SERVER['PHP_SELF']."?title=".$Config['homepage']."'>
						".$lng['home']."
						</a>
						</li>
						<li class=tabs>
						$articleedit
						</li>
						$adminpanel
						<li class=tabs>
						$loginlogout
						</li>
					</ul>
					</td>
				</tr>
				<tr>
					<td class=bodytop width=100% background=".$templateimagesdir."bodytop.jpg>
						<div align=$searchalign class=search>
						<form action='".$_SERVER['PHP_SELF']."' method='get'>
						<input type=submit class=mybutton value='".$lng['search']."'>
						<input class=searchedit name=search type=text>
						</form>
						</div>						
					</td>
				</tr>
				<tr>
					<td class=bodymiddle width=100% background=".$templateimagesdir."bodymiddle.jpg>
						<h1>".$lng['searchfor']." '$keyword' :</h1>
					</td>
				</tr>
				<tr>
					<td class=bodymiddle width=100% background=".$templateimagesdir."bodymiddle.jpg>
						<div id=page style='text-indent: 0px'>
						<p align=justify>
							<br>".$lng['searchintitle']."<hr><br>
							$searchresult1<br>
							<br>".$lng['searchintext']."<hr><br>
							$searchresult2 
						</p>
						</div>
					</td>
				</tr>
				<tr>
					<td class=bodybottom width=100% background=".$templateimagesdir."bodybottom.jpg>
						<p class=footer>
						".$lng['footer']."
						</p>
					</td>
				</tr>
			</table>
			</div>
			</body>
			</html>
			");
}

function editPage($title) {
	global $lng;
	global $templateimagesdir;
	doLogin("edit=$title",true);
	if ($_SESSION['logged-in'] == true) {
		$loginlogout = "<a class=tabs href='".$_SERVER['PHP_SELF']."?logout=1'>".$lng['tablogout']."</a>";
		if ($_SESSION['isadmin'] == true)
			{
			$adminpanel = "<li class=tabs><a class=tabs href='".$_SERVER['PHP_SELF']."?admin=1&article=$title'>".$lng['tabadmin']."</a></li>";
			}		
		} else {
		$loginlogout = "<a class=tabs href='".$_SERVER['PHP_SELF']."?login=1'>".$lng['tablogin']."</a>";
		}
	$fileupload = "<a class=tabs href='".$_SERVER['PHP_SELF']."?fileupload=1&article=$title'>".$lng['tabfile']."</a>";	
	$articleedit = "<a class=tabs href='".$_SERVER['PHP_SELF']."?title=$title'>".$lng['article']."</a>";
	
	if ($_SESSION['logged-in']) {
	  	if ($_POST['save']) {
			$datafile = erfanwiki_encode($title);
			$handle = fopen("./data/pages/$datafile",'w');
			fwrite($handle, stripslashes($_POST['page']));
			fclose($handle);
	  		putMessage("".
			"
			<p>
				".$lng['changessaved']."
			</p>
			<p>
				<ul class=button>
				<li class=button>
				<a href='".$_SERVER['PHP_SELF']."?title=$title' class='button'>
					".$lng['countinue']."
				</a>
				</li>
				</ul>
			</p>",
			$lng['messagecaption']);
	  	} else {
			$datafile =  erfanwiki_encode($title);		
			if (!$page = @file_get_contents("./data/pages/".$datafile)) {
				$page = $lng['newpagetext'];
			}
		
			print(getHtmlHead($title,"").
			"
			<body>
			<div align=center>
			<table id=mainbody border=0 cellpadding=0 cellspacing=0>
				<tr>
					<td>
					<ul class=tabs>
						<li class=tabs>
						<a class=tabs href='".$_SERVER['PHP_SELF']."?title=".$Config['homepage']."'>
						".$lng['home']."
						</a>
						</li>
						<li class=tabs>
						$articleedit
						</li>
						<li class=tabs>
						$fileupload
						</li>			
						$adminpanel
						<li class=tabs>
						$loginlogout
						</li>
					</ul>
					</td>
				</tr>
				<tr>
					<td class=bodytop width=100% background=".$templateimagesdir."bodytop.jpg>
						<br><h1>$title</h1>
					</td>
				</tr>
				<tr>
					<td class=bodymiddle width=100% background=".$templateimagesdir."bodymiddle.jpg>
						<div id=edit>
							<form action='".$_SERVER['PHP_SELF']."?edit=$title' method='post'>
							<p align=center>
								<textarea name='page' cols='80' rows='24'>$page</textarea>
								<input class=mybutton type='submit' name='save' value='".$lng['savechanges']."'>
							</p>
							</form>
						</div>
					</td>
				</tr>
				<tr>
					<td class=bodybottom width=100% background=".$templateimagesdir."bodybottom.jpg>
						<p class=footer>
						".$lng['footer']."
						</p>
					</td>
				</tr>
			</table>
			</div>
			</body>
			</html>
			");		
	  	}
	}
} // end editPage()

function createThumbnail($orgimg, $thumbnailwidth, $thumbnailquality) {
	$thumbpath = "./data/uploads/thumbs/{$orgimg}";
	$orgimg = "./data/uploads/{$orgimg}";
	$error = 0;
	if (function_exists('imagecreate') && function_exists('imagecopyresized')) {
		// Check if thumbnail directory exists. If not try to create it.
		if (!is_dir("./data/uploads/thumbs")) {
			$oldumask = umask(0);
			if (@!mkdir("./data/uploads/thumbs", 0777)) {
				$error = "Thumbnail directory could not be created.";
			}
			umask($oldumask);
		}
		// Get file size and file type.
		if ($error == 0) {
			if (!$size = @getimagesize($orgimg)) {
				$error = "Size of original image could not be calculated.";
			}
		}
		// Create link to old image.
		if ($error == 0) {
			switch ($size[2]) {
				case 1 :
					if (function_exists('imagecreatefromgif')) {
						$img = @imagecreatefromgif($orgimg);
						if ($img == "") {
							$error = "Could not open link to original image.";
						}
					} else {
						$error = "Could not open link to original image.";
					}
					break;
				case 2 :
					if (function_exists('imagecreatefromjpeg')) {
						$img = @imagecreatefromjpeg($orgimg);
						if ($img == "") {
							$error = "Could not open link to original image.";
						}
					} else {
						$error = "Could not open link to original image.";
					}
					break;
				case 3 :
					if (function_exists('imagecreatefrompng')) {
						$img = @imagecreatefrompng($orgimg);
						if ($img == "") {
							$error = "Could not open link to original image.";
						}
					} else {
						$error = "Could not open link to original image.";
					}
					break;
				default :
					$error = "Cannot create thumbnail. Original image is of an unsupported type.";
					break;
	        }
		}
		// Calculate the dimensions of the new image.
		if ($error == 0) {
			if (!strstr($thumbnailwidth, "%")) {
				if($size[0] > $size[1]) {
					$ratio = $size[0]/$thumbnailwidth;
					$height = $size[1]/$ratio;
					$height = round($height);
					$width = $size[0]/$ratio;
				} else {
					$ratio = $size[1]/$thumbnailwidth;
					$width = $size[0]/$ratio;
					$width = round($width);
					$height = $size[1]/$ratio;
				}
			} else {
				$ratio = str_replace("%", "", $thumbnailwidth)/100;
				$width = round($size[0]*$ratio);
				$height = round($size[1]*$ratio);
			}
		}
		// Create new image (true colour if available).
		if ($error == 0) {
			if (function_exists('imagecreatetruecolor')) {
				$newimg = imagecreatetruecolor($width, $height);
			} else {
				$newimg = imagecreate($width, $height);
			}
		}
		// Resample old image over new image.
		if ($error == 0) {
			if(!function_exists('imagecopyresampled') || !function_exists('imagecreatetruecolor')) {
				if (!@imagecopyresized($newimg, $img, 0, 0, 0, 0, $width, $height, $size[0], $size[1])) {
					$error = "Could not resize image.";
				}
			} else {
				if (!@imagecopyresampled($newimg, $img, 0, 0, 0, 0, $width, $height, $size[0], $size[1])) {
					$error = "Could not resample image.";
				}
			}
		}
		// Make the thumbnails, and save files.
		if ($error == 0) {
			switch ($size[2]) {
				case 1:
					if (!@imagegif($newimg, $thumbpath)) {
						$error = "Could not save thumbnail.";
					}
					break;
				case 2:
					if (!@imagejpeg($newimg, $thumbpath, $thumbnailquality)) {
						$error = "Could not save thumbnail.";
					}
					break;
				case 3:
					if (!@imagepng($newimg, $thumbpath)) {
						$error = "Could not save thumbnail.";
					}
					break;
				default :
					$error = "Could not create thumbnail. Image type not supported.";
			}
		}
		// Destroy image both links.
		@imagedestroy($newimg);
		@imagedestroy($img);
	} else {
		$error = "Image functions not available for thumbnail.";
	}
	return $error;
}

function fileupload($title) {
	global $lng;
	global $templateimagesdir;
	doLogin("fileupload=$title",true);
	if ($_SESSION['logged-in'] == true) {
		$loginlogout = "<a class=tabs href='".$_SERVER['PHP_SELF']."?logout=1'>".$lng['tablogout']."</a>";
		if ($_SESSION['isadmin'] == true)
			{
			$adminpanel = "<li class=tabs><a class=tabs href='".$_SERVER['PHP_SELF']."?admin=1&article=$title'>".$lng['tabadmin']."</a></li>";
			}
		} else {
		$loginlogout = "<a class=tabs href='".$_SERVER['PHP_SELF']."?login=1'>".$lng['tablogin']."</a>";
		}
	$editpage = "<a class=tabs href='".$_SERVER['PHP_SELF']."?edit=$title'>".$lng['edit']."</a>";	
	$articleedit = "<a class=tabs href='".$_SERVER['PHP_SELF']."?title=$title'>".$lng['article']."</a>";
	
	if ($_SESSION['logged-in']) 
		{
		if ($_GET['upload'] != '')
			{
			$uploaddir = './data/uploads/';
			$uploadfile = $uploaddir . $_FILES['userfile']['name'];
			$uploadfilerawname = $_FILES['userfile']['name'];
			$uploadinfo = $lng['uploadfailed'];
			if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) 
				{
				$uploadinfo = $lng['uploaddone'];
				if (preg_match('/\.jpg/i',$_FILES['userfile']['name']) || preg_match('/\.png/i',$_FILES['userfile']['name']) || preg_match('/\.gif/i',$_FILES['userfile']['name']))
					{
					createThumbnail($_FILES['userfile']['name'],150,80);
					}
				}
			putMessage("".
			"
			<p>
				".$uploadinfo."
			</p>
			<p>
				<ul class=button>
				<li class=button>
				<a href='".$_SERVER['PHP_SELF']."?edit=".$title."' class='button'>
					".$lng['countinue']."
				</a>
				</li>
				</ul>
			</p>",
			$lng['messagecaption']);
			die();
			}

			print(getHtmlHead($title,"").
			"
			<body>
			<div align=center>
			<table id=mainbody border=0 cellpadding=0 cellspacing=0>
				<tr>
					<td>
					<ul class=tabs>
						<li class=tabs>
						<a class=tabs href='".$_SERVER['PHP_SELF']."?title=".$Config['homepage']."'>
						".$lng['home']."
						</a>
						</li>
						<li class=tabs>
						$articleedit
						</li>
						<li class=tabs>
						$editpage
						</li>			
						$adminpanel
						<li class=tabs>
						$loginlogout
						</li>
					</ul>
					</td>
				</tr>
				<tr>
					<td class=bodytop width=100% background=".$templateimagesdir."bodytop.jpg>
						<br><h1>".$lng['uploadtitle']."</h1>
					</td>
				</tr>
				<tr>
					<td class=bodymiddle width=100% background=".$templateimagesdir."bodymiddle.jpg>
						<div id=page style='text-indent: 0px'>
							<p align=justify>
								".$lng['uploadcomment']."
								<form enctype=multipart/form-data action='".$_SERVER['PHP_SELF']."?fileupload=1&upload=1&article=".$title."' method=post>
								<input type=hidden name=MAX_FILE_SIZE value=4000000>
								<input class=upload name=userfile type=file><br>
								<input class=mybutton type=submit value='".$lng['uploadfile']."'>
								</form>								
							</p>
						</div>
					</td>
				</tr>
				<tr>
					<td class=bodybottom width=100% background=".$templateimagesdir."bodybottom.jpg>
						<p class=footer>
						".$lng['footer']."
						</p>
					</td>
				</tr>
			</table>
			</div>
			</body>
			</html>
			");		
	  	}
} // end upload file()

function displaypicture($picture,$title) {
	global $lng;
	global $Config;
	global $templateimagesdir;	
	global $searchalign;
	doLogin("title=$title",false);
	if ($_SESSION['logged-in'] == true) {
		$loginlogout = "<a class=tabs href='".$_SERVER['PHP_SELF']."?logout=1'>".$lng['tablogout']."</a>";
		if ($_SESSION['isadmin'] == true)
			{
			$adminpanel = "<li class=tabs><a class=tabs href='".$_SERVER['PHP_SELF']."?admin=1&article=$title'>".$lng['tabadmin']."</a></li>";
			}
		} else {
		$loginlogout = "<a class=tabs href='".$_SERVER['PHP_SELF']."?login=1'>".$lng['tablogin']."</a>";
		}
			
	$articleedit = "<a class=tabs href='".$_SERVER['PHP_SELF']."?title=$title'>".$lng['article']."</a>";
				
	$page = printWikiSyntax($title, $page);
	print(getHtmlHead($title,"").
	"
	<body>
	<div align=center>
	<table id=mainbody border=0 cellpadding=0 cellspacing=0>
		<tr>
			<td>
			<ul class=tabs>
				<li class=tabs>
				<a class=tabs href='".$_SERVER['PHP_SELF']."?title=".$Config['homepage']."'>
				".$lng['home']."
				</a>
				</li>
				<li class=tabs>
				$articleedit
				</li>
				$adminpanel
				<li class=tabs>
				$loginlogout
				</li>
			</ul>
			</td>
		</tr>
		<tr>
			<td class=bodytop width=100% background=".$templateimagesdir."bodytop.jpg>
				<div align=$searchalign class=search>
				<form action='".$_SERVER['PHP_SELF']."' method='get'>
				<input type=submit class=mybutton value='".$lng['search']."'>
				<input type=hidden name=article value='".$title."'>
				<input class=searchedit name=search type=text>
				</form>
				</div>
			</td>
		</tr>
		<tr>
			<td class=bodymiddle width=100% background=".$templateimagesdir."bodymiddle.jpg>
				<h1>".$lng['showpicture']."</h1>
			</td>
		</tr>
		<tr>
			<td class=bodymiddle width=100% background=".$templateimagesdir."bodymiddle.jpg>
				<div id=page>
					<p align=justify>
						".$lng['clickfororgsize']."
					</p>
					<p align=center>
						<a class=imglink target=_new href='./data/uploads/$picture'>
						<img src='./data/uploads/$picture' border=1 width=500px>
						</a>
					</p>
				</div>
			</td>
		</tr>
		<tr>
			<td class=bodybottom width=100% background=".$templateimagesdir."bodybottom.jpg>
				<p class=footer>
				".$lng['footer']."
				</p>
			</td>
		</tr>
	</table>
	</div>
	</body>
	</html>
	");
} // end displaypicture

function displayPage($title,$highlight) {
	global $lng;
	global $templateimagesdir;	
	global $fnd_printtemplate;
	global $searchalign;
	doLogin("title=$title",false);
	if ($_SESSION['logged-in'] == true) {
		$loginlogout = "<a class=tabs href='".$_SERVER['PHP_SELF']."?logout=1'>".$lng['tablogout']."</a>";
		if ($_SESSION['isadmin'] == true)
			{
			$adminpanel = "<li class=tabs><a class=tabs href='".$_SERVER['PHP_SELF']."?admin=1&article=$title'>".$lng['tabadmin']."</a></li>";
			}
		} else {
		$loginlogout = "<a class=tabs href='".$_SERVER['PHP_SELF']."?login=1'>".$lng['tablogin']."</a>";
		}
			
	if ($fnd_printtemplate == true) {
		$printpage = "<a target=_new class=tabs href='".$_SERVER['PHP_SELF']."?print=$title'>".$lng['print']."</a>";
		}
	
	$articleedit = "<a class=tabs href='".$_SERVER['PHP_SELF']."?edit=$title'>".$lng['edit']."</a>";
	
	$datafile =  erfanwiki_encode($title);
	if (!$page = @file_get_contents("./data/pages/".$datafile)) {
	putMessage("".
	"
	<p>
		".$lng['newpagemessage']."
	</p>
	<p>
		<ul class=button>
		<li class=button>
		<a href='".$_SERVER['PHP_SELF']."?edit=$title&referer=".$_GET['referer']."' class='button'>
			".$lng['yes']."
		</a>
		</li>
		<li class=button>
		<a href='".$_SERVER['PHP_SELF']."?title=".$_GET['referer']."' class='button'>
			".$lng['no']."
		</a>
		</li>
		</ul>
	</p>",
	$lng['messagecaption']);
				   
	} else {
		global $Config;
		if ($highlight != '')
			{
			$page = preg_replace("/".$highlight."/"," @@".$highlight."@@ ",$page);
			}
			
		$page = printWikiSyntax($title, $page);
		print(getHtmlHead($title,"").
		"
		<body>
		<div align=center>
		<table id=mainbody border=0 cellpadding=0 cellspacing=0>
			<tr>
				<td>
				<ul class=tabs>
					<li class=tabs>
					<a class=tabs href='".$_SERVER['PHP_SELF']."?title=".$Config['homepage']."'>
					".$lng['home']."
					</a>
					</li>
					<li class=tabs>
					$articleedit
					</li>
					<li class=tabs>
					$printpage						
					</li>
					$adminpanel
					<li class=tabs>
					$loginlogout
					</li>
				</ul>
				</td>
			</tr>
			<tr>
				<td class=bodytop width=100% background=".$templateimagesdir."bodytop.jpg>
					<div align=$searchalign class=search>
					<form action='".$_SERVER['PHP_SELF']."' method='get'>
					<input type=submit class=mybutton value='".$lng['search']."'>
					<input type=hidden name=article value='".$title."'>
					<input class=searchedit name=search type=text>
					</form>
					</div>
				</td>
			</tr>
			<tr>
				<td class=bodymiddle width=100% background=".$templateimagesdir."bodymiddle.jpg>
					<h1>$title</h1>
				</td>
			</tr>
			<tr>
				<td class=bodymiddle width=100% background=".$templateimagesdir."bodymiddle.jpg>
					<div id=page>
					<p align=justify>$page</p>
					</div>
				</td>
			</tr>
			<tr>
				<td class=bodybottom width=100% background=".$templateimagesdir."bodybottom.jpg>
					<p class=footer>
					".$lng['footer']."
					</p>
				</td>
			</tr>
		</table>
		</div>
		</body>
		</html>
		");
	}
}
 
function configError() {
	global $lng;
	if ($_GET['error']=='config-exists') {
		putMessage("".
		"		
		<p>
			".$lng['wikidead']."
		</p>
		<p>
			<ul class=button>
			<li class=button>
			<a class='button' href='".$_SERVER['PHP_SELF']."?install=stage1'>
				".$lng['reinstall']."
			</a>
			</li>
			</ul>
		</p>",
		$lng['messagecaption']);
	} else {
	putMessage("".
	"
	<p>
		".$lng['configerror']."
	</p>
	<p>
		<ul class=button>
		<li class=button>
		<a class='button' href='".$_SERVER['PHP_SELF']."?install=stage1'>
			".$lng['yes']."
		</a>
		</li>
		<li class=button>
		<a class='button' href='".$_SERVER['PHP_SELF']."?error=config-exists'>
			".$lng['no']."
		</a>
		</li>
		</ul>
	</p>",
	$lng['messagecaption']);
	}
}

/** Return string */
function getHtmlHead($title, $style) {
	global $lng;
	global $templatecssfile;
	global $searchalign;
	if ($lng['direction']=='rtl') { $tabsfloat="right"; $searchalign="left"; }
	if ($lng['direction']=='ltr') { $tabsfloat="left"; $searchalign="right"; }
	$head ="
	<?xml version=\"1.0\" encoding=\"UTF-8\"?>
	<html dir=".$lng['direction'].">
	<head>
		<title>$title</title>
		<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
		<link type=\"text/css\" rel=\"stylesheet\" href=\"$templatecssfile\" />
		<style type=\"text/css\">$style
		li.tabs {
			float: $tabsfloat;
		}
		</style>
	</head>";
	return $head;
}

function putMessage($msg, $caption) {
	global $templateimagesdir;
	print(getHtmlHead($caption,"").
	"
	<html>
	<body>
	<br><br><br><br>
	<br><br><br><br>
	<br>
	<div align=center>
		<table id=dialog border=0 cellpadding=0 cellspacing=0>
			<tr>
				<td class=dialogtop width=100% background=".$templateimagesdir."dialogtop.jpg>
					<br><h1>$caption</h1>
				</td>
			</tr>
			<tr>
				<td class=dialogmiddle width=100% background=".$templateimagesdir."dialogmiddle.jpg>
					<div id=page>
					<p align=justify>$msg</p>
					</div>
				</td>
			</tr>
			<tr>
				<td class=dialogbottom width=100% background=".$templateimagesdir."dialogbottom.jpg>
				</td>
				</tr>
		</table>
		</div>
		</body>
		</html>
	");	
}
?>