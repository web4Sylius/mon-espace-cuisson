<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";

$msg = "";

///////////////////////// Désinscription à la news letter

if(isset($_POST['action']) && $_POST['action']=='add')
{
	if(isset($_FILES) && ($_FILES['csv']['error']==0))
	{
		if($_FILES['csv']['name'] != ""){
			$ext = pathinfo($_FILES['csv']['name'],PATHINFO_EXTENSION);
			if(strtolower($ext) == "csv")
			{

				///////////////////// On upload le fichier sur le serveur
				$resupload = '1';
                $fileToUpload ="./export/mailing/desinscriptions/".date("Ymdhi").".csv";

				$return = move_uploaded_file($_FILES['csv']['tmp_name'],$fileToUpload);

				if (!$return) 
					{	$msg .= " Erreur lors de l'envois du fichier sur le serveur";
						$resupload = '3';
					}
				

				if ($msg=="") 
				{
					//////////////////////// Ouverture du fichier transféré sur le serveur
					$csv = fopen($fileToUpload, "r");

					//print_r ("Fichier a traiter :".$fileToUpload);
					//print_r (" csv: ". $csv[0]);

					//////////////////////// Pour chaque mail trouvé on passe le champ allowemail='0'
					$numLigne = 0;
					$numDesinscript = 0;
					while($lignetab = fgetcsv($csv, 1000,";"))
					{
						$numLigne++;

						$countNbCol = count($lignetab);
						$lignetab[0] = strtolower($lignetab[0]);		//// mise en minuscules
						$charFound = strpbrk($lignetab[0],",/\\ \t"); 	//// recherche la 1er d'un des caractères spécifiés
						
						if (($countNbCol > 1 ) || ($charFound !== FALSE))
						{
							$msg="Erreur(s) ligne ".$numLigne." lors d'une desinscription";
							$resupload = 4;
							break;
						}

						$adrMail = $lignetab[0];
						
						$req = $DB_site2->query("SELECT userid, mail FROM utilisateur WHERE mail='".mysqli_real_escape_string($DB_site2->link_id, $adrMail)."'");

						//print_r($adrMail."   req".($req)."<BR>");
						
						while ($row = $DB_site2->fetch_array($req)) 
							{
								
								if($row['mail'] != "")
								{
									$DB_site2->query("UPDATE utilisateur SET allowemail='0' WHERE userid=".$row['userid']."");
									$numDesinscript++;
									//print_r("user : ".$row['userid']."   mail : ".$row['mail']."<BR>");
								}
							}
					
					}

					$msgFinOk = "Nb Desinscription ".$numDesinscript."/".$numLigne." Emails dans la liste";

					///////////////////////// On log upload ok + desinscription
					$DB_site2->query("INSERT INTO suivi_newsletter SET type='mail', commercial='".$user_info['username']."', date=".time().", fichier='".$_POST['name']."', action='".$resupload."'");


				}else
				{ 	////////////////////////// On log l'erreur d'upload sur le serveur 
					$DB_site2->query("INSERT INTO suivi_newsletter SET type='mail', commercial='".$user_info['username']."', date=".time().", fichier='".$_POST['name']."', action='".$resupload."'");
				}


			}else
				$msg = ", Ce type de fichier n'est pas autorisé";
		}
	}else
		$msg = "Vous n'avez pas sélectionné de fichier";
}

// Supprimer un fichier sur le repertoire du serveur, sans confirmation !!!!
if(isset($_GET['action']) && $_GET['action'] == 'delete'){
	unlink("./export/mailing/desinscriptions/".$_GET['value']);
	$DB_site2->query("INSERT INTO suivi_newsletter SET type='mail', commercial='".$user_info['username']."', date=".time().", fichier='".date("YmdHi").".csv', action='2'");
}

// Listes fichiers
$dir = "./export/mailing/desinscriptions";

/////////////////////////////// TSI ////////////////////////////////////////
/////////////////////  filtrage, ne montre que les .csv ////////////////////
$namefiltercsv = "#(\.csv|\.CSV|\.Csv)$#";
$files = preg_grep($namefiltercsv, scandir($dir,SCANDIR_SORT_DESCENDING));
sort($files);
//$files = scandir($dir);


/////////////////////// Evolution: limiter le nombre de fichiers affichés 
if(count($files) >= 1)
	foreach($files as $key => $value){
		if(!in_array($value, array(".","..",".svn"))){
			$listcsv .= "<tr>
				<td>".$value."</td>
				<td><a href=\"".$dir."/".$value."\"><img src=\"./images/icon/save.png\" alt=\"Télécharger\"></a></td>
				<td><a href=\"javascript:void(0)\" onclick=\"setAction('delete','".$value."')\"><img src=\"./images/supprimer.gif\" alt=\"Supprimer\"></a></td>
			</tr>";
		}
	}
else
	$listcsv = "<tr>
		<td colspan=\"3\">Aucun fichier csv</td>
	</tr>";




