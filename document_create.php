<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Créer un Document</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light p-4">
<div class="container bg-white p-4 rounded shadow-sm">
    <h2 class="mb-4">Créer un Document (Vente / Achat)</h2>
    <form action="save_document.php" method="POST">
        
        <!-- En-tête du Document -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="form-label">Type de Document</label>
                <select name="type_doc" class="form-select" required>
                    <option value="facture_vente">Facture Vente</option>
                    <option value="bl_vente">Bon de Livraison Vente</option>
                    <option value="devis">Devis</option>
                    <option value="bc_achat">Bon de Commande Achat</option>
                    <option value="bl_achat">Bon de Livraison Achat</option>
                    <option value="facture_achat">Facture Achat</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">N° Document</label>
                <input type="text" name="numero_doc" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Date</label>
                <input type="date" name="date_doc" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Client / Fournisseur</label>
                <input type="text" name="tier_nom" class="form-control" placeholder="Nom du Client/Fournisseur" required>
            </div>
        </div>

        <!-- Champs Optionnels (N° BC, N° BL, Matricule) -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label">N° Bon de Commande</label>
                <input type="text" name="num_bc" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">N° Bon de Livraison</label>
                <input type="text" name="num_bl" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Matricule</label>
                <input type="text" name="matricule" class="form-control">
            </div>
        </div>

        <!-- Tableau des Articles -->
        <h4 class="mt-4">Articles</h4>
        <table class="table table-bordered" id="itemsTable">
            <thead class="table-dark">
                <tr>
                    <th>N° Article</th>
                    <th>Article</th>
                    <th>Quantité</th>
                    <th>Unité</th>
                    <th>Prix Unitaire</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><input type="text" name="items[0][num]" class="form-control" required></td>
                    <td><input type="text" name="items[0][designation]" class="form-control" required></td>
                    <td><input type="number" name="items[0][qty]" class="form-control qty" oninput="calculateRow(this)" required></td>
                    <td><input type="text" name="items[0][unite]" class="form-control" value="PCS"></td>
                    <td><input type="number" step="0.01" name="items[0][prix]" class="form-control prix" oninput="calculateRow(this)" required></td>
                    <td><input type="number" step="0.01" name="items[0][total]" class="form-control total" readonly></td>
                    <td><button type="button" class="btn btn-danger btn-sm" onclick="this.closest('tr').remove()">X</button></td>
                </tr>
            </tbody>
        </table>
        <button type="button" class="btn btn-secondary mb-3" onclick="addRow()">+ Ajouter un article</button>

        <div class="text-end">
            <button type="submit" class="btn btn-success btn-lg">Enregistrer</button>
        </div>
    </form>
</div>

<script>
let itemIndex = 1;

function calculateRow(element) {
    let row = element.closest('tr');
    let qty = parseFloat(row.querySelector('.qty').value) || 0;
    let prix = parseFloat(row.querySelector('.prix').value) || 0;
    row.querySelector('.total').value = (qty * prix).toFixed(2);
}

function addRow() {
    let table = document.getElementById('itemsTable').getElementsByTagName('tbody')[0];
    let newRow = table.insertRow();
    newRow.innerHTML = `
        <td><input type="text" name="items[${itemIndex}][num]" class="form-control" required></td>
        <td><input type="text" name="items[${itemIndex}][designation]" class="form-control" required></td>
        <td><input type="number" name="items[${itemIndex}][qty]" class="form-control qty" oninput="calculateRow(this)" required></td>
        <td><input type="text" name="items[${itemIndex}][unite]" class="form-control" value="PCS"></td>
        <td><input type="number" step="0.01" name="items[${itemIndex}][prix]" class="form-control prix" oninput="calculateRow(this)" required></td>
        <td><input type="number" step="0.01" name="items[${itemIndex}][total]" class="form-control total" readonly></td>
        <td><button type="button" class="btn btn-danger btn-sm" onclick="this.closest('tr').remove()">X</button></td>
    `;
    itemIndex++;
}
</script>
</body>
</html>
