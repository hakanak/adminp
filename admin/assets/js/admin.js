// Dosya: /admin/assets/js/admin.js
// Admin Panel JavaScript Fonksiyonları

/**
 * Slug generator (Türkçe karakter desteği)
 */
function generateSlug(text) {
    const turkce = {
        'ç': 'c', 'ğ': 'g', 'ı': 'i', 'ö': 'o', 'ş': 's', 'ü': 'u',
        'Ç': 'c', 'Ğ': 'g', 'İ': 'i', 'Ö': 'o', 'Ş': 's', 'Ü': 'u'
    };

    let slug = text.toLowerCase();

    // Türkçe karakterleri değiştir
    for (let [tr, en] of Object.entries(turkce)) {
        slug = slug.replace(new RegExp(tr, 'g'), en);
    }

    // Alfanumerik olmayan karakterleri tire ile değiştir
    slug = slug.replace(/[^a-z0-9]+/g, '-');

    // Baştaki ve sondaki tireleri kaldır
    slug = slug.replace(/^-|-$/g, '');

    return slug;
}

/**
 * Karakter sayaçları
 */
function initCharCounters() {
    const fields = [
        { input: '#seo_title', counter: '#seo_title_count', max: 70 },
        { input: '#seo_description', counter: '#seo_desc_count', max: 160 }
    ];

    fields.forEach(field => {
        const input = document.querySelector(field.input);
        const counter = document.querySelector(field.counter);

        if (!input || !counter) return;

        const update = () => {
            const len = input.value.length;
            counter.textContent = `${len}/${field.max}`;

            if (len > field.max) {
                counter.className = 'text-danger';
            } else if (len > field.max * 0.8) {
                counter.className = 'text-warning';
            } else {
                counter.className = 'text-success';
            }
        };

        input.addEventListener('input', update);
        update();
    });
}

/**
 * Google önizlemesini güncelle
 */
function updateGooglePreview() {
    const title = document.querySelector('#seo_title')?.value ||
                  document.querySelector('#title')?.value || '';
    const desc = document.querySelector('#seo_description')?.value || '';
    const slug = document.querySelector('#slug')?.value || '';
    const siteTitle = document.body.dataset.siteTitle || 'Site';

    const previewTitle = document.querySelector('#preview-title');
    const previewUrl = document.querySelector('#preview-url');
    const previewDesc = document.querySelector('#preview-desc');

    if (previewTitle) {
        previewTitle.textContent = title ? `${title} | ${siteTitle}` : siteTitle;
    }

    if (previewUrl) {
        const baseUrl = window.location.origin;
        previewUrl.textContent = `${baseUrl}/${slug}`;
    }

    if (previewDesc) {
        previewDesc.textContent = desc || 'Meta açıklama girilmemiş...';
    }
}

/**
 * SEO Skor Analizi
 */
