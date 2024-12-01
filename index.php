<?php
include 'config.php';

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
    <link rel="icon" href="http.png" type="image/x-icon" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bağlantılar</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            padding-top: 2rem;
        }
        .container {
            max-width: 900px;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card-header {
            background-color: #4a90e2;
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 1rem;
        }
        .link-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            border-bottom: 1px solid #eee;
            transition: background-color 0.2s;
        }
        .link-item:last-child {
            border-bottom: none;
        }
        .link-item:hover {
            background-color: #f8f9fa;
        }
        .link-item a {
            color: #2c3e50;
            text-decoration: none;
            flex-grow: 1;
            margin-right: 1rem;
            word-break: break-all;
        }
        .delete-icon {
            color: #dc3545;
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 5px;
            transition: all 0.2s;
        }
        .delete-icon:hover {
            background-color: #dc3545;
            color: white;
        }
        .form-container {
            background-color: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        .btn-primary {
            background-color: #4a90e2;
            border: none;
            padding: 0.5rem 2rem;
        }
        .btn-primary:hover {
            background-color: #357abd;
        }
        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #ced4da;
        }
        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 0.2rem rgba(74, 144, 226, 0.25);
            border-color: #4a90e2;
        }
        .category-title {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 600;
        }
        @media (max-width: 768px) {
            .container {
                padding: 0 15px;
            }
        }
    </style>
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
                    <input type="text" name="description" class="form-control" placeholder="Bağlantının Açıklaması" required>
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

    <!-- Bootstrap JS ve Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleNewCategoryInput(selectBox) {
            const newCategoryInput = document.getElementById('new-category-input');
            if (selectBox.value === 'new') {
                newCategoryInput.style.display = 'block';
                newCategoryInput.required = true;
            } else {
                newCategoryInput.style.display = 'none';
                newCategoryInput.required = false;
            }
        }

        function deleteLink(linkId, element) {
            if (confirm("Bu bağlantıyı silmek istediğinize emin misiniz?")) {
                const xhr = new XMLHttpRequest();
                xhr.open("POST", "delete.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            const linkItem = element.closest('.link-item');
                            const cardBody = linkItem.closest('.card-body');
                            
                            // Önce link öğesini kaldır
                            linkItem.style.opacity = '0';
                            setTimeout(() => {
                                linkItem.remove();
                                
                                // Eğer card-body varsa ve başka link kalmadıysa mesaj göster
                                if (cardBody && cardBody.querySelectorAll('.link-item').length === 0) {
                                    cardBody.innerHTML = '<div class="p-3 text-center text-muted">Bu kategoride henüz bağlantı bulunmuyor.</div>';
                                }
                            }, 300);
                        } else {
                            alert("Silme işlemi başarısız oldu.");
                        }
                    }
                };
                xhr.send("link_id=" + linkId);
            }
        }

        function editLink(element) {
            const linkItem = element.closest('.link-item');
            const linkId = linkItem.dataset.linkId;
            const url = linkItem.querySelector('a').textContent.trim();
            const description = linkItem.querySelector('.link-description').textContent.trim();
            
            // Modal alanlarını doldur
            document.getElementById('edit_link_id').value = linkId;
            document.getElementById('edit_url').value = url;
            document.getElementById('edit_description').value = description;
            
            // Modal'ı göster
            new bootstrap.Modal(document.getElementById('editLinkModal')).show();
        }

        function updateLink() {
            const form = document.getElementById('editLinkForm');
            const formData = new FormData(form);
            
            fetch('', {
                method: 'POST',
                body: new URLSearchParams(formData),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const linkId = formData.get('link_id');
                    const linkItem = document.querySelector(`.link-item[data-link-id="${linkId}"]`);
                    
                    // Link içeriğini güncelle
                    linkItem.querySelector('a').textContent = formData.get('edit_url');
                    linkItem.querySelector('.link-description').textContent = formData.get('edit_description');
                    
                    // Modal'ı kapat
                    bootstrap.Modal.getInstance(document.getElementById('editLinkModal')).hide();
                }
            })
            .catch(error => {
                alert('Güncelleme sırasında bir hata oluştu.');
            });
        }
    </script>
</body>
</html>
