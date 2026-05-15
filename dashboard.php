<?php
require_once "includes/config.php";
require_once "includes/functions.php";
require_login();

$users = $pdo->query("SELECT COUNT(*) total FROM utilisateurs")->fetch()["total"];
$bureaux = $pdo->query("SELECT COUNT(*) total FROM bureaux_vote")->fetch()["total"];
$partis = $pdo->query("SELECT COUNT(*) total FROM partis_politiques")->fetch()["total"];
$resultats = $pdo->query("SELECT COUNT(*) total FROM resultats")->fetch()["total"];
?>
<?php include "includes/header.php"; ?>
<div class="card">
    <h1>Tableau de bord</h1>
    <p>Bienvenue, <strong><?= e($_SESSION['user']['nom']) ?></strong> — rôle : <span class="badge"><?= e($_SESSION['user']['role']) ?></span></p>
</div>

<div class="grid">
    <div class="stat"><h2><?= $users ?></h2><p>Utilisateurs</p></div>
    <div class="stat"><h2><?= $bureaux ?></h2><p>Bureaux de vote</p></div>
    <div class="stat"><h2><?= $partis ?></h2><p>Partis politiques</p></div>
    <div class="stat"><h2><?= $resultats ?></h2><p>Résultats enregistrés</p></div>
</div>

<div class="card">
    <h2>Actions rapides</h2>
    <a class="btn" href="resultats.php">Saisir / consulter les résultats</a>
    <a class="btn btn-success" href="pv.php">Transmettre un PV</a>
</div>
<?php include "includes/footer.php"; ?>
