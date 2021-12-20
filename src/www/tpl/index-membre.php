<?php
if ((!defined("REZEMPLOIKEY")) || (REZEMPLOIKEY != "3f5gd4ng2h5j4gh24,gh2j54fd2g54fg2h45")) {
    header("location: index.php");
    exit;
}

require_once(__DIR__ . "/../" . ProtectedFolder . "/utilisateurs.php");
if (!isUtilisateurConnecte()) {
    header("location: login.php");
    exit;
}

$PageTitle = _TitreDuSite;
require_once(__DIR__ . "/_header.php");
?><?php
require_once(__DIR__ . "/_footer.php");

// TODO : rappel infos de l'utilisateur
// TODO : actualité de ses contacts
// TODO : rappel messages en attente
// TODO : formulaire de recherche (CV / personnes / offres d'emploi et entreprises)