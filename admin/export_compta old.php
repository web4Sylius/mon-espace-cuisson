<?php

require 'admin_global.php';
include './header.php';
include './menu.php';


?>
   <style>
        /* ------------------------------ */
        /*  GLOBAL PAGE STYLE             */
        /* ------------------------------ */
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        h2 {
            margin-top: 25px;
            font-size: 22px;
            color: #333;
        }



        .export-line {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
        }

        .export-line label {
            font-size: 13px;
        }

        .export-line input[type="date"] {
            padding: 4px 6px;
        }

        .btn-generate {
            padding: 6px 14px;
            background: #2c6aa0;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-generate:hover {
            background: #1f4f78;
        }


        .controle-ca {
            .form-row {
                display: flex;
                gap: 30px;
                margin-bottom: 25px;
            }

            .form-group {
                flex: 1;
            }

            label {
                display: block;
                margin-bottom: 6px;
                font-weight: bold;
            }

            input[type="date"] {
                width: 100%;
                padding: 8px 10px;
                border: 1px solid #ccc;
                border-radius: 4px;
            }

            .submit-zone {
                text-align: center;
                margin-top: 30px;
            }

            button {
                background: #2c7be5;
                color: #fff;
                border: none;
                padding: 12px 30px;
                font-size: 15px;
                border-radius: 4px;
                cursor: pointer;
            }

            button:hover {
                background: #1a68d1;
            }
        }

    </style>
<?php
$tm1s = time() - (86400 * 9);
//$tm1s=time()-(86400*5);
$yesterday = $tm1s + (86400 * 6);
//$yesterday=$tm1s+(86400*2);

$ducheque = time() - (86400 * 6);
//$ducheque=time()-(86400*2);
$aucheque = time();
?>
    <h1>Exports comptabilité</h1>
    <br>


    <ul class="menu-links">
        <li class="export-line">
            <label>Du :</label>
            <input type="date" class="date-du">

            <label>Au :</label>
            <input type="date" class="date-au">

            <button
                    type="button"
                    class="btn-generate"
                    data-url="https://www.mon-espace-cuisson.com/admin/export/export_fournisseur.php"
            >
                Générer l’export fournisseurs
            </button>
            Date de saisie par Cécile, du lundi au vendredi
            <?php // echo "https://www.mon-espace-cuisson.com/admin/export/export_fournisseur.php?du=" . date("d/m/Y", $tm1s) . "&au=" . date("d/m/Y", $yesterday) . "&admin_username=9d3bb895f22bf0afa958d68c2a58ded7", "acilyon-fournis-" . date("Ymd", $tm1s) . "-au-" . date("md", $yesterday) . ".csv" ?>
        </li>

        <li class="export-line">
            <label>Du :</label>
            <input type="date" class="date-du">

            <label>Au :</label>
            <input type="date" class="date-au">

            <button
                    type="button"
                    class="btn-generate"
                    data-url="https://www.mon-espace-cuisson.com/admin/export/export_frais.php"
            >
                Générer l’export frais
            </button>
            Date de saisie par Cécile, du lundi au vendredi
        </li>

        <li class="export-line">
            <label>Du :</label>
            <input type="date" class="date-du">

            <label>Au :</label>
            <input type="date" class="date-au">

            <button
                    type="button"
                    class="btn-generate"
                    data-url="https://www.mon-espace-cuisson.com/admin/export/export_compta2.php"
            >
                Générer l’export clients
            </button>
             Date de facturation clients
        </li>

        <li class="export-line">
            <label>Du :</label>
            <input type="date" class="date-du">

            <label>Au :</label>
            <input type="date" class="date-au">

            <button
                    type="button"
                    class="btn-generate"
                    data-url="https://www.mon-espace-cuisson.com/admin/export/export_cheque.php"
            >
                Générer l’export chèque
            </button>
            Date de dépot de chèques, du lundi au vendredi
        </li>

        <li class="export-line">
            <label>Du :</label>
            <input type="date" class="date-du">

            <label>Au :</label>
            <input type="date" class="date-au">

            <button
                    type="button"
                    class="btn-generate"
                    data-url="https://www.mon-espace-cuisson.com/admin/export/export_cutoff.php"
            >
                Générer l’export Cut Off / FNP
            </button>
            Envoi le lundi. Au plus tard le 07 de chaque mois
        </li>

        <li class="export-line">
            <label>Du :</label>
            <input type="date" class="date-du">

            <label>Au :</label>
            <input type="date" class="date-au">

            <button
                    type="button"
                    class="btn-generate"
                    data-url="https://www.mon-espace-cuisson.com/admin/export/export_reajustement.php"
            >
                Générer l’export Réajustement
            </button>
            Envoi le lundi
        </li>

        <li class="export-line">
            <label>Du :</label>
            <input type="date" class="date-du">

            <label>Au :</label>
            <input type="date" class="date-au">

            <button
                    type="button"
                    class="btn-generate"
                    data-url="https://www.mon-espace-cuisson.com/admin/export/export_facilite_paiement.php"
            >
                Générer l’export Facilités de paiement
            </button>
            Date de réception des règlements, mettre un mois complet du 01 au 31
        </li>
		
        <li class="export-line">
            <label>Du :</label>
            <input type="date" class="date-du">

            <label>Au :</label>
            <input type="date" class="date-au">

            <button
                    type="button"
                    class="btn-generate"
                    data-url="https://www.mon-espace-cuisson.com/admin/export/export_fournisseur2.php"
            >
                Générer l’export Acilyon Developpements pour fournisseurs Acilyon
            </button>
        </li>
    </ul>
	
	
    <br>
    <br>
    <br>

    <div class="page-container controle-ca">
        <h1>Contrôle CA</h1>

        <form action="export/export_ca_2026.php" method="GET" target="_blank">
            <div class="form-row">
                <div class="form-group">
                    <label>Date de début</label>
                    <input type="date" name="date_debut" required>
                </div>

                <div class="form-group">
                    <label>Date de fin</label>
                    <input type="date" name="date_fin" required>
                </div>
            </div>

            <div class="submit-zone">
                <button type="submit">Générer l’export CSV</button>
            </div>
        </form>
    </div>
	
    <script>
        document.querySelectorAll('.btn-generate').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const container = btn.closest('.export-line');
                const du = container.querySelector('.date-du').value;
                const au = container.querySelector('.date-au').value;

                if (!du || !au) {
                    alert('Veuillez sélectionner les deux dates');
                    return;
                }

                // Conversion YYYY-MM-DD -> DD/MM/YYYY
                const formatDate = d => {
                    const parts = d.split('-');
                    return parts[2] + '/' + parts[1] + '/' + parts[0];
                };

                const url =
                    btn.dataset.url +
                    '?du=' + encodeURIComponent(formatDate(du)) +
                    '&au=' + encodeURIComponent(formatDate(au)) +
                    '&admin_username=9d3bb895f22bf0afa958d68c2a58ded7';

                window.location.href = url;
            });
        });
    </script>

