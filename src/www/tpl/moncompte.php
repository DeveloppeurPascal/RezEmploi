<?php
if ((!defined("REZEMPLOIKEY")) || (REZEMPLOIKEY != "3f5gd4ng2h5j4gh24,gh2j54fd2g54fg2h45")) {
    header("location: index.php");
    exit;
}

require_once(__DIR__ . "/../" . ProtectedFolder . "/utilisateurs.php");
if (!isUtilisateurConnecteParticulier()) {
    header("location: login.php");
    exit;
}

$PageTitle = "Mon compte";
require_once(__DIR__ . "/_header.php");
?><?php
require_once(__DIR__ . "/_footer.php");

// TODO : mise à jour de l'adresse email
// TODO : changement de mot de passe
// TODO : gestion 2FA
// TODO : infos du CV
// TODO : gestion de la liste des contacts
// TODO : gestion de la liste des personnes suivies
// TODO : gestion de la liste des entreprises suivies
// TODO : activation espace entreprise (payant)
// TODO : activation de l'espace publicitaire (payant)
// TODO : CRUD sur l'actualité de l'utilisateur