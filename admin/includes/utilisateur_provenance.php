<br>
<div class="commande_detail" id="utilisateur_provenance">
     <h2>PROVENANCE</h2>
     <div class="table_modules_boutiques no_border" style="width:880px;">
          <div id="liste_provenances" style="width:100%; overflow-y:hidden; height:300px;">
               <table id="stat" class="table_factures">
                    <tr>
                         <td class="td_head_factures"><a href="javascript:jQuery('#liste_provenances').css('height','auto');">Tout afficher</a></td>
                         <td class="td_head_factures">Visite</td>
                         <td class="td_head_factures">Provenance</td>
                    </tr>
                    <?php
                         if($visits != false){
                              foreach($visits as $visit){
                                   $plus = "";
                                   $av   = "";
                                   if(isset($articlesvu[$visit->statid])){
                                        arsort($articlesvu[$visit->statid]);
                                        $plus = "<img src=\"images/plus.jpg\" id=\"plus".$visit->statid."\" onclick=\"show_av(".$visit->statid.")\">";
                                        $av   = ""
                                        . "<div id=\"art".$visit->statid."\" style=\"width:980px;overflow:auto;border:solid 1px #CCC;display:none;\">"
                                             . "<table>"
                                                  . "<tr>"
                                                       . "<td>Nb vues</td>";
                                                       foreach($articlesvu[$visit->statid] as $artid=>$nb){
														   $av.="<td>".$nb."</td>";
													   }
                                                  $av .= "</tr>";
                                                  foreach($rows as $row=>$lbl){
														$av.="<tr><td>$lbl</td>";
                                                        foreach($articlesvu[$visit->statid] as $artid=>$nb){
															$av.="<td>".$td[$artid][$row]."</td>";
														}
                                                        $av.="</tr>";
                                                  }
                                                  $av .= ""
                                              . "</table>"
                                        . "</div>";
                                   }
                                   ?>
                                   <tr>
                                        <td><?php echo $plus; ?></td>
                                        <td><?php echo date("d/m/Y H:i",$visit->timestamp); ?></td>
                                        <td><?php echo $visit->provenance; ?></td>
                                   </tr>
                                   <tr>
                                        <td colspan="5"><?php echo $av; ?></td>
                                   </tr>
                                   <?php
                              }
                         }
                    ?>
               </table>
          </div>
     </div>
</div>
<div class="clear"></div>