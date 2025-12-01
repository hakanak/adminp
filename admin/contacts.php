<?php
// Dosya: /admin/contacts.php
// İletişim mesajları yönetimi

require_once __DIR__ . '/inc/auth.php';

$pageTitle = 'İletişim Mesajları';
$db = Database::getInstance();

$action = $_GET['action'] ?? 'list';
$contactId = (int)($_GET['id'] ?? 0);

// İşlemler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf()) {
    $formAction = $_POST['form_action'] ?? '';

    if ($formAction === 'mark_read') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $db->update('contacts', ['is_read' => 1], 'id = ?', [$id]);
            flash('success', 'Mesaj okundu olarak işaretlendi.', 'success');
        }
        redirect(adminUrl('contacts.php'));
    } elseif ($formAction === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $db->delete('contacts', 'id = ?', [$id]);
            flash('success', 'Mesaj silindi.', 'success');
        }
        redirect(adminUrl('contacts.php'));
    }
}

// Detay görünümü
if ($action === 'view' && $contactId > 0) {
    $contact = $db->fetchOne("SELECT * FROM contacts WHERE id = ?", [$contactId]);

    if (!$contact) {
        flash('error', 'Mesaj bulunamadı.', 'error');
        redirect(adminUrl('contacts.php'));
    }

    // Okundu olarak işaretle
    if (!$contact['is_read']) {
        $db->update('contacts', ['is_read' => 1], 'id = ?', [$contactId]);
    }

    include __DIR__ . '/inc/header.php';
    ?>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Mesaj Detayı</h3>
                    <div class="card-actions">
                        <a href="<?= adminUrl('contacts.php') ?>" class="btn btn-sm btn-outline-primary">
                            Listeye Dön
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="datagrid mb-4">
                        <div class="datagrid-item">
                            <div class="datagrid-title">Gönderen</div>
                            <div class="datagrid-content"><strong><?= e($contact['name']) ?></strong></div>
                        </div>
                        <?php if ($contact['email']): ?>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Email</div>
                            <div class="datagrid-content">
                                <a href="mailto:<?= e($contact['email']) ?>"><?= e($contact['email']) ?></a>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if ($contact['phone']): ?>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Telefon</div>
                            <div class="datagrid-content">
                                <a href="tel:<?= e($contact['phone']) ?>"><?= e($contact['phone']) ?></a>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if ($contact['subject']): ?>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Konu</div>
                            <div class="datagrid-content"><?= e($contact['subject']) ?></div>
                        </div>
                        <?php endif; ?>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Tarih</div>
                            <div class="datagrid-content"><?= formatDate($contact['created_at']) ?></div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">IP Adresi</div>
                            <div class="datagrid-content"><?= e($contact['ip_address']) ?></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Mesaj:</label>
                        <div class="border rounded p-3 bg-light">
                            <?= nl2br(e($contact['message'])) ?>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-flex">
                        <?php if ($contact['email']): ?>
                            <a href="mailto:<?= e($contact['email']) ?>" class="btn btn-primary">
                                <i class="ti ti-mail me-2"></i> Yanıtla
                            </a>
                        <?php endif; ?>
                        <button type="button" class="btn btn-danger ms-auto" onclick="deleteContact(<?= $contact['id'] ?>)">
                            <i class="ti ti-trash me-2"></i> Sil
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form id="deleteForm" method="POST" style="display:none;">
        <?= csrfField() ?>
        <input type="hidden" name="form_action" value="delete">
        <input type="hidden" name="id" id="deleteId">
    </form>

    <script>
    function deleteContact(id) {
        if (confirm('Bu mesajı silmek istediğinize emin misiniz?')) {
            document.getElementById('deleteId').value = id;
            document.getElementById('deleteForm').submit();
        }
    }
    </script>

    <?php
    include __DIR__ . '/inc/footer.php';
}
// Liste görünümü
else {
    $page = (int)($_GET['page'] ?? 1);
    $perPage = ADMIN_ITEMS_PER_PAGE;
    $offset = ($page - 1) * $perPage;
    $filter = $_GET['filter'] ?? 'all';

    $where = '1=1';
    $params = [];

    if ($filter === 'unread') {
        $where .= ' AND is_read = 0';
    } elseif ($filter === 'read') {
        $where .= ' AND is_read = 1';
    }

    $totalContacts = $db->count('contacts', $where, $params);
    $totalPages = ceil($totalContacts / $perPage);

    $contacts = $db->fetchAll(
        "SELECT * FROM contacts WHERE $where ORDER BY created_at DESC LIMIT $perPage OFFSET $offset",
        $params
    );

    $unreadCount = $db->count('contacts', 'is_read = 0');

    include __DIR__ . '/inc/header.php';
    ?>

    <!-- Filtreler -->
    <div class="card mb-3">
        <div class="card-body">
            <div class="btn-group" role="group">
                <a href="<?= adminUrl('contacts.php') ?>" class="btn btn-outline-primary <?= $filter === 'all' ? 'active' : '' ?>">
                    Tümü (<?= $totalContacts ?>)
                </a>
                <a href="<?= adminUrl('contacts.php?filter=unread') ?>" class="btn btn-outline-primary <?= $filter === 'unread' ? 'active' : '' ?>">
                    Okunmamış (<?= $unreadCount ?>)
                </a>
                <a href="<?= adminUrl('contacts.php?filter=read') ?>" class="btn btn-outline-primary <?= $filter === 'read' ? 'active' : '' ?>">
                    Okunmuş (<?= $totalContacts - $unreadCount ?>)
                </a>
            </div>
        </div>
    </div>

    <!-- Mesajlar Tablosu -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table table-striped">
                <thead>
                    <tr>
                        <th>Durum</th>
                        <th>Gönderen</th>
                        <th>Konu</th>
                        <th>Tarih</th>
                        <th class="w-1"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($contacts)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Mesaj bulunamadı</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($contacts as $c): ?>
                            <tr class="<?= !$c['is_read'] ? 'fw-bold' : '' ?>">
                                <td>
                                    <?php if (!$c['is_read']): ?>
                                        <span class="badge bg-red">Yeni</span>
                                    <?php else: ?>
                                        <span class="text-muted">Okundu</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div><?= e($c['name']) ?></div>
                                    <?php if ($c['email']): ?>
                                        <div class="small text-muted"><?= e($c['email']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= e($c['subject'] ?: truncate($c['message'], 50)) ?>
                                </td>
                                <td>
                                    <div class="small"><?= timeAgo($c['created_at']) ?></div>
                                </td>
                                <td>
                                    <a href="<?= adminUrl('contacts.php?action=view&id=' . $c['id']) ?>" class="btn btn-sm btn-primary">
                                        Görüntüle
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="card-footer">
                <?= pagination($page, $totalPages, adminUrl('contacts.php' . ($filter !== 'all' ? '?filter=' . $filter : ''))) ?>
            </div>
        <?php endif; ?>
    </div>

    <?php
    include __DIR__ . '/inc/footer.php';
}
?>
