<?php
require_once 'config/db.php';

// جلب الإحصائيات والأرقام الرئيسية
try {
    // إجمالي عدد السلع فـ المخزن
    $totalArticles = $pdo->query("SELECT COUNT(*) FROM articles")->fetchColumn();
    
    // عدد السلع القريبة من النفاد (أقل أو يساوي 5)
    $lowStockCount = $pdo->query("SELECT COUNT(*) FROM articles WHERE quantite_stock <= 5")->fetchColumn();

    // إجمالي الزبناء
    $totalClients = $pdo->query("SELECT COUNT(*) FROM clients")->fetchColumn();

    // إجمالي الموردين
    $totalFournisseurs = $pdo->query("SELECT COUNT(*) FROM fournisseurs")->fetchColumn();

    // إجمالي وثائق البيع (Factures + BL)
    $totalSalesDocs = $pdo->query("SELECT COUNT(*) FROM documents WHERE type_doc IN ('facture_vente', 'bl_vente')")->fetchColumn();

    // إجمالي وثائق الشراء (Factures + BL + BC)
    $totalAchatDocs = $pdo->query("SELECT COUNT(*) FROM documents WHERE type_doc IN ('facture_achat', 'bl_achat', 'bc_achat')")->fetchColumn();

    // جلب أحدث 5 وثائق تم إنشاؤها
    $stmtRecent = $pdo->query("
        SELECT d.*, 
               COALESCE(c.nom, f.nom, 'Non spécifié') AS tier_nom
        FROM documents d
        LEFT JOIN clients c ON d.client_id = c.id
        LEFT JOIN fournisseurs f ON d.fournisseur_id = f.id
        ORDER BY d.id DESC LIMIT 5
    ");
    $recentDocs = $stmtRecent->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    // فـ حالة تعذر الاتصال أو عدم وجود الداتابيز بعد
    $totalArticles = $lowStockCount = $totalClients = $totalFournisseurs = $totalSalesDocs = $totalAchatDocs = 0;
    $recentDocs = [];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - Gestion Commerciale</title>
    <!-- Bootstrap 5 CSS & Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <!-- شريط الملاحة (Navbar) -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="bi bi-box-seam me-2"></i>Gestion Stock & Commerciale
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php"><i class="bi bi-speedometer2"></i> Tableau de bord</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="stock.php"><i class="bi bi-boxes"></i> Stock & Produits</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="clients.php"><i class="bi bi-people"></i> Clients</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="fournisseurs.php"><i class="bi bi-truck"></i> Fournisseurs</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary ms-lg-2" href="document_create.php"><i class="bi bi-file-earmark-plus"></i> Nouveau Document</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">

        <!-- أزرار الوصول السريع (Quick Actions) -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">Tableau de Bord</h3>
                <p class="text-muted mb-0">Aperçu général de votre activité et gestion de stock.</p>
            </div>
            <div>
                <a href="document_create.php" class="btn btn-success me-2">
                    <i class="bi bi-cart-plus me-1"></i> Créer Vente / Devis
                </a>
                <a href="stock.php" class="btn btn-outline-dark">
                    <i class="bi bi-plus-circle me-1"></i> Ajouter Article
                </a>
            </div>
        </div>

        <!-- كروت الإحصائيات (Stat Cards) -->
        <div class="row g-3 mb-4">
            
            <!-- Produits / Stock -->
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-3 bg-white">
                    <div class="card-body d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle me-3">
                            <i class="bi bi-box-seam fs-3"></i>
                        </div>
                        <div>
                            <span class="text-muted d-block fs-7">Articles en Stock</span>
                            <h4 class="fw-bold mb-0"><?= $totalArticles ?></h4>
                            <?php if ($lowStockCount > 0): ?>
                                <small class="text-danger fw-semibold"><i class="bi bi-exclamation-triangle"></i> <?= $lowStockCount ?> Stock faible</small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Clients -->
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-3 bg-white">
                    <div class="card-body d-flex align-items-center">
                        <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle me-3">
                            <i class="bi bi-people fs-3"></i>
                        </div>
                        <div>
                            <span class="text-muted d-block fs-7">Total Clients</span>
                            <h4 class="fw-bold mb-0"><?= $totalClients ?></h4>
                            <small class="text-muted"><a href="clients.php" class="text-decoration-none">Gérer ➔</a></small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fournisseurs -->
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-3 bg-white">
                    <div class="card-body d-flex align-items-center">
                        <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-circle me-3">
                            <i class="bi bi-truck fs-3"></i>
                        </div>
                        <div>
                            <span class="text-muted d-block fs-7">Total Fournisseurs</span>
                            <h4 class="fw-bold mb-0"><?= $totalFournisseurs ?></h4>
                            <small class="text-muted"><a href="fournisseurs.php" class="text-decoration-none">Gérer ➔</a></small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ventes & Achats -->
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-3 bg-white">
                    <div class="card-body d-flex align-items-center">
                        <div class="bg-info bg-opacity-10 text-info p-3 rounded-circle me-3">
                            <i class="bi bi-receipt fs-3"></i>
                        </div>
                        <div>
                            <span class="text-muted d-block fs-7">Documents Créés</span>
                            <h4 class="fw-bold mb-0"><?= ($totalSalesDocs + $totalAchatDocs) ?></h4>
                            <small class="text-muted">Ventes: <?= $totalSalesDocs ?> | Achats: <?= $totalAchatDocs ?></small>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- أحدث الوثائق المسجلة (Recent Documents) -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0"><i class="bi bi-clock-history me-2"></i>Derniers Documents Enregistrés</h5>
                <a href="document_create.php" class="btn btn-sm btn-outline-primary">+ Nouveau</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>N° Document</th>
                                <th>Type</th>
                                <th>Client / Fournisseur</th>
                                <th>Date</th>
                                <th>N° BC / BL</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($recentDocs) > 0): ?>
                                <?php foreach ($recentDocs as $doc): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($doc['numero_doc']) ?></strong></td>
                                        <td>
                                            <?php
                                            $badgeClass = 'bg-secondary';
                                            if (strpos($doc['type_doc'], 'facture') !== false) $badgeClass = 'bg-success';
                                            elseif (strpos($doc['type_doc'], 'bl') !== false) $badgeClass = 'bg-info text-dark';
                                            elseif ($doc['type_doc'] === 'devis') $badgeClass = 'bg-warning text-dark';
                                            ?>
                                            <span class="badge <?= $badgeClass ?>">
                                                <?= strtoupper(str_replace('_', ' ', $doc['type_doc'])) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($doc['tier_nom']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($doc['date_doc'])) ?></td>
                                        <td>
                                            <small class="text-muted">
                                                <?= $doc['num_bc'] ? 'BC: ' . htmlspecialchars($doc['num_bc']) : '' ?>
                                                <?= $doc['num_bl'] ? ' | BL: ' . htmlspecialchars($doc['num_bl']) : '' ?>
                                            </small>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="bi bi-folder-x fs-2 d-block mb-2"></i>
                                        Aucun document n'a été créé pour le moment.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
