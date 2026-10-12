<?php

$sql = "SELECT f.facturid";
$sql .= ", f.heurefacture";
$sql .= ", f.valide";
$sql .= ", f.moyenid";
$sql .= ", f.numerofacture";
$sql .= ", f.nom";
$sql .= ", f.prenom";
$sql .= ", f.raisonsociale";
$sql .= ", f.adresse";
$sql .= ", f.codepostal";
$sql .= ", f.ville";
$sql .= ", f.paysid";
$sql .= ", f.telephone";
$sql .= ", f.mail";
$sql .= ", u.prenom AS billing_first_name";
$sql .= ", u.nom AS billing_last_name";
$sql .= ", u.raisonsociale AS billing_company";
$sql .= ", u.adresse AS billing_address";
$sql .= ", u.codepostal AS billing_zip";
$sql .= ", u.ville AS billing_city";
$sql .= ", u.paysid AS billing_country";
$sql .= ", u.telephone AS billing_phone";
$sql .= ", f.infotranspo";
$sql .= ", f.modelivraison";
$sql .= ", f.prixport";
$sql .= ", f.tvaport";
$sql .= ", f.cadeauid";
$sql .= ", f.provenanceid";
$sql .= ", f.suivi";
$sql .= ", f.suivi2";
$sql .= ", f.mp_facturid";
$sql .= ", u.userid";
$sql .= " FROM facture f";
$sql .= " INNER JOIN utilisateur u ON (f.userid=u.userid)";
define('MPORDER_SQL_SELECT', $sql);


class MP_Order {

        var $id;
        var $userid;
        var $userid2;
        var $vendor;
        var $order_date;
        var $order_status;
        var $order_state;
        var $payment_method;
        var $payment_date;
        var $payment_name;
        var $cancel_date;
        var $billing_date;
        var $billing_deliver;
        var $billing_number;
        var $vat_intra;
        var $billing_civi;
        var $billing_first_name;
        var $billing_last_name;
        var $billing_company;
        var $billing_address;
        var $billing_zip;
        var $billing_city;
        var $billing_country;
        var $billing_phone;
        var $billing_email;
        var $shipping_civi;
        var $shipping_first_name;
        var $shipping_last_name;
        var $shipping_company;
        var $shipping_address;
        var $shipping_zip;
        var $shipping_city;
        var $shipping_country;
        var $shipping_phone;
        var $shipping_details;
        var $delivery_type;
        var $full_delivery;
        var $items_price;
        var $shipping_price;
        var $vat_100;
        var $currency;
        var $currency_exchange_rate;
        var $buy_prices_sum;
        var $reduc;
        var $reduc_type;
        var $market_place_id;
        var $market_place;
        var $mp_facturid;
        var $commission;
        var $comment;
        var $suivi2;
        var $interetbca;
        var $info_importante;

        var $nbr_produit;
        var $nom = array();
        var $qte = array();
        var $prix = array();

        var $DB;



        var $orderstates=array(0=>"non traitée",1=>"validée",2=>"refus commerçant",3=>"refus cb",4=>"abandon",5=>"expédiée");
        var $marketplaces = array(
            "01" => "Amazon",
            //"04" => "Price Minister",
            //"05" => "Pixmania",
            "07" => "Rue du Commerce",
            //"10" => "Ebay",
            "12" => "Fnac",
            "13" => "CDiscount",
            "14" => "LaRedoute",
            //"15" => "Galeries Lafayette",
            //"16" => "Mister Good Deal",
            //"18" => "Delamaison",
			"19" => "Conforama",
            "21" => "Showroomprive",
            "22" => "Retif"
        );

        function MP_Order($id=0,$DB=null) {
              asort($this->marketplaces);
              if($id!=0) return $this->load($id);
              if(!is_null($DB)) $this->DB = $DB;
        }

