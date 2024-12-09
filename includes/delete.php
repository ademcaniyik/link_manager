<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['link_id'])) {
    $linkId = $_POST['link_id'];

    // Linki sil
    $stmt = $pdo->prepare("DELETE FROM links WHERE id = :id");
    $success = $stmt->execute(['id' => $linkId]);

    echo json_encode(['success' => $success]);
}
?>
