<?php


// header('Content-type: text/html; charset=iso-8859-1'); 
require ("global.php");
$txt=htmlentities($_GET['txt']);
// $txt=addslashes($txt);
$site_code=$template->site_code();

if(strpos($txt," ")>0)
  $words=explode(" ",$txt);
else
  $words=array($txt);

if($txt === "wineart") {
  $txt = "wine art";
}

$marque=false;

$where="";
$nbword=0;
// $nbword permet de ne pas effectuer la recherche s'il n'y a que des mots avec 3 lettres, ou moins, en mot clé
$words=explode(" ",htmlentities(html_entity_decode($txt)));
foreach($words as $word) {

 if(strlen($word)>3 || $word === 'vin'){
  $where .= " AND (m.libelle LIKE '%".mysqli_real_escape_string($DB_site->link_id,$word)."%' OR a.libelle".$template->langue." LIKE '%".mysqli_real_escape_string($DB_site->link_id,$word)."%' OR a.artcode LIKE '%".mysqli_real_escape_string($DB_site->link_id,$word)."%' OR a.refs_fabricant LIKE '%".mysqli_real_escape_string($DB_site->link_id,$word)."%' OR a.artcode_eurocave LIKE '%".mysqli_real_escape_string($DB_site->link_id,$word)."%' OR a.gamme".$template->langue." LIKE '%".mysqli_real_escape_string($DB_site->link_id,$word)."%')";
  $nbword++;
  $marque=true;
}
}


// Résultats - MARQUES -

$filtermarque=array();
$marquelibelle=array();


$compteur = 1;
$r=$DB_site->query("SELECT m.libelle,m.marqueid,m.image,m.type".$template->langue.",m.url".$template->langue." FROM marque m INNER JOIN article_marque am ON m.marqueid = am.marqueid INNER JOIN article a ON a.artid = am.artid WHERE 1 $where AND a.active = '1' AND m.marqueid<>18 GROUP BY m.libelle LIMIT 6");

$nbmarque = $DB_site->num_rows($r);
if($DB_site->num_rows($r)==0 || $nbword == 0){
  print "<div class='flex-result-search'><div class='flex-bloc'><div class='bloc-marque'><div class=\"cat\"><strong>".$template->translate("/categorie/marque","F")."</strong><span class=\"line\"></span></div><div class='flex-marque'>".$template->translate("/categorie/pas_resultat")."</div></div>";
}
if($DB_site->num_rows($r)>0 && $nbword != 0) {
  print "<div class='flex-result-search'><div class='flex-bloc'><div class='bloc-marque'><div class=\"cat\"><strong>".$template->translate("/categorie/marque","F")."</strong><span class=\"line\"></span></div><div class='flex-marque'>";

  while($list=$DB_site->fetch_array($r)){

    if($list[4]=="") $list[4]=$list[0];
    print "<div class=\"item item-marques item-nb".$compteur."\" style=\"text-align: center;\"><a class=\"marque\" href=\"/".url_rewrite($list[4])."-".$list[1].".htm\" onclick=\"saveMotCle('".$txt."')\"><img src=\"/marques/$list[1]_99_25.jpg\" alt='".$list['libelle']."'></a></div>";
    $filtermarque[]=mysqli_real_escape_string($DB_site->link_id,$list[1]);
    $marquelibelle[$list[1]]=$list[0];
      //$txt=str_ireplace($list[0],"",$txt);

    $compteur++;
  }
  if($nbmarque <2) {
    print "</div>
    <div class='voir_tout toutes_marques'><a href='/recherche-rapide.php?action=".implode('%20', $words)."&actif=marques'>".$template->translate("/recherche/voir_la","F") ." ". $template->translate("/categorie/filtres/marque","F")." <img src='/configurations/www.mon-espace-cuisson.com/images/templates/fleche-blanche.png' alt='fleche'/></a></div>
    </div>";
  }
  else {

    print "</div>
    <div class='voir_tout toutes_marques'><a href='/recherche-rapide.php?action=".implode('%20', $words)."&actif=marques'>".$template->translate("/recherche/voir_les","F") ." ". $nbmarque ." ". $template->translate("/admin/menu/marques","F")." <img src='/configurations/www.mon-espace-cuisson.com/images/templates/fleche-blanche.png' alt='fleche'/></a></div>
    </div>";
  }
  $txt=trim($txt);
}