        function internal_load($order) {
            $this->id             = trim($order['facturid']);
            $this->userid   = intval($order['userid']);
            $this->order_date     = $order['heurefacture'];
            $this->order_status   = intval($order['valide']);
            $this->order_state    = $this->orderstates[$this->order_status];
            $this->billing_date   = $order['heurefacture'];
            $this->billing_number = intval($order['numerofacture']);
            $this->billing_deliver= $order['datelivraison'];
 
            $this->billing_civi        = trim($order['billing_civi']);
            $this->billing_first_name  = trim($order['billing_first_name']);
            $this->billing_last_name   = trim($order['billing_last_name']);
            $this->billing_company     = trim($order['billing_company']);
            $this->billing_address     = trim($order['billing_address']);
            $this->billing_zip         = trim($order['billing_zip']);
            $this->billing_city        = trim($order['billing_city']);
            $this->billing_country     = trim($order['billing_country']);
            $this->billing_phone       = trim($order['billing_phone']);
            $this->billing_email       = trim($order['mail']);

            $this->shipping_civi       = trim($order['shipping_civi']);
            $this->shipping_first_name = trim($order['prenom']);
            $this->shipping_last_name  = trim($order['nom']);
            $this->shipping_company    = trim($order['raisonsociale']);
            $this->shipping_address    = trim($order['adresse']);
            $this->shipping_zip        = trim($order['codepostal']);
            $this->shipping_city       = trim($order['ville']);
            $this->shipping_country    = trim($order['paysid']);
            $this->shipping_phone      = trim($order['telephone']);

            $this->shipping_details    = trim($order['infotranspo']);
            $this->delivery_type  = trim($order['modelivraison']);
            
            $this->shipping_price = floatval($order['prixport']);
            $this->vat_100        = floatval($order['tvaport']);
            $this->items_price  =$this->calc_items_price();

            $this->reduc          = floatval($order['cadeauid']);
            $this->market_place_id= trim($order['provenanceid']);
            $this->market_place   = $this->marketplaces[$this->market_place_id];
        $this->mp_facturid  = $order['mp_facturid'];
           
            $this->comment        = stripslashes($order['suivi']);
        $this->suivi2   = stripslashes($order['suivi2']);
            $this->interetbca = trim($order['interetbca']);
            $this->info_importante=$order['info_importante'];
        }

        /* Charge la commande depuis la base de données. Renvoie FALSE en cas d'erreur. */
        function load($id) {
            $result = $this->DB->query(MPORDER_SQL_SELECT." WHERE f.facturid = '".$id."'");
            if( $result == FALSE || ($order = $this->DB->fetch_array($result)) == FALSE ) {
                return FALSE;
            } else {
                if($this->DB->num_rows($result)==0) return false;
                $this->internal_load($order);
                return TRUE;
            }
        }

        function load_or_die($id) {
            $this->load($id) || die_on_error($this->database->error());
        }

        /* Crée une nouvelle commande en base de données. Renvoie FALSE en cas d'erreur. */
        function create($userid = 0) {
            $order_date = time();
            if( $this->payment_date == 0 ) {
                $payment_date = 'NULL';
            } else {
                $payment_date = sprintf('FROM_UNIXTIME(%u)', $this->payment_date);
            }
            if( $this->cancel_date == 0 ) {
                $cancel_date = 'NULL';
            } else {
                $cancel_date = sprintf('FROM_UNIXTIME(%u)', $this->cancel_date);
            }
            if( $this->billing_deliver == 0 ) {
                $billing_deliver = 'NULL';
            } else {
                $billing_deliver = sprintf('FROM_UNIXTIME(%u)', $this->billing_deliver);
            }
            
            if ($this->userid2 != "" | $this->userid2 != 0)
            {
                $this->userid = $this->userid2;   
            }
            else
            {
                $requete=$this->DB->query("SELECT userid FROM utilisateur WHERE mail='".$this->billing_email."'");
                $mpuser=$this->DB->fetch_array($requete);

                if($mpuser==false || trim($this->billing_email)=="")
                $this->userid=$this->createuser();
                else
                $this->userid=$mpuser['userid'];
            }
            $sql = "INSERT INTO facture SET";
            $sql .= " numerofacture      = '0'";
            $sql .= ", codenumerofacture = ''";
            $sql .= ", userid            = ".$this->userid;
            $sql .= ", datefacture       = '".date("Y-m-d", $this->billing_date)."'";
            $sql .= ", heurefacture      = '".$this->billing_date."'";
            $sql .= ", prixport          = ".floatval($this->shipping_price);
            $sql .= ", tvaport           = '".$this->vat_100."'";
            $sql .= ", datelivraison     = '".$this->billing_deliver."'";
            $sql .= ", moyenid           = ".$this->payment_method;
            $sql .= ", valide            = 7";
            $sql .= ", civilite          = '".mysqli_real_escape_string($this->DB->link_id,$this->shipping_civi)."'";
            $sql .= ", nom               = '".mysqli_real_escape_string($this->DB->link_id,mb_strtoupper($this->shipping_last_name))."'";
            $sql .= ", prenom            = '".mysqli_real_escape_string($this->DB->link_id,$this->shipping_first_name)."'";
            $sql .= ", codepostal        = '".mysqli_real_escape_string($this->DB->link_id,$this->shipping_zip)."'";
            $sql .= ", adresse           = '".mysqli_real_escape_string($this->DB->link_id,$this->shipping_address)."'";
            $sql .= ", ville             = '".mysqli_real_escape_string($this->DB->link_id,mb_strtoupper($this->shipping_city))."'";
            $sql .= ", telephone         = '".mysqli_real_escape_string($this->DB->link_id,$this->shipping_phone)."'";
            $sql .= ", raisonsociale     = '".mysqli_real_escape_string($this->DB->link_id,$this->shipping_company)."'";
            $sql .= ", paysid            = ".$this->shipping_country;
            $sql .= ", mail              = '".mysqli_real_escape_string($this->DB->link_id,$this->billing_email)."'";
            $sql .= ", langue            = 'FR'";
            $sql .= ", infotranspo       = '".mysqli_real_escape_string($this->DB->link_id,$this->shipping_details)."'";
            $sql .= ", provenance        = 'mp'";
            $sql .= ", provenanceid      = '".$this->market_place_id."'";
            $sql .= ", mp_facturid       = '".$this->mp_facturid."'";
            $sql .= ", commission        = '".$this->commission."'";
            $sql .= ", commercial        = '".$_COOKIE['admin_username']."'";
            $sql .= ", info_importante   = '".mysqli_real_escape_string($this->DB->link_id,$this->info_importante)."'";

            $return = $this->DB->query($sql);
            $this->id = mysqli_insert_id($this->DB->link_id);

            return $return;

            
        }

