<?php
session_start();
if (!file_exists("step"))
{
	if (!($fstep = fopen("step","w+")))
	{
		die("Couldn't create temporary installation file.");
	}
	fputs($fstep,"0\n");
	fclose($fstep);
}

function runCommand($comm)
{
	global $step;
	$result = `$comm 2>&1`;
	if (trim($result) != "")
	{
		echo "<br /><br /><br /><div align=center>";
		echo "The following error occured on server:<br>";
		echo "<b>$result</b>";
		echo "</div>";
		die();
	}
	$step++;
	if (!($fstep = fopen("step","w+")))
	{
		die("Couldn't open temporary installation file.");
	}
	fputs($fstep,"$step\n");
	fclose($fstep);	
}

function setPermissions($dir)
{
	if (!is_dir($dir))
	{
		return;
	}
	if ($handle = opendir($dir))
	{
		while (false !== ($file = readdir($handle)))
		{
			if ($file == "." || $file == ".." || "$dir/$file" == __FILE__)
			{
				continue;
			}
			if (is_dir("$dir/$file"))
			{
				chmod("$dir/$file",0755);
				setPermissions("$dir/$file");
			}
			else
			{
				$info = pathinfo("$dir/$file");
				if (strtoupper($info["extension"]) == "XML")
				{
					chmod("$dir/$file",0600);
				}
				else
				{
					chmod("$dir/$file",0644);
				}				
			}
		}

	}
	else
	{
		echo "Couldn't open $dir directory.";
	}
}
$lines = file("step");
$step = trim($lines[0]);
if ($step<1)
{
    if (!is_writeable(dirname($_SERVER['SCRIPT_FILENAME'])))
    {
    	die("Sorry, I don't have write permission on <b>".dirname($_SERVER['SCRIPT_FILENAME']).
    		" </b>directory in your server.<br />Please make it writeable.");
    }
    $pkg_file = "CoursePackage";
    if (!file_exists($pkg_file.".tar.gz"))
    {
    	die ("Couldn't find '<b>$pkg_file.tar.gz</b>' file.");
    }
    runCommand("gunzip '$pkg_file.tar.gz'");
}
if ($step<2)
{
    runCommand("tar -xf '$pkg_file.tar'");
}
if ($step<3)
{
    setPermissions(dirname($_SERVER['SCRIPT_FILENAME']));
}
if ($step<4)
{
    runCommand("rm  $pkg_file.tar -f");
}


function valid_email($Email)
{
    
    if (filter_var($Email, FILTER_VALIDATE_EMAIL)) 
    {   } 
    else {
        show_error("mistake in email");
        }
       
}


define('INSTALL',true);
require 'includes/common.php';
?>
<html>
	<head>
		<link rel="stylesheet" type="text/css" href="themes/default/style/style.css">
	</head>
	<body>
<?php
$info = parseCourseInfo();
$infoFile = "syllabus/courseinfo.xml";
$adminsFile = "admin/admins.inf";
reset($info);
while (list($key,$value) = each($info))
{
	if (!is_array($info[$key]))
	{
		if (isset($_POST[$key]))
		{
			$info[$key] = html_entity_decode(stripslashes($_POST[$key]));
		}
	}
	else
	{
		unset($info[$key]);
	}
}
if (isset($_POST['Ins_Name'])) $info['Instructor']['Name'] = $_POST['Ins_Name'];
if (isset($_POST['Ins_Email'])) $info['Instructor']['Email'] = $_POST['Ins_Email'];
if (isset($_POST['Ins_HomePage'])) $info['Instructor']['HomePage'] = $_POST['Ins_HomePage'];

