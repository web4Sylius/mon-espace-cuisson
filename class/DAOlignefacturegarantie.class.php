<?php

// MUTLTISITE

class DAOlignefacturegarantie {

      public static function loadOne($DB,$lignefacturegarantieid) {
            $lfg=$DB->query_first("SELECT * FROM lignefacturegarantie WHERE lignefacturegarantie=".intval($lignefacturegarantieid));
            $lignefacturegarantie=new lignefacturegarantie($DB,$lfg['lignefacturegarantie'],$lfg['lignefactureid'],$lfg['garantieid'],$lfg['libelle'],$lfg['differenceprix'],$lfg['paht'],$lfg['ppi'],$lfg['duree'],$lfg['fournisseur'],$lfg['codeFournisseur'],$lfg['pays'],$lfg['dureeReelle'],$lfg['type']);
            return $lignefacturegarantie;
      }

      public static function loadLigneFactureGarantieByLigneFacEtGarantie($DB,$lignefactureid,$garantieid) {
            $lfg=$DB->query("SELECT * FROM lignefacturegarantie WHERE lignefactureid=".intval($lignefactureid)." AND garantieid=".intval($garantieid).";");
            if ($DB->num_rows($lfg) > 0) {
                  $lignefacturegarantie=new lignefacturegarantie($DB,$lfg['lignefacturegarantie'],$lfg['lignefactureid'],$lfg['garantieid'],$lfg['libelle'],$lfg['differenceprix'],$lfg['paht'],$lfg['ppi'],$lfg['duree'],$lfg['fournisseur'],$lfg['codeFournisseur'],$lfg['pays'],$lfg['dureeReelle'],$lfg['type']);
                  return $lignefacturegarantie;
            } else {
                  return false;
            }
            
      }
      /*
      public static function loadLigneFactureGarantieByLigneFacEtNumFac($DB,$lignefactureid,$factureid) {
            $lfg=$DB->query_first("SELECT lignefacturegarantieid FROM lignefacturegarantie lfg INNER JOIN lignefacture lf ON lfg.lignefactureid=lf.lignefactureid WHERE lf.lignefactureid=".intval($ignefactureid)." AND lf.factureid=".intval($factureid).";");
            return $lfg[0];
            
      }
      */
     
     //Insert la garantie dans une commande
     //MAJ 29/06 SM : $prix, $ppi et $paht ne sont jamais pris en compte, le prix n'étant plus personnalisable au produit depuis l'ajout des
     // nouveaux tarifs SFG, par tranches de prix
     public static function insert($DB,$site_code,$garantieid,$artid,$lignefactureid,$libelle,$duree,$fournisseur,$codeFournisseur,$pays,$dureeReelle,$type,$prix=null,$ppi=null,$paht=null) {

          $garantieid = intval($garantieid);
      
          $sql = "SELECT SUM(lfc.differenceprix)";
          $sql .= " FROM lignefacturecaracteristique lfc";
          $sql .= " INNER JOIN lignefacture lf ON (lfc.lignefactureid = lf.lignefactureid)";
          $sql .= " WHERE lfc.lignefactureid = '".$lignefactureid."'";
          $prixcaract = $DB->query_first($sql);
          $prixcarac= intval($prixcaract[0]);
      
          if(is_null($prix) && is_null($ppi) && is_null($paht)){
            $sql = "SELECT a.prix";
               $sql .= " FROM lignefacture lf INNER JOIN article a ON a.artid = lf.artid";
               $sql .= " WHERE lignefactureid = ".$lignefactureid;
               $tabLigneFacture = $DB->query_first($sql);
               $prixarticle=intval($tabLigneFacture['prix']);
               
               if($prixcarac != ""){
                  $prixarticle+=$prixcarac;
               }
               
               $tabGarantie = new garantie($DB,$site_code,'FR',$garantieid,$artid, $prixarticle, $lignefactureid);
            
               /*$sql = "SELECT gt.garantie_tranche_paht as paht, gt.garantie_tranche_prix as prix, gt.garantie_tranche_ppi as ppi";
               $sql .= " FROM article_garantie_liste agl";
               $sql .= " INNER JOIN garantie_tranche gt ON (gt.garantieid = agl.garantieid)";
               $sql .= " INNER JOIN lignefacture lf ON (lf.artid = agl.artid)";
               $sql .= " WHERE agl.artid = ".intval($artid);
               $sql .= " AND agl.garantieid = ".$garantieid;
               $sql .= " AND lf.lignefactureid = ".$lignefactureid;
               $sql .= " AND lf.prix BETWEEN gt.garantie_tranche_prix_mini AND gt.garantie_tranche_prix_maxi";

               $article_garantie = $DB->query_first($sql);

               if($article_garantie == "") return false;

               $prixsites = unserialize($article_garantie['prix']);
               $ppisites  = unserialize($article_garantie['ppi']);*/

               if($tabGarantie->prixsites === false){

                    $prix = $tabGarantie->prix;
                    $ppi  = $tabGarantie->ppi;

               }else{

                    $prix = $tabGarantie->prixsites[$site_code];
                    $ppi  = $tabGarantie->ppisites[$site_code];
               }
          
               $paht = $tabGarantie->paht;
            
          }
          
          $lignefactureid = intval($lignefactureid);
          $libelle        = stripslashes($libelle);
          $libelle        = strip_tags($libelle);
          $libelle        = addslashes($libelle);
          $type           = stripslashes($type);
          $type           = strip_tags($type);
          $type           = addslashes($type);
          $pays           = stripslashes($pays);
          $pays           = strip_tags($pays);
          $pays           = addslashes($pays);
          
          $sql = "INSERT INTO lignefacturegarantie SET";
          $sql .= " lignefactureid   = '".$lignefactureid."'";
          $sql .= ", garantieid      = '".$garantieid."'";
          $sql .= ", libelle         = '".$libelle."'";
          $sql .= ", differenceprix  = '".floatval($prix)."'";
          $sql .= ", paht            = '".floatval($paht)."'";
          $sql .= ", ppi             = '".floatval($ppi)."'";
          $sql .= ", duree           = '".$duree."'";
          $sql .= ", codeFournisseur = '".$codeFournisseur."'";
          $sql .= ", fournisseur     = '".$fournisseur."'";
          $sql .= ", pays            = '".$pays."'";
          $sql .= ", dureeReelle     = '".$dureeReelle."'";
          $sql .= ", type            = '".$type."'";
          
          return $DB->query($sql);
     }

