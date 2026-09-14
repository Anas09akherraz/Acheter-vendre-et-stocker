<?php
require_once 'config/db.php';

// إضافة زبون جديد
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $nom = trim($_POST['nom']);
    $telephone = trim($_POST['telephone']);

    if (!empty($nom) && !empty($telephone)) {
        $stmt = $pdo->prepare("INSERT INTO clients (nom, telephone) VALUES (?, ?)");
        $stmt->execute([$nom, $telephone]);
        header("Location: clients.php?msg=added");
        exit();
    }
}

// مسح زبون
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM clients WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: clients.php?msg=deleted");
    exit();
}

// جلب قائمة الزبناء
$stmt = $pdo->query("SELECT * FROM clients ORDER BY id DESC");
$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Clients</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light p-4">
<div class="container bg-white p-4 rounded shadow-sm">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>👤 Gestion des Clients</h2>
        <a href="fournisseurs.php" class="btn btn-outline-secondary">Aller vers Fournisseurs ➔</a>
    </div>

    <!-- Formulaire Ajouter Client -->
    <div class="card mb-4 border-0 bg-light">
        <div class="card-body">
            <h5 class="card-title mb-3">Ajouter un nouveau client</h5>
            <form action="clients.php" method="POST" class="row g-3">
                <input type="hidden" name="action" value="add">
                
                <div class="col-md-6">
                    <label class="form-label">Nom du Client (اسم الزبون)</label>
                    <input type="text" name="nom" class="form-control" placeholder="Ex: Ahmed Alami" required>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Téléphone (رقم الهاتف)</label>
                    <input type="text" name="telephone" class="form-control" placeholder="Ex: 0661234567" required>
                </div>
                
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-success w-100">+ Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des Clients -->
    <h4 class="mb-3">Liste des clients</h4>
    <table class="table table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th>N° Client</th>
                <th>Nom Complète</th>
                <th>Téléphone</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($clients) > 0): ?>
                <?php foreach ($clients as $client): ?>
                    <tr>
                        <td><strong>#<?= $client['id'] ?></strong></td>
                        <td><?= htmlspecialchars($client['nom']) ?></td>
                        <td><?= htmlspecialchars($client['telephone']) ?></td>
                        <td>
                            <a href="clients.php?delete=<?= $client['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Voulez-vous supprimer ce client ?')">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="text-center text-muted">Aucun client trouvé.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