        function create_or_die() {
            $this->create() || die_on_error();
        }

        /* Met à jour la commande existante en base de données. Renvoie FALSE en cas d'erreur. */
        function update() {
            if( $this->payment_date == 0 ) {
                $payment_date = 'NULL';
            } else {
                $payment_date = sprintf('FROM_UNIXTIME(%u)', $this->payment_date);
            }
            if( $this->cancel_date == 0 ) {
                $cancel_date = 'NULL';
            } else {
                $cancel_date = sprintf('FROM_UNIXTIME(%u)', $this->cancel_date);
            }
            if( $this->billing_date == 0 ) {
                $billing_date = 'NULL';
            } else {
                $billing_date = sprintf('FROM_UNIXTIME(%u)', $this->billing_date);
            }
            if( $this->billing_deliver == 0 ) {
                $billing_deliver = 'NULL';
            } else {
                $billing_deliver = sprintf('FROM_UNIXTIME(%u)', $this->billing_deliver);
            }

        $sql = "UPDATE utilisateur SET";
            $sql .= " mail                     = '".mysqli_real_escape_string($this->DB->link_id,$this->billing_email)."'";
            $sql .= ", civilite                 = '".mysqli_real_escape_string($this->DB->link_id,$this->billing_civi)."'";
            $sql .= ", nom                     = '".mysqli_real_escape_string($this->DB->link_id,mb_strtoupper($this->billing_last_name))."'";
            $sql .= ", prenom                  = '".mysqli_real_escape_string($this->DB->link_id,$this->billing_first_name)."'";
            $sql .= ", codepostal              = '".mysqli_real_escape_string($this->DB->link_id,$this->billing_zip)."'";
            $sql .= ", adresse                 = '".mysqli_real_escape_string($this->DB->link_id,$this->billing_address)."'";
            $sql .= ", ville                   = '".mysqli_real_escape_string($this->DB->link_id,mb_strtoupper($this->billing_city))."'";
            $sql .= ", telephone               = '".mysqli_real_escape_string($this->DB->link_id,$this->billing_phone)."'";
            $sql .= ", raisonsociale           = '".mysqli_real_escape_string($this->DB->link_id,$this->billing_company)."'";
            $sql .= ", paysid                  = ".$this->billing_country."";
            $sql .= ", langue                  = 'FR'";
            $sql .= ", allowemail              = '1'";
            $sql .= ", nom_livraison           = '".mysqli_real_escape_string($this->DB->link_id,mb_strtoupper($this->shipping_last_name))."'";
            $sql .= ", prenom_livraison        = '".mysqli_real_escape_string($this->DB->link_id,$this->shipping_first_name)."'";
            $sql .= ", codepostal_livraison    = '".mysqli_real_escape_string($this->DB->link_id,$this->shipping_zip)."'";
            $sql .= ", adresse_livraison       = '".mysqli_real_escape_string($this->DB->link_id,$this->shipping_address)."'";
            $sql .= ", ville_livraison         = '".mysqli_real_escape_string($this->DB->link_id,mb_strtoupper($this->shipping_city))."'";
            $sql .= ", paysid_livraison        = '".$this->shipping_country."'";
            $sql .= ", raisonsociale_livraison = '".mysqli_real_escape_string($this->DB->link_id,$this->shipping_company)."'";
            $sql .= ", infotranspo             = '".mysqli_real_escape_string($this->DB->link_id,$this->shipping_details)."'";
            $sql .= ", telephone_livraison     = '".mysqli_real_escape_string($this->DB->link_id,$this->shipping_phone)."'";
            $sql .= ", connu                   = '".$this->marketplaces[$this->market_place_id]."'";
            $sql .= " WHERE userid=".$this->userid;
            
        $result=$this->DB->query($sql);
        if($result==false) return false;

        $sql = "UPDATE facture SET";
            $sql .= " datefacture    = '".date("Y-m-d", $this->billing_date)."'";
            $sql .= ", heurefacture  = '".$this->billing_date."'";
            $sql .= ", prixport      = '".floatval($this->shipping_price)."'";
            $sql .= ", tvaport       = '".$this->vat_100."'";
            $sql .= ", datelivraison = '".$this->billing_deliver."'";
            $sql .= ", moyenid       = 1";
            $sql .= ", valide        = 7";
            $sql .= ", civilite      = '".mysqli_real_escape_string($this->DB->link_id,$this->shipping_civi)."'";
            $sql .= ", nom           = '".mysqli_real_escape_string($this->DB->link_id,mb_strtoupper($this->shipping_last_name))."'";
            $sql .= ", prenom        = '".mysqli_real_escape_string($this->DB->link_id,$this->shipping_first_name)."'";
            $sql .= ", codepostal    = '".mysqli_real_escape_string($this->DB->link_id,$this->shipping_zip)."'";
            $sql .= ", adresse       = '".mysqli_real_escape_string($this->DB->link_id,$this->shipping_address)."'";
            $sql .= ", ville         = '".mysqli_real_escape_string($this->DB->link_id,mb_strtoupper($this->shipping_city))."'";
            $sql .= ", telephone     = '".mysqli_real_escape_string($this->DB->link_id,$this->shipping_phone)."'";
            $sql .= ", raisonsociale = '".mysqli_real_escape_string($this->DB->link_id,$this->shipping_company)."'";
            $sql .= ", paysid        = ".$this->shipping_country."";
            $sql .= ", mail          = '".mysqli_real_escape_string($this->DB->link_id,$this->billing_email)."'";
            $sql .= ", langue        = 'FR'";
            $sql .= ", infotranspo   = '".mysqli_real_escape_string($this->DB->link_id,$this->shipping_details)."'";
            $sql .= ", provenance    = 'mp'";
            $sql .= ", provenanceid  = '".$this->market_place_id."'";
            $sql .= ", commission        = '".$this->commission."'";
            $sql .= " WHERE facturid = ".$this->id;
        $result=$this->DB->query($sql);
        return $result;

          
        }