function analyzeSEO() {
    const title = document.querySelector('#seo_title')?.value ||
                  document.querySelector('#title')?.value || '';
    const desc = document.querySelector('#seo_description')?.value || '';
    const slug = document.querySelector('#slug')?.value || '';

    // İçeriği al (TinyMCE veya normal textarea)
    let content = '';
    if (typeof tinymce !== 'undefined' && tinymce.get('content')) {
        content = tinymce.get('content').getContent({ format: 'text' });
    } else if (document.querySelector('#content')) {
        content = document.querySelector('#content').value || '';
    }

    // Skor ve ipuçları
    let score = 0;
    let tips = [];

    // Başlık kontrolü
    if (title.length > 0) {
        if (title.length >= 50 && title.length <= 60) {
            score += 20;
            tips.push('✅ Başlık uzunluğu ideal (50-60 karakter)');
        } else if (title.length >= 40 && title.length <= 70) {
            score += 15;
            tips.push('⚠️ Başlık uzunluğu kabul edilebilir (40-70 karakter)');
        } else if (title.length > 0) {
            score += 10;
            tips.push('❌ Başlık 50-60 karakter arası olmalı (şu an: ' + title.length + ')');
        }
    } else {
        tips.push('❌ SEO başlığı girilmemiş');
    }

    // Açıklama kontrolü
    if (desc.length > 0) {
        if (desc.length >= 140 && desc.length <= 160) {
            score += 20;
            tips.push('✅ Açıklama uzunluğu ideal (140-160 karakter)');
        } else if (desc.length >= 100 && desc.length <= 170) {
            score += 15;
            tips.push('⚠️ Açıklama uzunluğu kabul edilebilir (100-170 karakter)');
        } else if (desc.length > 0) {
            score += 10;
            tips.push('❌ Açıklama 140-160 karakter arası olmalı (şu an: ' + desc.length + ')');
        }
    } else {
        tips.push('❌ Meta açıklama girilmemiş');
    }

    // Slug kontrolü
    if (slug.length > 0) {
        if (slug.length >= 3 && slug.length <= 50) {
            score += 10;
            tips.push('✅ Slug formatı uygun');
        } else {
            tips.push('⚠️ Slug çok kısa veya uzun');
        }

        // Slug Türkçe karakter kontrolü
        if (!/[çğıöşü]/i.test(slug)) {
            score += 5;
        } else {
            tips.push('❌ Slug\'da Türkçe karakter var');
        }
    }

    // İçerik uzunluğu
    const wordCount = content.split(/\s+/).filter(w => w.length > 0).length;
    if (wordCount >= 300) {
        score += 20;
        tips.push(`✅ İçerik uzunluğu yeterli (${wordCount} kelime)`);
    } else if (wordCount >= 150) {
        score += 10;
        tips.push(`⚠️ İçerik biraz kısa (${wordCount} kelime, en az 300 önerilir)`);
    } else if (wordCount > 0) {
        tips.push(`❌ İçerik çok kısa (${wordCount} kelime, en az 300 olmalı)`);
    }

    // Başlık ve açıklamada anahtar kelime varlığı
    if (title && desc) {
        const titleWords = title.toLowerCase().split(/\s+/).filter(w => w.length > 3);
        const descLower = desc.toLowerCase();

        let foundKeywords = 0;
        titleWords.forEach(word => {
            if (descLower.includes(word)) {
                foundKeywords++;
            }
        });

        if (foundKeywords > 0) {
            score += 10;
            tips.push('✅ Başlık ve açıklama birbiriyle ilişkili');
        }
    }

    // İçerikte başlık kelimesi var mı?
    if (title && content) {
        const titleLower = title.toLowerCase();
        const contentLower = content.toLowerCase();
        const titleWords = titleLower.split(/\s+/).filter(w => w.length > 3);

        let foundInContent = 0;
        titleWords.forEach(word => {
            if (contentLower.includes(word)) {
                foundInContent++;
            }
        });

        if (foundInContent >= titleWords.length * 0.5) {
            score += 15;
            tips.push('✅ Başlıktaki kelimeler içerikte bulunuyor');
        } else {
            tips.push('⚠️ Başlıktaki kelimeler içerikte yeterince yok');
        }
    }

    // Maksimum 100 olarak sınırla
    score = Math.min(100, score);

    updateScoreUI(score, tips);
}

/**
 * Skor UI'ını güncelle
 */
function updateScoreUI(score, tips) {
    const scoreEl = document.querySelector('#seo-score');
    const tipsEl = document.querySelector('#seo-tips');

    if (scoreEl) {
        scoreEl.textContent = score;
        scoreEl.classList.remove('good', 'ok', 'poor');

        if (score >= 70) {
            scoreEl.classList.add('good');
        } else if (score >= 40) {
            scoreEl.classList.add('ok');
        } else {
            scoreEl.classList.add('poor');
        }
    }

    if (tipsEl) {
        tipsEl.innerHTML = tips.map(t => `<div class="seo-tip">${t}</div>`).join('');
    }
}

/**
 * Resim önizleme
 */
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();

        reader.onload = function(e) {
            const preview = document.querySelector(previewId);
            if (preview) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
        };

        reader.readAsDataURL(input.files[0]);
    }
}

