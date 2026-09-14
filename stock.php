<?php
require_once 'config/db.php';

// إضافة سلعة جديدة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $designation = trim($_POST['designation']);
    $unite = trim($_POST['unite']);
    $prix = floatval($_POST['prix_unitaire']);
    $quantite = intval($_POST['quantite_stock']);

    if (!empty($designation)) {
        $stmt = $pdo->prepare("INSERT INTO articles (designation, unite, prix_unitaire, quantite_stock) VALUES (?, ?, ?, ?)");
        $stmt->execute([$designation, $unite, $prix, $quantite]);
        header("Location: stock.php?msg=added");
        exit();
    }
}

// مسح سلعة
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM articles WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: stock.php?msg=deleted");
    exit();
}

// جلب جميع السلع من المخزن
$stmt = $pdo->query("SELECT * FROM articles ORDER BY id DESC");
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion du Stock</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light p-4">
<div class="container bg-white p-4 rounded shadow-sm">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>📦 Gestion du Stock & Articles</h2>
        <a href="document_create.php" class="btn btn-outline-primary">+ Créer un Document</a>
    </div>

    <!-- Formulaire d'ajout d'article -->
    <div class="card mb-4 border-0 bg-light">
        <div class="card-body">
            <h5 class="card-title mb-3">Ajouter un nouveau produit</h5>
            <form action="stock.php" method="POST" class="row g-3">
                <input type="hidden" name="action" value="add">
                
                <div class="col-md-4">
                    <label class="form-label">Désignation (اسم السلعة)</label>
                    <input type="text" name="designation" class="form-control" placeholder="Ex: Clavier Sans Fil" required>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Unité (الوحدة)</label>
                    <input type="text" name="unite" class="form-control" value="PCS" required>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Prix Unitaire (DH)</label>
                    <input type="number" step="0.01" name="prix_unitaire" class="form-control" placeholder="0.00" required>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Quantité Initiale (الكمية)</label>
                    <input type="number" name="quantite_stock" class="form-control" value="0" required>
                </div>
                
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-success">+ Enregistrer la marchandise</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste du Stock -->
    <h4 class="mb-3">Liste des produits en stock</h4>
    <table class="table table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th>N° Article</th>
                <th>Désignation</th>
                <th>Prix Unitaire</th>
                <th>Stock Disponible</th>
                <th>Unité</th>
                <th>État</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($articles) > 0): ?>
                <?php foreach ($articles as $item): ?>
                    <tr>
                        <td><strong>#<?= $item['id'] ?></strong></td>
                        <td><?= htmlspecialchars($item['designation']) ?></td>
                        <td><?= number_format($item['prix_unitaire'], 2) ?> DH</td>
                        <td><strong><?= $item['quantite_stock'] ?></strong></td>
                        <td><?= htmlspecialchars($item['unite']) ?></td>
                        <td>
                            <?php if ($item['quantite_stock'] <= 5): ?>
                                <span class="badge bg-danger">Stock Faible</span>
                            <?php else: ?>
                                <span class="badge bg-success">En Stock</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="stock.php?delete=<?= $item['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Voulez-vous vraiment supprimer cet article ?')">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center text-muted">Aucun produit dans le stock.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