        function update_or_die() {
            $this->update() || die_on_error();
        }

    function createuser(){
        $sql = "INSERT INTO utilisateur SET";
            $sql .= " mail                     = '".mysqli_real_escape_string($this->DB->link_id,$this->billing_email)."'";
            $sql .= ", password                = '".md5($this->mkpassword())."'";
            $sql .= ", civilite                = '".mysqli_real_escape_string($this->DB->link_id,$this->billing_civi)."'";
            $sql .= ", nom                     = '".mysqli_real_escape_string($this->DB->link_id,$this->billing_last_name)."'";
            $sql .= ", prenom                  = '".mysqli_real_escape_string($this->DB->link_id,$this->billing_first_name)."'";
            $sql .= ", codepostal              = '".mysqli_real_escape_string($this->DB->link_id,$this->billing_zip)."'";
            $sql .= ", adresse                 = '".mysqli_real_escape_string($this->DB->link_id,$this->billing_address)."'";
            $sql .= ", ville                   = '".mysqli_real_escape_string($this->DB->link_id,$this->billing_city)."'";
            $sql .= ", telephone               = '".mysqli_real_escape_string($this->DB->link_id,$this->billing_phone)."'";
            $sql .= ", raisonsociale           = '".mysqli_real_escape_string($this->DB->link_id,$this->billing_company)."'";
            $sql .= ", dateinscription         = NOW()";
            $sql .= ", provenance              = 'mp'";
            $sql .= ", paysid                  = ".$this->billing_country."";
            $sql .= ", langue                  = 'FR'";
            $sql .= ", allowemail              = '0'";
            $sql .= ", allowsms                = '0'";
            $sql .= ", nom_livraison           = '".mysqli_real_escape_string($this->DB->link_id,$this->shipping_last_name)."'";
            $sql .= ", prenom_livraison        = '".mysqli_real_escape_string($this->DB->link_id,$this->shipping_first_name)."'";
            $sql .= ", codepostal_livraison    = '".mysqli_real_escape_string($this->DB->link_id,$this->shipping_zip)."'";
            $sql .= ", adresse_livraison       = '".mysqli_real_escape_string($this->DB->link_id,$this->shipping_address)."'";
            $sql .= ", ville_livraison         = '".mysqli_real_escape_string($this->DB->link_id,$this->shipping_city)."'";
            $sql .= ", paysid_livraison        = '".$this->shipping_country."'";
            $sql .= ", raisonsociale_livraison = '".mysqli_real_escape_string($this->DB->link_id,$this->shipping_company)."'";
            $sql .= ", infotranspo             = '".mysqli_real_escape_string($this->DB->link_id,$this->shipping_details)."'";
            $sql .= ", telephone_livraison     = '".mysqli_real_escape_string($this->DB->link_id,$this->shipping_phone)."'";
            $sql .= ", connu                   = '".$this->marketplaces[$this->market_place_id]."'";
            $sql .= ", commercial              = ''";
            $sql .= ", conseils              = 'non'";
        
        $result=$this->DB->query($sql);
        if($result==false) return false;
        return mysqli_insert_id($this->DB->link_id);
    }

