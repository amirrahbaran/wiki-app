<?php

/*
 	WIKI Application : a wiki with no database based on PrintWiki
 
    Authors: Amir Reza Rahbaran, Esfahan, Iran <amirrezarahbaran@gmail.com>
 
    Version:  0.1  (your constructive criticism is appreciated, please see our
 
   Licence:  GNU General Public License

   This program is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.
 */

if ($masterID != "WIKIAPP")
	{
	die("<big><big><big>ACCESS DENIED !");
	}
	
function adminpanel($page,$article) {
	global $lng;
	global $Config;
	global $templateimagesdir;
	$articleedit = "<a class=tabs href='".$_SERVER['PHP_SELF']."?title=$article'>".$lng['article']."</a>";
	$loginlogout = "<a class=tabs href='".$_SERVER['PHP_SELF']."?login=1'>".$lng['tablogin']."</a>";
	doLogin("edit=$title",true);
	if ($_SESSION['logged-in']) 
		{
		$loginlogout = "<a class=tabs href='".$_SERVER['PHP_SELF']."?logout=1'>".$lng['tablogout']."</a>";
		$tabtemplate = "<li class=tabs><a class=tabs href='".$_SERVER['PHP_SELF']."?admin=1&article=$article'>".$lng['tabtemplate']."</a></li>";
		$tablanguage = "<li class=tabs><a class=tabs href='".$_SERVER['PHP_SELF']."?admin=2&article=$article'>".$lng['tablanguage']."</a></li>";
		$tabbackup = "<li class=tabs><a class=tabs href='".$_SERVER['PHP_SELF']."?admin=3&article=$article'>".$lng['tabbackup']."</a></li>";
		$tabusers = "<li class=tabs><a class=tabs href='".$_SERVER['PHP_SELF']."?admin=4&article=$article'>".$lng['tabusers']."</a></li>";
									
		if ($_POST['save'])
			{
			if ($configHandle = fopen("./data/config.php",'w')) 
				{				
				$cfgtemp="";
				$cfglang="";
				if ($_POST["template"] != "")	{ $cfgtemp="\$Config[\"template\"]    = \"".$_POST["template"]."\";\n"; }
				if ($_POST["language"] != "")	{ $cfglang="\$Config[\"language\"]    = \"".$_POST["language"]."\";\n"; }				
				$configData = ("<?php\n".
				"\$Config[\"homepage\"] = \"".$Config["homepage"]."\";\n".
				"\$Config[\"username\"] = \"".$Config["username"]."\";\n".
				"\$Config[\"password\"] = \"".$Config["password"]."\";\n".
				"\$Config[\"email\"]    = \"".$Config["homepage"]."\";\n".
				$cfgtemp . $cfglang .
				"?>");
				$article=$_POST["article"];
				fwrite($configHandle, $configData);
				fclose($configHandle);
				putMessage("".
				"
				<p>
					".$lng['changessaved']."
				</p>
				<p>
					<ul class=button>
					<li class=button>
					<a class=button href='".$_SERVER['PHP_SELF']."?admin=".$_GET['admin']."'>
						".$lng['countinue']."
					</a>
					</li>
					</ul>
				</p>",
				$lng['messagecaption']);				
				die();
				}
			}
					
		// Template
		if ($page == "1")
			{
			//--- Reading filenames into array  ( List of  templates )
			$handle=opendir("./templates");
			$list_ignore = array ('.','..','print');
			while ($file = readdir($handle))
			{
				if (!in_array($file,$list_ignore))
					{
					$myresult2[]=$file;
					}
			}
			if ($myresult2) { sort($myresult2); }
			closedir($handle);	
			$templist = "";
			if ($myresult2) 
				{
				foreach ($myresult2 as $myfiles)
					{
					if ($myfiles == $Config['template'])
						{
						$templist = $templist  . "<option selected>" . $myfiles . "</option>";
						} else {
						$templist = $templist  . "<option>" . $myfiles . "</option>";
						}
					}
				}
			
			$smalltitle = " (<small><small>".$lng['tabtemplate']."<big><big>) ";
			$pagecontent = "".
				"
				<form action='".$_SERVER['PHP_SELF']."?admin=1' method='post'>
				".$lng['selectdeftemp']."
				<br><br>						
				<table id=dialogtable border=0>
					<tr>
						<td width=20%>
							".$lng['selecttemp']."
						</td>
						<td width=40%>
							<select name='template'>
							$templist
							</select>
						</td>
					</tr>
				</table>
				<input type=hidden name=language value='".$Config['language']."'>
				<br>
				<input class=mybutton type='submit' name='save' value='".$lng['savechanges']."'>
				<br>
				<input type=hidden name='article' value='".$article."'>
				</form>
				";
			}
		
		// Language
		if ($page == "2")
			{
			//--- Reading filenames into array  ( List of languages )
			$handle=opendir("./languages");
			while ($file = readdir($handle))
			{
				if (!is_dir($file) && $file!='default.php')
					{
					$temp = preg_replace("/.php/i","",$file);
					$myresult1[]=$temp;
					}
			}
			if ($myresult1) { sort($myresult1); }
			closedir($handle);		
			$langlist = "";
			if ($myresult1)
				{
				foreach ($myresult1 as $myfiles)
					{
					if ($myfiles == $Config['language'])
						{
						$langlist = $langlist  . "<option selected>" . $myfiles . "</option>";
						} else {
						$langlist = $langlist  . "<option>" . $myfiles . "</option>";
						}
					}
				}

			$smalltitle = " (<small><small>".$lng['tablanguage']."<big><big>) ";
			$pagecontent = "".
				"
				<form action='".$_SERVER['PHP_SELF']."?admin=2' method='post'>
				".$lng['selectdeflang']."
				<br><br>
				<table id=dialogtable border=0>
					<tr>
						<td width=20%>
							".$lng['selectlang']."
						</td>
						<td width=40%>
							<select name='language'>
							$langlist
							</select>
						</td>
					</tr>
				</table>
				<input type=hidden name=template value='".$Config['template']."'>
				<br>
				<input class=mybutton type='submit' name='save' value='".$lng['savechanges']."'>
				<br>
				<input type=hidden name='article' value='".$article."'>
				</form>						
				";
			}
			
		// Backup
		if ($page == "3")
			{
			// Process backup uploads
			if ($_GET['upload'] != '')
				{
				$uploaddir = './data/backups/';
				$uploadfile = $uploaddir . $_FILES['userfile']['name'];
				$uploadfilerawname = $_FILES['userfile']['name'];
				$uploadinfo = $lng['uploadfailed'];
				if (preg_match('/\.zip/i',$_FILES['userfile']['name']) == true)
					{
					if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) 
						{ 
						$uploadinfo = $lng['uploaddone'];
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
					<a href='".$_SERVER['PHP_SELF']."?admin=3&article=".$article."' class='button'>
						".$lng['countinue']."
					</a>
					</li>
					</ul>
				</p>",
				$lng['messagecaption']);
				die();
				}

			//--- Delete selected backup file 
			if ($_GET['op'] == 'del')
				{
				if ($_GET['confirm'] != "1")
					{
					putMessage("".
					"
					<p>
						".$lng['deleteconfirm']."
					</p>
					<p>
						<ul class=button>
						<li class=button>
						<a class=button href='".$_SERVER['PHP_SELF']."?admin=3&file=".$_GET['file']."&op=del&confirm=1&article=$article'>
							".$lng['yes']."
						</a>
						</li>
						<li class=button>
						<a class=button href='".$_SERVER['PHP_SELF']."?admin=3&article=$article'>
							".$lng['no']."
						</a>
						</li>
						</ul>
					</p>",
					$lng['messagecaption']);
					die();
					} else {
						$filename = "./data/backups/" . $_GET['file'];
						if (file_exists($filename) == ture)
							{
							unlink($filename);
							}
					}
				}
				
			//--- Restore selected backup file 
			if ($_GET['op'] == 'restore')
				{
				if ($_GET['confirm'] != "1")
					{
					putMessage("".
					"
					<p>
						".$lng['restoreconfirm']."
					</p>
					<p>
						<ul class=button>
						<li class=button>
						<a class=button href='".$_SERVER['PHP_SELF']."?admin=3&file=".$_GET['file']."&op=restore&confirm=1&article=$article'>
							".$lng['yes']."
						</a>
						</li>
						<li class=button>
						<a class=button href='".$_SERVER['PHP_SELF']."?admin=3&article=$article'>
							".$lng['no']."
						</a>
						</li>
						</ul>
					</p>",
					$lng['messagecaption']);
					die();
					} else {
						$filename = "./data/backups/" . $_GET['file'];
						if (file_exists($filename) == ture && file_exists('./includes/pclzip.lib.php') == true)
							{
							require_once ("./includes/pclzip.lib.php");
							$archive = new PclZip($filename);
							$archive->extract(PCLZIP_OPT_PATH, './',PCLZIP_OPT_REMOVE_PATH, './',PCLZIP_OPT_REPLACE_NEWER);
							}
					}
				}
				
			//--- Reading filenames into array  ( List of  Backups )
			$handle=opendir("./data/backups");
			while ($file = readdir($handle))
			{
				if (!is_dir($file))
					{
					$myresult3[]=$file;
					}
			}
			if ($myresult3) { sort($myresult3); }
			closedir($handle);	
			$backuplist = "";
			if ($myresult3)
				{
				foreach ($myresult3 as $myfiles)
					{
					$backuplist = $backuplist .
					"
					<tr>
						<td align=center width=30%>
							<span dir=ltr>
							<a href='./data/backups/" . $myfiles . "'>".$myfiles."</a>
							</span>
						</td>
						<td align=center width=30%>
							<a href='./data/backups/" . $myfiles . "'>".$lng['download']."</a> 
							<a href='".$_SERVER['PHP_SELF']."?admin=3&file=$myfiles&op=del&article=$article'>".$lng['delete']."</a> 
							<a href='".$_SERVER['PHP_SELF']."?admin=3&file=$myfiles&op=restore&article=$article'>".$lng['restore']."</a> 
						</td>
					</tr>
					";
					}
				}
			
			$backupexistmsg = "";
			$smalltitle = " (<small><small>".$lng['tabbackup']."<big><big>) ";
			if (file_exists('./includes/pclzip.lib.php') == true)
				{
				require_once ("./includes/pclzip.lib.php");
				$backuplink = date("Y-m-d") . ".zip";
				$p_archive = "./data/backups/" . date("Y-m-d") . ".zip";
				if (file_exists($p_archive) == true)
					{
					if ($_GET['newbackup'] != '1')
						{
						$backupexistmsg = "".
							"
							".$lng['backupexists']."
							<a href='".$_SERVER['PHP_SELF']."?admin=3&file=$myfiles&newbackup=1&article=$article'>
							".$lng['clickhere']."
							<br>
							";
						} else {
							unlink($p_archive); 
							$v_zip = new PclZip($p_archive);
							$v_list=$v_zip->add('./data/pages',PCLZIP_OPT_REMOVE_PATH, 'pages');
							$v_list=$v_zip->add('./data/uploads',PCLZIP_OPT_REMOVE_PATH, 'uploads');
							$v_list=$v_zip->add('./data/users',PCLZIP_OPT_REMOVE_PATH, 'users');
							$backupexistmsg = "".
								"
								".$lng['newbackupcreated']."
								<br>
								";
							}
					} else {
						$v_zip = new PclZip($p_archive);
						$v_list=$v_zip->add('./data/pages',PCLZIP_OPT_REMOVE_PATH, 'pages');
						$v_list=$v_zip->add('./data/uploads',PCLZIP_OPT_REMOVE_PATH, 'uploads');
						$v_list=$v_zip->add('./data/users',PCLZIP_OPT_REMOVE_PATH, 'users');
						}
				
				$pagecontent = "".
					"
					".$lng['backupfilelist']."
					<br>
					$backupexistmsg
					<br>
					<table id=installtable border=1>
						<tr>
							<td align=center width=30%>
								<strong>".$lng['backupfile']."</strong>
							</td>
							<td align=center width=30%>
								<strong>".$lng['options']."</strong>
							</td>
						</tr>
						".$backuplist."
					</table>							
					<br>
						".$lng['uploadmsg']."
					<br>
					<form enctype=multipart/form-data action='".$_SERVER['PHP_SELF']."?admin=3&upload=1&article=".$article."' method=post>
					<input type=hidden name=MAX_FILE_SIZE value=4000000>
					<input class=upload name=userfile type=file><br>
					<input class=mybutton type=submit value='".$lng['uploadfile']."'>
					</form>
					";
				} else {
					$pagecontent = $lng['nopclzip'];
				}
			}
			
		// Users
		if ($page == "4")
			{
			// Add user
			if ($_POST['adduser'] != '')
				{
				if ($_POST['username'] == '' || preg_match('/\W/',$_POST['username']) == true || strlen($_POST['username']) <=2)
					{
					putMessage("".
					"
					<p>
						".$lng['invalidusername']."
					</p>
					<p>
						<ul class=button>
						<li class=button>
						<a class=button href='".$_SERVER['PHP_SELF']."?admin=4&showadduserform=".$_GET['showadduserform']."&article=$article'>
							".$lng['countinue']."
						</a>
						</li>
						</ul>
					</p>",
					$lng['messagecaption']);
					die();
					}
				if (file_exists('./data/users/' . wikiapp_encode($_POST['username'])) == ture)
					{
					$isfound = true;
					putMessage("".
					"
					<p>
						".$lng['usernameexists']."
					</p>
					<p>
						<ul class=button>
						<li class=button>
						<a class=button href='".$_SERVER['PHP_SELF']."?admin=4&showadduserform=".$_GET['showadduserform']."&article=$article'>
							".$lng['countinue']."
						</a>
						</li>
						</ul>
					</p>",
					$lng['messagecaption']);
					die();
					} else {
						$userdata = $_POST['username'] . ":" . md5($_POST['password']) . ":" . $_POST['realname'] . ":" . $_POST['email'] . ":" . $_POST['usertype'] . ":" . $_POST['active'];
						$datafile = wikiapp_encode($_POST['username']);
						$handle = fopen('./data/users/'.$datafile,'w');
						fwrite($handle,$userdata);
						fclose($handle);
						putMessage("".
						"
						<p>
							".$lng['usercreated']."
						</p>
						<p>
							<ul class=button>
							<li class=button>
							<a class=button href='".$_SERVER['PHP_SELF']."?admin=4&showadduserform=".$_GET['showadduserform']."&article=$article'>
								".$lng['countinue']."
							</a>
							</li>
							</ul>
						</p>",
						$lng['messagecaption']);
						die();
						}
				}

			// Edit user
			if ($_POST['edituser'] != '')
				{
				if ($_POST['username'] != $_GET['username'])
					{
					putMessage("".
					"
					<p>
						".$lng['canteditusername']."
					</p>
					<p>
						<ul class=button>
						<li class=button>
						<a class=button href='".$_SERVER['PHP_SELF']."?admin=4&showedituserform=1&username=".$_GET['username']."&article=$article'>
							".$lng['countinue']."
						</a>
						</li>
						</ul>
					</p>",
					$lng['messagecaption']);
					die();
					}
				$filename = "./data/users/" . wikiapp_encode($_GET['username']);
				if (file_exists($filename) == ture)
					{
					$tempinfo = file($filename);
					$tempinfo = implode('',$tempinfo);	
					$userinfo = explode(':',$tempinfo);
					if ($_POST['password'] != '******')
						{
						$userinfo[1]=md5($_POST['password']);
						}
					$userinfo[2]=$_POST['realname'];
					$userinfo[3]=$_POST['email'];
					
					if ($_SESSION['username'] != $_GET['username'])
						{
						$message = $lng['useredited'];
						$userinfo[4]=$_POST['usertype'];
						$userinfo[5]=$_POST['active'];
						} else { $message = $lng['usereditedself'];	}
					
					$userdata = $userinfo[0] . ":" . $userinfo[1] . ":" . $userinfo[2] . ":" . $userinfo[3] . ":" . $userinfo[4] . ":" . $userinfo[5];
					$datafile = wikiapp_encode($_GET['username']);
					$handle = fopen('./data/users/'.$datafile,'w');
					fwrite($handle,$userdata);
					fclose($handle);							
					putMessage("".
					"
					<p>
						$message
					</p>
					<p>
						<ul class=button>
						<li class=button>
						<a class=button href='".$_SERVER['PHP_SELF']."?admin=4&article=$article'>
							".$lng['countinue']."
						</a>
						</li>
						</ul>
					</p>",
					$lng['messagecaption']);
					die();
					}
				}

			// Delete user
			if ($_GET['op'] == 'del' && $_GET['username'] != '')
				{
				if ($_SESSION['username'] == $_GET['username'])
					{
					$message = $lng['cantdeleteself'];
						$messageoptions = "".
						"
						<li class=button>
						<a class=button href='".$_SERVER['PHP_SELF']."?admin=4&showadduserform=".$_GET['showadduserform']."&article=$article'>
							".$lng['countinue']."
						</a>
						</li>
						";
					} else {
						$message = $lng['userdeleteconfirm'];
						$messageoptions = "".
						"
						<li class=button>
						<a class=button href='".$_SERVER['PHP_SELF']."?admin=4&username=".$_GET['username']."&op=del&confirm=1&showadduserform=".$_GET['showadduserform']."&article=$article'>
							".$lng['yes']."
						</a>
						</li>
						<li class=button>
						<a class=button href='".$_SERVER['PHP_SELF']."?admin=4&showadduserform=".$_GET['showadduserform']."&article=$article'>
							".$lng['no']."
						</a>
						</li>
						";
						}
				
				if ($_GET['confirm'] != "1")
					{
					putMessage("".
					"
					<p>
						$message
					</p>
					<p>
						<ul class=button>
						$messageoptions
						</ul>
					</p>",
					$lng['messagecaption']);
					die();
					} else {
						$filename = "./data/users/" . wikiapp_encode($_GET['username']);
						if (file_exists($filename) == ture)
							{
							unlink($filename);
							}
					}				
				}
				
			//--- Reading filenames into array  ( List of  Users )
			$maxusr = 0;
			$handle=opendir("./data/users");			
			while ($file = readdir($handle))
				{
				if (!is_dir($file))
					{
					if (preg_match('/^' . $_GET['show'] . '(.*?)/i',wikiapp_decode($file)) == true)
						{
						$maxusr++;
						$tempinfo = file('./data/users/'.$file);
						$tempinfo = implode('',$tempinfo);	
						$myresult4[]=$tempinfo;
						}
					}
				}
			closedir($handle);			
			if ($myresult4) { sort($myresult4); }				
			
			if ($maxusr % 10 == 0)
				{
				$maxpages =intval($maxusr / 10);
				} else {				
					$maxpages =intval($maxusr / 10) + 1;
					}
			$showpage = 1;
			if ($_GET['showpage'] != '')
				{
				$showpage = $_GET['showpage'];
				}
			
			if ($showpage < $maxpages)
				{
				$nextpage = $showpage + 1;
				$nextpagelnk = "" .
				"
				<li class=button><a class=button href='".$_SERVER['PHP_SELF']."?admin=4&showadduserform=".$_GET['showadduserform']."&show=".$_GET['show']."&showpage=".$nextpage."&article=$article'>".$lng['next']."</a></li>
				";
				}
				
			if ($showpage >= $maxpages && $maxusr > 10)
				{
				$priorpage = $showpage - 1;
				$priorpagelnk = "" .
				"
				<li class=button><a class=button  href='".$_SERVER['PHP_SELF']."?admin=4&showadduserform=".$_GET['showadduserform']."&show=".$_GET['show']."&showpage=".$priorpage."&article=$article'>".$lng['prior']."</a></li>
				";
				}
				
			if ($showpage < $maxpages && $showpage > 1)
				{
				$nextpage = $showpage + 1;
				$nextpagelnk = "" .
				"
				<li class=button><a class=button  href='".$_SERVER['PHP_SELF']."?admin=4&showadduserform=".$_GET['showadduserform']."&show=".$_GET['show']."&showpage=".$nextpage."&article=$article'>".$lng['next']."</a></li>
				";

				$priorpage = $showpage - 1;
				$priorpagelnk = "" .
				"
				<li class=button><a class=button href='".$_SERVER['PHP_SELF']."?admin=4&showadduserform=".$_GET['showadduserform']."&show=".$_GET['show']."&showpage=".$priorpage."&article=$article'>".$lng['prior']."</a></li>
				";
				}
			
			$userslist = "";
			if ($myresult4)
				{
				$usrcnt = 0;
				$usrstart = ($showpage - 1) * 10;
				$usrend = ($showpage) * 10;
				foreach ($myresult4 as $myfiles)
					{					
					$usrcnt++;
					if ($usrcnt >$usrstart && $usrcnt <=$usrend)
						{					
						$userinfo = explode(':',$myfiles);
						if ($userinfo[4] == 1) 
							{
							$useraccess=$lng['accessmanage'];
							} else {
								$useraccess=$lng['accessedit'];
								}
						if ($userinfo[5] == 1) 
							{
							$userstatus=$lng['userenable'];
							} else {
								$userstatus=$lng['userdisable'];
								}
						$userslist = $userslist .
						"
						<tr>
							<td align=center>
							" . $usrcnt . "
							</td>
							<td align=center width=20%>
								<span dir=ltr>
								" . $userinfo[0] . "
								</span>
							</td>
							<td align=center width=20%>
								" . $userinfo[2] . "
							</td>
							<td align=center width=20%>
								<span dir=ltr>
								" . $userinfo[3] . "
								</span>
							</td>
							<td align=center width=10%>
								" . $useraccess . "
							</td>
							<td align=center width=10%>
								" . $userstatus . "
							</td>
							<td align=center width=20%>
								<a href='".$_SERVER['PHP_SELF']."?admin=4&username=".$userinfo[0]."&showedituserform=1&article=$article'>".$lng['useroptedit']."</a>
								<a href='".$_SERVER['PHP_SELF']."?admin=4&username=".$userinfo[0]."&op=del&showadduserform=".$_GET['showadduserform']."&article=$article'>".$lng['useroptdelete']."</a>
							</td>
						</tr>
						";
							
						}
					}
				}	
			
			if ($_GET['showadduserform'] != '')
				{
				$addedituserform = "".
				"
				<form action='".$_SERVER['PHP_SELF']."?admin=4&showadduserform=1' method='post'>
				".$lng['newusertitle']."
				<br><br>
				<table id=dialogtable border=0>
					<tr>
						<td width=20%>
							".$lng['username']."
						</td>
						<td width=40%>
							<input type=text name=username>
						</td>
					</tr>
					<tr>
						<td width=20%>
							".$lng['password']."
						</td>
						<td width=40%>
							<input type=password name=password>
						</td>
					</tr>
					<tr>
						<td width=20%>
							".$lng['email']."
						</td>
						<td width=40%>
							<input type=text name=email>
						</td>
					</tr>
					<tr>
						<td width=20%>
							".$lng['realname']."
						</td>
						<td width=40%>
							<input type=text name=realname>
						</td>
					</tr>
					<tr>
						<td width=20%>
							".$lng['useraccess']."
						</td>
						<td width=40%>
							<select name=usertype>
								<option value=0>".$lng['accessedit']."</option>
								<option value=1>".$lng['accessmanage']."</option>
							</select>
						</td>
					</tr>							
					<tr>
						<td width=20%>
							".$lng['status']."
						</td>
						<td width=40%>
							<select name=active>
								<option value=1>".$lng['userenable']."</option>
								<option value=0>".$lng['userdisable']."</option>
							</select>
						</td>
					</tr>												
				</table>
				<br>
				<input class=mybutton type='submit' name='adduser' value='".$lng['adduser']."'>
				<br>
				<input type=hidden name='article' value='".$article."'>
				</form>
				";
				} else {
					$clickhere = "<a href='".$_SERVER['PHP_SELF']."?admin=4&showadduserform=1&article=$article'>".$lng['here']."</a>";
					$addedituserform = "".
					"					
					".$lng['toadduserclickherep1']." $clickhere ".$lng['toadduserclickherep2']."
					<br>
					";
				}
				
			if ($_GET['showedituserform'] != '')
				{
				$tempinfo = file('./data/users/'.wikiapp_encode($_GET['username']));
				$tempinfo = implode('',$tempinfo);	
				$userinfo = explode(':',$tempinfo);
				if ($userinfo[4] == 1)
					{
					$usertypeoptions = "".
					"
					<option value=0>".$lng['accessedit']."</option>
					<option selected value=1>".$lng['accessmanage']."</option>
					";
					} else {
						$usertypeoptions = "".
						"
						<option selected value=0>".$lng['accessedit']."</option>
						<option value=1>".$lng['accessedit']."</option>
						";
						}
						
				if ($userinfo[5] == 1)
					{
					$activeoptions = "".
					"
					<option secelted value=1>".$lng['userenable']."</option>
					<option value=0>".$lng['userdisable']."</option>
					";
					} else {
						$activeoptions = "".
						"
						<option value=1>".$lng['userenable']."</option>
						<option selected value=0>".$lng['userdisable']."</option>
						";
						}					
						
				$addedituserform = "".
				"
				<form action='".$_SERVER['PHP_SELF']."?admin=4&username=".$_GET['username']."' method='post'>
				".$lng['editusertitle']."
				<br><br>
				<table id=dialogtable border=0>
					<tr>
						<td width=20%>
							".$lng['username']."
						</td>
						<td width=40%>
							<input type=text name=username value='".$userinfo[0]."'>
						</td>
					</tr>
					<tr>
						<td width=20%>
							".$lng['password']."
						</td>
						<td width=40%>
							<input type=password name=password value='******'>
						</td>
					</tr>
					<tr>
						<td width=20%>
							".$lng['email']."
						</td>
						<td width=40%>
							<input type=text name=email value='".$userinfo[2]."'>
						</td>
					</tr>
					<tr>
						<td width=20%>
							".$lng['realname']."
						</td>
						<td width=40%>
							<input type=text name=realname value='".$userinfo[3]."'>
						</td>
					</tr>
					<tr>
						<td width=20%>
							".$lng['useraccess']."
						</td>
						<td width=40%>
							<select name=usertype>
								$usertypeoptions
							</select>
						</td>
					</tr>							
					<tr>
						<td width=20%>
							".$lng['status']."
						</td>
						<td width=40%>
							<select name=active>
								$activeoptions
							</select>
						</td>
					</tr>												
				</table>
				<br>
				<input class=mybutton type='submit' name='edituser' value='".$lng['edituser']."'>
				<br>
				<input type=hidden name='article' value='".$article."'>
				</form>
				";
				}				
				
			$smalltitle = " (<small><small>".$lng['users']."<big><big>) ";
			$pagecontent = "".
				"
				".$addedituserform."
				<br>
				".$lng['userslist']."
				<hr>
					<center>
					<span dir=ltr>
					[
					<a href='".$_SERVER['PHP_SELF']."?admin=4&showadduserform=".$_GET['showadduserform']."&show=a&article=$article'>A</a> 
					<a href='".$_SERVER['PHP_SELF']."?admin=4&showadduserform=".$_GET['showadduserform']."&show=b&article=$article'>B</a> 
					<a href='".$_SERVER['PHP_SELF']."?admin=4&showadduserform=".$_GET['showadduserform']."&show=c&article=$article'>C</a> 
					<a href='".$_SERVER['PHP_SELF']."?admin=4&showadduserform=".$_GET['showadduserform']."&show=d&article=$article'>D</a> 
					<a href='".$_SERVER['PHP_SELF']."?admin=4&showadduserform=".$_GET['showadduserform']."&show=e&article=$article'>E</a> 
					<a href='".$_SERVER['PHP_SELF']."?admin=4&showadduserform=".$_GET['showadduserform']."&show=f&article=$article'>F</a> 
					<a href='".$_SERVER['PHP_SELF']."?admin=4&showadduserform=".$_GET['showadduserform']."&show=g&article=$article'>G</a> 
					<a href='".$_SERVER['PHP_SELF']."?admin=4&showadduserform=".$_GET['showadduserform']."&show=h&article=$article'>H</a> 
					<a href='".$_SERVER['PHP_SELF']."?admin=4&showadduserform=".$_GET['showadduserform']."&show=i&article=$article'>I</a> 
					<a href='".$_SERVER['PHP_SELF']."?admin=4&showadduserform=".$_GET['showadduserform']."&show=j&article=$article'>J</a> 
					<a href='".$_SERVER['PHP_SELF']."?admin=4&showadduserform=".$_GET['showadduserform']."&show=k&article=$article'>K</a> 
					<a href='".$_SERVER['PHP_SELF']."?admin=4&showadduserform=".$_GET['showadduserform']."&show=l&article=$article'>L</a> 
					<a href='".$_SERVER['PHP_SELF']."?admin=4&showadduserform=".$_GET['showadduserform']."&show=m&article=$article'>M</a> 
					<a href='".$_SERVER['PHP_SELF']."?admin=4&showadduserform=".$_GET['showadduserform']."&show=n&article=$article'>N</a> 
					<a href='".$_SERVER['PHP_SELF']."?admin=4&showadduserform=".$_GET['showadduserform']."&show=o&article=$article'>O</a> 
					<a href='".$_SERVER['PHP_SELF']."?admin=4&showadduserform=".$_GET['showadduserform']."&show=p&article=$article'>P</a> 
					<a href='".$_SERVER['PHP_SELF']."?admin=4&showadduserform=".$_GET['showadduserform']."&show=q&article=$article'>Q</a> 
					<a href='".$_SERVER['PHP_SELF']."?admin=4&showadduserform=".$_GET['showadduserform']."&show=r&article=$article'>R</a> 
					<a href='".$_SERVER['PHP_SELF']."?admin=4&showadduserform=".$_GET['showadduserform']."&show=s&article=$article'>S</a> 
					<a href='".$_SERVER['PHP_SELF']."?admin=4&showadduserform=".$_GET['showadduserform']."&show=t&article=$article'>T</a> 
					<a href='".$_SERVER['PHP_SELF']."?admin=4&showadduserform=".$_GET['showadduserform']."&show=u&article=$article'>U</a> 
					<a href='".$_SERVER['PHP_SELF']."?admin=4&showadduserform=".$_GET['showadduserform']."&show=v&article=$article'>V</a> 
					<a href='".$_SERVER['PHP_SELF']."?admin=4&showadduserform=".$_GET['showadduserform']."&show=w&article=$article'>W</a> 
					<a href='".$_SERVER['PHP_SELF']."?admin=4&showadduserform=".$_GET['showadduserform']."&show=x&article=$article'>X</a> 
					<a href='".$_SERVER['PHP_SELF']."?admin=4&showadduserform=".$_GET['showadduserform']."&show=y&article=$article'>Y</a> 
					<a href='".$_SERVER['PHP_SELF']."?admin=4&showadduserform=".$_GET['showadduserform']."&show=z&article=$article'>Z</a> 
					<a href='".$_SERVER['PHP_SELF']."?admin=4&showadduserform=".$_GET['showadduserform']."&article=$article'>(all)</a> 
					 ]</span>
					</center>
				<hr>
				<table align=center id=installtable style='width: 90%;' border=1>
					<tr>
						<td align=center>
						#
						</td>
						<td align=center width=20%>
							<strong>".$lng['username']."</strong>
						</td>
						<td align=center width=20%>
							<strong>".$lng['realname']."</strong>
						</td>
						<td align=center width=20%>
							<strong>".$lng['email']."</strong>
						</td>					
						<td align=center width=10%>
							<strong>".$lng['useraccess']."</strong>
						</td>
						<td align=center width=10%>
							<strong>".$lng['status']."</strong>
						</td>					
						<td align=center width=20%>
							<strong>".$lng['options']."</strong>
						</td>										
					</tr>
						$userslist
				</table>
				<table align=center>
				<tr>
					<td width=50%>
						<ul class=button>
							$priorpagelnk
						</ul>
					</td>
					<td width=50%>
						<ul class=button>
							$nextpagelnk
						</ul>
					</td>
				</tr>
				</table>
				<br>
				";
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
					$tabtemplate
					$tablanguage
					$tabbackup
					$tabusers
					<li class=tabs>
					$loginlogout
					</li>
				</ul>
				</td>
			</tr>
			<tr>
				<td class=bodytop width=100% background=".$templateimagesdir."bodytop.jpg>
					<br><h1>".$lng['managewiki']." $smalltitle</h1>
				</td>
			</tr>
			<tr>
				<td class=bodymiddle width=100% background=".$templateimagesdir."bodymiddle.jpg>
					<div id=page style='text-indent: 0px'>
						<p align=justify>
							$pagecontent 
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
} // end adminpanel

?>