// Suivi newsletter
$cptLigne=0;
$actiontxt = array("1"=>"Désinscription + Ajout du fichier", "2"=>"Suppression du fichier", "3"=>"Erreur transfert du fichier", "4"=>"Erreur format du fichier");
$req = $DB_site2->query("SELECT * FROM suivi_newsletter WHERE type='mail' ORDER BY date DESC");
while(($suivi = $DB_site2->fetch_array($req)) && ($cptLigne<20))
{
	$cptLigne++;
	$tdsuivi .= "<tr>
		<td>".date("d/m/Y",$suivi['date'])."<br>".date("H:i",$suivi['date'])."</td>
		<td>".$suivi['commercial']."</td>
		<td>".$suivi['fichier']."</td>
		<td>".$actiontxt[$suivi['action']]."</td>
	</tr>";
}

?>

<style>
.div_left { border: 1px solid black; width: 47%; height: 270px; float: left; padding: 10px; }
.div_right { border: 1px solid black; width: 47%; height: 375px; float: right; padding: 10px; }
.list_csv { width: 100%; height: 340px; overflow: auto; }
.list_csv table { border-collapse: collapse; margin: auto; }
.list_csv tr { border-top: 1px solid #C1C3D1; border-bottom-: 1px solid #C1C3D1; color:#666B85; font-size:16px; font-weight:normal; text-shadow: 0 1px 1px rgba(256, 256, 256, 0.1); }
.list_csv td { padding: 5px; }
.list_csv tr:hover td { background:#4E5066; color:#FFFFFF; border-top: 1px solid #22262e; border-bottom: 1px solid #22262e; }
.list_csv tr:nth-child(odd) td { background:#EBEBEB; }
.list_csv tr:nth-child(odd):hover td { background:#4E5066; }
.tab_suivi { width: 98%; margin-top: 10px; padding: 10px; background-color: #ebebeb; color: #000044; box-shadow: 1px 1px 3px #000; border: none; }
.tab_suivi h2 { text-align: center; }
.tab_suivi table { border-collapse: collapse; margin: auto; width: 100%; }
.tab_suivi thead { color: black; text-align: center; font-weight: bold; }
.tab_suivi tbody tr { border-top: 1px solid #C1C3D1; border-bottom-: 1px solid #C1C3D1; background-color: #DDDDDD; }
.tab_suivi tbody td { text-align: center; padding: 0px 10px; }
</style>

<script language="javascript">
function setAction(action, value){
	switch(action){
		case 'delete':
			window.location = "newsletter_email.php?action="+action+"&value="+value;
		break;

		case 'download':
			window.location = "./ajax/newsletter_export.php?value="+value;
		break;
	}
	
}

function file(value){
	var text = value.split(".");
	document.getElementById('name').value = text[0];
}

</script>

<h1 align="center">Gestion des Désinscriptions à la Newsletter</h1>

<table width="100%">
			<tr>
				<td align="left">
					<h3>Traitement des Désinscriptions par liste d'Email</h3>
				</td>
				<td align="right">
					<h3>Fichiers sur le Serveur</h3>
				</td>
			</tr>
</table>

<div class="div_left">
	<p>Veuillez bien sélectionner un fichier <strong>.CSV</strong> et : 
		<ul>
			<li>Vérifier à ce qu'il n'y ait qu'une <u>seule colonne</u> contenant les emails à désinscrire</li>
			<li>Le système désinscrit seulement les comptes pour lesquels les emails correspondent</li>
		</ul>
	</p>
	
	<form method="POST" action="newsletter_email.php" enctype="multipart/form-data">
		<input type="hidden" name="action" value="add">
		<table width="100%">
			<?php if ($msg =="") { ?>
				<br>
			<?php } else { ?>
			<tr>
				<td colspan="2" align="center">
					<div style="color:red; font-weight:bold;"><?php print_r ("Attention : ".$_FILES['csv']['name']." ".$msg); ?></div>
				</td>
			</tr>
			<?php } ?>
			<tr>
				<td><b>Fichier : </b></td>
				<td><input type="file" name="csv" onchange="file(this.value)"></td>
			</tr>
			<tr><td></td></tr>
			<tr>
				<td colspan="2" align="center"><input type="submit" value="Désinscrire"></td>
			</tr>
			<?php if ($msgFinOk =="") { ?>
				<br>
			<?php } else { ?>
			<tr>
				<td colspan="2" align="center">
					<div style="color:green; font-weight:bold;"><?php print_r ("Resutat : ".$msgFinOk); ?></div>
				</td>
			</tr>
			<?php } ?>
		</table>
	</form>
</div>

<div class="div_right">
	<p>Liste des fichier .csv :</p>
	<div class="list_csv">
		<table>
			<?php echo $listcsv; ?>
		</table>
	</div>
</div>

<div style="clear: both;"></div>

<h3>Historique des 20 Derniers Traitements</h3>

<div class="tab_suivi">
	<h2>Suivi Newsletter</h2>
	<div class="clear"></div>
	<table>
		<thead style="color:black; text-align:center; font-weight:bold;">
			<tr>
				<td>Date</td>
				<td>Utilisateur</td>
				<td>Fichier</td>
				<td>Action</td>
			</tr>
		</thead>
		<tbody>
			<?php echo $tdsuivi; ?>
		</tbody>
	</table>
</div>
