<?php
require_once "includes/config.php";
require_once "includes/functions.php";
require_login();

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $bureau_id = $_POST["bureau_id"];
    $commentaire = trim($_POST["commentaire"]);

    if (!isset($_FILES["fichier_pv"]) || $_FILES["fichier_pv"]["error"] !== 0) {
        $error = "Veuillez sélectionner un fichier PV.";
    } else {
        $allowed = ["pdf", "jpg", "jpeg", "png"];
        $ext = strtolower(pathinfo($_FILES["fichier_pv"]["name"], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $error = "Format non autorisé. Utilisez PDF, JPG ou PNG.";
        } else {
            $filename = "pv_" . time() . "_" . rand(1000,9999) . "." . $ext;
            $destination = "uploads/pv/" . $filename;

            if (move_uploaded_file($_FILES["fichier_pv"]["tmp_name"], $destination)) {
                $stmt = $pdo->prepare("INSERT INTO proces_verbaux (bureau_id, fichier_pv, commentaire, transmis_par) VALUES (?, ?, ?, ?)");
                $stmt->execute([$bureau_id, $destination, $commentaire, $_SESSION["user"]["id"]]);
                log_action($pdo, "Transmission d'un procès-verbal");
                $message = "PV transmis avec succès.";
            } else {
                $error = "Erreur lors de l'envoi du fichier.";
            }
        }
    }
}

$bureaux = $pdo->query("SELECT * FROM bureaux_vote ORDER BY nom_bureau")->fetchAll();
$pvs = $pdo->query("
SELECT pv.*, b.nom_bureau, b.commune, u.nom AS utilisateur
FROM proces_verbaux pv
JOIN bureaux_vote b ON pv.bureau_id = b.id
JOIN utilisateurs u ON pv.transmis_par = u.id
ORDER BY pv.created_at DESC
")->fetchAll();
?>
<?php include "includes/header.php"; ?>
<div class="card">
    <h1>Transmission des PV</h1>
    <?php if ($message): ?><div class="alert alert-success"><?= e($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Bureau de vote</label>
            <select name="bureau_id" required>
                <option value="">Choisir un bureau</option>
                <?php foreach ($bureaux as $b): ?>
                <option value="<?= $b['id'] ?>"><?= e($b['code_bureau'].' - '.$b['nom_bureau']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Fichier PV</label>
            <input type="file" name="fichier_pv" required>
        </div>
        <div class="form-group">
            <label>Commentaire</label>
            <textarea name="commentaire"></textarea>
        </div>
        <button class="btn btn-success" type="submit">Transmettre</button>
    </form>
</div>

<div class="card">
    <h2>PV transmis</h2>
    <table class="table">
        <tr><th>Bureau</th><th>Commune</th><th>Fichier</th><th>Transmis par</th><th>Date</th></tr>
        <?php foreach ($pvs as $pv): ?>
        <tr>
            <td><?= e($pv['nom_bureau']) ?></td>
            <td><?= e($pv['commune']) ?></td>
            <td><a href="<?= e($pv['fichier_pv']) ?>" target="_blank">Voir le PV</a></td>
            <td><?= e($pv['utilisateur']) ?></td>
            <td><?= e($pv['created_at']) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php include "includes/footer.php"; ?>