        function list_shipments() {
            $result = $this->DB->query(sprintf('SELECT %s FROM shipment WHERE `order` = "%s" ORDER BY shipment_date DESC', SHIPMENT_SQL_SELECT, $this->id));
            if( $result == FALSE ) {
                return FALSE;
            } else {
                $shipments = array();
                while( $row = $this->DB->fetch_array($result) ) {
                    $shipment = new Shipment();
                    $shipment->internal_load($row);
                    $shipments[$shipment->id] = $shipment;
                }

                return $shipments;
            }
        }

        function list_shipments_or_die() {
            return ($result = $this->list_shipments()) !== FALSE ? $result : die_on_error();
        }

        function internal_list_lines($sql) {
            $result = $this->DB->query($sql);
            if( $result == FALSE ) {
                return FALSE;
            } else {
                $lines = array();
                while( $row = $this->DB->fetch_array($result) ) {
                    $line = new MP_Order_Line(0,$this->DB);
                    $line->internal_load($row);
                    $lines[$line->id] = $line;
                }

                return $lines;
            }
        }

        function list_lines() {
            $sql = "SELECT * FROM lignefacture WHERE factureid ='".$this->id."'";
            return $this->internal_list_lines($sql);
        }


        function list_undelivered_lines() {
            $sql = sprintf('SELECT *,COUNT(ref_tin) AS number FROM mp_order_line WHERE `order` = '.$this->id.' GROUP BY ref_tin');
            return $this->internal_list_lines($sql);
        }

        function list_lines_or_die() {
            return ($result = $this->list_lines()) !== FALSE ? $result : die_on_error($this->database->error());
        }


