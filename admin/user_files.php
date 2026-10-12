<?php

include("admin_global.php");
include_once("../class/projet.class.php");

header("Cache-Control: no-cache");
header("Content-type:text/xml");

print "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
print "<xml>";


if(isset($_GET['savid'])){
	$filetable ="sav_files";
	$filename="sav";
}elseif(isset($_GET['projetid'])){
	$filetable ="projet_files";
	$filename = "projetfiles";
}else{
	$filetable ="user_files";
	$filename = "userfiles";
}

if(isset($_GET['fid'])){
	if(isset($_GET['delfile'])){
		$f = $DB_site2->query_first("SELECT id, ext, date FROM $filetable WHERE id = '".$_GET['fid']."'");
		$year = date("Y", $f['date']);
		unlink($filename."/".$year."/".$f['id'].".".$f['ext']);
   		$DB_site2->query("DELETE FROM $filetable WHERE id=".intval($_GET['fid'])." OR parent=".intval($_GET['fid']));
   	}
  if(isset($_GET['newname'])) $DB_site2->query("UPDATE $filetable SET name='".strip_tags($_GET['newname'])."' WHERE id=".intval($_GET['fid']));
}

if(isset($_GET['newfolder'])){
    if(isset($_GET['savid'])){
    	$DB_site2->query("INSERT INTO sav_files SET savid=".$_GET['savid'].", parent=".intval($_GET['parent']).", date=".time().", utilisateur='".$user_info['username']."', name='".$_GET['name']."', state=1");
    }elseif(isset($_GET['projetid'])){
    	$DB_site2->query("INSERT INTO projet_files SET projetid=".$_GET['projetid'].",userid='".$_GET['userid']."', parent=".intval($_GET['parent']).", date=".time().", utilisateur='".$user_info['username']."', name='".$_GET['name']."', state=1");
    }else{
    	$DB_site2->query("INSERT INTO user_files SET userid=".$_GET['userid'].", facturid=".intval($_GET['facturid']).", parent=".intval($_GET['parent']).", date=".time().", utilisateur='".$user_info['username']."', name='".$_GET['name']."', state=1");
    }
      
}

if(isset($_GET['moveto'])){
    if(preg_match("/(\d+)\/(\d+)\/(\d+)/",$_GET['moveto'],$d)){
        $DB_site2->query("UPDATE user_files SET userid=".intval($d[1]).", facturid=".intval($d[2]).", parent=".intval($d[3])." WHERE id=".intval($_GET['fid']));
    }
    
    if(preg_match("/projet(\d+)(\/([\d\/]+))?$/",$_GET['moveto'],$d)){
        if(isset($d[3])){
            $folders=explode("/",$d[3]);
            $folder=end($folders);
        }else
            $folder=0;
        $projet=new projet($d[1]);
        $projet->fichier_move($_GET['fid'],$folder);
    }
}

if($_GET['action']=="tree"){
    print str_replace(" & ", "&amp;",tree());
}else
    print str_replace(" & ", "&amp;",getfiles());


print "</xml>";

