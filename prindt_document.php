<?php
require_once 'config/db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    die("Document non spécifié.");
}

// جلب تفاصيل الوثيقة مع معلومات الزبون أو المورد
$stmt = $pdo->prepare("
    SELECT d.*, 
           c.nom AS client_nom, c.telephone AS client_tel,
           f.nom AS fourn_nom, f.telephone AS fourn_tel
    FROM documents d
    LEFT JOIN clients c ON d.client_id = c.id
    LEFT JOIN fournisseurs f ON d.fournisseur_id = f.id
    WHERE d.id = ?
");
$stmt->execute([$id]);
$doc = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$doc) {
    die("Document introuvable.");
}

// جلب تفاصيل السلع الخاصة بالوثيقة
$stmtItems = $pdo->prepare("
    SELECT di.*, a.designation AS art_designation
    FROM document_items di
    LEFT JOIN articles a ON di.article_id = a.id
    WHERE di.document_id = ?
");
$stmtItems->execute([$id]);
$items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

// عناوين الوثائق بالفرنسية
$typeTitles = [
    'facture_vente' => 'FACTURE DE VENTE',
    'bl_vente'      => 'BON DE LIVRAISON',
    'devis'         => 'DEVIS',
    'bc_achat'      => 'BON DE COMMANDE',
    'bl_achat'      => 'BON DE RECEPTION',
    'facture_achat' => 'FACTURE D\'ACHAT'
];
$docTitle = $typeTitles[$doc['type_doc']] ?? strtoupper(str_replace('_', ' ', $doc['type_doc']));

$tierNom = $doc['client_nom'] ?: ($doc['fourn_nom'] ?: 'N/A');
$tierTel = $doc['client_tel'] ?: ($doc['fourn_tel'] ?: 'N/A');
$isClient = !empty($doc['client_id']) || in_array($doc['type_doc'], ['facture_vente', 'bl_vente', 'devis']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $docTitle ?> - N° <?= htmlspecialchars($doc['numero_doc']) ?></title>
    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
        }

        .document-box {
            max-width: 800px;
            margin: 30px auto;
            background: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .company-title {
            font-size: 24px;
            font-weight: bold;
            color: #0d6efd;
        }

        .doc-header-title {
            font-size: 20px;
            font-weight: bold;
            background-color: #f1f3f5;
            padding: 8px 15px;
            border-radius: 5px;
            display: inline-block;
        }

        .table-items th {
            background-color: #343a40 !important;
            color: white !important;
        }

        /* تنسيق خاص بالطابعة والـ PDF */
        @media print {
            body {
                background-color: #fff;
            }
            .no-print {
                display: none !important;
            }
            .document-box {
                box-shadow: none;
                margin: 0;
                padding: 0;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>

    <!-- أزرار التحكم (تختفي أوتوماتيكياً فـ الطباعة) -->
    <div class="container text-center mt-4 no-print">
        <a href="index.php" class="btn btn-outline-secondary me-2"><i class="bi bi-arrow-left"></i> Retour au tableau de bord</a>
        <button onclick="window.print()" class="btn btn-primary btn-lg"><i class="bi bi-printer me-2"></i> Imprimer / Enregistrer en PDF</button>
    </div>

    <!-- ورقة الوثيقة A4 -->
    <div class="document-box">
        
        <!-- الهيدر: معلومات الشركة والوثيقة -->
        <div class="row pb-3 mb-4 border-bottom align-items-center">
            <div class="col-6">
                <div class="company-title"><i class="bi bi-building"></i> MON ENTREPRISE SARL</div>
                <div class="text-muted small">
                    Avenue Hassan II, Agadir, Maroc<br>
                    Tél : +212 5 22 00 00 00 | Email : contact@entreprise.ma<br>
                    ICE : 000123456000089 | IF : 12345678
                </div>
            </div>
            <div class="col-6 text-end">
                <div class="doc-header-title text-uppercase mb-2"><?= $docTitle ?></div>
                <div class="fw-bold fs-5">N° : <?= htmlspecialchars($doc['numero_doc']) ?></div>
                <div class="text-muted">Date : <?= date('d/m/Y', strtotime($doc['date_doc'])) ?></div>
            </div>
        </div>

        <!-- معلومات الزبون / المورد وتفاصيل الإرسال -->
        <div class="row mb-4">
            <div class="col-6">
                <div class="p-3 bg-light rounded border">
                    <h6 class="fw-bold text-muted text-uppercase small mb-2"><?= $isClient ? 'Client' : 'Fournisseur' ?></h6>
                    <h5 class="fw-bold mb-1"><?= htmlspecialchars($tierNom) ?></h5>
                    <div class="text-muted small">Tél : <?= htmlspecialchars($tierTel) ?></div>
                </div>
            </div>
            
            <div class="col-6">
                <div class="p-3 bg-light rounded border h-100">
                    <h6 class="fw-bold text-muted text-uppercase small mb-2">Références & Logistique</h6>
                    <div class="small">
                        <?php if ($doc['num_bc']): ?>
                            <div><strong>N° Bon de Commande :</strong> <?= htmlspecialchars($doc['num_bc']) ?></div>
                        <?php endif; ?>
                        <?php if ($doc['num_bl']): ?>
                            <div><strong>N° Bon de Livraison :</strong> <?= htmlspecialchars($doc['num_bl']) ?></div>
                        <?php endif; ?>
                        <?php if ($doc['matricule']): ?>
                            <div><strong>Matricule Véhicule :</strong> <?= htmlspecialchars($doc['matricule']) ?></div>
                        <?php endif; ?>
                        <?php if (!$doc['num_bc'] && !$doc['num_bl'] && !$doc['matricule']): ?>
                            <div class="text-muted">Aucune référence supplémentaire.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- جدول السلع والخدمات -->
        <div class="table-responsive mb-4">
            <table class="table table-bordered table-striped align-middle table-items">
                <thead>
                    <tr class="text-center">
                        <th style="width: 10%;">N° Art</th>
                        <th>Désignation</th>
                        <th style="width: 12%;">Quantité</th>
                        <th style="width: 10%;">Unité</th>
                        <th style="width: 18%;">Prix Unitaire</th>
                        <th style="width: 18%;">Total HT</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $grandTotal = 0;
                    if (count($items) > 0): 
                        foreach ($items as $item): 
                            $grandTotal += $item['total'];
                    ?>
                        <tr>
                            <td class="text-center">#<?= $item['article_id'] ?></td>
                            <td><?= htmlspecialchars($item['art_designation'] ?: 'Article') ?></td>
                            <td class="text-center"><?= $item['quantite'] ?></td>
                            <td class="text-center"><?= htmlspecialchars($item['unite']) ?></td>
                            <td class="text-end"><?= number_format($item['prix_unitaire'], 2) ?> DH</td>
                            <td class="text-end fw-bold"><?= number_format($item['total'], 2) ?> DH</td>
                        </tr>
                    <?php 
                        endforeach; 
                    else:
                    ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">Aucun article dans ce document.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- المجموع الكلي والتوقيع -->
        <div class="row pt-2 align-items-end">
            <div class="col-6">
                <div class="border p-3 rounded text-center text-muted small" style="min-height: 100px;">
                    Cachet et Signature
                </div>
            </div>
            <div class="col-6">
                <table class="table table-bordered mb-0">
                    <tr>
                        <td class="bg-light fw-bold text-end">Total HT :</td>
                        <td class="text-end fw-bold"><?= number_format($grandTotal, 2) ?> DH</td>
                    </tr>
                    <tr>
                        <td class="bg-light fw-bold text-end">TVA (0%) :</td>
                        <td class="text-end">0.00 DH</td>
                    </tr>
                    <tr class="fs-5">
                        <td class="bg-dark text-white fw-bold text-end">Total TTC :</td>
                        <td class="bg-dark text-white fw-bold text-end"><?= number_format($grandTotal, 2) ?> DH</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- الفوتر السفلية -->
        <div class="mt-5 pt-4 border-top text-center text-muted small">
            Merci pour votre confiance ! — <i>Mon Entreprise SARL</i>
        </div>

    </div>

</body>
</html>
