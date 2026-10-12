<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/global.php");

ini_set("max_input_time",300);
ini_set("max_upload_size","4M");

if(!isset($_GET['savid'])) exit;

$formstyle = "";

//Recherche toutes les commandes du client, avec statut "validée" au minimum, et qui n'ont pas de SAV en cours
$sql = "SELECT f.facturid, f.userid";
$sql .= " FROM facture f";
$sql .= " WHERE (";
$sql .= "   SELECT COUNT(id)";
$sql .= "   FROM sav";
$sql .= "   WHERE facturid = f.facturid";
$sql .= "   AND fin = 0";
$sql .= ") = 0";
$sql .= " AND userid = " . $user_info['userid'];
$sql .= " AND valide IN (1,5,7,8)";
$sql .= " ORDER BY heurefacture";
$facture = $DB_site->query_first($sql);

//Si l'utilisateur en cours ne correspond pas à celui enregistré dans la commande, on sort
if($facture['userid'] != $user_info['userid']) exit;

//Utilisé lors d'une suppression de fichier
if(isset($_POST['fid']))
{
    $sql = "DELETE FROM sav_files";
    $sql .= " WHERE id = " . intval($_POST['fid']);
    $sql .= " AND savid = " . intval($_GET['savid']);
    $DB_site->query($sql);
} 

$msg2style  = "style=\"display:none;\"";
$msg3       = $template->translate("/sav/envoyer_fichier","F")." :";
$acceptfile = array("jpg","jpeg","png");


if(isset($_FILES['sendfile']))
{
    $msg2style = "";
    $formstyle = "style=\"display:none;\"";
    $onload    = "setTimeout('delaimsg2()',2000);";
    $msg3      = $template->translate("/sav/envoi_autre_fichier","F")." :";
    $extension = strtolower(pathinfo($_FILES['sendfile']['name'], PATHINFO_EXTENSION));
    
    if(!in_array($extension,$acceptfile))
    {
        $msg2 = $template->translate("/sav/type_fichier_interdit","F");
    }
    else
    {
        //Référencement du fichier dans la BDD (et rattachement au SAV)
        $sql = "INSERT INTO sav_files SET";
        $sql .= " savid = '".$_GET['savid']."'";
        $sql .= ", date = ".time()."";
        $sql .= ", utilisateur = ''";
        $sql .= ", name = '".strip_tags($_FILES['sendfile']['name'])."'";
        $sql .= ", size = ".$_FILES['sendfile']['size']."";
        $sql .= ", ext = '$extension'";
        $DB_site->query($sql);
        $id = $DB_site->insert_id();
        
        $year=date("Y",time());
        //Mise à jour du statut du fichier, selon le résultat de l'upload
        if(!move_uploaded_file($_FILES['sendfile']['tmp_name'], "admin/sav/$year/" . $id . "." . $extension))
        {
            $msg2 = $template->translate("/sav/fichier_pas_envoye","F");
            
            $sql = "UPDATE sav_files SET";
            $sql .= " state = -1";
            $sql .= " WHERE id = " . $id;
            $DB_site->query($sql);
        }
        else
        {
            $sql = "UPDATE sav_files SET";
            $sql .= " state = 1";
            $sql .= " WHERE id = " . $id;
            $DB_site->query($sql);
            
            $msg2 = sprintf($template->translate("/sav/envoi_termine","F"),strip_tags($_FILES['sendfile']['name']));
        }
    }
}

$sql = "SELECT id, name";
$sql .= " FROM sav_files";
$sql .= " WHERE savid = " . $_GET['savid'];
$sql .= " AND date > " . (time() - 1200);
$requete = $DB_site->query($sql);
while($f = $DB_site->fetch_array($requete)) $fichiers .= "<span class=\"file\">" . $f['name'] . " <img src=\"" . SITE_URL . "/images/templates/red_cross.gif\" onclick=\"remove(" . $f['id'] . ");\" title=\"" . $template->translate("/sav/supprimer","F") . "\"></span>";

?>
<html>
    <head>
        <script language="javascript">
            function delaimsg2(){
                document.getElementById('msg2').style.display='none';
                document.forms.ulform.style.display='block';
            }
            function remove(fid){
                document.getElementsByName('fid')[0].value=fid;
                document.forms.del.submit();
            }
        </script>
        <style>
            body { font-size:12px; color:#363E42; margin:0; }
            form { margin:0; }
            #files { margin-top:8px; }
            .file { padding:2px; margin-right:5px; }
            .file img { cursor:pointer; }
        </style>
    </head>
    <body onload="<?php echo $onload; ?>">
        <form name="ulform" method="post" enctype="multipart/form-data" <?php echo $formstyle; ?>>
            <?php echo $msg3; ?>
            <br>
            <input type="file" name="sendfile" onchange="document.forms.ulform.style.display='none'; document.getElementById('msg').style.display='block'; document.forms.ulform.submit();">
        </form>
        <div id="msg2" <?php echo $msg2style; ?>><?php echo $msg2; ?></div>
        <div id="msg" style="display:none;"><?php echo $template->translate("/sav/envoi_en_cours","F"); ?> <img src="<?php echo SITE_URL; ?>/images/templates/ajax_wait.gif" width="16" align="absmiddle"></div>
        <div id="files"><?php echo $fichiers; ?></div>
        <form name="del" method="post">
            <input type="hidden" name="fid">
        </form>
    </body>
</html>