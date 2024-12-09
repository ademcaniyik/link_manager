

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
        xhr.open("POST", "includes/delete.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
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
