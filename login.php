<?php
session_start();
require_once "includes/config.php";
require_once "includes/functions.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $mot_de_passe = $_POST["mot_de_passe"];

    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ? AND statut = 'actif' LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($mot_de_passe, $user["mot_de_passe"])) {
        $_SESSION["user"] = $user;
        log_action($pdo, "Connexion au système");
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Email ou mot de passe incorrect.";
    }
}
?>
<?php include "includes/header.php"; ?>
<div class="login-wrapper">
    <div class="card login-card">
        <h1>Connexion</h1>
        <p>Accédez à la plateforme Secure Vote</p>
        <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required placeholder="admin@securevote.com">
            </div>
            <div class="form-group">
                <label>Mot de passe</label>
                <input type="password" name="mot_de_passe" required placeholder="admin123">
            </div>
            <button class="btn" type="submit">Se connecter</button>
        </form>
        <p><small>Compte test : admin@securevote.com / admin123</small></p>
    </div>
</div>
<?php include "includes/footer.php"; ?>