/**
 * Form değişiklik uyarısı
 */
let formChanged = false;

function initFormChangeWarning() {
    const forms = document.querySelectorAll('form[method="POST"]');

    forms.forEach(form => {
        const inputs = form.querySelectorAll('input, textarea, select');

        inputs.forEach(input => {
            input.addEventListener('change', () => {
                formChanged = true;
            });
        });

        form.addEventListener('submit', () => {
            formChanged = false;
        });
    });

    window.addEventListener('beforeunload', (e) => {
        if (formChanged) {
            e.preventDefault();
            e.returnValue = '';
            return '';
        }
    });
}

/**
 * AJAX resim yükleme
 */
function uploadImageAjax(file, callback) {
    const formData = new FormData();
    formData.append('image', file);

    fetch('ajax/upload.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            callback(data.url);
        } else {
            alert('Resim yüklenirken hata oluştu: ' + (data.message || 'Bilinmeyen hata'));
        }
    })
    .catch(error => {
        console.error('Upload error:', error);
        alert('Resim yüklenirken hata oluştu.');
    });
}

/**
 * Confirm dialog (daha güzel)
 */
function confirmDelete(message) {
    return confirm(message || 'Silmek istediğinize emin misiniz?');
}

/**
 * Sayfa yüklendiğinde
 */
document.addEventListener('DOMContentLoaded', function() {
    // Karakter sayaçları
    initCharCounters();

    // Title -> Slug otomatik
    const titleInput = document.querySelector('#title');
    const slugInput = document.querySelector('#slug');

    if (titleInput && slugInput) {
        // Sadece yeni kayıt eklerken (slug boşsa)
        const isNewRecord = !slugInput.value || slugInput.value === '';

        if (isNewRecord) {
            titleInput.addEventListener('blur', () => {
                if (!slugInput.value) {
                    slugInput.value = generateSlug(titleInput.value);
                    updateGooglePreview();
                }
            });
        }

        // Slug değiştiğinde önizlemeyi güncelle
        slugInput.addEventListener('input', updateGooglePreview);
    }

    // SEO alanları değişince preview güncelle
    ['#seo_title', '#seo_description', '#title', '#slug'].forEach(sel => {
        const el = document.querySelector(sel);
        if (el) {
            el.addEventListener('input', updateGooglePreview);
            el.addEventListener('input', analyzeSEO);
        }
    });

    // TinyMCE yüklendiğinde
    if (typeof tinymce !== 'undefined') {
        tinymce.on('addeditor', function(e) {
            e.editor.on('keyup', analyzeSEO);
            e.editor.on('change', analyzeSEO);
        });
    }

    // İlk yükleme
    updateGooglePreview();
    analyzeSEO();

    // Form değişiklik uyarısı
    // initFormChangeWarning(); // İsteğe bağlı aktif edebilirsiniz

    // Tooltip'leri aktif et
    if (typeof bootstrap !== 'undefined') {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    // Dropdown'ları aktif et
    if (typeof bootstrap !== 'undefined') {
        const dropdownElementList = [].slice.call(document.querySelectorAll('[data-bs-toggle="dropdown"]'));
        dropdownElementList.map(function (dropdownToggleEl) {
            return new bootstrap.Dropdown(dropdownToggleEl);
        });
    }
});

/**
 * Toplu işlemler
 */
function selectAll(checkbox) {
    const checkboxes = document.querySelectorAll('input[name="selected[]"]');
    checkboxes.forEach(cb => cb.checked = checkbox.checked);
}

function bulkAction(action) {
    const selected = document.querySelectorAll('input[name="selected[]"]:checked');

    if (selected.length === 0) {
        alert('Lütfen en az bir öğe seçin.');
        return false;
    }

    if (action === 'delete') {
        if (!confirm(`${selected.length} öğeyi silmek istediğinize emin misiniz?`)) {
            return false;
        }
    }

    return true;
}
