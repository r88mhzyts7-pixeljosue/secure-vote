<?php
require_once "includes/config.php";
require_once "includes/functions.php";
require_login();

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $bureau_id = $_POST["bureau_id"];
    $parti_id = $_POST["parti_id"];
    $voix = (int) $_POST["voix"];

    if ($voix < 0) {
        $error = "Le nombre de voix est invalide.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO resultats (bureau_id, parti_id, voix, saisi_par) VALUES (?, ?, ?, ?)");
        $stmt->execute([$bureau_id, $parti_id, $voix, $_SESSION["user"]["id"]]);
        log_action($pdo, "Saisie d'un résultat électoral");
        $message = "Résultat enregistré avec succès.";
    }
}

$bureaux = $pdo->query("SELECT * FROM bureaux_vote ORDER BY nom_bureau")->fetchAll();
$partis = $pdo->query("SELECT * FROM partis_politiques ORDER BY nom_parti")->fetchAll();

$resultats = $pdo->query("
SELECT r.*, b.nom_bureau, b.commune, p.nom_parti, p.sigle, u.nom AS utilisateur
FROM resultats r
JOIN bureaux_vote b ON r.bureau_id = b.id
JOIN partis_politiques p ON r.parti_id = p.id
JOIN utilisateurs u ON r.saisi_par = u.id
ORDER BY r.created_at DESC
")->fetchAll();
?>
<?php include "includes/header.php"; ?>
<div class="card">
    <h1>Transmission des résultats</h1>
    <?php if ($message): ?><div class="alert alert-success"><?= e($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Bureau de vote</label>
            <select name="bureau_id" required>
                <option value="">Choisir un bureau</option>
                <?php foreach ($bureaux as $b): ?>
                <option value="<?= $b['id'] ?>"><?= e($b['code_bureau'].' - '.$b['nom_bureau'].' / '.$b['commune']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Parti politique</label>
            <select name="parti_id" required>
                <option value="">Choisir un parti</option>
                <?php foreach ($partis as $p): ?>
                <option value="<?= $p['id'] ?>"><?= e($p['sigle'].' - '.$p['nom_parti']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Nombre de voix</label>
            <input type="number" name="voix" min="0" required>
        </div>
        <button class="btn" type="submit">Enregistrer</button>
    </form>
</div>

<div class="card">
    <h2>Résultats enregistrés</h2>
    <table class="table">
        <tr><th>Bureau</th><th>Commune</th><th>Parti</th><th>Voix</th><th>Saisi par</th><th>Date</th></tr>
        <?php foreach ($resultats as $r): ?>
        <tr>
            <td><?= e($r['nom_bureau']) ?></td>
            <td><?= e($r['commune']) ?></td>
            <td><?= e($r['sigle']) ?></td>
            <td><?= e($r['voix']) ?></td>
            <td><?= e($r['utilisateur']) ?></td>
            <td><?= e($r['created_at']) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php include "includes/footer.php"; ?>