if(strpos($txt," ")>0)
  $sp=explode(" ",mysqli_real_escape_string($DB_site->link_id,$txt));
else
  $sp=array(mysqli_real_escape_string($DB_site->link_id,$txt));

$catids=array();
$artids=array();
$marquesql="";
if(count($filtermarque)>0)
	$marquesql=" AND am.marqueid IN ('".implode("','",$filtermarque)."')";
$sql="SELECT c.catid,c.libelle".$template->langue.",c.parentid,a.artid
FROM article a
INNER JOIN categorie c USING(catid)
INNER JOIN article_marque am ON(a.artid=am.artid)
INNER JOIN article_site AS a_s ON(a.artid=a_s.artid)
WHERE ((a.libelle".$template->langue." LIKE '%".implode("%' AND a.libelle".$template->langue." LIKE '%",$sp)."%') OR (a.gamme".$template->langue." LIKE '%".implode("%' AND a.libelle".$template->langue." LIKE '%",$sp)."%'))".$marquesql."
AND a_s.active    = 1
AND a_s.site_code = '".$template->site_code()."'
AND a.pro IN ('".($sitepro?2:1)."','3')
";


$r=$DB_site->query($sql);


while($list=$DB_site->fetch_array($r)){
  if($list[2]==0) $list[2]=$list[0];
  $catids[$list[2]][$list[0]]=$list[1];
  $artids[]=$list[3];
}


$where="";
$or="";
$words=explode(" ",html_entity_decode($txt));
foreach($words as $word){
 if(strlen($word)>3 || $word === 'vin'){

  $where .= " AND (c.libelle".$template->langue." LIKE '%".mysqli_real_escape_string($DB_site->link_id,$word)."%' OR a.libelle".$template->langue." LIKE '%".mysqli_real_escape_string($DB_site->link_id,$word)."%' OR m.libelle LIKE '%".mysqli_real_escape_string($DB_site->link_id,$word)."%' OR a.artcode LIKE '%".mysqli_real_escape_string($DB_site->link_id,$word)."%' OR a.refs_fabricant LIKE '%".mysqli_real_escape_string($DB_site->link_id,$word)."%' OR a.artcode_eurocave LIKE '%".mysqli_real_escape_string($DB_site->link_id,$word)."%' OR a.gamme".$template->langue." LIKE '%".mysqli_real_escape_string($DB_site->link_id,$word)."%' AND a.active = '1')";
}
}


// Résultats - CATEGORIES -
$marqueids="";
$compteur = 1;

$reqmarque=$DB_site->query_first("SELECT libelle FROM marque WHERE libelle = '".mysqli_real_escape_string($DB_site->link_id,$word)."'");

$req=$DB_site->query("SELECT c.libelle".$template->langue." AS 'libelle',c.catid,c.url".$template->langue." AS 'url', m.marqueid AS 'idmarque' FROM categorie c INNER JOIN article a ON c.catid = a.catid INNER JOIN article_marque am ON am.artid = a.artid INNER JOIN marque m ON m.marqueid = am.marqueid WHERE 1 $where AND a.active = '1' GROUP BY catid ORDER BY SUM(a.ventes) DESC");