      public static function insertDistinctPrix($DB,$garantieid,$artid,$prix,$lignefactureid,$libelle,$duree,$fournisseur,$codeFournisseur,$pays,$dureeReelle,$type) {
            $garantie=$DB->query_first("SELECT * FROM article_garantie_liste WHERE artid=".intval($artid)." AND garantieid=".intval($garantieid));
            if($garantie=="") {
                  $garantie=$DB->query_first("SELECT * FROM garantie WHERE garantieid=".intval($garantieid));
                  if($garantie=="") return false;
            }

            $garantieid=intval($garantieid);
            $lignefactureid=intval($lignefactureid);
            $prix=floatval($prix);
            $libelle=stripslashes($libelle);
            $libelle=strip_tags($libelle);
            $libelle=addslashes($libelle);
            $type=stripslashes($type);
            $type=strip_tags($type);
            $type=addslashes($type);
            $pays=stripslashes($pays);
            $pays=strip_tags($pays);
            $pays=addslashes($pays);

            return $DB->query("INSERT INTO lignefacturegarantie SET lignefactureid='".$lignefactureid."', garantieid='".$garantieid."',
              libelle='".$libelle."', differenceprix='".$prix."', paht='".$garantie['paht']."', ppi='".$garantie['ppi']."', duree='".$duree."',
              codeFournisseur='".$codeFournisseur."', fournisseur='".$fournisseur."', pays='".$pays."', dureeReelle='".$dureeReelle."', type='".$type."';");
      }

      public static function insertFourn($DB,$site_code,$artid,$lignefactureid,$pays,$libelle,$pro){

        $les_garanties = DAOgarantie::loadMaxGarantieArtByPays($DB,"base",'FR',$pays,$artid);
        $garFourn = array();
        foreach ($les_garanties as $la_garantie) {
          if($la_garantie->utilisation_garantie == 0 && $la_garantie->destinataire_garantie == intval($pro)) $garFourn [] = $la_garantie;
        }
        if(count($garFourn) > 1 ) return false;

        $garantieid=intval($garFourn[0]->garantieid);
        $lignefactureid=intval($lignefactureid);
        $prix=floatval($garFourn[0]->prix);
        $libelle=stripslashes($libelle);
        $libelle=strip_tags($libelle);
        $libelle=addslashes($libelle);
        $type=stripslashes($garFourn[0]->type_garantie);
        $type=strip_tags($type);
        $type=addslashes($type);
        $pays=stripslashes($pays);
        $pays=strip_tags($pays);
        $pays=addslashes($pays);
        $duree = $garFourn[0]->duree;
        $codeFournisseur = DAOfournisseur::loadCodeFournisseurByName($DB,$garFourn[0]->fournisseur);
        $fournisseur = $garFourn[0]->fournisseur;


        return $DB->query("INSERT INTO lignefacturegarantie SET lignefactureid='".$lignefactureid."', garantieid='".$garantieid."', libelle='".$libelle."', differenceprix='".$prix."', paht='".$garantie['paht']."', ppi='".$garantie['ppi']."', duree='".$duree."',codeFournisseur='".$codeFournisseur."', fournisseur='".$fournisseur."', pays='".$pays."', dureeReelle='".$duree."', type='".$type."';");
      }
}
