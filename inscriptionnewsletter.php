<?php

require ("global.php");


$navigationbit="<a href=\"index.php\" class=\"navig\">".${$title}."</a> > Inscription newsletter";

eval( "\$TemplateMenuBas = \"$templatecache[MenuBas]\";" );


/*
<input type="radio" name="choixnewsletter" value="1" checked> Inscription 
<input type="radio" name="choixnewsletter" value="0"> Désinscription
*/

if (isset($action) and $action == "mail")
	{
	if ($adresse == "" or !ereg("@", $adresse))
		{
		$error = "$multilangue[adresseinexistanteouincorrecte]" ;
		}
	else
		{
		switch ($choixnewsletter)
			{
			case '0':
				$ad_newsletters=$DB_site->query_first("select adresse_mail from mails_newsletter where adresse_mail = '$adresse'");
				$ad_users=$DB_site->query_first("select mail as adresse_mail from utilisateur where mail = '$adresse'");
				if ($ad_newsletters[adresse_mail] != "")
					{
					$DB_site->query("update mails_newsletter set allow_email = '0' where  adresse_mail = '$adresse'");
					$error = "$multilangue[adressesupprimeerecoitplusnewsletter]" ;
					}	
				elseif ($ad_users[adresse_mail] != "") 
					{ 
					$DB_site->query("update utilisateur set allowemail = '0' where mail = '$adresse'");
					$error = "$multilangue[adressesupprimeerecoitplusnewsletter]" ;
					}
				else
					{
					$error = "$multilangue[desoleadresseintrouvable]" ;
					}
				break ;
			case '1':
				$ad_newsletters=$DB_site->query_first("select adresse_mail from mails_newsletter where adresse_mail = '$adresse'");
				$ad_users=$DB_site->query_first("select mail as adresse_mail from utilisateur where mail = '$adresse'");
				if ($ad_newsletters[adresse_mail] != "")
					{
					$DB_site->query("update mails_newsletter set allow_email = '1' where  adresse_mail = '$adresse'");
					}	
				elseif ($ad_users[adresse_mail] != "") 
					{ 
					$DB_site->query("update utilisateur set allowemail = '1' where mail = '$adresse'");
					}
				else
					{
					$DB_site->query("INSERT INTO mails_newsletter (adresse_mail, allow_email) VALUES ('$adresse', '1')");
					}
				$error = "$multilangue[adresseajouteerecevranewsletter]" ;
				break ;
			}
			
		}
	}



if (substr($_SERVER[PHP_SELF], 0, 3) == "/V2") 
	{
	$scriptname=substr($_SERVER[PHP_SELF], 3);
	} 
else 
	{
	$scriptname=$_SERVER[PHP_SELF];
	}
	
$pagetitles=$DB_site->query_first(" SELECT $title FROM pagetitle WHERE scriptname='$scriptname' LIMIT 1");
if ($pagetitles[$title]!="") 
	{
	eval( "\$pagetitle = stripslashes(\"$pagetitles[$title]\");" );
	} 
else 
	{
	$pagetitle=${$title};
	}
$pagedescriptions=$DB_site->query_first(" SELECT $title FROM description WHERE scriptname='$scriptname' LIMIT 1");
if ($pagetitles[$title]!="") 
	{
	eval( "\$pagedescription = stripslashes(\"$pagedescriptions[$title]\");" );
	} 
else 
	{
	$pagedescription=${$title};
	}
$pagekeywordss=$DB_site->query_first(" SELECT $title FROM keywords WHERE scriptname='$scriptname' LIMIT 1");
if ($pagekeywordss[$title]!="") 
	{
	eval( "\$pagekeywords = stripslashes(\"$pagekeywordss[$title]\");" );
	} 
else 
	{
	$pagekeywords=${$title};
	}

// eval("\$framedroite=stripslashes(\"$templatecache[FrameDroiteSmall]\");");
eval( "\$TemplateNavigation = \"$templatecache[Navigation]\";" );
eval( "\$TemplateMenu = \"$templatecache[Menu]\";" );
eval( "\$TemplateHeader = \"$templatecache[Header]\";" );
eval( "\$TemplateFooter = \"$templatecache[Footer]\";" );

eval( "\$TemplateInscriptionNewsletterPage = stripslashes(\"$templatecache[InscriptionNewsletterPage]\");" );
echo $TemplateInscriptionNewsletterPage;
flush();

?>