if (isset($_GET['cmd']) && $_GET['cmd'] == "finish")
{
	if (trim($_POST['adminUsername']) == "")
	{
		$msg = "Administrator username was invalid.";
	}
	elseif (trim($_POST['adminPass1']) == "")
	{
		$msg = "Administrator password was invalid.";
	}
	elseif (strlen(trim($_POST['adminPass1']))<5)
	{
		$msg = "Administrator password was too short.";
	}
	elseif (trim($_POST['adminPass1']) != trim($_POST['adminPass2']))
	{
		$msg = "The two passwords do not match.";
	}
	else
	{
		if (! ($fout = fopen($infoFile,"w")) )
		{
			die("Couldn't open $infoFile for writing.");
		}
		if (! ($fout2 = fopen($adminsFile,"w")) )
		{
			die("Couldn't open $usersFile for writing.");
		}
		fputs($fout,"<?xml version='1.0' encoding='UTF-8' ?>\n");
		fputs($fout,"<Course>\n");
		reset($info);
		while (list($key,$value) = each($info))
		{
			if (is_array($info[$key]))
			{
				fputs($fout,"\t<$key>\n");
				reset($info[$key]);
				while (list($inkey,$invalue) = each($info[$key]))
				{
					fputs($fout,"\t\t<$inkey><![CDATA[$invalue]]></$inkey>\n");
				}
				fputs($fout,"\t</$key>\n");
			}
			else
			{
				if (trim($key) != "")
				{
					fputs($fout,"\t<$key><![CDATA[$value]]></$key>\n");
				}
			}
		}
		fputs($fout,"</Course>\n");
		fclose($fout);

		fputs($fout2,trim($_POST['adminUsername'])."|".md5(trim($_POST['adminPass1']))."|"."admin");
		fclose($fout2);
?>
		<BR><BR><BR>
		<DIV align=center>
			<b>Congratulations!</b><br /><br />
		Installation Completed Successfully.<br /><br /><br />
		Go to [<a href="<?php echo $indexFile; ?>">Generated Course Site</a> ]
		</DIV>
<?php
		`rm install.php -f`;
		die();
	}
	echo "<span class=error>$msg</span><br /><br />";
}
?>
<form method=post action="install.php?cmd=finish">
	<TABLE cellSpacing=0 cellPadding=0  border=0 width="97%" align=center>
		<tr>
			<td>
				<?php createSectionTitle('Administrator Information'); ?>
			</td>
		</tr>
		<tr>
			<td>
				<table class=blockcontent2 align=center width="90%" border=0>
					<tr>
						<td>
							<br />
						</td>
					</tr>
					<tr>
						<td align=right>
							Administrator Username:
						</td>
						<td>
							<input name=adminUsername size=20 required=required>
						</td>
					</tr>
					<tr>
						<td align=right>
							Password:
						</td>
						<td>
							<input name=adminPass1 size=20 type=password required=required>
						</td>
					</tr>
					<tr>
						<td align=right>
							Retype Password:
						</td>
						<td>
							<input name=adminPass2 size=20 type=password required=required>
						</td>
					</tr>
					<tr>
						<td align=right>
							Email:
						</td>
						<td>
							<input type=email name=adminMail size=40 required=required>
						</td>
					</tr>
					<tr>
						<td>
							<br />
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				<br />
			</td>
		</tr>
		<TR>
			<TD>
				<?php createSectionTitle('Course Information'); ?>
			</TD>
		</TR>
		<tr>
			<td>

				<table class=blockcontent2 align=center width="90%" cellspacing=5 cellpadding=1>
					<tr>
						<td>
							<br />
						</td>
					</tr>
					<tr>
						<td align=right>
							Course Name:
						</td>
						<td>
							<input name=Name size=35 value="<?php echo $info['Name']; ?>">
						</td>
					</tr>
					<tr>
						<td align=right>
							Course Code:
						</td>
						<td>
							<input name=Number size=10 value="<?php echo $info['Number']; ?>">
						</td>
					</tr>
					<tr>
						<td align=right>
							Semester:
						</td>
						<td>
							<input name=Semester size=15 value="<?php echo $info['Semester']; ?>">
						</td>
					</tr>
					<tr>
						<td align=right>
							Group:
						</td>
						<td>
							<input name=Group size=3 value="<?php echo $info['Group']; ?>">
						</td>
					</tr>
					<tr>
						<td align=right>
							Credit:
						</td>
						<td>
							<input name=Credit size=3 value="<?php echo $info['Credit']; ?>"> <span class=comment>Unit(s)</span>
						</td>
					</tr>
					<tr>
						<td colspan=2>
							<hr />
						</td>
					</tr>
					<tr>
						<td align=right>
							Department:
						</td>
						<td>
							<input name=Department size=15 value="<?php if (!isset($_GET['cmd'])) {
														echo "CE";
													}
													else
													{
														echo $info['Department'];
													}
												?>">
						</td>
					</tr>
					<tr>
						<td align=right>
							University:
						</td>
						<td>
							<input name=University size=35 
								value="<?php if (!isset($_GET['cmd'])) {
													echo "Sharif University of Technology";
												}
												else
												{
													echo $info['University'];
												}?>">
						</td>
					</tr>
					<tr>
						<td colspan=2>
							<hr />
						</td>
					</tr>
					<tr>
						<td align=right>
							Class time:
						</td>
						<td>
							<input name=LectureClass size=40 value="<?php echo $info['LectureClass']; ?>">
						</td>
					</tr>
					<tr>
						<td align=right>
							Room:
						</td>
						<td>
							<input name=Room size=15 value="<?php echo $info['Room']; ?>">
						</td>
					</tr>
					<tr>
						<td colspan=2>
							<hr />
						</td>
					</tr>
					<tr>
						<td align=right valign=top>
							Text book(s):
						</td>
						<td>
							<textarea name=TextBook cols=70 rows=7><?php echo $info['TextBook']; ?></textarea>
						</td>
					</tr>
					<tr>
						<td align=right valign=top>
							Evaluation (Grading Policy):
						</td>
						<td>
							<textarea name=Evaluation cols=70 rows=6><?php echo $info['Evaluation']; ?></textarea>
						</td>
					</tr>
					<tr>
						<td colspan=2>
							<hr />
						</td>
					</tr>
					<tr>
						<td align=right>
							Instructor Name:
						</td>
						<td>
							<input name=Ins_Name size=30 value="<?php echo $info['Instructor']['Name']; ?>">
						</td>
					</tr>
					<tr>
						<td align=right>
							Email:
						</td>
						<td>
							<input name=Ins_Email size=40 type=email value="<?php echo $info['Instructor']['Email']; ?>">
						</td>
					</tr>
					<tr>
						<td align=right>
							Homepage:
						</td>
						<td>
							<input name=Ins_HomePage size=40 value="<?php echo $info['Instructor']['HomePage']; ?>">
						</td>
					</tr>
					<tr>
						<td colspan=2>
							<hr />
						</td>
					</tr>
					<tr>
						<td align=center colspan=2>
							<input type=submit value="    Finish    ">
						</td>
					</tr>
					<tr>
						<td colspan=2>
							<hr />
						</td>
					</tr>
				</table>

			</td>
		</tr>
	</TABLE>
<input type=hidden name="startDate" value="<?php echo date("F Y",time()); ?>">
</form>
</body>
</html>