function getfiles(){

	global $DB_site2;
	$savtypes=array(0=>"transport-installation",1=>"panne",2=>"erreur produit",3=>"rétractation",4=>"manque produit");

    if(isset($_GET['userid'])){
        $user=$DB_site2->query_first("SELECT nom,prenom,raisonsociale  FROM utilisateur WHERE userid=".$_GET['userid']);
        $path="<path name=\"".$user['nom']." ".$user['prenom']."\" dataurl=\"user_files.php?userid=".$_GET['userid']."\" />";
    }
	$parent="";

	//Gère le cas des pj sav (pour avoir le bon url de retour)
	if(isset($_GET['savid']) && !isset($_GET['facturid'])){
		$f = $DB_site2->query_first("SELECT facturid FROM sav WHERE id ='".$_GET['savid']."'");
		$_GET['facturid'] = $f[0];
	}
	
    if(isset($_GET['userid'])) $filetype="user";
    if(isset($_GET['facturid'])) $filetype="facture";
    if(isset($_GET['projetid'])) $filetype="projet";
    
    switch($filetype){
        case "facture":
	
		$facturid = $_GET['facturid'];
		$f=$DB_site2->query_first("SELECT facturid,f.passefacture,valide FROM facture f WHERE facturid=".$_GET['facturid']);
		$path.="<path name=\"".$f['facturid']."\" dataurl=\"user_files.php?userid=".$_GET['userid']."&amp;facturid=".$f['facturid']."\" />";
    
		if(isset($_GET['savid'])){

			$savid = $_GET['savid'];

			if(isset($_GET['mailid'])){
				$ret .= fichierMail($DB_site2, $_GET['mailid'], $_GET['mail']);
				$parent="<parent id=\"".$savid."\" dataurl=\"user_files.php?userid=".$_GET['userid']."&amp;facturid=".$facturid."&amp;savid=".$savid."&amp;mail=1\" mod=\"r\" />";
			}elseif(isset($_GET['mail'])){ //SAV PJ mail

				$ret .= dossierMail($DB_site2, $_GET['userid'], 'sav', $savid,$user);
				//icone retour sav mail
				$parent="<parent id=\"".$savid."\" dataurl=\"user_files.php?userid=".$_GET['userid']."&amp;facturid=".$facturid."&amp;savid=".$savid."\" mod=\"r\" />";
				
				
			}else{

				if(isset($_GET['parent'])){

					$r=$DB_site2->query("SELECT * FROM sav_files WHERE parent='".$_GET['parent']."'");

					while($f=$DB_site2->fetch_array($r)){
						$year = date("Y",$f[date]);

						if($f['ext']==""){ //si dossier
							$count=$DB_site2->query_first("SELECT COUNT(*),SUM(size) FROM sav_files WHERE parent=".$f['id']);
							$ret.="<folder id=\"$f[id]\" dataurl=\"user_files.php?userid=".$_GET['userid']."&amp;savid=".$savid."&amp;parent=".$f['id']."\" name=\"".str_replace("&","&amp;",$f[name])."\" utilisateur=\"$f[utilisateur]\" date=\"$f[date]\" size=\"".$count[1]."\" mod=\"rw\" />";
						}else{// si fichier
							$ret.="<file id=\"$f[id]\" name=\"".str_replace("&","&amp;",$f[name])."\" path=\"sav/$year/$f[id].$f[ext]\" ext=\"$f[ext]\" utilisateur=\"$f[utilisateur]\" date=\"$f[date]\" size=\"$f[size]\" mod=\"rw\" />";
						}
					}

					$upload="<upload dataurl=\"sav2_ul.php?userid=".$_GET['userid']."&amp;savid=$savid&amp;parent=".$_GET['parent']."&amp;action=fb.load()\" />";
					//icone retour projet
					$parent="<parent id=\"".$savid."\" dataurl=\"user_files.php?userid=".$_GET['userid']."&amp;savid=$savid&amp;facturid=".$_GET['facturid']."\" utilisateur=\"\" date=\"".time()."\" mod=\"rw\" />";

				}else{
					//dossier pj sav par mail
					$ret.="<folder id=\"$savid\" dataurl=\"user_files.php?userid=".$_GET['userid']."&amp;facturid=".$_GET['facturid']."&amp;savid=".$savid."&amp;mail=1\" name=\"Mails\" mod=\"r\" />";

					$sav=$DB_site2->query_first("SELECT value AS type FROM sav_fields WHERE name='sav_typelitige' AND savid=".$_GET['savid']);
					$path.="<path name=\"SAV ".$savtypes[$sav['type']]."\" dataurl=\"user_files.php?userid=".$_GET['userid']."&amp;facturid=".$_GET['facturid']."&amp;savid=".$_GET['savid']."&amp;parent=0\" />";
					
					//Retour du Sav vers base commande
					$parent="<parent id=\"".$_GET['facturid']."\" dataurl=\"user_files.php?userid=".$_GET['userid']."&amp;facturid=".$_GET['facturid']."\" utilisateur=\"\" date=\"".time()."\" mod=\"rw\" />";
					$req=$DB_site2->query("SELECT * FROM sav_files WHERE savid=".$_GET['savid']." AND parent=".intval($_GET['parent']));
					while($file=$DB_site2->fetch_array($req)){
						$year=date("Y",$file['date']);
						if($file['ext']==""){
							$count=$DB_site2->query_first("SELECT COUNT(*),SUM(size) FROM sav_files WHERE parent=".$file['id']);
							$ret.="<folder id=\"$file[id]\" dataurl=\"user_files.php?userid=".$_GET['userid']."&amp;facturid=".$_GET['facturid']."&amp;savid=".$_GET['savid']."&amp;parent=".$file['id']."\" name=\"".str_replace("&","&amp;",$file[name])."\" utilisateur=\"$file[utilisateur]\" date=\"$file[date]\" size=\"".$count[1]."\" mod=\"rw\" />";
						}else{
							$ret.="<file id=\"".$file['id']."\" name=\"".$file['name']."\" path=\"sav/$year/".$file['id'].".".$file['ext']."\" ext=\"".$file['ext']."\" utilisateur=\"".$file['utilisateur']."\" date=\"".$file['date']."\" size=\"".$file['size']."\" mod=\"rw\" />";
						}
					}
				}				
			}
			
			$upload="<upload dataurl=\"sav2_ul.php?savid=".$_GET['savid']."&amp;parent=".intval($_GET['parent'])."&amp;action=fb.load()\" />";
			
		}else{//page commande

			if(isset($_GET['mail']) ){//Pj reçu par mail, on se trouve dans le dossier mail

				if(isset($_GET['mailid'])){
					//PJ mail_inbox
					$ret .= fichierMail($DB_site2, $_GET['mailid'], $_GET['mail']);
					//icone retour du dossier entrant page commande
					$parent="<parent id=\"".$facturid."\" dataurl=\"user_files.php?userid=".$_GET['userid']."&amp;facturid=$facturid&amp;mail=1\" mod=\"r\" />";
	
				}else{
					$ret .= dossierMail($DB_site2, $_GET['userid'], 'cde', $facturid,$user);
					//icone retour page commande dossier mail
					$parent="<parent id=\"".$facturid."\" dataurl=\"user_files.php?userid=".$_GET['userid']."&amp;facturid=$facturid\" mod=\"r\" />";
				}
				
				

			}else{

				if(isset($_GET['parent']) && $_GET['parent'] != 0){
					$p=$DB_site2->query_first("SELECT * FROM user_files WHERE id=".$_GET['parent']);
					$path.="<path name=\"".$p['name']."\" dataurl=\"user_files.php?userid=".$_GET['userid']."&amp;parent=".$p['id']."\" />";
					$parent="<parent id=\"".$p['parent']."\" dataurl=\"user_files.php?userid=".$_GET['userid']."&amp;facturid=".$_GET['facturid']."&amp;parent=".$p['parent']."\" utilisateur=\"\" date=\"".time()."\" mod=\"rw\" />";
				}else{
					//dossier SAV si il en existe un
					$req=$DB_site2->query("SELECT sav.id ,value AS type FROM sav INNER JOIN sav_fields on(sav.id=savid) WHERE name='sav_typelitige' AND facturid=".$f['facturid']);
					while($sav=$DB_site2->fetch_array($req)){
						$ret.="<folder id=\"".$sav['id']."\" name=\"SAV ".$sav['id']."\" dataurl=\"user_files.php?userid=".$_GET['userid']."&amp;facturid=".$f['facturid']."&amp;savid=".$sav['id']."\" img=\"images/sav.png\" utilisateur=\"\" date=\"".time()."\" size=\"0\" mod=\"ro\" />";
					}
					//Pj facture si commande validée ou expédiée
					if($f['valide']==5 || $f['valide']==8) $ret.="<file id=\"$f[facturid]\" name=\"Facture $f[facturid].pdf\" path=\"http://".$_SERVER['SERVER_NAME']."/facture2.php?facturid=$f[facturid]&amp;passe=$f[passefacture]&amp;nopub=1\" ext=\"pdf\" utilisateur=\"\" date=\"\" size=\"\" mod=\"ro\" />";
					//Bon de livraison BL
					if($f['valide']>0) $ret.="<file id=\"bl$f[facturid]\" name=\"BL $f[facturid].pdf\" path=\"http://".$_SERVER['SERVER_NAME']."/facture2.php?facturid=$f[facturid]&amp;passe=$f[passefacture]&amp;cde=1\" ext=\"pdf\" utilisateur=\"\" date=\"\" size=\"\" mod=\"ro\" />";
					//devis
					$p=strrev(md5($f['facturid']."devis"));
					$ret.="<file id=\"d$f[facturid]\" name=\"Devis $f[facturid].pdf\" path=\"http://".$_SERVER['SERVER_NAME']."/devis.php?devisid=$f[facturid]&amp;p=$p\" ext=\"pdf\" utilisateur=\"\" date=\"\" size=\"\" mod=\"ro\" />";
					//pro forma
					$ret.="<file id=\"pf$f[facturid]\" name=\"Facture pro forma $f[facturid].pdf\" path=\"http://".$_SERVER['SERVER_NAME']."/facture3.php?facturid=$f[facturid]&amp;passe=$f[passefacture]&amp;proforma=1\" ext=\"pdf\" utilisateur=\"\" date=\"\" size=\"\" mod=\"ro\" />";
		    		//contrat SFG si il y en a un
					$sfg=$DB_site2->query_first("SELECT facturid FROM sfg WHERE facturid=".$f['facturid']." AND receive>0");
					if($sfg[0]==$f['facturid']) $ret.="<file id=\"sfg$f[facturid]\" name=\"Contrat SFG $f[facturid].pdf\" path=\"sfg_pdf/$f[facturid].pdf\" ext=\"pdf\" utilisateur=\"\" date=\"\" size=\"\" mod=\"ro\" />";

					//Dossier de retour
					$parent="<parent id=\"".$_GET['facturid']."\" dataurl=\"user_files.php?userid=".$_GET['userid']."\" utilisateur=\"\" date=\"".time()."\" mod=\"rw\" />";
					//dossier mail page commande
					$ret.="<folder id=\"$facturid\" dataurl=\"user_files.php?userid=".$_GET['userid']."&amp;facturid=".$facturid."&amp;mail=1\" name=\"Mails\" mod=\"r\" />";
				}
		

				$req=$DB_site2->query("SELECT * FROM user_files WHERE userid=".$_GET['userid']." AND facturid=".$facturid." AND parent=".intval($_GET['parent']));
				while($file=$DB_site2->fetch_array($req)){
					$year=date("Y",$file['date']);
					if($file['ext']==""){
						$count=$DB_site2->query_first("SELECT COUNT(*),SUM(size) FROM user_files WHERE parent=".$file['id']);
						$ret.="<folder id=\"$file[id]\" dataurl=\"user_files.php?userid=".$_GET['userid']."&amp;facturid=".$_GET['facturid']."&amp;parent=".$file['id']."\" name=\"".str_replace("&","&amp;",$file[name])."\" utilisateur=\"$file[utilisateur]\" date=\"$file[date]\" size=\"".$count[1]."\" mod=\"rw\" />";
					}else{
						if(file_exists("userfiles/$year/".$file['id'].".".$file['ext'])){
							$path = "userfiles/$year/".$file['id'].".".$file['ext'];
						}else{
							$path = "userfiles/".$file['id'].".".$file['ext'];
						}
						$ret.="<file id=\"".$file['id']."\" name=\"".$file['name']."\" path=\"$path\" ext=\"".$file['ext']."\" utilisateur=\"".$file['utilisateur']."\" date=\"".$file['date']."\" size=\"".$file['size']."\" mod=\"rw\" />";
                    }
				
				}
				$upload="<upload dataurl=\"user_ul.php?userid=".$_GET['userid']."&amp;facturid=".$_GET['facturid']."&amp;parent=".intval($_GET['parent'])."&amp;action=fb.load()\" />";
			}
			
		}
        break;
        
        
		case "user":
		//Page utilisateur

        $userid = $_GET['userid'];
    
    	//affichage 1er niveau

        if(isset($_GET['mailid'])){
            $ret .=fichierMail($DB_site2, $_GET['mailid'], $_GET['mail']);
            $parent="<parent id=\"".$userid."\" dataurl=\"user_files.php?userid=".$userid."&amp;mail=1\" utilisateur=\"\" date=\"".time()."\" mod=\"rw\" />";
        }elseif(isset($_GET['mail'])){		

            $ret .=	dossierMail($DB_site2,$userid,'user','',$user);
            $parent="<parent id=\"".$userid."\" dataurl=\"user_files.php?userid=".$userid."\" utilisateur=\"\" date=\"".time()."\" mod=\"rw\" />";
            
            

        }else{

            if(intval($_GET['parent'])==0){
                $req=$DB_site2->query("SELECT facturid,valide FROM facture WHERE userid=".$_GET['userid']);
                while($facture=$DB_site2->fetch_array($req))
                    $ret.="<folder id=\"".$facture['facturid']."\" name=\"".($facture['valide']==0?"Devis ":"CDE ").$facture['facturid']."\" dataurl=\"user_files.php?userid=".$_GET['userid']."&amp;facturid=".$facture['facturid']."\" img=\"images/".($facture['valide']==0?"devis":"panier").".png\" utilisateur=\"\" date=\"".time()."\" size=\"0\" mod=\"ro\" />";
                    $parent="<parent id=\"".$_GET['facturid']."\" utilisateur=\"\" date=\"".time()."\" mod=\"rw\" />";
            }else{
                $p=$DB_site2->query_first("SELECT * FROM user_files WHERE id=".$_GET['parent']);
                $path.="<path name=\"".$p['name']."\" dataurl=\"user_files.php?userid=".$_GET['userid']."&amp;parent=".$p['id']."\" />";
                $parent="<parent id=\"".$p['parent']."\" dataurl=\"user_files.php?userid=".$_GET['userid']."&amp;parent=".$p['parent']."\" utilisateur=\"\" date=\"".time()."\" mod=\"rw\" />";
            }

            $r=$DB_site2->query("SELECT * FROM user_files WHERE userid=".$_GET['userid']." AND facturid=0 AND parent=".intval($_GET['parent']));
            while($f=$DB_site2->fetch_array($r)){
                $year=date("Y",$f['date']);
                if($f['ext']==""){
                    $count=$DB_site2->query_first("SELECT COUNT(*),SUM(size) FROM user_files WHERE parent=".$f['id']);
                    $ret.="<folder id=\"$f[id]\" dataurl=\"user_files.php?userid=".$_GET['userid']."&amp;parent=".$f['id']."\" name=\"".str_replace("&","&amp;",$f[name])."\" utilisateur=\"$f[utilisateur]\" date=\"$f[date]\" size=\"".$count[1]."\" mod=\"rw\" />";
                }else{
                    if(file_exists("userfiles/$year/$f[id].$f[ext]")){
                        $path = "userfiles/$year/$f[id].$f[ext]";
                    }else{
                        $path = "userfiles/$f[id].$f[ext]";
                    }
                    $ret.="<file id=\"$f[id]\" name=\"".str_replace("&","&amp;",$f[name])."\" path=\"$path\" ext=\"$f[ext]\" utilisateur=\"$f[utilisateur]\" date=\"$f[date]\" size=\"$f[size]\" mod=\"rw\" />";
                }
            }
            
            if(intval($_GET['parent'])==0){
                $projets=new projets();
                foreach($projets->utilisateur($userid) as $projet){
                    $name = $projet->libelle." (PJT ".$projet->projetid.")";
                    $ret.="<folder id=\"".$projet->projetid."\" dataurl=\"user_files.php?userid=".$userid."&amp;projetid=".$projet->projetid."\" name=\"".$name."\" date=\"".$projet->date->gettimestamp()."\" mod=\"r\" />";			
                }
            }

            //dossier pj user par mail
            $ret.="<folder id=\"$userid\" dataurl=\"user_files.php?userid=".$userid."&amp;mail=1\" name=\"Mails\" mod=\"r\" />";

            $upload="<upload dataurl=\"user_ul.php?userid=".$_GET['userid']."&amp;parent=".intval($_GET['parent'])."&amp;action=fb.load()\" />";
        }
        break;
        
        case "projet":
        //PJ projet

        $projetid = $_GET['projetid'];
        $projet=new projet($projetid);
        
        if(!isset($_GET['parent'])) $_GET['parent']=0;
        $urluserid="";
        if(isset($_GET['userid'])) $urluserid="&amp;userid=".$_GET['userid'];
        $url="user_files.php?projetid=".$projet->projetid;
        
        $path.="<path name=\"".htmlspecialchars($projet->libelle)."\" dataurl=\"".$url.$urluserid."\" />";

        if(isset($_GET['mailid'])){
            $ret .= fichierMail($DB_site2,$_GET['mailid'], $_GET['mail']);
            $parent="<parent id=\"$projetid\" dataurl=\"user_files.php?".$urluserid."&amp;projetid=".$projetid."&amp;mail=1\" mod=\"r\" />";

        }elseif(isset($_GET['mail'])){ //PJ liées au mails

            $ret .=dossierMail($DB_site2,$userid,'pjt',$projetid,$user);
            //icone retour user mail
            $parent="<parent id=\"$projetid\" dataurl=\"user_files.php?".$urluserid."&amp;projetid=".$projetid."\" mod=\"r\" />";

        }else{

            //dossier dans le dossier du projet
            foreach($projet->fichiers($_GET['parent']) as $fichier){
                $year = date("Y",$fichier['date']);

                if($fichier['ext']==""){ //si dossier
                    $count=$DB_site2->query_first("SELECT COUNT(*),SUM(size) FROM projet_files WHERE parent=".$fichier['id']);
                    $ret.="<folder id=\"$fichier[id]\" dataurl=\"user_files.php?".$urluserid."&amp;projetid=".$projetid."&amp;parent=".$fichier['id']."\" name=\"".str_replace("&","&amp;",$fichier['name'])."\" utilisateur=\"$fichier[utilisateur]\" date=\"$fichier[date]\" size=\"".$count[1]."\" mod=\"rw\" />";
                }else{// si fichier
                    $ret.="<file id=\"$fichier[id]\" name=\"".str_replace("&","&amp;",$fichier['name'])."\" path=\"projetfiles/$year/$fichier[id].$fichier[ext]\" ext=\"$fichier[ext]\" utilisateur=\"$fichier[utilisateur]\" date=\"$fichier[date]\" size=\"$fichier[size]\" mod=\"rw\" />";
                }
            }

            $upload="<upload dataurl=\"projet_ul.php?".$urluserid."&amp;projetid=".$projetid."&amp;parent=".$_GET['parent']."&amp;action=fb.load()\" />";
            //icone retour projet
            if($_GET['parent']==0){
                if(isset($_GET['userid']))
                    $parent="<parent id=\"".$projetid."\" dataurl=\"user_files.php?".$urluserid."\" utilisateur=\"\" date=\"".time()."\" mod=\"rw\" />";
                else
                    $parent="<parent id=\"".$projetid."\" utilisateur=\"\" date=\"".time()."\" mod=\"rw\" />";
                
                if(dossierMail($DB_site2,$userid,'pjt',$projetid,$user)!="")
                    $ret.="<folder id=\"".$projetid."\" dataurl=\"user_files.php?".$urluserid."&amp;projetid=".$projetid."&amp;mail=1\" name=\"Mails\" mod=\"r\" />";
            }else{
                $apath=array();
                $parentloop=$_GET['parent'];
                while($parentloop>0){
                    $p=$DB_site2->query_first("SELECT * FROM projet_files WHERE id=".$parentloop);
                    if($parent=="")
                        $parent="<parent id=\"".$projetid."\" dataurl=\"user_files.php?".$urluserid."&amp;projetid=$projetid&amp;parent=".$p['parent']."\" utilisateur=\"\" date=\"".time()."\" mod=\"rw\" />";
                    $apath[]="<path name=\"".$p['name']."\" dataurl=\"user_files.php?".$urluserid."&amp;projetid=$projetid&amp;parent=$p[id]\" />";
                    $parentloop=$p['parent'];
                }
                $path.=implode("",array_reverse($apath));
            }
        }
    }

	$ret.=$path.$parent.$upload;
	return $ret;
}

