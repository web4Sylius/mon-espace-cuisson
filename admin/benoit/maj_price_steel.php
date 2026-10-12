<?php
$document_root = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING);
$admin_username = filter_input(INPUT_COOKIE, 'admin_username', FILTER_SANITIZE_STRING);
if(empty($admin_username)){
     $admin_username = filter_input(INPUT_GET, 'admin_username', FILTER_SANITIZE_STRING);
}

ini_set("display_errors","on");

require_once($document_root.'/configurations/www.mon-espace-cuisson.com/config_mysql.php');
require_once($document_root.'/class/mysql.php');

$DB_site=new DB_Sql;
$DB_site->appname='boutique';
$DB_site->appshortname='boutique';
$DB_site->database=$DBname;
$DB_site->server=$db_server;
$DB_site->user=$DBusername;
$DB_site->password=$DBpassword;
$DB_site->connect();

require_once($document_root.'/admin/export/xlexport.php');

if (isset($admin_username) && $admin_username != "") {
     if($admin_username != "9d3bb895f22bf0afa958d68c2a58ded7"){
          //Validation d'identité avant autorisation de téléchargement
          $sql = "SELECT *";
          $sql .= " FROM acces_admin";
          $sql .= " WHERE user='".$admin_username."'";
          $sql .= " AND connecte = 1";
          $sql .= " ORDER BY timestamp DESC";
          $sql .= " LIMIT 0,1";
          $acces = $DB_site->query_first($sql);
          
          if($acces['timestamp']==false || $acces['timestamp']<time()-3600){
               exit;
          }else{
               $sql = "SELECT *";
               $sql .= " FROM parametresgeneraux";
               $sql .= " WHERE username = '".$admin_username."'";
               $user_info = $DB_site->query_first($sql);
               if($user_info[username] != $admin_username){
                    exit;
               }
          }
     }
}else{
     exit;
}


// récupération du fichier CSV
$row = 1;
if (($handle = fopen("./steel.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
        $num = count($data);
        // echo "$num fields in line $row:<br>\n";
        $row++;
       // for ($c=0; $c < $num; $c++) {
       //     echo $data[$c] . "<br />\n";
       // }
		$article=$DB_site->query_first("SELECT * FROM article WHERE artcode_eurocave LIKE '".$data[0]."' ");
		if($article[artcode_eurocave]==$data[0]){
			echo "article trouvé : ".$data[0]."<br>";
			echo "Prix vente TTC : $article[prix] € -> ".ceil($data[1]*1.2)." €<br>";
			echo "Prix vente HT : ".$article[prixht]." € -> ".$data[1]." €<br>";
			echo "Prix achat HT : $article[paht] € -> ".($data[1]/2)." €<br>";
			/*
			$DB_site->query("UPDATE article_fournisseur
							SET
							prix=".($data[1]/2)."
							WHERE artid = '".$article[artid]."' ");
			$DB_site->query("UPDATE article_site
							SET
							prix=".ceil($data[1]*1.2).",
							prixconseilleht=".$data[1]."
							WHERE artid = '".$article[artid]."' ");
			
			$DB_site->query("UPDATE article
							SET
							prixconseilleht=".$data[1]."
							WHERE artid = '".$article[artid]."' ");
			$DB_site->query("UPDATE article
							SET
							prix=".ceil($data[1]*1.2).",
							prixht=".$data[1].",
							paht=".($data[1]/2)."
							WHERE artid = '".$article[artid]."' ");
			
			$DB_site->query("INSERT INTO `article_history`
							(`artid`, `utilisateur`, `date`, `key`, `value`)
							VALUES
							('".$article[artid]."', 'benoit', '".time()."', 'paht', '".($data[1]/2)."') ");
			$DB_site->query("INSERT INTO `article_history`
							(`artid`, `utilisateur`, `date`, `key`, `value`)
							VALUES
							('".$article[artid]."', 'benoit', '".time()."', 'prixconseille', '".ceil($data[1]*1.2)."') ");
			*/
			echo "<br><br>";
		} else {
			echo "article non trouvé : $data[0]<br>";
		}
    }
    fclose($handle);
}




?>