<?php

define('MPORDER_SQL_SELECT', "id, vendor, UNIX_TIMESTAMP(order_date) AS order_date, order_status,
        payment_method, UNIX_TIMESTAMP(payment_date) AS payment_date, UNIX_TIMESTAMP(cancel_date)
        AS cancel_date, UNIX_TIMESTAMP(billing_date) AS billing_date, billing_number,
        vat_intra, billing_first_name, billing_last_name, billing_company, billing_address_1,
        billing_address_2, billing_zip, billing_city, billing_country, billing_phone,
        billing_fax, billing_email, shipping_first_name, shipping_last_name, shipping_company,
        shipping_address_1, shipping_address_2, shipping_zip, shipping_city, shipping_country,
        shipping_phone, shipping_fax, shipping_details, delivery_type, full_delivery,
        items_price, shipping_price, vat_100, currency, currency_exchange_rate, buy_prices_sum, reduc, reduc_type, marketplace, commission,comment,suivi2");


class MP_Order {

        var $id;
        var $vendor;
        var $order_date;
        var $order_status;
        var $order_state;
        var $payment_method;
        var $payment_date;
        var $payment_name;
        var $cancel_date;
        var $billing_date;
        var $billing_number;
        var $vat_intra;
        var $billing_first_name;
        var $billing_last_name;
        var $billing_company;
        var $billing_address_1;
        var $billing_address_2;
        var $billing_zip;
        var $billing_city;
        var $billing_country;
        var $billing_phone;
        var $billing_fax;
        var $billing_email;
        var $shipping_first_name;
        var $shipping_last_name;
        var $shipping_company;
        var $shipping_address_1;
        var $shipping_address_2;
        var $shipping_zip;
        var $shipping_city;
        var $shipping_country;
        var $shipping_phone;
        var $shipping_fax;
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
        var $commission;
        var $comment;
	    var $suivi2;

        var $orderstates=array(0=>"Nouvelle commande",1=>"Commande en attente de livraison",2=>"Commande livrée",3=>"Commande annulée",4=>"Commande en S.A.V.");
        var $marketplaces=array(""=>"Autre","01"=>"Amazon","04"=>"Price Minister","05"=>"Pixmania","06"=>"Windill","07"=>"Rue du Commerce","08"=>"Edengo","09"=>"Ebuyclub","10"=>"Ebay","11"=>"Le guide de la maison", "21" => "Showroomprive","22" => "Retif");

        function MP_Order($id=0) {
              asort($this->marketplaces);
              if($id!=0) return $this->load($id);
        }

        function internal_load($order) {
            $this->id             = trim($order['id']);
            $this->vendor         = intval($order['vendor']);
            $this->order_date     = $order['order_date'];
            $this->order_status   = intval($order['order_status']);
            $this->order_state    = $this->orderstates[$this->order_status];
            $this->payment_date   = intval($order['payment_date']);
            $this->cancel_date    = intval($order['cancel_date']);
            $this->billing_date   = $order['billing_date'];
            $this->billing_number = intval($order['billing_number']);
            $this->vat_intra      = trim($order['vat_intra']);
            $this->billing_first_name  = trim($order['billing_first_name']);
            $this->billing_last_name   = trim($order['billing_last_name']);
            $this->billing_company     = trim($order['billing_company']);
            $this->billing_address_1   = trim($order['billing_address_1']);
            $this->billing_address_2   = trim($order['billing_address_2']);
            $this->billing_zip         = trim($order['billing_zip']);
            $this->billing_city        = trim($order['billing_city']);
            $this->billing_country     = trim($order['billing_country']);
            $this->billing_phone       = trim($order['billing_phone']);
            $this->billing_fax         = trim($order['billing_fax']);
            $this->billing_email       = trim($order['billing_email']);
            $this->shipping_first_name = trim($order['shipping_first_name']);
            $this->shipping_last_name  = trim($order['shipping_last_name']);
            $this->shipping_company    = trim($order['shipping_company']);
            $this->shipping_address_1  = trim($order['shipping_address_1']);
            $this->shipping_address_2  = trim($order['shipping_address_2']);
            $this->shipping_zip        = trim($order['shipping_zip']);
            $this->shipping_city       = trim($order['shipping_city']);
            $this->shipping_country    = trim($order['shipping_country']);
            $this->shipping_phone      = trim($order['shipping_phone']);
            $this->shipping_fax        = trim($order['shipping_fax']);
            $this->shipping_details    = trim($order['shipping_details']);
            $this->delivery_type  = trim($order['delivery_type']);
            $this->full_delivery  = intval($order['full_delivery']);
            $this->items_price    = floatval($order['items_price']);
            $this->shipping_price = floatval($order['shipping_price']);
            $this->vat_100        = floatval($order['vat_100']);
            $this->currency       = trim($order['currency']);
            $this->currency_exchange_rate = floatval($order['currency_exchange_rate']);
            $this->buy_prices_sum = floatval($order['buy_prices_sum']);
            $this->reduc          = floatval($order['reduc']);
            $this->reduc_type     = trim($order['reduc_type']);
            $this->market_place_id= trim($order['marketplace']);
            $this->market_place   = $this->marketplaces[$this->market_place_id];
            $this->commission     = floatval($order['commission']);
            $this->comment        = stripslashes($order['comment']);
	        $this->suivi2	= stripslashes($order['suivi2']);
        }

        /* Charge la commande depuis la base de données. Renvoie FALSE en cas d'erreur. */
        function load($id) {
            $result = @mysql_query("SELECT ".MPORDER_SQL_SELECT." FROM mp_orders WHERE id = '".$id."'");
            if( $result == FALSE || ($order = @mysql_fetch_array($result)) == FALSE ) {
                return FALSE;
            } else {
                if(mysql_num_rows($result)==0) return false;
                $this->internal_load($order);
                return TRUE;
            }
        }

        function load_or_die($id) {
            $this->load($id) || die_on_error($this->database->error());
        }

        /* Crée une nouvelle commande en base de données. Renvoie FALSE en cas d'erreur. */
        function create() {
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
            if( $this->billing_date == 0 ) {
                $billing_date = 'NULL';
            } else {
                $billing_date = sprintf('FROM_UNIXTIME(%u)', $this->billing_date);
            }

            $sql = <<<SQL
            INSERT INTO mp_orders(id, vendor, order_date, order_status, payment_method,
                payment_date, cancel_date, billing_date, billing_number, vat_intra,
                billing_first_name, billing_last_name, billing_company, billing_address_1,
                billing_address_2, billing_zip, billing_city, billing_country, billing_phone,
                billing_fax, shipping_first_name, shipping_last_name, shipping_company,
                shipping_address_1, shipping_address_2, shipping_zip, shipping_city,
                shipping_country, shipping_phone, shipping_fax, shipping_details, delivery_type,
                full_delivery, items_price, shipping_price, vat_100, currency, currency_exchange_rate, buy_prices_sum, reduc, reduc_type, billing_email, marketplace,commission,comment)
            VALUES ("%s",%u, FROM_UNIXTIME(%u), %u, %u, %s, %s, %s, %u, "%s", "%s", "%s", "%s", "%s",
                "%s", "%s", "%s", "%s", "%s", "%s", "%s", "%s", "%s", "%s", "%s", "%s", "%s", "%s",
                "%s", "%s", "%s", "%s", %u, %f, %f, %f, "%s", %f, %f,%f,"%s","%s","%s",%f,"%s")
SQL;

            $query = sprintf($sql,$this->id, $this->vendor, $order_date, $this->order_status,
                $this->payment_method, $payment_date, $cancel_date, $billing_date, $this->get_billing_number(),
                htmlspecialchars(mysql_real_escape_string($this->vat_intra)),
                htmlspecialchars(mysql_real_escape_string($this->billing_first_name)),
                htmlspecialchars(mysql_real_escape_string($this->billing_last_name)),
                htmlspecialchars(mysql_real_escape_string($this->billing_company)),
                htmlspecialchars(mysql_real_escape_string($this->billing_address_1)),
                htmlspecialchars(mysql_real_escape_string($this->billing_address_2)),
                htmlspecialchars(mysql_real_escape_string($this->billing_zip)),
                htmlspecialchars(mysql_real_escape_string($this->billing_city)),
                htmlspecialchars(mysql_real_escape_string($this->billing_country)),
                htmlspecialchars(mysql_real_escape_string($this->billing_phone)),
                htmlspecialchars(mysql_real_escape_string($this->billing_fax)),
                htmlspecialchars(mysql_real_escape_string($this->shipping_first_name)),
                htmlspecialchars(mysql_real_escape_string($this->shipping_last_name)),
                htmlspecialchars(mysql_real_escape_string($this->shipping_company)),
                htmlspecialchars(mysql_real_escape_string($this->shipping_address_1)),
                htmlspecialchars(mysql_real_escape_string($this->shipping_address_2)),
                htmlspecialchars(mysql_real_escape_string($this->shipping_zip)),
                htmlspecialchars(mysql_real_escape_string($this->shipping_city)),
                htmlspecialchars(mysql_real_escape_string($this->shipping_country)),
                htmlspecialchars(mysql_real_escape_string($this->shipping_phone)),
                htmlspecialchars(mysql_real_escape_string($this->shipping_fax)),
                htmlspecialchars(mysql_real_escape_string($this->shipping_details)),
                htmlspecialchars(mysql_real_escape_string($this->delivery_type)),
                $this->full_delivery, $this->items_price, $this->shipping_price, $this->vat_100,
                htmlspecialchars(mysql_real_escape_string($this->currency)),
                $this->currency_exchange_rate,
                $this->buy_prices_sum,
                $this->reduc,
                $this->reduc_type,
                mysql_real_escape_string($this->billing_email),
                $this->market_place_id,
                $this->commission,
                htmlspecialchars(mysql_real_escape_string($this->comment)));
            if( @mysql_query($query) == FALSE ) {
                return FALSE;
            } else {
                $this->order_date = $order_date;

                return TRUE;
            }
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

            $sql = <<<SQL
            UPDATE mp_orders
            SET vendor = %u, order_date = FROM_UNIXTIME(%u), order_status = %u,
                payment_method = %u, payment_date = %s, cancel_date = %s, billing_date = %s,
                billing_number = %u, vat_intra = "%s", billing_first_name = "%s",
                billing_last_name = "%s", billing_company = "%s", billing_address_1 = "%s",
                billing_address_2 = "%s", billing_zip = "%s", billing_city = "%s",
                billing_country = "%s", billing_phone = "%s", billing_fax = "%s", billing_email = "%s",
                shipping_first_name = "%s", shipping_last_name = "%s", shipping_company = "%s",
                shipping_address_1 = "%s", shipping_address_2 = "%s", shipping_zip = "%s",
                shipping_city = "%s", shipping_country = "%s", shipping_phone = "%s",
                shipping_fax = "%s", shipping_details = "%s", delivery_type = "%s",
                full_delivery = %u, items_price = %f, shipping_price = %f, vat_100 = %f,
                currency = "%s", currency_exchange_rate = %f, buy_prices_sum = %f, reduc = %f, reduc_type = "%s", marketplace = "%s", commission=%f, comment="%s"
            WHERE id = "%s"
SQL;

            $query = sprintf($sql, $this->vendor, $this->order_date, $this->order_status,
                $this->payment_method, $payment_date, $cancel_date, $billing_date, $this->billing_number,
                htmlspecialchars(mysql_real_escape_string($this->vat_intra)),
                htmlspecialchars(mysql_real_escape_string($this->billing_first_name)),
                htmlspecialchars(mysql_real_escape_string($this->billing_last_name)),
                htmlspecialchars(mysql_real_escape_string($this->billing_company)),
                htmlspecialchars(mysql_real_escape_string($this->billing_address_1)),
                htmlspecialchars(mysql_real_escape_string($this->billing_address_2)),
                htmlspecialchars(mysql_real_escape_string($this->billing_zip)),
                htmlspecialchars(mysql_real_escape_string($this->billing_city)),
                htmlspecialchars(mysql_real_escape_string($this->billing_country)),
                htmlspecialchars(mysql_real_escape_string($this->billing_phone)),
                htmlspecialchars(mysql_real_escape_string($this->billing_fax)),
                htmlspecialchars(mysql_real_escape_string($this->billing_email)),
                htmlspecialchars(mysql_real_escape_string($this->shipping_first_name)),
                htmlspecialchars(mysql_real_escape_string($this->shipping_last_name)),
                htmlspecialchars(mysql_real_escape_string($this->shipping_company)),
                htmlspecialchars(mysql_real_escape_string($this->shipping_address_1)),
                htmlspecialchars(mysql_real_escape_string($this->shipping_address_2)),
                htmlspecialchars(mysql_real_escape_string($this->shipping_zip)),
                htmlspecialchars(mysql_real_escape_string($this->shipping_city)),
                htmlspecialchars(mysql_real_escape_string($this->shipping_country)),
                htmlspecialchars(mysql_real_escape_string($this->shipping_phone)),
                htmlspecialchars(mysql_real_escape_string($this->shipping_fax)),
                htmlspecialchars(mysql_real_escape_string($this->shipping_details)),
                htmlspecialchars(mysql_real_escape_string($this->delivery_type)),
                $this->full_delivery, $this->items_price, $this->shipping_price, $this->vat_100,
                htmlspecialchars(mysql_real_escape_string($this->currency)),
                $this->currency_exchange_rate,
                $this->buy_prices_sum,
                $this->reduc,
                $this->reduc_type,
                $this->market_place_id,
                $this->commission,
                $this->comment,
                $this->id);

            $result=mysql_query($query);
            return $result;
        }

        function update_or_die() {
            $this->update() || die_on_error();
        }

        function list_shipments() {
            $result = @mysql_query(sprintf('SELECT %s FROM shipment WHERE `order` = "%s" ORDER BY shipment_date DESC', SHIPMENT_SQL_SELECT, $this->id));
            if( $result == FALSE ) {
                return FALSE;
            } else {
                $shipments = array();
                while( $row = @mysql_fetch_array($result) ) {
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
            $result = @mysql_query($sql);
            if( $result == FALSE ) {
                return FALSE;
            } else {
                $lines = array();
                while( $row = @mysql_fetch_array($result) ) {
                    $line = new MP_Order_Line();
                    $line->internal_load($row);
                    $line->number=$row['number'];
                    $lines[$line->id] = $line;
                }

                return $lines;
            }
        }

        function list_lines() {
            $sql = "SELECT *,COUNT(ref_tin) AS number FROM mp_order_line WHERE `order` ='".$this->id."' GROUP BY ref_tin";
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
                foreach( $allowed_status as $key => $value ) { $allowed_status[$key] = 'order_status = ' . $value; }
                $status_string = implode($allowed_status, ' OR ');
            }
            if($condition!="") $status_string="($status_string) AND ($condition)";

                // Recherche le nombre total de commandes répondant aux critères
            $result_pages = mysql_query(sprintf('SELECT COUNT(*) AS nb FROM mp_orders WHERE (%s)',$status_string));

                // Exécute la requête de liste
            $sql=sprintf("SELECT ".MPORDER_SQL_SELECT." FROM mp_orders WHERE (%s) ORDER BY %s",$status_string,$sort_by);

            $result = mysql_query($sql);

            if( $result == FALSE ) {
                    return FALSE;
            } else {
                    // Calcule la dernière page (-1 => aucune page, aucune commande trouvée)
                    //$pages_row = @mysql_fetch_array($result_pages);
                    //$last_page = floor(($pages_row['nb']-1) / LIMIT_ORDERS_PER_PAGE);
                    $orders = array();
                    while( $row = @mysql_fetch_array($result) ) {
                        $order = new MP_Order();
                        $order->internal_load($row);
                        $orders[$order->id] = $order;
                    }
                    return $orders;
                    //return array('orders' => $orders, 'last_page' => $last_page);
           }
        }

        function get_billing_number() {
            $result = @mysql_query('SELECT MAX(billing_number) AS billing_number FROM mp_orders');
            if( $result == FALSE ) {
                return FALSE;
            } else {
                $row = mysql_fetch_array($result);
                if($row['billing_number']==0) $row['billing_number']=110481;
                $new_billing_number = $row['billing_number'] + 1;
                return $new_billing_number;
            }
        }

        function get_billing_number_or_die() {
            return ($result = $this->get_billing_number()) !== FALSE ? $result : die_on_error($this->database->error());
        }

        function calc_items_price(){
                 $requete=mysql_query("SELECT SUM(price) AS `total` FROM mp_order_line WHERE `order`='".$this->id."'");
                 $this->items_price=floatval(mysql_result($requete,0,'total'));
                 mysql_query("UPDATE mp_orders SET items_price=".$this->items_price." WHERE id='".$this->id."'");
        }

        function totalTTC(){
                 return ($this->items_price+$this->shipping_price)*TVA_COEFF;
        }

        function getbuyprice(){
            $return=0;
            $sql = "SELECT buy_price FROM mp_order_line WHERE `order` = '".$this->id."'";
            $requete=mysql_query($sql);
            while($linerow=mysql_fetch_object($requete)){
                  $return+=$linerow->buy_price;
            }
            return $return;
        }

        function getCustomerOrders($customer){
                 $return=array();
                 $sql="SELECT ".ORDER_SQL_SELECT." FROM mp_orders WHERE billing_first_name='".$customer->billing_first_name."' AND billing_last_name='".$customer->billing_last_name."' AND billing_address_1='".$customer->billing_address_1."'";
                 $requete=mysql_query($sql);
                 if(mysql_num_rows($requete)==0) return false;
                 while($item=mysql_fetch_array($requete)){
                     $order=new MP_Order();
                     $order->internal_load($item);
                     $return[]=$order;
                 }
                 return $return;
        }

	function compte_analytique(){
		 $ca=mysql_fetch_array(mysql_query("SELECT * FROM compte_analytique WHERE paysid=0 AND site='".$this->market_place_id."'"));
		 return $ca['code'];
	}

    }

    class MP_Order_line{
          var $id;
        var $order;
        var $shipment;
        var $ref_tin;
        var $barcode;
        var $product_name;
        var $special_offer;
        var $stock;
        var $price;
        var $commission;
        var $cancelled;
        var $buy_price;
        var $strikeout_price;
        var $number;

        // Etat transitoire de l'objet, champs calculés
        var $database, $quantity, $total_price;

        function MP_Order_Line($id=0) {

        }

        function internal_load($line) {
            $this->id            = intval($line['id']);
            $this->order         = trim($line['order']);
            $this->shipment      = intval($line['shipment']);
            $this->ref_tin       = trim($line['ref_tin']);
            $this->barcode       = trim($line['barcode']);
            $this->product_name  = trim($line['product_name']);
            $this->special_offer = intval($line['special_offer']) != 0;
            $this->stock         = intval($line['stock']) != 0;
            $this->price         = floatval($line['price']);
            $this->commission    = floatval($line['commission']);
            $this->cancelled     = intval($line['cancelled']) != 0;
            $this->buy_price     = $line['buy_price'];
            $this->strikeout_price=$line['strikeout_price'];
        }

        /* Charge la ligne de commande depuis la base de données. Renvoie FALSE en cas d'erreur. */
        function load($id) {
            $query = sprintf("SELECT * FROM mp_order_line WHERE id = ".$id);
            $result = @mysql_query($query);
            if( $result == FALSE || ($line = @mysql_fetch_array($result)) == FALSE ) {
                return FALSE;
            } else {
                $this->internal_load($line);
                return TRUE;
            }
        }

        function create() {
            $sql = "INSERT INTO mp_order_line(`order`, shipment, ref_tin, barcode, product_name, special_offer, stock, price, commission, cancelled, buy_price,strikeout_price) VALUES (\"%s\", %s, \"%s\", \"%s\", \"%s\", %u, %u, %f, %f, %u, %f, %f)";

            $shipment = intval($this->shipment);
            if( $shipment == 0 ) {
                $shipment = 'NULL';
            }
            $query = sprintf($sql, $this->order, $shipment,
                @mysql_real_escape_string($this->ref_tin),
                @mysql_real_escape_string($this->barcode),
                @mysql_real_escape_string($this->product_name),
                $this->special_offer, $this->stock, $this->price, $this->commission,
                $this->cancelled, $this->buy_price,$this->strikeout_price);
            if( @mysql_query($query) == FALSE ) {
                return FALSE;
            } else {
                $this->id = mysql_insert_id();
                return TRUE;
            }
        }

        function create_or_die() {
            $this->create() || die_on_error($this->database->error());
        }

        function update() {
            $shipment = intval($this->shipment);
            if( $shipment == 0 ) {
                $shipment = 'NULL';
            }

            $sql = <<<SQL
            UPDATE mp_order_line
            SET `order` = "%s", shipment = %s, ref_tin = "%s", barcode = "%s", product_name = "%s",
                special_offer = %u, stock = %u, price = %f, commission = %f, cancelled = %u, buy_price = %f, strikeout_price = %f
            WHERE id = %u
SQL;

            $query = sprintf($sql, $this->order, $shipment,
                @mysql_real_escape_string($this->ref_tin),
                @mysql_real_escape_string($this->barcode),
                @mysql_real_escape_string($this->product_name),
                $this->special_offer, $this->stock, $this->price, $this->commission,
                $this->cancelled, $this->buy_price, $this->strikeout_price, $this->id);
            return @mysql_query($query) != FALSE;
        }

        function delete(){
               return mysql_query("DELETE FROM mp_order_line WHERE id=".$this->id);
        }

        function updcaract($caractvalid,$libelle_caract){
                 mysql_query("DELETE FROM lignefacturecaracteristique WHERE lignefactureid=".$this->id);
                 $requete=mysql_query("SELECT libelleFR FROM caracteristiquevaleur WHERE caractvalid=".$caractvalid);
                 $caractlibelle=mysql_fetch_array($requete);
                 return mysql_query("INSERT INTO mp_lignefacturecaracteristique SET lignefactureid=".$this->id.", caractvalid=".$caractvalid.", libelle_caract='".$libelle_caract."', libelle_caractval='".$caractlibelle[0]."'");
        }


    }

?>