function tree(){
    global $DB_site2;
    
    if(isset($_GET['projetid'])){
        $projet=new projet($_GET['projetid']);
        $ret="<folder id=\"projet".$projet->projetid."\" name=\"".$projet->libelle."\" img=\"\" utilisateur=\"\" date=\"".time()."\" size=\"0\" mod=\"ro\" parent=\"-1\" />";
        $ret.=xmldossier("projet".$projet->projetid,$projet->dossiers());

    }elseif(isset($_GET['userid'])){
        $user=$DB_site2->query_first("SELECT userid,nom,prenom FROM utilisateur WHERE userid=".$_GET['userid']);
        
        $ret="<folder id=\"".$user['userid']."/0/0\" name=\"".$user['nom']." ".$user['prenom']."\" parent=\"-1\" />";
        $req=$DB_site2->query("SELECT facturid,valide FROM facture WHERE userid=".$_GET['userid']);
        while($facture=$DB_site2->fetch_array($req)){
            $ret.="<folder id=\"".$user['userid']."/".$facture['facturid']."/0\" name=\"".($facture['valide']==0?"Devis ":"Commande ").$facture['facturid']."\" img=\"images/".($facture['valide']==0?"devis":"panier").".png\" utilisateur=\"\" date=\"".time()."\" size=\"0\" mod=\"ro\" parent=\"".$user['userid']."/0/0\" />";
            $req2=$DB_site2->query("SELECT * FROM user_files WHERE userid=".$_GET['userid']." AND facturid=".$facture['facturid']." AND ext='' ORDER BY name");
            while($folder=$DB_site2->fetch_array($req2)){
                $ret.="<folder id=\"".$user['userid']."/".$facture['facturid']."/".$folder['id']."\" name=\"".$folder['name']."\" img=\"\" utilisateur=\"\" date=\"".time()."\" size=\"0\" mod=\"ro\" parent=\"".$user['userid']."/".$facture['facturid']."/0\" />";
            }
        }
    }
    
    return $ret;
}

