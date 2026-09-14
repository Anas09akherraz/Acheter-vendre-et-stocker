<?php
require_once 'config/db.php';

// إضافة مورد جديد
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $nom = trim($_POST['nom']);
    $telephone = trim($_POST['telephone']);

    if (!empty($nom) && !empty($telephone)) {
        $stmt = $pdo->prepare("INSERT INTO fournisseurs (nom, telephone) VALUES (?, ?)");
        $stmt->execute([$nom, $telephone]);
        header("Location: fournisseurs.php?msg=added");
        exit();
    }
}

// مسح مورد
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM fournisseurs WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: fournisseurs.php?msg=deleted");
    exit();
}

// جلب قائمة الموردين
$stmt = $pdo->query("SELECT * FROM fournisseurs ORDER BY id DESC");
$fournisseurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Fournisseurs</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light p-4">
<div class="container bg-white p-4 rounded shadow-sm">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>🚚 Gestion des Fournisseurs</h2>
        <a href="clients.php" class="btn btn-outline-secondary">Aller vers Clients ➔</a>
    </div>

    <!-- Formulaire Ajouter Fournisseur -->
    <div class="card mb-4 border-0 bg-light">
        <div class="card-body">
            <h5 class="card-title mb-3">Ajouter un nouveau fournisseur</h5>
            <form action="fournisseurs.php" method="POST" class="row g-3">
                <input type="hidden" name="action" value="add">
                
                <div class="col-md-6">
                    <label class="form-label">Nom de Société / Fournisseur (اسم المورد)</label>
                    <input type="text" name="nom" class="form-control" placeholder="Ex: Société STE SARL" required>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Téléphone (رقم الهاتف)</label>
                    <input type="text" name="telephone" class="form-control" placeholder="Ex: 0522123456" required>
                </div>
                
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-success w-100">+ Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des Fournisseurs -->
    <h4 class="mb-3">Liste des fournisseurs</h4>
    <table class="table table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th>N° Fournisseur</th>
                <th>Nom / Raison Sociale</th>
                <th>Téléphone</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($fournisseurs) > 0): ?>
                <?php foreach ($fournisseurs as $fournisseur): ?>
                    <tr>
                        <td><strong>#<?= $fournisseur['id'] ?></strong></td>
                        <td><?= htmlspecialchars($fournisseur['nom']) ?></td>
                        <td><?= htmlspecialchars($fournisseur['telephone']) ?></td>
                        <td>
                            <a href="fournisseurs.php?delete=<?= $fournisseur['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Voulez-vous supprimer ce fournisseur ?')">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="text-center text-muted">Aucun fournisseur trouvé.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
