<style>
	.tableGarantie {border: 2px solid black;border-collapse: collapse;}
	.tableGarantie tr {border: 2px solid black;}
	.tableGarantie tr td {border: 2px solid black;}
</style>
<h3>Tableau Récapitulatif</h3>
<table class="tableGarantie">
	<?php $trPays="
		<tr class='td_location' align='left' bgcolor='grey'>
		<td colspan=2></td>";
		$lesGaranties=array();
		$lesPaysCouverts=array();
		$i=0;
		$p=$DB_site2->query_first("SELECT COUNT(*) FROM pays WHERE typepays='EE';");
		$nbPaysEE=$p[0];
		$allPays=$DB_site2->query("SELECT * FROM pays ORDER BY typepays ASC, libelle ASC;");
		$nbPays=$nbPaysEE+2;
		while($pays=$DB_site2->fetch_array($allPays)){
			if($pays['typepays']=='EE') {
				$i++;
				$trPays.="
				<td style='width:40px;'>$pays[libelle]</td>";
			} elseif($pays['typepays']=='RE') {
				$i=$nbPaysEE+1;
			} elseif($pays['typepays']=='RM') {
				$i=$nbPaysEE+2;
			}
			$garanties = DAOgarantie::loadAllGarantiesByPays($DB_site2,null,'FR',$pays['paysid']);
			foreach ($garanties as $garantie) {
				if (!array_key_exists($garantie->garantieid, $lesGaranties)) {
					$lesGaranties[$garantie->garantieid]=$garantie;
					$lesPaysCouvertsByG=array();
					$lesPaysCouvertsByG[]=$i;
					$lesPaysCouverts[$garantie->garantieid]=$lesPaysCouvertsByG;
				} else {
					$lesPaysCouvertsByG=array();
					$lesPaysCouvertsByG=$lesPaysCouverts[$garantie->garantieid];
					$lesPaysCouvertsByG[]=$i;
					$lesPaysCouverts[$garantie->garantieid]=$lesPaysCouvertsByG;
				}
			}
		}
		$trPays.="
		<td style='width:40px;'>RE</td>
		<td style='width:40px;'>RM</td>
	</tr>";
	echo $trPays;
	$allTypes=$DB_site2->query("SELECT * FROM typearticle ORDER BY libelle ASC;");
	while($type=$DB_site2->fetch_array($allTypes)){
		$lesMarques=array();
		$marques = DAOtype::loadMarquesByTypes($DB_site2,$type['typearticleid']);
		?>
		<tr bgcolor="#49DC4E">
			<td rowspan=<?php echo $DB_site2->num_rows($marques)+1;?>><?php echo $type['libelle'];?></td>
		</tr>
		<?php
		while($marque=$DB_site2->fetch_array($marques)){
			$lesMarques[$type['typearticleid']]=$marque['libelle'];
			?>
			<tr bgcolor="#49DC4E">
				<td><?php echo "$marque[libelle]";?></td>
				<?php
				$lesGaranties2=array();
				$garanties = DAOgarantie::loadGarantiesByTypeMarque($DB_site2,null,'FR',$type['typearticleid'],$marque['marqueid']);
				foreach ($garanties as $garantie) {
					$lesGaranties2[$garantie->garantieid]=$garantie;
				}
				$value=array();
				$result=array_intersect_key($lesGaranties,$lesGaranties2);
				if (count($result)>0) {
					foreach ($lesPaysCouverts as $idGar => $lesPaysCouvertsByG) {
						for ($i=1; $i <= $nbPays; $i++) {
							for ($j=0; $j < count($lesPaysCouvertsByG); $j++) {
								if ($i==$lesPaysCouvertsByG[$j]) {
									if ($result[$idGar]!='') {
										$value[$i].=$result[$idGar]->fournisseur."<br/>";
									}
									break;
								}
							}
						}
					}
					for ($i=1; $i <= $nbPays; $i++) {
						if (count($value[$i])>0) {
							echo "<td>".$value[$i]."</td>";
						} else {
							echo "<td bgcolor=\"#EF4646\"></td>";
						}
					}
				} else {
					echo "<td colspan=".$nbPays." bgcolor=\"#EF4646\"></td>";
				}
				?>
			</tr>
			<?php
		}
		echo $trPays;
	}
	?>
</table>
<br/><a href="garanties.php">Retour</a>