function xmldossier($parents,$dossiers){
    $ret="";
    foreach($dossiers as $dossier){
        $ret.="<folder id=\"".$parents."/".$dossier['id']."\" name=\"".$dossier['name']."\" img=\"\" utilisateur=\"\" date=\"".time()."\" size=\"0\" mod=\"ro\" parent=\"".$parents."\" />";
        if(isset($dossier['subfolders'])) $ret.=xmldossier($parents."/".$dossier['id'],$dossier['subfolders']);
    }
    return $ret;
}

function dossierMail($DB,$userid,$type,$typeid,$user){
	$ret = "";
	$folder="";

	switch ($type) {
		case 'cde':
			$sqlwhere = " AND facturid ='".$typeid."' AND savid=0 AND projetid=0";
			$folder="facturid";
			$paramurl="userid=".$_GET['userid'];
			break;
		case 'pjt':
			$sqlwhere = " AND projetid='".$typeid."' AND savid=0 AND facturid=0";
			$folder="projetid";
			$paramurl="&amp;projetid=".$typeid;
			if(isset($_GET['userid'])) $paramurl.="&amp;userid=".$_GET['userid'];
			break;
		case 'sav':
			$sqlwhere = " AND savid='".$typeid."' AND projetid=0";
			$folder="savid";
			$paramurl="userid=".$_GET['userid'];
			break;
		case 'user':
			$sqlwhere = " AND savid=0 AND facturid=0 AND projetid=0 AND userid=$userid";
			$folder="";
            $paramurl="userid=".$_GET['userid'];
			break;			
	}
	
	if($folder != "") $folder = "&amp;$folder=$typeid";

	$req=$DB->query("SELECT * FROM mail_inbox WHERE 1".$sqlwhere." ORDER BY date DESC");
	while($mail = $DB->fetch_array($req)){
		$year = date("Y", $mail['date']);
		$date = date("d/m/Y-H:i", $mail['date']);
		$req2 = $DB->query_first("SELECT COUNT(*) FROM mail_attachment WHERE moyen = 'mail_in' AND mailid ='".$mail['id']."'");
		if($req2[0] > 0){
			$name = $date."-Entrant-".mail_expediteur($DB,$mail['from']);
			$ret.="<folder id=\"".$mail['id']."\" dataurl=\"user_files.php?".$paramurl.$folder."&amp;mail=in&amp;mailid=".$mail['id']."\" name=\"$name\" mod=\"r\" />";
		}
	}
	$req=$DB->query("SELECT * FROM mail_outbox WHERE 1".$sqlwhere." ORDER BY date DESC");
	while($mail = $DB->fetch_array($req)){
		$year = date("Y", $mail['date']);
		$date = date("d/m/Y-H:i", $mail['date']);
		$req2 = $DB->query_first("SELECT COUNT(*) FROM mail_attachment WHERE moyen = 'mail_out' AND mailid ='".$mail['id']."'");
		if($req2[0] > 0){
			$name = $date."-Sortant-".mail_expediteur($DB,$mail['from']);
			$ret.="<folder id=\"".$mail['id']."\" dataurl=\"user_files.php?".$paramurl.$folder."&amp;mail=out&amp;mailid=".$mail['id']."\" name=\"$name\" mod=\"r\" />";
		}
	}

	return $ret;
}

