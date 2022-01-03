<?php
if ((!defined("REZEMPLOIKEY")) || (REZEMPLOIKEY != "3f5gd4ng2h5j4gh24,gh2j54fd2g54fg2h45")) {
    header("location: index.php");
    exit;
}

require_once(__DIR__ . "/../" . ProtectedFolder . "/utilisateurs.php");
if (!isUtilisateurConnecteParticulier()) {
    header("location: login.php");
    exit;
} else {
    $UtilisateurConnecte = getUtilisateurConnecte();
}

// Opération demandée (CRUD)
$op = isset($_POST["op"]) ? $_POST["op"] : "dsp";

// Affichage ("1" ou rien) (page complète ou formulaire spécifique) ou prise en charge des modifications provenant d'un formulaire ("0")
$dsp = (isset($_POST["dsp"]) ? $_POST["dsp"] : "1") == "1";

// checksum généré lors de l'appel précédent de la page
$verif = isset($_POST["v"]) ? $_POST["v"] : "";

require_once(__DIR__ . "/../" . ProtectedFolder . "/infos.php");
require_once(__DIR__ . "/../" . ProtectedFolder . "/erreurs.php");
require_once(__DIR__ . "/../" . ProtectedFolder . "/fonctions.php");


$mode = $op;
if ($dsp) {
    // chargement des infos de la partie identification (table "cv_infos")
    $qry = $db->prepare("select * from cv_infos where utilisateur_code=:uc");
    $qry->execute(array(":uc" => $UtilisateurConnecte->code));
    if (false !== ($recordcvi = $qry->fetch(PDO::FETCH_OBJ))) {
        $priv_key = $recordcvi->priv_key;
        $titre = $recordcvi->titre;
        $nom = $recordcvi->nom;
        $prenom = $recordcvi->prenom;
        $dn_annee = $recordcvi->datenaissance_annee;
        $dn_mois = $recordcvi->datenaissance_mois;
        $dn_jour = $recordcvi->datenaissance_jour;
        $datenaissance = $dn_jour . "/" . $dn_mois . "/" . $dn_annee; // TODO : à personnaliser pour pays anglosaxons MM/DD/YYYY
        $datenaissance_publique = $recordcvi->datenaissance_publiee == 1;
        $ne_code = $recordcvi->niveau_etude_code;
        $qry = $db->prepare("select libelle from niveaux_etudes where code=:c");
        $qry->execute(array(":c" => $ne_code));
        if (false !== ($recordne = $qry->fetch(PDO::FETCH_OBJ))) {
            $niveauetudes = $recordne->libelle;
        } else {
            $niveauetudes = "aucun";
        }
    } else {
        $priv_key = "";
        $titre = "";
        $nom = "";
        $prenom = "";
        $dn_annee = 0;
        $dn_mois = 0;
        $dn_jour = 0;
        $datenaissance = "00/00/0000";
        $datenaissance_publique = false;
        $ne_code = -1;
        $niveauetudes = "aucun";
    }

    $code = -1;
}