        function list_orders($allowed_status, $sort_by,$condition="") {
            if( $allowed_status=="" ) {
                $status_string=1;
            }else {
                // Prépare la partie de SQL de filtre sur les status
                foreach( $allowed_status as $key => $value ) { $allowed_status[$key] = 'valide = ' . $value; }
                $status_string = implode($allowed_status, ' OR ');
            }
            if($condition!="") $status_string="($status_string) AND ($condition)";

                // Recherche le nombre total de commandes répondant aux critères
            $result_pages = $this->DB->query(sprintf('SELECT COUNT(*) AS nb FROM facture WHERE (%s)',$status_string));

                // Exécute la requête de liste
            $sql=sprintf(MPORDER_SQL_SELECT." WHERE (%s) AND f.provenance='mp' ORDER BY %s",$status_string,$sort_by);
            $result = $this->DB->query($sql);

            if( $result == FALSE ) {
                    return FALSE;
            } else {
                    // Calcule la dernière page (-1 => aucune page, aucune commande trouvée)
                    //$pages_row = @$this->DB->fetch_array($result_pages);
                    //$last_page = floor(($pages_row['nb']-1) / LIMIT_ORDERS_PER_PAGE);
                    $orders = array();
                    while( $row = $this->DB->fetch_array($result) ) {
                        $order = new MP_Order(0,$DB_site2);
                        $order->internal_load($row);
                        $orders[$order->id] = $order;
                    }
                    return $orders;
                    //return array('orders' => $orders, 'last_page' => $last_page);
           }
        }

    function search($txt){
          $sql="SELECT ".MPORDER_SQL_SELECT." FROM mp_orders WHERE billing_number='$txt' OR id='$txt' OR billing_last_name LIKE '%$txt%' ORDER BY id";
          $result = $this->DB->query($sql);

          if( $result == FALSE ) {
                    return FALSE;
          } else {
                   
                    $orders = array();
                    while( $row = $this->DB->fetch_array($result) ) {
                        $order = new MP_Order(0,$DB_site2);
                        $order->internal_load($row);
                        $orders[$order->id] = $order;
                    }
                    return $orders;
                   
        }
    }

        function get_billing_number() {
            $result = $this->DB->query('SELECT MAX(numerofacture) AS billing_number FROM facture');
            if( $result == FALSE ) {
                return FALSE;
            } else {
                $row = $this->DB->fetch_array($result);
                if($row['billing_number']==0) $row['billing_number']=110481;
                $new_billing_number = $row['billing_number'] + 1;
                return $new_billing_number;
            }
        }

        function get_billing_number_or_die() {
            return ($result = $this->get_billing_number()) !== FALSE ? $result : die_on_error($this->database->error());
        }

        function calc_items_price(){
                 $requete="SELECT SUM(qte*prix/(1+(tva/100))) AS `total` FROM lignefacture WHERE `factureid`='".$this->id."'";
                 $result = $this->DB->query_first($requete);
                 return floatval($result['total']);
                
        }

        function totalTTC(){
                 return ($this->items_price+($this->shipping_price/(1+$this->vat_100/100)))*TVA_COEFF + $this->interetbca;
        }

        function getbuyprice(){
            $return=0;
            $sql = "SELECT buy_price FROM mp_order_line WHERE `order` = '".$this->id."'";
            $requete=$this->DB->query($sql);
            while($linerow=$this->DB->fetch_array($requete)){
                  $return+=$linerow['buy_price'];
            }
            return $return;
        }

        function getCustomerOrders($customer){
                 $return=array();
                 $sql="SELECT ".ORDER_SQL_SELECT." FROM mp_orders WHERE billing_first_name='".$customer->billing_first_name."' AND billing_last_name='".$customer->billing_last_name."' AND billing_address='".$customer->billing_address."'";
                 $requete=$this->DB->query($sql);
                 if($this->DB->num_rows($requete)==0) return false;
                 while($item=$this->DB->fetch_array($requete)){
                     $order=new MP_Order(0,$DB_site2);
                     $order->internal_load($item);
                     $return[]=$order;
                 }
                 return $return;
        }

    function compte_analytique(){
         $ca=$this->DB->fetch_array($this->DB->query("SELECT * FROM compte_analytique WHERE paysid=0 AND site='".$this->market_place_id."'"));
         return $ca['code'];
    }