<!--<h2>Envoyé le lundi :</h2>
    <ul class="menu-links">
        <li><a href="<?php /*echo "https://www.mon-espace-cuisson.com/admin/export/export_fournisseur.php?du=" . date("d/m/Y", $tm1s) . "&au=" . date("d/m/Y", $yesterday) . "&admin_username=9d3bb895f22bf0afa958d68c2a58ded7", "acilyon-fournis-" . date("Ymd", $tm1s) . "-au-" . date("md", $yesterday) . ".csv", "text/csv";*/?>">Lien vers l'export fournisseurs</a></li>
        <li><a href="<?php /*echo "https://www.mon-espace-cuisson.com/admin/export/export_frais.php?du=" . date("d/m/Y", $tm1s) . "&au=" . date("d/m/Y", $yesterday) . "&admin_username=9d3bb895f22bf0afa958d68c2a58ded7", "acilyon-frais-" . date("Ymd", $tm1s) . "-au-" . date("md", $yesterday) . ".csv", "text/csv";*/?>">Lien vers l'export frais</a></li>
        <li><a href="<?php /*echo "https://www.mon-espace-cuisson.com/admin/export/export_compta2.php?du=" . date("d/m/Y", $tm1s) . "&au=" . date("d/m/Y", $yesterday) . "&admin_username=9d3bb895f22bf0afa958d68c2a58ded7", "acilyon-clients-" . date("Ymd", $tm1s) . "-au-" . date("md", $yesterday) . ".csv", "text/csv";*/?>">Lien vers l'export compta</a></li>
        <li><a href="<?php /*echo "https://www.mon-espace-cuisson.com/admin/export/export_cheque.php?du=" . date("d/m/Y", $ducheque) . "&au=" . date("d/m/Y", $aucheque) . "&admin_username=9d3bb895f22bf0afa958d68c2a58ded7", "acilyon-cheques-" . date("Ymd", $ducheque) . "-au-" . date("md", $aucheque) . ".csv", "text/csv";*/?>">Lien vers l'export chèques</a></li>
    </ul>
<h2>Entre le 1 et le 7 de chaque mois :</h2>
    <ul class="menu-links">
        <li><a href="<?php /*echo "https://www.mon-espace-cuisson.com/admin/export/export_cutoff.php?du=1/1/" . date("Y") . "&au=" . date("d/m/Y", $yesterday) . "&admin_username=9d3bb895f22bf0afa958d68c2a58ded7", "acilyon-FNP-" . date("Ym", $yesterday) . ".ods", "application/vnd.oasis.opendocument.spreadsheet";*/?>">Lien vers l'export Cut off</a></li>
        <?php
/*            $from = new DateTime("first day of last month");
        */?>
    </ul>
<h2>Entre le 15 et le 21 de chaque mois :</h2>
    <ul class="menu-links">
        <li><a href="<?php /*echo
 "https://www.mon-espace-cuisson.com/admin/export/export_reajustement.php?admin_username=9d3bb895f22bf0afa958d68c2a58ded7", "reajustement-" . $from->format("Ym") . ".ods", "application/vnd.oasis.opendocument.spreadsheet-template";*/?>">Réajustement</a></li>
        <?php
/*            $lastMonth = '' . date('Ym') - 1;
        */?>
        <li><a href="<?php /*echo  'https://www.mon-espace-cuisson.com/admin/export/export_facilite_paiement.php?' . 'admin_username=9d3bb895f22bf0afa958d68c2a58ded7', 'FacilitePaiement_' . $lastMonth . '.ods', 'application/vnd.oasis.opendocument.spreadsheet-template';*/?>">Facilités de paiement</a></li>
            ?>">Réajustement</a></li>
        <li></li>
    </ul>-->

<?php
include './footer.php';