if (("chginfos" == $op) && (!$dsp)) {
    // traitement des infos passées en saisie pour cv_infos
    $code = intval(isset($_POST["code"]) ? $_POST["code"] : "-1");
    $titre = isset($_POST["titre"]) ? trim(strip_tags($_POST["titre"])) : "";
    $nom = isset($_POST["nom"]) ? trim(strip_tags($_POST["nom"])) : "";
    if (empty($nom)) {
        ajoute_erreur("Saisissez au moins votre nom.", "nom");
    }
    $prenom = isset($_POST["prenom"]) ? trim(strip_tags($_POST["prenom"])) : "";
    if (empty($prenom)) {
        ajoute_erreur("Saisissez votre prénom.", "prenom");
    }
    $dn_annee = intval(isset($_POST["naisannee"]) ? $_POST["naisannee"] : "0");
    if ($dn_annee >= StartAnneeNaissance) {
        $dn_mois = intval(isset($_POST["naismois"]) ? $_POST["naismois"] : "0");
        if (($dn_mois >= 1) && ($dn_mois <= 12)) {
            $dn_jour = intval(isset($_POST["naisjour"]) ? $_POST["naisjour"] : "0");
            if (($dn_jour < 1) || ($dn_jour > 31)) {
                $dn_jour = 0;
            } else {
                // TODO : s'assurer éventuellement que la date est cohérente (bon nombre de jours par rapport au mois sur cette année)
            }
        } else {
            $dn_mois = 0;
            $dn_jour = 0;
        }
    } else {
        $dn_annee = 0;
        $dn_mois = 0;
        $dn_jour = 0;
    }
    $datenaissance = $dn_jour . "/" . $dn_mois . "/" . $dn_annee; // TODO : à personnaliser pour pays anglosaxons MM/DD/YYYY
    $datenaissance_publique = (1 == intval(isset($_POST["naispub"]) ? $_POST["naispub"] : "0"));
    $ne_code = intval(isset($_POST["niveau_etude"]) ? $_POST["niveau_etude"] : "-1");
    if ($ne_code > -1) {
        $qry = $db->prepare("select libelle from niveaux_etudes where code=:c");
        $qry->execute(array(":c" => $ne_code));
        if (false === ($record = $qry->fetch(PDO::FETCH_OBJ))) {
            ajoute_erreur("Niveau d'études inconnu.", "niveau_etude");
            $ne_code = -1;
        } else {
            $niveauetudes = $record->libelle;
        }
    }
    if (!checkVerifChecksum($verif, KEY_VERIF_CV, $_SESSION["priv_key"], $UtilisateurConnecte->code, $UtilisateurConnecte->priv_key, $code)) {
        ajoute_erreur("Mise à jour impossible. Données incohérentes.");
        $code = -1;
    }
    if (empty($erreurs)) {
        $qry = $db->prepare("select code from cv_infos where utilisateur_code=:uc");
        $qry->execute(array(":uc" => $UtilisateurConnecte->code));
        if (false !== ($record = $qry->fetch(PDO::FETCH_OBJ))) { // Mise à jour des données
            $qry = $db->prepare("update cv_infos set titre=:t, nom=:n, prenom=:p, datenaissance_annee=:a, datenaissance_mois=:m, datenaissance_jour=:j, datenaissance_publiee=" . ($datenaissance_publique ? 1 : 0) . ", niveau_etude_code=:ne where code=:c");
            $qry->execute(array(":c" => $record->code, ":t" => $titre, ":n" => $nom, ":p" => $prenom, ":a" => $dn_annee, ":m" => $dn_mois, ":j" => $dn_jour, ":ne" => $ne_code));
        } else { // Pas de code, donc ajout de données pour cet utilisateur
            $qry = $db->prepare("insert into cv_infos (utilisateur_code, priv_key, titre, nom, prenom, datenaissance_annee, datenaissance_mois, datenaissance_jour, datenaissance_publiee, niveau_etude_code) values(:uc,:pk,:t,:n,:p,:a,:m,:j," . ($datenaissance_publique ? 1 : 0) . ",:ne)");
            $qry->execute(array(":uc" => $UtilisateurConnecte->code, ":pk" => generer_identifiant(), ":t" => $titre, ":n" => $nom, ":p" => $prenom, ":a" => $dn_annee, ":m" => $dn_mois, ":j" => $dn_jour, ":ne" => $ne_code));
        }
        $mode = "dsp";
    }
} else if (("dlt" == $op) && $dsp) {
    // affichage de la page en suppression
    $code = intval(isset($_POST["code"]) ? $_POST["code"] : "-1");
    $qry = $db->prepare("select * from types_diplomes where code=:c");
    $qry->execute(array(":c" => $code));
    if (false !== ($record = $qry->fetch(PDO::FETCH_OBJ))) {
        $libelle = $record->libelle;
        $priv_key = $record->priv_key;
    } else {
        ajoute_erreur("Enregistrement inexistant, suppression impossible.");
        $mode = "lst";
    }
} else if ("dlt" == $op) {
    // traitement des infos passées en suppression
    $code = intval(isset($_POST["code"]) ? $_POST["code"] : "-1");
    $qry = $db->prepare("select * from types_diplomes where code=:c");
    $qry->execute(array(":c" => $code));
    if ((false !== ($record = $qry->fetch(PDO::FETCH_OBJ))) && checkVerifChecksum($verif, KEY_VERIF, $record->code, $record->priv_key)) {
        $qry = $db->prepare("delete from types_diplomes where code=:c");
        $qry->execute(array(":c" => $code));
        $mode = "lst";
    } else {
        ajoute_erreur("Enregistrement inexistant, suppression impossible.");
        $mode = "lst";
    }
}