    function mkpassword(){
          $chars="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
          $maxchars=count($chars);
          for($i=0;$i<7;$i++){
          $rdm=rand(0,$maxchars-1);
          $chr=substr($chars,$rdm,1);
          $result.=$chr;
          }
          return $result;
    }

    }

   class MP_Order_line{
        var $id;
        var $order;
        var $qte;
        var $artid;
        var $ref_tin;
        var $product_name;
        var $price;
        var $paht;
        var $commission;
        var $cancelled;
        var $tva;
        var $ecopart;
        var $DB;

       

        function MP_Order_Line($id=0,$DB) {
            $this->DB = $DB;
        }

        function internal_load($line) {
            $this->id            = intval($line['lignefactureid']);
            $this->artid    = $line['artid'];
            $this->order         = trim($line['factureid']);
            $this->ref_tin       = trim($line['artcode']);
            $this->qte      = intval($line['qte']);
            $this->product_name  = trim($line['libelle']);
            $this->price         = floatval($line['prix']);
            $this->paht     = floatval($line['paht']);
            $this->cancelled     = intval($line['remboursement']) != 0;
            $this->tva      = floatval($line['tva']);
            $this->ecopart  =  floatval($line['ecopart']);
        }

//      // Charge la ligne de commande depuis la base de données. Renvoie FALSE en cas d'erreur.
        function load($id) {
            $query = sprintf("SELECT * FROM lignefacture WHERE lignefactureid = ".$id);
            $result = $this->DB->query($query);
            if( $result == FALSE || ($line = $this->DB->fetch_array($result)) == FALSE ) {
                return FALSE;
            } else {
                $this->internal_load($line);
                return TRUE;
            }
        }

        function create() {

        $sql="INSERT INTO lignefacture SET factureid='".$this->order."',artid='".$this->artid."',artcode='".$this->ref_tin."',qte=".$this->qte.",libelle='".mysqli_real_escape_string($this->DB->link_id,$this->product_name)."',prix=".$this->price.", paht=".floatval($this->paht).", tva=".((TVA_COEFF-1)*100).", ecopart=".floatval($this->ecopart);
        
        $return=$this->DB->query($sql);
        $this->id=mysqli_insert_id($this->DB->link_id);
        return $return;

            $sql = "INSERT INTO mp_order_line(`order`, shipment, ref_tin, barcode, product_name, special_offer, stock, price, commission, cancelled, buy_price,strikeout_price) VALUES (\"%s\", %s, \"%s\", \"%s\", \"%s\", %u, %u, %f, %f, %u, %f, %f)";

            $shipment = intval($this->shipment);
            if( $shipment == 0 ) {
                $shipment = 'NULL';
            }
            $query = sprintf($sql, $this->order, $shipment,
                @mysqli_real_escape_string($this->DB->link_id,$this->ref_tin),
                @mysqli_real_escape_string($this->DB->link_id,$this->barcode),
                @mysqli_real_escape_string($this->DB->link_id,$this->product_name),
                $this->special_offer, $this->stock, $this->price, $this->commission,
                $this->cancelled, $this->buy_price,$this->strikeout_price);
            if( @$this->DB->query($query) == FALSE ) {
                return FALSE;
            } else {
                $this->id = mysqli_insert_id($this->DB->link_id);
                return TRUE;
            }
        }

       function create_or_die() {
            $this->create() || die_on_error($this->database->error());
        }

       function update() {
           
            $sql = "UPDATE lignefacture
            SET factureid = \"%s\", artcode = \"%s\", libelle = \"%s\",
               prix = %f, remboursement = %u,
            WHERE id = %u";

            $query = sprintf($sql, $this->order,
                mysqli_real_escape_string($this->DB->link_id,$this->ref_tin),
                mysqli_real_escape_string($this->DB->link_id,$this->product_name),
                $this->price,
                $this->cancelled, $this->id);
            return $this->DB->query($query) != FALSE;
        }

        function delete(){
               return $this->DB->query("DELETE FROM lignefacture WHERE lignefactureid=".$this->id);
        }

        function updcaract($caractvalid,$libelle_caract){
//                  $this->DB->query("DELETE FROM lignefacturecaracteristique WHERE lignefactureid=".$this->id);
                 $requete=$this->DB->query("SELECT libelleFR FROM caracteristiquevaleur WHERE caractvalid=".$caractvalid);
                 $caractlibelle=$this->DB->fetch_array($requete);
         $requete=$this->DB->query("SELECT * FROM article_caractval WHERE caractvalid=".$caractvalid." AND artid=".intval($this->artid));
         $article_caractval=$this->DB->fetch_array($requete);
         $this->DB->query("UPDATE lignefacture SET prix=prix+".floatval($article_caractval['differenceprix'])." WHERE lignefactureid=".$this->id);
                 return $this->DB->query("INSERT INTO lignefacturecaracteristique SET lignefactureid=".$this->id.", caractvalid=".$caractvalid.", libelle_caract='".$libelle_caract."', libelle_caractval='".$caractlibelle[0]."', differenceprix=".floatval($article_caractval['differenceprix']).", paht=".floatval($article_caractval['achat']));
        }


    }

?>
