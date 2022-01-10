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
        $mode = "lstinfos";
    }
    if (empty($erreurs)) {
        $qry = $db->prepare("select code from cv_infos where utilisateur_code=:uc");
        $qry->execute(array(":uc" => $UtilisateurConnecte->code));
        if (false !== ($record = $qry->fetch(PDO::FETCH_OBJ))) { // Mise à jour des données
            $qry = $db->prepare("update cv_infos set titre=:t, nom=:n, prenom=:p, datenaissance_annee=:a, datenaissance_mois=:m, datenaissance_jour=:j, datenaissance_publiee=" . ($datenaissance_publique ? 1 : 0) . ", niveau_etude_code=:ne where code=:c");
            $qry->execute(array(":c" => $record->code, ":t" => $titre, ":n" => $nom, ":p" => $prenom, ":a" => $dn_annee, ":m" => $dn_mois, ":j" => $dn_jour, ":ne" => $ne_code));
        } else { // Pas de code, donc ajout de données pour cet utilisateur
            $qry = $db->prepare("insert into cv_infos (utilisateur_code, priv_key, titre, nom, prenom, datenaissance_annee, datenaissance_mois, datenaissance_jour, datenaissance_publiee, niveau_etude_code) values (:uc,:pk,:t,:n,:p,:a,:m,:j," . ($datenaissance_publique ? 1 : 0) . ",:ne)");
            $qry->execute(array(":uc" => $UtilisateurConnecte->code, ":pk" => generer_identifiant(), ":t" => $titre, ":n" => $nom, ":p" => $prenom, ":a" => $dn_annee, ":m" => $dn_mois, ":j" => $dn_jour, ":ne" => $ne_code));
        }
        $mode = "lstinfos";
    }
} else if (("chglangue" == $op) && $dsp) {
    // affichage de la page en modification ou ajout
    $langue_code = intval(isset($_POST["code"]) ? $_POST["code"] : "-1");
    if (-1 == $langue_code) {
        $code = -1;
        $niveau_langue_code = -1;
        $pratique_langue_code = -1;
    } else {
        $code = $langue_code;
        $qry = $db->prepare("select * from cv_langues where langue_code=:lc and utilisateur_code=:uc");
        $qry->execute(array(":lc" => $langue_code, ":uc" => $UtilisateurConnecte->code));
        if (false !== ($record = $qry->fetch(PDO::FETCH_OBJ))) {
            $priv_key = $record->priv_key;
            $niveau_langue_code = $record->niveau_langue_code;
            $pratique_langue_code = $record->pratique_langue_code;
        } else {
            ajoute_erreur("Enregistrement inexistant, modification impossible.");
            $mode = "dsp";
        }
    }
} else if ("chglangue" == $op) {
    // traitement des infos passées en modification
    $code = intval(isset($_POST["code"]) ? $_POST["code"] : "-1");
    if (!checkVerifChecksum($verif, KEY_VERIF_CV, $_SESSION["priv_key"], $UtilisateurConnecte->code, $UtilisateurConnecte->priv_key, $code)) {
        ajoute_erreur("Mise à jour impossible. Données incohérentes.");
        $code = -1;
        $mode = "lstlangues";
    } else { // TODO : contrôler que le code / utilisateur existe et qu'on a le droit dessus
        $langue_code = intval(isset($_POST["langue"]) ? $_POST["langue"] : "-1");
        $qry = $db->prepare("select code from langues where code=:c");
        $qry->execute(array(":c" => $langue_code));
        if (false !== ($record = $qry->fetch(PDO::FETCH_OBJ))) {
            $niveau_langue_code = intval(isset($_POST["niveau"]) ? $_POST["niveau"] : "-1");
            $qry = $db->prepare("select code from niveaux_langues where code=:c");
            $qry->execute(array(":c" => $niveau_langue_code));
            if (false !== ($record = $qry->fetch(PDO::FETCH_OBJ))) {
                $pratique_langue_code = intval(isset($_POST["pratique"]) ? $_POST["pratique"] : "-1");
                $qry = $db->prepare("select code from pratiques_langues where code=:c");
                $qry->execute(array(":c" => $pratique_langue_code));
                if (false !== ($record = $qry->fetch(PDO::FETCH_OBJ))) {
                    if (-1 == $code) { // Ajout
                        $qry = $db->prepare("insert into cv_langues (langue_code, utilisateur_code, priv_key, niveau_langue_code, pratique_langue_code) values (:lc, :uc, :pk, :nlc, :plc)");
                        $qry->execute(array(":pk" => generer_identifiant(), ":uc" => $UtilisateurConnecte->code, ":lc" => $langue_code, ":nlc" => $niveau_langue_code, ":plc" => $pratique_langue_code));
                    } else { // Mise à jour
                        $qry = $db->prepare("update cv_langues set langue_code=:lc, niveau_langue_code=:nlc, pratique_langue_code=:plc where langue_code=:c and utilisateur_code=:uc");
                        $qry->execute(array(":c" => $code, ":uc" => $UtilisateurConnecte->code, ":lc" => $langue_code, ":nlc" => $niveau_langue_code, ":plc" => $pratique_langue_code));
                    }
                    $mode = "lstlangues";
                } else {
                    ajoute_erreur("Pratique inexistante, modification impossible.");
                }
            } else {
                ajoute_erreur("Niveau inexistant, modification impossible.");
            }
        } else {
            ajoute_erreur("Langue inexistante, modification impossible.");
        }
    }
} else if (("dltlangue" == $op) && $dsp) {
    // affichage de la page en suppression
    $langue_code = intval(isset($_POST["code"]) ? $_POST["code"] : "-1");
    if (-1 == $langue_code) {
        $code = -1;
        $niveau_langue_code = -1;
        $pratique_langue_code = -1;
    } else {
        $code = $langue_code;
        $qry = $db->prepare("select * from cv_langues where langue_code=:lc and utilisateur_code=:uc");
        $qry->execute(array(":lc" => $langue_code, ":uc" => $UtilisateurConnecte->code));
        if (false !== ($record = $qry->fetch(PDO::FETCH_OBJ))) {
            $priv_key = $record->priv_key;
            $niveau_langue_code = $record->niveau_langue_code;
            $pratique_langue_code = $record->pratique_langue_code;
        } else {
            ajoute_erreur("Enregistrement inexistant, modification impossible.");
            $mode = "dsp";
        }
    }
} else if ("dltlangue" == $op) {
    // traitement des infos passées en suppression
    $code = intval(isset($_POST["code"]) ? $_POST["code"] : "-1");
    if (!checkVerifChecksum($verif, KEY_VERIF_CV, $_SESSION["priv_key"], $UtilisateurConnecte->code, $UtilisateurConnecte->priv_key, $code)) {
        ajoute_erreur("Mise à jour impossible. Données incohérentes.");
        $code = -1;
        $mode = "lstlangues";
    } else {
        $langue_code = $code;
        $qry = $db->prepare("select * from cv_langues where langue_code=:lc and utilisateur_code=:uc");
        $qry->execute(array(":lc" => $langue_code, ":uc" => $UtilisateurConnecte->code));
        if (false !== ($record = $qry->fetch(PDO::FETCH_OBJ))) {
            $qry = $db->prepare("delete from cv_langues where langue_code=:lc and utilisateur_code=:uc");
            $qry->execute(array(":lc" => $langue_code, ":uc" => $UtilisateurConnecte->code));
        } else {
            ajoute_erreur("Enregistrement inexistant, suppression impossible.");
        }
        $mode = "lstlangues";
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
        <?php
        // **************************************************
        // Bloc d'info en tête de page
        if ("dsp" == $mode) {
            ?>
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
            <?php
        }

        // **************************************************
        // Bloc d'identification
        if (("dsp" == $mode) || ("lstinfos" == $mode) || ("chginfos" == $mode)) {
            ?>
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
                            if (("dsp" == $mode) || ("lstinfos" == $mode)) { ?>
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
                                        <button type="button" class="btn btn-warning"
                                                onclick="btnAnnulerClick('lstinfos');">
                                            Annuler
                                        </button>
                                        <button type="button" class="btn btn-primary" onclick="btnRetourClick();">
                                            Retour
                                        </button><?php
                                    } else {
                                        ?>
                                        <button type="button" class="btn btn-primary"
                                                onclick="btnModifierInfosClick();">
                                            Modifier
                                        </button><?php
                                        if ("lstinfos" == $mode) {
                                            ?>
                                            <button type="button" class="btn btn-primary" onclick="btnRetourClick();">
                                                Retour
                                            </button><?php
                                        }
                                    }
                                    ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="row-100"></div>
                </div>
            </section>
            <?php
        }

        // **************************************************
        // Bloc de langues
        if (("dsp" == $mode) || ("lstlangues" == $mode) || ("chglangue" == $mode) || ("dltlangue" == $mode)) {
            ?>
            <section class="fdb-block pt-0">
            <div class="container">
                <div class="row text-center justify-content-center pt-5">
                    <div class="col-12 col-md-7">
                        <h1>Langues</h1>
                    </div>
                </div>
                <div class="row justify-content-center pt-4">
                    <div class="col-12 col-md-7">
                        <?php
                        if (("dsp" == $mode) || ("lstlangues" == $mode)) {
                            $qry = $db->prepare("select cv.langue_code as cv_code, l.libelle as langue_libelle, nl.libelle as niveau_libelle, pl.libelle as pratique_libelle from cv_langues cv,langues l,niveaux_langues nl, pratiques_langues pl where (cv.utilisateur_code=:uc) and (cv.langue_code=l.code) and (cv.niveau_langue_code=nl.code) and (cv.pratique_langue_code=pl.code) order by l.libelle");
                            $qry->execute(array(":uc" => $UtilisateurConnecte->code));
                            ?>
                            <table class="table text-center mt-5 d-table">
                            <tbody>
                            <tr>
                                <th scope="col">Langue</th>
                                <th scope="col">Niveau</th>
                                <th scope="col">Pratique</th>
                                <th scope="col">
                                    <button type="button" class="btn btn-primary"
                                            onclick="btnAjouterLangueClick();">Ajouter
                                    </button>
                                </th>
                            </tr>
                            <?php while (false !== ($record = $qry->fetch(PDO::FETCH_OBJ))) { ?>
                                <tr>
                                    <td><?php print(htmlentities($record->langue_libelle)); ?></td>
                                    <td><?php print(htmlentities($record->niveau_libelle)); ?></td>
                                    <td><?php print(htmlentities($record->pratique_libelle)); ?></td>
                                    <td>
                                        <button type="button" class="btn btn-primary"
                                                onclick="btnModifierLangueClick(<?php print($record->cv_code); ?>);">
                                            Modifier
                                        </button>
                                        <button type="button" class="btn btn-primary"
                                                onclick="btnSupprimerLangueClick(<?php print($record->cv_code); ?>)">
                                            Supprimer
                                        </button>
                                    </td>
                                </tr>
                            <?php } ?>
                            </tbody>
                            </table><?php
                            if ("lstlangues" == $mode) {
                                ?>
                                <div class="row mt-4">
                                    <div class="col text-center">
                                        <button type="button" class="btn btn-primary" onclick="btnRetourClick();">
                                            Retour
                                        </button>
                                    </div>
                                </div>
                                <?php
                            }
                        } else if ("chglangue" == $mode) { ?>
                            <div class="row">
                                <?php if (-1 < $langue_code) { ?>
                                    <div class="col">Langue : <?php
                                        $qry = $db->prepare("select libelle from langues where code=:c");
                                        $qry->execute(array(":c" => $langue_code));
                                        if (false !== ($record = $qry->fetch(PDO::FETCH_OBJ))) {
                                            print(htmlentities($record->libelle));
                                        } else {
                                            print("n/a");
                                        }
                                        ?></div><input type="hidden" name="langue"
                                                       value="<?php print($langue_code); ?>">
                                <?php } else { ?>
                                    <div class="col"><label for="langue">Langue</label>
                                        <select class="form-control" id="langue"
                                                name="langue"><?php
                                            $qry = $db->prepare("select langues.* from langues left join cv_langues on langues.code=cv_langues.langue_code and cv_langues.utilisateur_code=:uc where (cv_langues.langue_code is null) order by langues.libelle");
                                            $qry->execute(array(":uc" => $UtilisateurConnecte->code));
                                            while (false !== ($record = $qry->fetch(PDO::FETCH_OBJ))) {
                                                print("<OPTION VALUE=\"" . $record->code . "\"" . (($langue_code == $record->code) ? " selected" : "") . ">" . htmlentities($record->libelle) . "</OPTION>");
                                            }
                                            ?></select>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="row">
                                <div class="col"><label for="niveau">Niveau</label>
                                    <select class="form-control" id="niveau"
                                            name="niveau"><?php
                                        $qry = $db->prepare("select * from niveaux_langues order by libelle");
                                        $qry->execute();
                                        while (false !== ($record = $qry->fetch(PDO::FETCH_OBJ))) {
                                            print("<OPTION VALUE=\"" . $record->code . "\"" . (($niveau_langue_code == $record->code) ? " selected" : "") . ">" . htmlentities($record->libelle) . "</OPTION>");
                                        }
                                        ?></select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col"><label for="pratique">Pratique</label>
                                    <select class="form-control" id="pratique"
                                            name="pratique"><?php
                                        $qry = $db->prepare("select * from pratiques_langues order by libelle");
                                        $qry->execute();
                                        while (false !== ($record = $qry->fetch(PDO::FETCH_OBJ))) {
                                            print("<OPTION VALUE=\"" . $record->code . "\"" . (($pratique_langue_code == $record->code) ? " selected" : "") . ">" . htmlentities($record->libelle) . "</OPTION>");
                                        }
                                        ?></select>
                                </div>
                            </div>
                            <div class="row mt-4">
                                <div class="col text-center">
                                    <button type="submit" class="btn btn-success">Enregistrer</button>
                                    <button type="button" class="btn btn-warning"
                                            onclick="btnAnnulerClick('lstlangues');">
                                        Annuler
                                    </button>
                                    <button type="button" class="btn btn-primary" onclick="btnRetourClick();">
                                        Retour
                                    </button>
                                </div>
                            </div><?php
                        } else if ("dltlangue" == $mode) { ?>
                            <div class="row">
                                <div class="col">Langue : <?php
                                    $qry = $db->prepare("select libelle from langues where code=:c");
                                    $qry->execute(array(":c" => $langue_code));
                                    if (false !== ($record = $qry->fetch(PDO::FETCH_OBJ))) {
                                        print(htmlentities($record->libelle));
                                    } else {
                                        print("n/a");
                                    }
                                    ?></div>
                            </div>
                            <div class="row">
                                <div class="col">Niveau : <?php
                                    $qry = $db->prepare("select libelle from niveaux_langues where code=:c");
                                    $qry->execute(array(":c" => $niveau_langue_code));
                                    if (false !== ($record = $qry->fetch(PDO::FETCH_OBJ))) {
                                        print(htmlentities($record->libelle));
                                    } else {
                                        print("n/a");
                                    }
                                    ?></div>
                            </div>
                            <div class="row">
                                <div class="col">Pratique : <?php
                                    $qry = $db->prepare("select libelle from pratiques_langues where code=:c");
                                    $qry->execute(array(":c" => $pratique_langue_code));
                                    if (false !== ($record = $qry->fetch(PDO::FETCH_OBJ))) {
                                        print(htmlentities($record->libelle));
                                    } else {
                                        print("n/a");
                                    }
                                    ?></div>
                            </div>
                            <div class="row mt-4">
                                <div class="col text-center">
                                    <button type="submit" class="btn btn-success">Supprimer</button>
                                    <button type="button" class="btn btn-warning"
                                            onclick="btnAnnulerClick('lstlangues');">
                                        Annuler
                                    </button>
                                    <button type="button" class="btn btn-primary" onclick="btnRetourClick();">
                                        Retour
                                    </button>
                                </div>
                            </div><?php
                        } ?>
                    </div>
                </div>
                <div class="row-100"></div>
            </div>
            </section><?php
        }
        ?>
    </form>

    <script>
        function btnAjouterLangueClick() {
            document.getElementById('code').value = -1;
            document.getElementById('op').value = 'chglangue';
            document.getElementById('dsp').value = '1';
            document.getElementById('frm').submit();
        }

        function btnModifierLangueClick(id) {
            document.getElementById('code').value = id;
            document.getElementById('op').value = 'chglangue';
            document.getElementById('dsp').value = '1';
            document.getElementById('frm').submit();
        }

        function btnSupprimerLangueClick(id) {
            document.getElementById('code').value = id;
            document.getElementById('op').value = 'dltlangue';
            document.getElementById('dsp').value = '1';
            document.getElementById('frm').submit();
        }

        function btnModifierInfosClick() {
            document.getElementById('code').value = -1;
            document.getElementById('op').value = 'chginfos';
            document.getElementById('dsp').value = '1';
            document.getElementById('frm').submit();
        }

        function btnAnnulerClick(op) {
            document.getElementById('code').value = -1;
            document.getElementById('op').value = op;
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

// TODO : permettre de basculer d'une zone à l'autre de notre page complète (en affichage) de façon plus rapide (sous menu, onglets ou autre)