if($DB_site->num_rows($req)==0 || $nbword == 0){
  print "<div class='bloc-cat'><div class=\"cat\"><strong>".$template->translate("/categorie/categories","F")."</strong><span class=\"line\"></span></div><div class='flex-cat'>".$template->translate("/categorie/pas_resultat")."</div></div></div>";
}
if($DB_site->num_rows($req)>0 && $nbword != 0){
  print "<div class='bloc-cat'><div class=\"cat\"><strong>".$template->translate("/categorie/categories","F")."</strong><span class=\"line\"></span></div><div class='flex-cat'>";

  $nbcat = $DB_site->num_rows($req);

  while($list=$DB_site->fetch_array($req)){
   if($compteur != 7) {
    $categorie = new categorie($DB_site,$list['catid'],$template->langue);

    if($reqmarque != '') { //si le mot clé est une marque, on met en param la marque dans le lien de la catégorie
      $url = "categorie.php?catid=".$list['catid']."&marqueids=".$list['idmarque'];
    }
    else {
      $url = $template->mkurl(""==""?"categorie":str_replace("_","",""),$categorie->catid,$categorie);
    }
    
    print "<div class=\"item cat-recherche item-nb".$compteur."\"><a href=\"".$url."\" onclick=\"saveMotCle('".$txt."')\">".$list['libelle']."</a></div>";
    $compteur++;

  }
}
if($nbcat < 2) {
  print"</div><div class='voir_tout'><a href='/recherche-rapide.php?action=".implode('%20', $words)."&actif=cats'>".$template->translate("/recherche/voir_la","F") ." categorie <img src='/configurations/www.mon-espace-cuisson.com/images/templates/fleche-blanche.png' alt='fleche'/></a></div></div></div>";
}
else {
  print"</div><div class='voir_tout'><a href='/recherche-rapide.php?action=".implode('%20', $words)."&actif=cats'>".$template->translate("/recherche/voir_les","F") ." ". $nbcat ." categories <img src='/configurations/www.mon-espace-cuisson.com/images/templates/fleche-blanche.png' alt='fleche'/></a></div></div></div>";
}
}


$wheremarque=$whereartid="";
$ref="artcode";
if(count($artids)>0)
	$whereartid="AND a.artid IN (".implode(",",$artids).")";
if(count($filtermarque)>0)
	$wheremarque="AND marqueid IN (".implode(",",$filtermarque).")";