$PageTitle = "Mon CV";
require_once(__DIR__ . "/_header.php");
require_once(__DIR__ . "/../" . ProtectedFolder . "/textesdusite.php"); ?>
    <form action="moncv.php" method="post" id="frm">
        <input type="hidden" name="op" id="op" value="<?php print(isset($mode) ? $mode : ""); ?>">
        <input type="hidden" name="dsp" id="dsp" value="0">
        <input type="hidden" name="v" id="v"
               value="<?php print(getVerifChecksum(KEY_VERIF_CV, $_SESSION["priv_key"], $UtilisateurConnecte->code, $UtilisateurConnecte->priv_key, (isset($code) ? $code : -1))); ?>">
        <input type="hidden" name="code" id="code" value="<?php print(isset($code) ? $code : -1); ?>">
        <?php if ("dsp" == $mode) { ?>
            <section class="fdb-block py-0">
                <div class="container py-5 my-5">
                    <div class="row py-5">
                        <div class="col py-5">
                            <div class="fdb-box fdb-touch">
                                <div class="row text-center justify-content-center">
                                    <div class="col-12 col-md-9 col-lg-7"><?php
                                        if (getTexteDuSite("moncv", $titre_page, $texte_page)) {
                                            if (!empty($titre_page)) print("<h1>" . $titre_page . "</h1>");
                                            print($texte_page);
                                        } else {
                                            require_once(__DIR__ . "/../" . ProtectedFolder . "/erreurs.php");
                                            ajoute_erreur("Page \"moncv\" non trouvée.");
                                        } ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        <?php } ?>
        <section class="fdb-block pt-0">
            <div class="container">
                <div class="row text-center justify-content-center pt-5">
                    <div class="col-12 col-md-7">
                        <h1>Identification</h1>
                    </div>
                </div>
                <div class="row justify-content-center pt-4">
                    <div class="col-12 col-md-7">
                        <?php
                        if ("dsp" == $mode) { ?>
                            <div class="row">
                                <div class="col">
                                    Titre : <?php print(htmlentities($titre)); ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    Nom : <?php print(htmlentities($nom)); ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    Prénom : <?php print(htmlentities($prenom)); ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    Date de naissance
                                    : <?php print(htmlentities($datenaissance . " (" . ($datenaissance_publique ? "Publique" : "Privee") . ")")); ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    Niveau d'études : <?php print(htmlentities($niveauetudes)); ?>
                                </div>
                            </div>
                            <?php
                        } else if ("chginfos" == $mode) { ?>
                            <div class="row">
                                <div class="col"><label for="titre">Votre titre</label>
                                    <input type="text" class="form-control" placeholder="Titre"
                                           id="titre"
                                           name="titre"
                                           value="<?php print(isset($titre) ? htmlentities($titre) : ""); ?>"
                                           autofocus>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col"><label for="nom">Votre nom</label>
                                    <input type="text" class="form-control" placeholder="Nom"
                                           id="nom"
                                           name="nom"
                                           value="<?php print(isset($nom) ? htmlentities($nom) : ""); ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col"><label for="prenom">Votre prénom</label>
                                    <input type="text" class="form-control" placeholder="Prénom"
                                           id="prenom"
                                           name="prenom"
                                           value="<?php print(isset($prenom) ? htmlentities($prenom) : ""); ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col"><label for="naisjour">Votre date de naissance</label>
                                    <select name="naisjour" id="naisjour">
                                        <option value="0">Jour</option><?php
                                        for ($i = 1; $i < 32; $i++) {
                                            print("<OPTION VALUE=\"" . $i . "\"" . (($dn_jour == $i) ? " selected" : "") . ">" . $i . "</OPTION>");
                                        }
                                        ?></select>
                                    <select name="naismois" id="naismois">
                                        <option value="0">Mois</option><?php
                                        for ($i = 1; $i < 13; $i++) {
                                            print("<OPTION VALUE=\"" . $i . "\"" . (($dn_mois == $i) ? " selected" : "") . ">" . $i . "</OPTION>");
                                            // TODO : éventuellement remplacer le numéro du mois par son libellé
                                        }
                                        ?></select>
                                    <select name="naisannee" id="naisannee">
                                        <option value="0">Année</option><?php
                                        for ($i = StartAnneeNaissance; $i < intval(date("Y")); $i++) {
                                            print("<OPTION VALUE=\"" . $i . "\"" . (($dn_annee == $i) ? " selected" : "") . ">" . $i . "</OPTION>");
                                        }
                                        ?></select>
                                    <select name="naispub" id="naispub">
                                        <option value="0">Privée</option>
                                        <option value="1"<?php print($datenaissance_publique ? " selected" : ""); ?>>
                                            Publique
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col"><label for="niveau_etude">Niveau d'études</label>
                                    <select class="form-control" id="niveau_etude"
                                            name="niveau_etude">
                                        <option value="-1">aucun</option>
                                        <?php
                                        $qry = $db->prepare("select * from niveaux_etudes order by libelle");
                                        $qry->execute();
                                        while (false !== ($record = $qry->fetch(PDO::FETCH_OBJ))) {
                                            print("<OPTION VALUE=\"" . $record->code . "\"" . (($ne_code == $record->code) ? " selected" : "") . ">" . htmlentities($record->libelle) . "</OPTION>");
                                        }
                                        ?></select>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="row mt-4">
                            <div class="col text-center">
                                <?php if ("chginfos" == $mode) { ?>
                                    <button type="submit" class="btn btn-success">Enregistrer</button>
                                    <button type="button" class="btn btn-warning" onclick="btnRetourClick();">
                                        Annuler
                                    </button>
                                <?php } else { ?>
                                    <button type="button" class="btn btn-primary" onclick="btnModifierInfosClick();">
                                        Modifier
                                    </button>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row-100"></div>
            </div>
        </section>
    </form>

    <script>
        function btnModifierInfosClick() {
            document.getElementById('code').value = -1;
            document.getElementById('op').value = 'chginfos';
            document.getElementById('dsp').value = '1';
            document.getElementById('frm').submit();
        }

        function btnRetourClick() {
            document.getElementById('code').value = -1;
            document.getElementById('op').value = 'dsp';
            document.getElementById('dsp').value = '1';
            document.getElementById('frm').submit();
        }
    </script><?php
require_once(__DIR__ . "/_erreur-formulaire.php");
require_once(__DIR__ . "/_footer.php");

// TODO : infos du CV

// TODO : ajouter date de naissance en saisie
// TODO : ajouter niveau d'études en saisie