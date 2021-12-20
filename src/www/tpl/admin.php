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

$PageTitle = "Admin";
require_once(__DIR__ . "/_header.php");
?><h1>Administration</h1>
    <h2>Tables générales</h2>
    <ul>
        <li><a href="admin-langues.php">Gestion des langues</a></li>
    </ul><?php
require_once(__DIR__ . "/_footer.php");

// TODO : CRUD utilisateurs
// TODO : CRUD entreprises
// TODO : tous les accès "modération"
// TODO : gestion de toutes les tables du site