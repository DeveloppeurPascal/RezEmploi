<?php
if ((!defined("REZEMPLOIKEY")) || (REZEMPLOIKEY != "3f5gd4ng2h5j4gh24,gh2j54fd2g54fg2h45")) {
    header("location: index.php");
    exit;
}

require_once(__DIR__ . "/../" . ProtectedFolder . "/utilisateurs.php");
if ((!isUtilisateurConnecteAdmin()) && (!isUtilisateurConnecteSuperAdmin())) {
    header("location: login.php");
    exit;
}

$op = isset($_POST["op"]) ? $_POST["op"] : "";
$dsp = (isset($_GET["upd"]) ? $_GET["upd"] : "0") == "1";

if (("add" == $op) && $dsp) {
    // affichage de la page en ajout
    $code = -1;
    $libelle = "";
    $mode = "add";
} else if ("add" == $op) {
    // traitement des infos passées en ajout
    // TODO : à compléter
} else if (("chg" == $op) && $dsp) {
    // affichage de la page en modification
    $code = -1; // TODO : récupérer ID dans les paramètres POST
    $libelle = ""; // TODO : à charger depuis la table si l'enregistrement existe
    $mode = "chg"; // TODO : si enregistrement existe, sinon page 404
} else if ("chg" == $op) {
    // traitement des infos passées en modification
    // TODO : à compléter
} else if (("dlt" == $op) && $dsp) {
    // affichage de la page en suppression
    $code = -1; // TODO : récupérer ID dans les paramètres POST
    $libelle = ""; // TODO : à charger depuis la table si l'enregistrement existe
    $mode = "dlt"; // TODO : si enregistrement existe, sinon page 404
} else if ("dlt" == $op) {
    // traitement des infos passées en suppression
    // TODO : à compléter
} else if (("dsp" == $op) && $dsp) {
    // affichage de la page en détail
    $code = -1; // TODO : récupérer ID dans les paramètres POST
    $libelle = ""; // TODO : à charger depuis la table si l'enregistrement existe
    $mode = "dsp"; // TODO : si enregistrement existe, sinon page 404
} else {
    // affichage de la liste
    $mode = "lst";
}

switch ($mode) {
    case    "add":
        $PageTitle = "Ajout d'une langue";
        break;
    case    "chg":
        $PageTitle = "Modification d'une langue";
        break;
    case    "dlt":
        $PageTitle = "Suppression d'une langue";
        break;
    case    "dsp":
        $PageTitle = "Détail d'une langue";
        break;
    case    "lst":
        $PageTitle = "Liste des langues";
        break;
    default:
        // TODO : envoyer en 404 not found
}
require_once(__DIR__ . "/_header.php");
?>
    <form action="admin-langues.php" method="post">
    <input type="hidden" name="op" value="<?php print($mode); ?>">
    <input type="hidden" name="upd" value="1">
    <section class="fdb-block pt-0">
    <div class="container">
        <div class="row text-center justify-content-center pt-5">
            <div class="col-12 col-md-7">
                <h1><?php print($PageTitle); ?></h1>
            </div>
        </div>
        <?php if ("lst" == $mode) { ?>
            <table class="table text-center mt-5 d-table d-lg-none">
                <tbody>
                <tr>
                    <td class="text-center border-0" colspan="2">
                        <h2 class="font-weight-light">Hobby</h2>
                        <p class="h2">$99</p>
                        <p><a href="https://www.froala.com" class="btn btn-outline-primary">Buy Now</a></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Support</th>
                    <td>3 months</td>
                </tr>
                <tr>
                    <th scope="row">Full source code</th>
                    <td>âœ“</td>
                </tr>
                <tr>
                    <th scope="row">SaaS / Subscription</th>
                    <td>âœ“</td>
                </tr>
                <tr>
                    <th scope="row">Intranet</th>
                    <td></td>
                </tr>
                <tr>
                    <th scope="row">Downloadable Software</th>
                    <td></td>
                </tr>
                <tr>
                    <th scope="row">Redistribute</th>
                    <td></td>
                </tr>
                <tr>
                    <th scope="row">Custom code</th>
                    <td></td>
                </tr>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="row justify-content-center pt-4">
                <div class="col-12 col-md-7">
                    <form>
                        <?php if (("chg" == $mode) || ("dsp" == $mode) || ("dlt" == $mode)) { ?>
                            <div class="row">
                                <div class="col">
                                    Code : <?php print($code); ?>
                                </div>
                            </div>
                        <?php }
                        if (("dsp" == $mode) || ("dlt" == $mode)) { ?>
                            <div class="row">
                                <div class="col">
                                    Langue : <?php print(htmlentities($libelle)); ?>
                                </div>
                            </div>
                        <?php }
                        if (("add" == $mode) || ("chg" == $mode)) { ?>
                            <div class="row">
                                <div class="col">
                                    <input type="text" class="form-control" placeholder="Langue" id="libelle"
                                           name="libelle"
                                           value="<?php print(isset($libelle) ? htmlentities($libelle) : ""); ?>"
                                           autofocus>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="row mt-4">
                            <div class="col text-center">
                                <?php if (("add" == $mode) || ("chg" == $mode)) { ?>
                                    <button type="submit" class="btn btn-success">Enregistrer</button>
                                <?php }
                                if ("dlt" == $mode) { ?>
                                    <button type="button" class="btn btn-danger">Supprimer</button>
                                <?php }
                                if (("add" == $mode) || ("chg" == $mode) || ("dlt" == $mode)) { ?>
                                    <button type="button" class="btn btn-warning">Annuler</button>
                                <?php }
                                if ("dsp" == $mode) { ?>
                                    <button type="button" class="btn btn-primary">Retour</button>
                                <?php } ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        <?php } ?>
        <div class="row-100"></div>
    </div>
    </section><?php
require_once(__DIR__ . "/_footer.php");