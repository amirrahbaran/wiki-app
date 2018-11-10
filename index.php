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
 
 $masterID = "ERFANWIKI";
 $fnd_mainfunctions = false;
 $fnd_language = false;
 $fnd_template = false;
 $fnd_printtemplate = false;
 $caninstall = false;
 
 //--- Loading selected language and template, if not found loading default language and template instead.
if (file_exists('./data/config.php') )  {
 	require_once './data/config.php';
	//--- Load wiki languagae
	if (file_exists('./languages/'.$Config["language"].'.php') )
		{
		require_once './languages/'.$Config["language"].'.php';
		$fnd_language = true;
		} else {
		//--- Load wiki default languagae
		if (file_exists('./languages/default.php') )  {
			require_once './languages/default.php';
			$fnd_language = true;
			} else {
			$fnd_language = false;
			}
		}
	//--- Load wiki template
	if (file_exists('./templates/'.$Config["template"].'/info.php') )
		{
		//require_once './templates/'.$Config["template"].'/info.php';
		$templateimagesdir = './templates/'.$Config["template"].'/images/';
		$templatecssfile = './templates/'.$Config["template"].'/template.css';
		$fnd_template = true;
		} else {
			//--- Load wiki default template
			if (file_exists('./templates/default/info.php') )
				{
				//require_once './templates/default/info.php';
				$templateimagesdir = './templates/default/images/';
				$templatecssfile = './templates/default/template.css';
				$fnd_template = true;
				}			
		}		
	//--- Load wiki print template
	if (file_exists('./templates/print/info.php') )
		{
		//require_once './templates/default/info.php';
		$printcssfile = './templates/print/template.css';
		$fnd_printtemplate = true;
		}	
	} else {
	//--- Load wiki default languagae
	if (file_exists('./languages/default.php') )
		{
		require_once './languages/default.php';
		$fnd_language = true;
		}
	//--- Load wiki default template
	if (file_exists('./templates/default/info.php') )
		{
		//require_once './templates/default/info.php';
		$templateimagesdir = './templates/default/images/';
		$templatecssfile = './templates/default/template.css';
		$fnd_template = true;
		}	
	}

if (file_exists('./includes/install.php') ) 
	{
	$caninstall = true;
	}

if (file_exists('./includes/wikimain.php') )	
	{
 	require_once './includes/wikimain.php';
	$fnd_mainfunctions = true;
	}

/*
 * Main execution block.
 */

//--- Send mail to erfan wiki team !
if ($_GET['insfrmurl'] != '')
	{
	$mailmessage = "Title : " . $_GET['insfrmtitle'] . "\n URL : " . $_GET['insfrmurl'] . "\n Email : " .  $_GET['insfrmemail'];
	@mail("info@openmind.ir,amirrezarahbaran@gmail.com,buttercupgreen@gmail.com", "New ERFAN WIKI installed.", $mailmessage);
	}
 
doLogin('',false);

$halt = "no"; 
if ($fnd_mainfunctions && $fnd_language && $fnd_template )
	{ 
	if (!file_exists('./data/config.php') || $_GET['install']!="") 
		{
		$halt = "yes";
		if ($caninstall == true)
			{
			require_once './includes/install.php';
			install();
			} else {
				print("<big><big><big>ERFAN WIKI is secured.<small><small><br>");
				print("please upload install.php file and try again.");
				}
		}
	if ($halt == "no")
		{
		require_once './data/config.php';
		if (!$Config)
			{
			$halt = "yes";
			configError(); 
			}
		}
		
	// Administrator panel.
	if ($halt == "no")
		{
		if ($_GET['admin']!="")
			{
			if ($_SESSION['isadmin'] == true)
				{
				if (file_exists('./includes/adminpanel.php') == true)
					{
					$halt = "yes";
					require_once './includes/adminpanel.php';
					$title = $_GET['article'];
					$page = $_GET['admin'];
					adminpanel($page,$title);
					}
				} else { die("<big><big><big>ACCESS DENIED !"); };
			}
		}

	// File upload 
	if ($halt == "no")
		{
		if ($_GET['fileupload']!="")
			{
			$halt = "yes";
			$title = $_GET['article'];
			fileupload($title);
			}
		}

	// Search
	if ($halt == "no")
		{
		if ($_GET['search']!="")
			{
			$halt = "yes";
			$keyword = $_GET['search'];
			$title = $_GET['article'];
			smartsearch($keyword, $title);
			}
		}

	// Edit page.
	if ($halt == "no")
		{
		if ($_GET['edit']!="")
			{
			$halt = "yes";
			$title = $_GET['edit'];
			editPage($title);			
			}
		}
	
	// Login.
	if ($halt == "no")
		{
		if ($_GET['login']) 
			{
			$halt = "yes";
			doLogin("title=$title",true);
			}
		}

	// Logout.
	if ($halt == "no")
		{
		if ($_GET['logout']) 
			{
			$halt = "yes";
			doLogout("title=$title");
			}
		}
	
	// Display printable version
	if ($halt == "no")
		{
		if ($_GET['print'] != '') 
			{
			$halt = "yes";
			$templateimagesdir = "";
			$templatecssfile = $printcssfile;
			$title = $_GET['print'];
			displayPage($title,"");
			}
		}
	
	// Display picture.
	if ($halt == "no")
		{
		if ($_GET['picture'] != '') 
			{
			$halt = "yes";			
			$picture = $_GET['picture'];
			$title = $_GET['article'];
			displaypicture($picture,$title);
			}
		}

	// Display page.
	if ($halt == "no")
		{
		$title = $_GET['title'];
		$highlight = $_GET['highlight'];
		if ($_GET['title'] == '') 
			{
			$halt = "yes";
			$title = $Config['homepage'];
			displayPage($title,"");
			} else {
				$halt = "yes";
				displayPage($title,$highlight);
				}
		}

	} else {
	print("<big><big><big>ERFAN WIKI is dead.<small><small><br>");
	print("please upload ERFAN WIKI files and install again.");	
	}
?>