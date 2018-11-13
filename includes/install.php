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
	
function install() {
	global $lng;
	global $Config;
	global $templateimagesdir;	
	if ($_GET['install']=="" || $_GET['install']=="stage1") {
		$currentDir = $_SERVER['SCRIPT_FILENAME'];
		$button="Continue";

		if (!file_exists('./data')) { mkdir('./data'); }
		
		if (file_exists("./data")) {
			$data_exists = true;
			$test01 = $lng['yes'];
		} else {
			$data_exists = false;
			$test01 = $lng['no'];
		}

		if ($data_exists) {
			if (is_writable("./data")) {
				if (!file_exists('./data/users')) { mkdir('./data/users'); }
				if (!file_exists('./data/pages')) { mkdir('./data/pages'); }
				if (!file_exists('./data/uploads')) { mkdir('./data/uploads'); }
				if (!file_exists('./data/uploads/thumbs')) { mkdir('./data/uploads/thumbs'); }
				if (!file_exists('./data/backups')) { mkdir('./data/backups'); }
				$data_writable = true;
				$test02 = $lng['yes'];
			} else {
				$data_writable = false;
				$test02 = $lng['no'];
			}
		}
		
		if (file_exists("./data/config.php")) {
				$reinstall = true;
				$test03 = $lng['yes'];
			} else {
				$reinstall = false;
				$test03 = $lng['no'];
			}			
		
		if ($data_exists==false)
			{
			$sugg01 = $lng['sg1nodatadir'];
			}
			
		if ($data_writable==false)
			{
			$sugg02 = $lng['sg2datachmode'];
			}
			
		if ($reinstall==true)
			{
			$sugg03 = $lng['sg3reinstallwiki'];
			}
			
		print(getHtmlHead($title,"").
		"
		<body>
		<br>
		<br>
		<div align=center>
		<table id=mainbody border=0 cellpadding=0 cellspacing=0>
			<tr>
				<td class=bodytop width=100% background=".$templateimagesdir."bodytop.jpg height=60>
					<br><h1>".$lng['installwikiapp']."</h1>
				</td>
			</tr>
			<tr>
				<td class=bodymiddle width=100% background=".$templateimagesdir."bodymiddle.jpg>
					<div id=page style='text-indent: 0px'>
					<p align=justify>
						".$lng['installtests']."
						<table id=installtable border=1>
							<tr>
								<td width=80%>
									".$lng['checkdata']."
								</td>
								<td width=20%>
									<p align=center>
									$test01
									</p>
								</td>
							</tr>
							<tr>
								<td width=80%>
									".$lng['checkdatawrite']."
								</td>
								<td width=20%>
									<p align=center>
									$test02
									</p>
								</td>
							</tr>
							<tr>
								<td width=80%>
									".$lng['checkconfigfile']."
								</td>
								<td width=20%>
									<p align=center>
									$test03
									</p>
								</td>
							</tr>
						</table>
						<br>
						<br>
						$sugg01
						$sugg02
						$sugg03
		");
		
		if ($data_exists && $data_writable) {
			if ($reinstall) {
				$message01=
				"
				".$lng['okreinstall']."
				<form action='".$_SERVER['PHP_SELF']."' method='get'>
				<input type=hidden name=install value=stage2>
				<input type=submit class=mybutton value='".$lng['reinstall']."'>
				</form>
				";
			} else {
				$message01=
				"
				".$lng['okinstall']."
				<form action='".$_SERVER['PHP_SELF']."' method='get'>
				<input type=hidden name=install value=stage2>
				<input type=submit class=mybutton value='".$lng['install']."'>
				</form>
				";
			}
		} else {
				$message01=
				"
				<form action='".$_SERVER['PHP_SELF']."' method='get'>
				<input type=hidden name=install value=stage1>
				<input type=submit class=mybutton value='".$lng['retry']."'>
				</form>
				";
		}

	print("".
		"
		<p>
		$message01
					</p>
					</div>
				</td>
			</tr>
			<tr>
				<td class=bodybottom width=100% background=".$templateimagesdir."bodybottom.jpg height=40>
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
		
	else if ($_GET['install'] == 'stage2') {
		if (file_exists("./data/config.php")) 
			{
			include("./data/config.php");
			}
			if ($Config['homepage']=="") $Config['homepage'] = $lng['defaulthome'];
			if ($Config['username']=="") $Config['username'] = $lng['defaultadmin'];
			
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
			sort($myresult1);
			closedir($handle);	

			$langlist = "";
			foreach ($myresult1 as $myfiles)
				{
				if ($myfiles == $Config['language'])
					{
					$langlist = $langlist  . "<option selected>" . $myfiles . "</option>";
					} else {
					$langlist = $langlist  . "<option>" . $myfiles . "</option>";
					}
				}
		
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
			sort($myresult2);
			closedir($handle);	

			$templist = "";
			foreach ($myresult2 as $myfiles)
				{
				if ($myfiles == $Config['template'])
					{
					$templist = $templist  . "<option selected>" . $myfiles . "</option>";
					} else {
					$templist = $templist  . "<option>" . $myfiles . "</option>";
					}
				}			
			
			$form01 = $Config['homepage'];
			$form02 = $Config['username'];
			$form03 = $Config['email'];
			$installform = "<form action=" .$_SERVER['PHP_SELF'] . "?install=stage3 method=post>";

		print(getHtmlHead($title,"").
		"
		<body>
		<br>
		<br>
		<div align=center>
		<table id=mainbody border=0 cellpadding=0 cellspacing=0>
			<tr>
				<td class=bodytop width=100% background=".$templateimagesdir."bodytop.jpg height=60>
					<br><h1>".$lng['installwikiapp']."</h1>
				</td>
			</tr>
			<tr>
				<td class=bodymiddle width=100% background=".$templateimagesdir."bodymiddle.jpg>
					<div id=page style='text-indent: 0px'>
					<p align=justify>
						".$lng['instnote']."						
						<br><br><br>
						".$lng['setyourhomepage']."
						$installform
						<table id=dialogtable border=0>
							<tr>
								<td width=20%>
									".$lng['frmhomepage']."
								</td>
								<td width=40%>
								<input type=text name=homepage value='$form01'>	
								</td>
							</tr>
							</table>
							<br>
							".$lng['setadminuserpass']."
						<table id=dialogtable border=0>
							<tr>
								<td width=20%>
									".$lng['username']."
								</td>
								<td width=40%>
									<input type=text name=username value='$form02'>
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
									".$lng['password']."
								</td>
								<td width=40%>
									<input type=password name=password-verification>
								</td>
							</tr>
						</table>
						<br>
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
							<br>
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
						<br>
							".$lng['setadminemail']."
						<table id=dialogtable border=0>
							<tr>
								<td width=20%>
									".$lng['email']."
								</td>
								<td width=40%>
								<input type=text name=email value='$form03'>
								</td>
							</tr>
						</table>
						<br>
						<br>
						<input type=submit class=mybutton value='".$lng['countinue']."'>
						</form>						
					</div>
				</td>
			</tr>
			<tr>
				<td class=bodybottom width=100% background=".$templateimagesdir."bodybottom.jpg height=40>
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
	
	else if ($_GET['install'] == 'stage3') {
		if ($_POST['homepage']=="") {
			$installstage1 = "<a class=button href=\"".$_SERVER['PHP_SELF']."?install=stage1\">".$lng['return']."</a>";
			putMessage("".
				"
				<p>
					".$lng['installfromstart']."
				</p>
				<p>
					<ul class=button>
					<li class=button>
						$installstage1
					</li>
					</ul>
				</p>",
				$lng['messagecaption']);
		} else {
			$test01 = "";
			$test02 = "";
			$test03 = "";
			$sugg01 = "";
			$sugg01 = "";
			$sugg01 = "";
			$installstage1 = "
			<form action=" .$_SERVER['PHP_SELF'] . " method=get>
			<input type=hidden name=install value=stage1>
			<input type=submit class=mybutton value='".$lng['reinstall']."'>
			</form>
			";
			$wikihomepage = "
			<form action=" .$_SERVER['PHP_SELF'] . " method=get>
			".$lng['instfrmmsg']."
			<br>
			<br>
			<table id=dialogtable border=0>
				<tr>
					<td width=20%>
						".$lng['instfrmtitle']."
					</td>
					<td width=40%>
						<input type=text name=insfrmtitle>
					</td>
				</tr>
				<tr>
					<td width=20%>
						".$lng['instfrmurl']."
					</td>
					<td width=40%>
						<input type=text name=insfrmurl>
					</td>
				</tr>
				<tr>
					<td width=20%>
						".$lng['instfrmemail']."
					</td>
					<td width=40%>
						<input type=text name=insfrmemail>
					</td>
				</tr>
			</table>						
			
			<br><br>
			<input type=submit class=mybutton value='".$lng['home']."'>
			</form>
			";
		
			if ($_POST['password']==$_POST['password-verification']) {
				$pwdOk = true;	
				$test01=$lng['yes'];
				$userdata = $_POST['username'] . ":" . md5($_POST['password']) . ":-----:" . $_POST['email'] . ":1:1";
				$datafile = wikiapp_encode($_POST['username']);
				$handle = fopen('./data/users/'.$datafile,'w');
				fwrite($handle,$userdata);
				fclose($handle);
				if ($configHandle = fopen("./data/config.php",'w')) {
					$configData = ("<?php\n\n".
						"\$Config[\"homepage\"] = \"".$_POST["homepage"]."\";\n".
						"\$Config[\"template\"]    = \"".$_POST["template"]."\";\n".
						"\$Config[\"language\"]    = \"".$_POST["language"]."\";\n".
						"?>");
					if ($e = fwrite($configHandle, $configData)) {
						if (fclose($configHandle)) {
							$configfile= ture;
							$test02=$lng['yes'];
						} else {
							$configfile= false;
							$test02=$lng['no'];
						}
						if (file_exists("./data/pages")) {							
							$pagesExists = true;
							$test03=$lng['yes'];
						} else {
							if (mkdir("./data/pages")) {
								$pagesExists = true;
								$test03=$lng['yes'];
							} else {
								$test03=$lng['no'];
							}
						}
					} else {
						$configfile= false;
						$test02=$lng['no'];
					}
				} else {
     				$configfile= false;			
					$test02=$lng['no'];
				}
	
				if ($pagesExists) {
					$datafile = wikiapp_encode($_POST['homepage']);
					if (file_exists("./data/pages/$datafile")) {
						$homepageExists = true;
						$test03=$lng['yes'];
					} else {
						$datafile = wikiapp_encode($_POST['homepage']);
						if ($homepageHandle = fopen("./data/pages/$datafile",'w')) {
							$homepageData = $lng['homepagedata'];
							fwrite($homepageHandle, $homepageData);
							$homepageExists = true;
							$test03=$lng['yes'];
						} else {
							$test03=$lng['no'];
						}
					}
				}

			} else {
				$pwdOk=false;
				$test01=$lng['no'];
			}

		if ($pwdOk==false)
			{
			$sugg01 = $lng['sg1invalidpassword'];
			$test02 = "---";
			$sugg02 = "";
			$test03 = "---";
			$sugg03 = "";
			} else {
				if ($configfile==false)
					{
					$sugg02 = $lng['sg2writeconfigerror'];
					}			
				if ($homepageExists==false)
					{
					$sugg03 = $lng['sg3writehomepageerror'];
					}
			}
			
			if ($pagesExists && $homepageExists && $configfile && $pwdOk) {
					$message01="
					".$lng['installsuccess']."
					$wikihomepage
					";
				} else {
				$message01="".$lng['installfail']."
				$installstage1
				";
				}
			
			$attention = "";
			$randname = rand();
			if (@rename("./includes/install.php","./includes/". $randname ."_install.php") !=true)
				{
				$attention = $lng['installphp'];
				}
			
			print(getHtmlHead($title,"").
			"
			<body>
			<br>
			<br>
			<div align=center>
			<table id=mainbody border=0 cellpadding=0 cellspacing=0>
				<tr>
					<td class=bodytop width=100% background=".$templateimagesdir."bodytop.jpg height=60>
						<br><h1>".$lng['installwikiapp']."</h1>
					</td>
				</tr>
				<tr>
					<td class=bodymiddle width=100% background=".$templateimagesdir."bodymiddle.jpg>
						<div id=page style='text-indent: 0px'>
						<p align=justify>
							".$lng['instoperations']."
							<br><br><br>
							<table id=installtable border=1>
								<tr>
									<td width=80%>
										".$lng['instcheckpass']."
									</td>	
									<td width=20%>
										<p align=center>
										$test01
										</p>
									</td>
								</tr>
								<tr>
									<td width=80%>
										".$lng['instwriteconfig']."
									</td>	
									<td width=20%>
										<p align=center>
										$test02
										</p>
									</td>
								</tr>
								<tr>
									<td width=80%>
										".$lng['instwritehomepage']."
									</td>	
									<td width=20%>
										<p align=center>
										$test03
										</p>
									</td>
								</tr>
						</table>
							<br>
							<br>
							$sugg01
							$sugg02
							$sugg03
							$attention
						</p>
						<p>
							$message01
						</p>
						</div>
					</td>
				<tr>
					<td class=bodybottom width=100% background=".$templateimagesdir."bodybottom.jpg height=40>
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
}

?>