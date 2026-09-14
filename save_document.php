<?php
require_once 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // البدء فـ المعاملة (Transaction) لضمان حفظ كل شيء بنجاح أو إلغاء الكل
        $pdo->beginTransaction();

        // 1. استقبال البيانات الأساسية من النماذج
        $type_doc   = $_POST['type_doc'] ?? '';
        $numero_doc = trim($_POST['numero_doc'] ?? '');
        $date_doc   = $_POST['date_doc'] ?? date('Y-m-d');
        $tier_nom   = trim($_POST['tier_nom'] ?? '');
        
        $num_bc    = !empty($_POST['num_bc']) ? trim($_POST['num_bc']) : NULL;
        $num_bl    = !empty($_POST['num_bl']) ? trim($_POST['num_bl']) : NULL;
        $matricule = !empty($_POST['matricule']) ? trim($_POST['matricule']) : NULL;

        $client_id = NULL;
        $fournisseur_id = NULL;

        // 2. تحديد هل الوثيقة خاصة بـ Vente أو Achat وإدارة Client/Fournisseur
        $is_vente = in_array($type_doc, ['facture_vente', 'bl_vente', 'devis']);
        $is_achat = in_array($type_doc, ['facture_achat', 'bl_achat', 'bc_achat']);

        if ($is_vente && !empty($tier_nom)) {
            // البحث عن الزبون أو إنشاؤه إذا لم يكن موجوداً
            $stmt = $pdo->prepare("SELECT id FROM clients WHERE nom = ?");
            $stmt->execute([$tier_nom]);
            $client = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($client) {
                $client_id = $client['id'];
            } else {
                $stmtInsert = $pdo->prepare("INSERT INTO clients (nom, telephone) VALUES (?, '0000000000')");
                $stmtInsert->execute([$tier_nom]);
                $client_id = $pdo->lastInsertId();
            }
        } elseif ($is_achat && !empty($tier_nom)) {
            // البحث عن المورد أو إنشاؤه إذا لم يكن موجوداً
            $stmt = $pdo->prepare("SELECT id FROM fournisseurs WHERE nom = ?");
            $stmt->execute([$tier_nom]);
            $fournisseur = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($fournisseur) {
                $fournisseur_id = $fournisseur['id'];
            } else {
                $stmtInsert = $pdo->prepare("INSERT INTO fournisseurs (nom, telephone) VALUES (?, '0000000000')");
                $stmtInsert->execute([$tier_nom]);
                $fournisseur_id = $pdo->lastInsertId();
            }
        }

        // 3. حفظ الوثيقة الرئيسية فـ جدول documents
        $sqlDoc = "INSERT INTO documents (type_doc, numero_doc, date_doc, client_id, fournisseur_id, num_bc, num_bl, matricule) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmtDoc = $pdo->prepare($sqlDoc);
        $stmtDoc->execute([$type_doc, $numero_doc, $date_doc, $client_id, $fournisseur_id, $num_bc, $num_bl, $matricule]);
        
        $document_id = $pdo->lastInsertId();

        // 4. معالجة السلع (Items) وتحديث المخزون
        if (isset($_POST['items']) && is_array($_POST['items'])) {
            foreach ($_POST['items'] as $item) {
                $num_article  = trim($item['num'] ?? '');
                $designation  = trim($item['designation'] ?? '');
                $qty          = intval($item['qty'] ?? 0);
                $unite        = trim($item['unite'] ?? 'PCS');
                $prix         = floatval($item['prix'] ?? 0);
                $total        = floatval($item['total'] ?? ($qty * $prix));

                if ($qty <= 0 || empty($designation)) {
                    continue; // تجاوز الأسطر الفارغة
                }

                // البحث عن السلعة فـ المخزن بالـ ID أو بالإسم
                $article_id = NULL;
                if (is_numeric($num_article)) {
                    $stmtArt = $pdo->prepare("SELECT id FROM articles WHERE id = ?");
                    $stmtArt->execute([intval($num_article)]);
                    $art = $stmtArt->fetch();
                    if ($art) $article_id = $art['id'];
                }

                if (!$article_id) {
                    $stmtArt = $pdo->prepare("SELECT id FROM articles WHERE designation = ?");
                    $stmtArt->execute([$designation]);
                    $art = $stmtArt->fetch();
                    if ($art) {
                        $article_id = $art['id'];
                    } else {
                        // إذا كانت السلعة غير موجودة، كنزيدوها فـ المخزن
                        $stmtInsArt = $pdo->prepare("INSERT INTO articles (designation, unite, prix_unitaire, quantite_stock) VALUES (?, ?, ?, 0)");
                        $stmtInsArt->execute([$designation, $unite, $prix]);
                        $article_id = $pdo->lastInsertId();
                    }
                }

                // حفظ سطر السلعة فـ تفاصيل الوثيقة
                $sqlItem = "INSERT INTO document_items (document_id, article_id, quantite, prix_unitaire, unite, total) 
                            VALUES (?, ?, ?, ?, ?, ?)";
                $stmtItem = $pdo->prepare($sqlItem);
                $stmtItem->execute([$document_id, $article_id, $qty, $prix, $unite, $total]);

                // 5. تحديث الكمية فـ المخزن (Stock Update)
                // - الفاتورة و Bon de Livraison ديال البيع كينقصو السلعة
                if (in_array($type_doc, ['facture_vente', 'bl_vente'])) {
                    $stmtStock = $pdo->prepare("UPDATE articles SET quantite_stock = quantite_stock - ? WHERE id = ?");
                    $stmtStock->execute([$qty, $article_id]);
                } 
                // - الفاتورة و Bon de Livraison ديال الشراء كيزيدو السلعة
                elseif (in_array($type_doc, ['facture_achat', 'bl_achat'])) {
                    $stmtStock = $pdo->prepare("UPDATE articles SET quantite_stock = quantite_stock + ? WHERE id = ?");
                    $stmtStock->execute([$qty, $article_id]);
                }
                // (ملاحظة: Devis و Bon de commande كيحفظو الوثيقة بلا ما يقيسو المخزن حيت مازال ما تمت التسليم)
            }
        }

        // إتمام العملية بنجاح
        $pdo->commit();
        
        // التوجيه إلى صفحة المخزن مع رسالة نجاح
        header("Location: stock.php?msg=doc_created");
        exit();

    } catch (Exception $e) {
        // فـ حالة وجود أي خطأ، يتم التراجع عن كل التغييرات
        $pdo->rollBack();
        die("Erreur lors de l'enregistrement : " . $e->getMessage());
    }
} else {
    header("Location: document_create.php");
    exit();
}
?>
