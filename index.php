<?php
include 'includes/config.php';

// Link ekleme veya kategori ekleme işlemi
if (isset($_POST['submit'])) {
    $linkUrl = $_POST['link_url'];
    $description = $_POST['description']; // Yeni açıklama alanı
    $selectedCategory = $_POST['category_id'];
    $newCategoryName = $_POST['new_category_name'] ?? '';

    // Yeni kategori ekleme
    if ($selectedCategory === "new" && !empty($newCategoryName)) {
        $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (:name)");
        $stmt->execute(['name' => $newCategoryName]);
        $categoryId = $pdo->lastInsertId();
    } else {
        $categoryId = $selectedCategory;
    }

    // Link ekleme
    if (!empty($linkUrl) && !empty($categoryId)) {
        $stmt = $pdo->prepare("INSERT INTO links (url, description, category_id) VALUES (:url, :description, :category_id)");
        $stmt->execute(['url' => $linkUrl, 'description' => $description, 'category_id' => $categoryId]);
    }
}

// Link güncelleme işlemi
if (isset($_POST['update_link'])) {
    $linkId = $_POST['link_id'];
    $newUrl = $_POST['edit_url'];
    $newDescription = $_POST['edit_description'];

    $stmt = $pdo->prepare("UPDATE links SET url = :url, description = :description WHERE id = :id");
    $stmt->execute(['url' => $newUrl, 'description' => $newDescription, 'id' => $linkId]);

    // JSON response döndür
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;
}

// Kategorileri ve linkleri çek
$categories = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
$linksByCategory = $pdo->query("SELECT links.id, links.url, links.description, categories.id as category_id, categories.name as category_name 
                                FROM links 
                                JOIN categories ON links.category_id = categories.id")
    ->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <link rel="icon" href="assets/img/http.png" type="image/x-icon" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bağlantılar Yöneticisi</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <div class="container">
        <h1 class="text-center mb-4">Bağlantı Yöneticisi</h1>
        <!-- Link ve kategori ekleme formu -->
        <div class="form-container">
            <form action="" method="POST" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="link_url" class="form-control" placeholder="Bağlantının URL'si" required>
                </div>
                <div class="col-md-4">
                    <input type="text" name="description" class="form-control" placeholder="Bağlantının Açıklaması">
                </div>
                <div class="col-md-2">
                    <select name="category_id" class="form-select" onchange="toggleNewCategoryInput(this)" required>
                        <option value="">Kategori</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                        <?php endforeach; ?>
                        <option value="new">+ Yeni Kategori</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" name="submit" class="btn btn-primary w-100">
                        <i class="fas fa-plus"></i> Ekle
                    </button>
                </div>
                <div class="col-12">
                    <input type="text" name="new_category_name" id="new-category-input" class="form-control"
                        placeholder="Yeni Kategori Adı" style="display: none;">
                </div>
            </form>
        </div>

        <!-- Kategoriler ve içindeki linkler -->
        <?php foreach ($categories as $category): ?>
            <div class="card">
                <div class="card-header">
                    <h3 class="category-title"><?= htmlspecialchars($category['name']) ?></h3>
                </div>
                <div class="card-body p-0">
                    <?php
                    $hasLinks = false;
                    foreach ($linksByCategory as $link):
                        if ($link['category_id'] == $category['id']):
                            $hasLinks = true;
                    ?>
                            <div class="link-item" data-link-id="<?= $link['id'] ?>">
                                <div class="link-content">
                                    <a href="<?= htmlspecialchars($link['url']) ?>" target="_blank">
                                        <i class="fas fa-link me-2"></i><?= htmlspecialchars($link['url']) ?>
                                    </a>
                                    <p class="link-description mb-0 ms-4 text-muted">
                                        <?= htmlspecialchars($link['description'] ?? '') ?>
                                    </p>
                                </div>
                                <div class="link-actions">
                                    <span class="edit-icon me-2" onclick="editLink(this)">
                                        <i class="fas fa-edit"></i>
                                    </span>
                                    <span class="delete-icon" onclick="deleteLink(<?= $link['id'] ?>, this)">
                                        <i class="fas fa-trash-alt"></i>
                                    </span>
                                </div>
                            </div>
                        <?php
                        endif;
                    endforeach;
                    if (!$hasLinks):
                        ?>
                        <div class="p-3 text-center text-muted">
                            Bu kategoride henüz bağlantılı bulunmuyor.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Düzenleme Modal -->
    <div class="modal fade" id="editLinkModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Bağlantıyı Düzenle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editLinkForm">
                        <input type="hidden" id="edit_link_id" name="link_id">
                        <div class="mb-3">
                            <label class="form-label">URL</label>
                            <input type="text" class="form-control" id="edit_url" name="edit_url" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Açıklama</label>
                            <input type="text" class="form-control" id="edit_description" name="edit_description" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="button" class="btn btn-primary" onclick="updateLink()">Kaydet</button>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>

</body>

</html>