$r=$DB_site->query("SELECT libelle".$template->langue.",artcode,a.artid,a.prix,commandable,artcode_eurocave,refs_fabricant,m.libelle AS marque FROM article a
  INNER JOIN article_marque ON(a.artid=article_marque.artid)
  INNER JOIN marque m USING(marqueid)
  INNER JOIN article_site AS a_s ON(a.artid=a_s.artid)
  WHERE (artcode LIKE '%".mysqli_real_escape_string($DB_site->link_id,$txt)."%')
  AND a_s.active = 1
  AND a_s.site_code = '".$template->site_code()."'
  AND pro IN ('".($sitepro?2:1)."','3')
  $wheremarque
  GROUP BY artcode
  ORDER BY a.ventes DESC LIMIT 6");
$rtotal=$DB_site->query("SELECT libelle".$template->langue.",artcode,a.artid,a.prix,commandable,artcode_eurocave,refs_fabricant,m.libelle AS marque FROM article a
  INNER JOIN article_marque ON(a.artid=article_marque.artid)
  INNER JOIN marque m USING(marqueid)
  INNER JOIN article_site AS a_s ON(a.artid=a_s.artid)
  WHERE (artcode LIKE '%".mysqli_real_escape_string($DB_site->link_id,$txt)."%')
  AND a_s.active = 1
  AND a_s.site_code = '".$template->site_code()."'
  AND pro IN ('".($sitepro?2:1)."','3')
  $wheremarque
  GROUP BY artcode
  ORDER BY a.ventes DESC");

if($DB_site->num_rows($r)==0){
  $ref="artcode_eurocave";
  $ref="refs_fabricant";
  $r=$DB_site->query("SELECT libelle".$template->langue.",artcode,a.artid,a.prix,commandable,artcode_eurocave,refs_fabricant,m.libelle AS marque FROM article a
   INNER JOIN article_marque ON(a.artid=article_marque.artid)
   INNER JOIN marque m USING(marqueid)
   INNER JOIN article_site AS a_s ON(a.artid=a_s.artid)
   WHERE (refs_fabricant LIKE '%".mysqli_real_escape_string($DB_site->link_id,$txt)."%' OR artcode_eurocave LIKE '%".mysqli_real_escape_string($DB_site->link_id,$txt)."%')
   AND a_s.active = 1
   AND a_s.site_code = '".$template->site_code()."'
   AND pro IN ('".($sitepro?2:1)."','3')
   $wheremarque
   GROUP BY artcode
   ORDER BY a.ventes DESC
   LIMIT 6");
  $rtotal=$DB_site->query("SELECT libelle".$template->langue.",artcode,a.artid,a.prix,commandable,artcode_eurocave,refs_fabricant,m.libelle AS marque FROM article a
    INNER JOIN article_marque ON(a.artid=article_marque.artid)
    INNER JOIN marque m USING(marqueid)
    INNER JOIN article_site AS a_s ON(a.artid=a_s.artid)
    WHERE (artcode LIKE '%".mysqli_real_escape_string($DB_site->link_id,$txt)."%')
    AND a_s.active = 1
    AND a_s.site_code = '".$template->site_code()."'
    AND pro IN ('".($sitepro?2:1)."','3')
    $wheremarque
    GROUP BY artcode
    ORDER BY a.ventes DESC");
}

if($DB_site->num_rows($r)==0 && $whereartid!=""){
  $ref="artcode";
  $r=$DB_site->query("SELECT libelle".$template->langue.",artcode,a.artid,a.prix,commandable,artcode_eurocave,refs_fabricant,m.libelle AS marque FROM article a
   INNER JOIN article_marque ON(a.artid=article_marque.artid)
   INNER JOIN marque m USING(marqueid)
   INNER JOIN article_site AS a_s ON(a.artid=a_s.artid)
   WHERE 1 $whereartid
   AND a_s.active = 1
   AND a_s.site_code = '".$template->site_code()."'
   AND pro IN ('".($sitepro?2:1)."','3')
   $wheremarque
   GROUP BY artcode
   ORDER BY a.ventes DESC
   LIMIT 6");
  $rtotal=$DB_site->query("SELECT libelle".$template->langue.",artcode,a.artid,a.prix,commandable,artcode_eurocave,refs_fabricant,m.libelle AS marque FROM article a
    INNER JOIN article_marque ON(a.artid=article_marque.artid)
    INNER JOIN marque m USING(marqueid)
    INNER JOIN article_site AS a_s ON(a.artid=a_s.artid)
    WHERE (artcode LIKE '%".mysqli_real_escape_string($DB_site->link_id,$txt)."%')
    AND a_s.active = 1
    AND a_s.site_code = '".$template->site_code()."'
    AND pro IN ('".($sitepro?2:1)."','3')
    $wheremarque
    GROUP BY artcode
    ORDER BY a.ventes DESC");
}

if(strpos($txt," ")!=false && $DB_site->num_rows($r)==0){
  $words=explode(" ",$txt);
  $where=$or="";
  $lastl=-1;
  $nword="";
  foreach($words as $word){
    if($lastl<=3 && strlen($word)<=3){
      $nword.=$word;
    }else{
      if($nword!="" && strlen($nword)>1){
       $where.=$or."refs_fabricant LIKE '".mysqli_real_escape_string($DB_site->link_id,$nword)."%'";
       $or=" OR  ";
     }
     $nword=$word;
   }
   $lastl=strlen($word);
 }
 if($lastl<=3)
  $where.=$or."refs_fabricant LIKE '".mysqli_real_escape_string($DB_site->link_id,$nword)."%'";
else
  $where.=$or."refs_fabricant LIKE '".mysqli_real_escape_string($DB_site->link_id,$word)."%'";
// var_dump($where);
$r=$DB_site->query("SELECT libelle".$template->langue.",artcode,a.artid,a.prix,commandable,artcode_eurocave,refs_fabricant,m.libelle AS marque FROM article a
 INNER JOIN article_marque ON(a.artid=article_marque.artid)
 INNER JOIN marque m USING(marqueid)
 INNER JOIN article_site AS a_s ON(a.artid=a_s.artid)
 WHERE ".$where." ".$whereartid." $wheremarque
 AND a_s.active = 1
 AND a_s.site_code = '".$template->site_code()."'
 AND pro IN ('".($sitepro?2:1)."','3')
 LIMIT 6");
$rtotal=$DB_site->query("SELECT libelle".$template->langue.",artcode,a.artid,a.prix,commandable,artcode_eurocave,refs_fabricant,m.libelle AS marque FROM article a
  INNER JOIN article_marque ON(a.artid=article_marque.artid)
  INNER JOIN marque m USING(marqueid)
  INNER JOIN article_site AS a_s ON(a.artid=a_s.artid)
  WHERE (artcode LIKE '%".mysqli_real_escape_string($DB_site->link_id,$txt)."%')
  AND a_s.active = 1
  AND a_s.site_code = '".$template->site_code()."'
  AND pro IN ('".($sitepro?2:1)."','3')
  $wheremarque
  GROUP BY artcode
  ORDER BY a.ventes DESC");
}


// Résultats - ARTICLES -
$where="";
$nbword=0;
$words=explode(" ",html_entity_decode($txt));
foreach($words as $word){
  if(strlen($word)>3 || $word === 'vin'){
    $where.=" AND (a.libelle".$template->langue." LIKE '%".mysqli_real_escape_string($DB_site->link_id,$word)."%' OR m.libelle LIKE '%".mysqli_real_escape_string($DB_site->link_id,$word)."%' OR a.artcode LIKE '%".mysqli_real_escape_string($DB_site->link_id,$word)."%' OR a.refs_fabricant LIKE '%".mysqli_real_escape_string($DB_site->link_id,$word)."%' OR a.artcode_eurocave LIKE '%".mysqli_real_escape_string($DB_site->link_id,$word)."%' OR a.gamme".$template->langue." LIKE '%".mysqli_real_escape_string($DB_site->link_id,$word)."%')";
    $nbword++;
  }
}

$req=$DB_site->query("SELECT * FROM article a INNER JOIN article_marque am ON am.artid = a.artid INNER JOIN marque m ON m.marqueid = am.marqueid WHERE 1 $where AND a.active = '1' GROUP BY a.artid ORDER BY SUM(a.ventes) DESC");
$nbarticle = $DB_site->num_rows($req);

$r=$DB_site->query("SELECT a.libelle".$template->langue." AS 'libellea',artcode,a.artid,a.prix,commandable,artcode_eurocave,refs_fabricant,m.libelle AS marque FROM article a INNER JOIN article_marque am ON am.artid = a.artid INNER JOIN marque m ON m.marqueid = am.marqueid WHERE 1 $where AND a.active = '1' GROUP BY a.artid ORDER BY SUM(a.ventes) DESC LIMIT 6");


// l'affichage pour les produits archivés :
if($DB_site->num_rows($req)==0 && $nbword != 0){

  $rarchiv=$DB_site->query("SELECT a.libelle".$template->langue." AS 'libellea',artcode,a.artid,a.prix,commandable,artcode_eurocave,refs_fabricant,m.libelle AS marque FROM article a INNER JOIN article_marque am ON am.artid = a.artid INNER JOIN marque m ON m.marqueid = am.marqueid WHERE 1 $where AND (a.active = '3' OR a.active = '5') GROUP BY a.artid ORDER BY SUM(a.ventes) DESC LIMIT 6");

  if($DB_site->num_rows($rarchiv)!=0) {

    $nbarticle = $DB_site->num_rows($rarchiv);
    print "<div class=\"cadre_recherche\"></div>";
    print "<div class='bloc-article'><div class=\"cat\"><strong style='width: initial;'>".$template->translate("/produit/archive","F")."</strong><span class=\"line line-prod\" style='width: 326px !important'></span></div>";
    print "<div class='flex-articles'>";
    $compteur = 1;

    while($list=$DB_site->fetch_array($rarchiv)){
      $autreimg=$DB_site->query_first("SELECT * FROM articlephoto WHERE artid=".$list['artid']." AND visible=3 AND (langue='' OR langue='".$template->langue."')");
      $autre="";
      if($autreimg!="") $autre="_".$autreimg['articlephotoid'];
      $img="<img src=\"/images/".$list['artid'].$autre."_60_60.jpg\" class='img-result-article'>";

      if($list['commandable']==0){
       $prix="<br><span class=\"price\">".$template->translate("/article/vignette/prix_sur_demande","F")."</span>";
     }else{
       $prices=article_prices($list['artid'],$template->tva_ref(),$template->site_code(),$sitepro);

       $prix=($prices!=false ? "<span class=\"price\">".$template->price_format($prices['prix'])." TTC</span>":"");
     }

     $urlart=$template->mkurl("article",$list['artid'],$list);

     print ""
     . "<div class='articles-result item-nb".$compteur."'>"
     . "<a href=\"".$urlart."\" onclick=\"saveMotCle('".$txt."')\">$img</a>"
     . "<a href=\"".$urlart."\" onclick=\"saveMotCle('".$txt."')\">"
     . "<span class=\"libelle\">".$list['libellea']."</span>"
     . "<br>"
     . "<span class=\"ref\">Ref.: ".$list['marque'] . " " . $list['artcode']."</span>"
     . "<br>"
     . "</a>"
     . "</div>";

     $compteur++;
   }
   if($nbarticle <2) {
    print "</div>
    <div class='voir_tout toutes_marques'><a href='/recherche-rapide.php?action=".implode('%20', $words)."&actif=produits'>".$template->translate("/recherche/voir_le","F") ." ". $template->translate("/categorie/produits","F")." <img src='/configurations/www.mon-espace-cuisson.com/images/templates/fleche-blanche.png' alt='fleche'/></a></div>
    </div>";
  }
  else {
    print "</div><div class='voir_tout tous_prod'><a href='/recherche-rapide.php?action=".implode('%20', $words)."&actif=produits'\">".$template->translate("/recherche/voir_les","F")." ". $nbarticle . " " . $template->translate("/article/unite/produits", "F")." <img src='/configurations/www.mon-espace-cuisson.com/images/templates/fleche-blanche.png' alt='fleche'/></a></div></div></div>";
  }
}
}
//fin de l'affichage pour les produits archivés

//affichage des produits actifs :

if($DB_site->num_rows($req)>0 && $nbword != 0){

  print "<div class=\"cadre_recherche\"></div>";
  print "<div class='bloc-article'><div class=\"cat\"><strong>".$template->translate("/categorie/produits","F")."</strong><span class=\"line line-prod\"></span></div>";
  print "<div class='flex-articles'>";
  $compteur = 1;

  while($list=$DB_site->fetch_array($r)){
    $autreimg=$DB_site->query_first("SELECT * FROM articlephoto WHERE artid=".$list['artid']." AND visible=1 AND (langue='' OR langue='".$template->langue."')");
    $autre="";
    if($autreimg!="") $autre="_".$autreimg['articlephotoid'];
    $img="<img src=\"/images/".$list['artid'].$autre."_60_60.jpg\" class='img-result-article'>";
    
    if($list['commandable']==0){
     $prix="<br><span class=\"price\">".$template->translate("/article/vignette/prix_sur_demande","F")."</span>";
   }else{
     $prices=article_prices($list['artid'],$template->tva_ref(),$template->site_code(),$sitepro);
     if($sitepro) {
       $prix=($prices!=false ? "<span class=\"price\">".$template->price_format($prices['prix'])." ".$template->translate("/facture/en_ht")."</span>":"");
     }
     else {
       $prix=($prices!=false ? "<span class=\"price\">".$template->price_format($prices['prix'])." ".$template->translate("/facture/en_ttc")."</span>":"");
     }
    
   }
   
   $urlart=$template->mkurl("article",$list['artid'],$list);
   
   print ""
   . "<div class='articles-result item-nb".$compteur."'>"
   . "<a href=\"".$urlart."\" onclick=\"saveMotCle('".$txt."')\">$img</a>"
   . "<a href=\"".$urlart."\" onclick=\"saveMotCle('".$txt."')\">"
   . "<span class=\"libelle\">".$list['libellea']."</span>"
   . "<br>"
   . "<span class=\"ref\">Ref.: ".$list['marque'] . " " . $list['artcode']."</span>"
   . "<br>"
   . $prix
   . "</a>"
   . "</div>";
   
   $compteur++;
 }
 if($nbarticle <2) {
  print "</div>
  <div class='voir_tout toutes_marques'><a href='/recherche-rapide.php?action=".implode('%20', $words)."&actif=produits'>".$template->translate("/recherche/voir_le","F") ." ". $template->translate("/categorie/produits","F")." <img src='/configurations/www.mon-espace-cuisson.com/images/templates/fleche-blanche.png' alt='fleche'/></a></div>
  </div>";
}
else {
  print "</div><div class='voir_tout tous_prod'><a href='/recherche-rapide.php?action=".implode('%20', $words)."&actif=produits'\">".$template->translate("/recherche/voir_les","F")." ". $nbarticle . " " . $template->translate("/article/unite/produits", "F")." <img src='/configurations/www.mon-espace-cuisson.com/images/templates/fleche-blanche.png' alt='fleche'/></a></div></div></div>";
}
}

if(($DB_site->num_rows($r)==0 || $nbword == 0) && $DB_site->num_rows($rarchiv)==0){
  print "<div class=\"cadre_recherche\"></div>";
  print "<div class='bloc-article'><div class=\"cat\"><strong>".$template->translate("/categorie/produits","F")."</strong><span class=\"line line-prod\"></span></div>";
  print "<div class='flex-articles'>".$template->translate("/categorie/pas_resultat")."</div></div>";
}


// Résultat - ARCHIVES -
/*if($DB_site->num_rows($r)==0){
  $req = $DB_site->query("SELECT libelle".$template->langue.",artcode,a.artid,artcode_eurocave,refs_fabricant,m.libelle AS marque FROM article a
							INNER JOIN article_marque am ON(a.artid=am.artid)
							INNER JOIN marque m USING(marqueid)
							INNER JOIN article_site AS a_s ON(a.artid=a_s.artid)
							WHERE 1 AND (a.refs_fabricant LIKE '%".mysqli_real_escape_string($DB_site->link_id,$txt)."%' OR a.artcode LIKE '%".mysqli_real_escape_string($DB_site->link_id,$txt)."%' OR a.artcode_eurocave LIKE '%".mysqli_real_escape_string($DB_site->link_id,$txt)."%' OR a.gammeFR LIKE '%".mysqli_real_escape_string($DB_site->link_id,$txt)."%' OR (HTML_UnEncode(libelleFR) LIKE '%".mysqli_real_escape_string($DB_site->link_id,$txt)."%'))
							AND a_s.active    = 3
							AND a_s.site_code = '".$template->site_code()."'
							AND a.pro IN ('1','3')
							GROUP BY artcode , libelleFR ASC LIMIT 6");

  if($DB_site->num_rows($req)>0){
    print "<div class=\"cadre_recherche\"></div>";
    print "<div class=\"cat\"><span class=\"line2\"></span><span class=\"strong\">".$template->translate("/categorie/archives","F")."</span><span class=\"line2\"></span></div>";
    print "<table>";
    while($list=$DB_site->fetch_array($req)){
      $autreimg=$DB_site->query_first("SELECT * FROM articlephoto WHERE artid=".$list['artid']." AND visible=1 AND (langue='' OR langue='".$template->langue."')");
      $autre="";

      if($autreimg!="") $autre="_".$autreimg['articlephotoid'];

      $img="<img src=\"/images/".$list['artid'].$autre."_60_60.jpg\">";
        
      $urlart=$template->mkurl("article",$list['artid'],$list);

      print ""
      . "<tr>"
        . "<td class=\"tdimg\"><a href=\"".$urlart."\" onclick=\"saveMotCle('".$txt."')\">$img</a></td>"
        . "<td class=\"tdlibelle\">"
          . "<a href=\"".$urlart."\" onclick=\"saveMotCle('".$txt."')\">"
            . "<span class=\"libelle\">".$list[0]."</span>"
            . "<br>"
            . "<strong>".$list['marque']."</strong><span class=\"ref\"><strong>Réf. :</strong> ".$list['artcode']."</span>"
          . "</a>"
        . "</td>"
      . "</tr>";
    }
    print "</table>";
  }
}*/

function verifword($w,$last=false){
  global $DB_site;

  if(preg_match("/\d+/",$w)) return $w;
  $result=$DB_site->query_first("SELECT COUNT(word) FROM words WHERE word='".mysqli_real_escape_string($DB_site->link_id,$w)."'");
  if($result[0]==0){
    if($last && strlen($w)>2){
     $endw=$DB_site->query_first("SELECT word FROM words WHERE word LIKE '".mysqli_real_escape_string($DB_site->link_id,$w)."%' ORDER BY word");
     if($endw[0]!="") return $endw[0];
   }

   $correct=$DB_site->query_first("SELECT word FROM words WHERE `soundex`=SOUNDEX('".mysqli_real_escape_string($DB_site->link_id,$w)."')");
   if($correct[0]!="" && strlen($w)>2){
    $display=true;
    return $correct[0];
  }else
  return $w;
}

return $w;
}



?>