function fichierMail($DB,$mailid,$type){
	global $rootpath;
	global $host;

	$ret="";
	switch ($type) {
		case 'in':
			$table = "mail_inbox";
			$moyen = "mail_in";
			break;
		case 'out':
			$table = "mail_outbox";
			$moyen = "mail_out";
			break;
		default:
			$table = "mail_inbox";
			$moyen = "mail_in";
			break;
	}

	$req = $DB->query("SELECT ma.*, m.utilisateur, m.date FROM mail_attachment as ma INNER JOIN $table as m ON m.id=ma.mailid  WHERE ma.moyen = '$moyen' AND ma.mailid ='$mailid'");
	while($pj = $DB->fetch_array($req)) {
		$year = date("Y", $pj['date']);					
		$ret.="<file id=\"".$pj['id']."\" name=\"".$pj['name']."\" path=\"/configurations/".$host."/mails/attachments/".$year."/".$pj['id'].".".$pj['ext']."\" ext=\"".$pj['ext']."\" utilisateur=\"".$pj['utilisateur']."\" date=\"".$mail_in['date']."\" size=\"".$pj['size']."\" mod=\"rw\" />";
	}

	return $ret;
}

function mail_expediteur($DB,$exp){
	$user = $DB->query_first("SELECT nom, prenom, raisonsociale FROM utilisateur WHERE mail='$exp'");
	if(!is_null($user[0])){

		if($user['raisonsociale'] != "")
			return $user['raisonsociale'];
		else
			return $user['nom'];
	}else{

		$fourn = $DB->query_first("SELECT societe FROM fournisseur WHERE mail='$exp' LIMIT 1");
		if(!is_null($fourn[0])) return $fourn['societe'];

	}

	return "John Doe";

}

?>
