<?php
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