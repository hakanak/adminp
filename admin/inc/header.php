<?php
// Dosya: /admin/inc/header.php
$adminUser = getAdminUser();
$settings = getSettings();
$pageTitle = $pageTitle ?? 'Panel';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <title><?= e($pageTitle) ?> | <?= e($settings['site_title']) ?> Admin</title>

    <!-- Tabler CSS -->
    <link href="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/css/tabler.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@tabler/icons@latest/iconfont/tabler-icons.min.css" rel="stylesheet">

    <!-- Custom Admin CSS -->
    <link href="<?= adminUrl('assets/css/admin.css') ?>" rel="stylesheet">

    <!-- Favicon -->
    <?php if (!empty($settings['favicon'])): ?>
    <link rel="icon" href="<?= siteUrl('uploads/' . $settings['favicon']) ?>" type="image/x-icon">
    <?php endif; ?>
</head>
<body data-site-title="<?= e($settings['site_title']) ?>">
    <div class="page">
        <!-- Navbar -->
        <header class="navbar navbar-expand-md navbar-light d-print-none">
            <div class="container-xl">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <h1 class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-md-3">
                    <a href="<?= adminUrl('dashboard.php') ?>">
                        <?php if (!empty($settings['logo'])): ?>
                            <img src="<?= siteUrl('uploads/' . $settings['logo']) ?>" height="36" alt="<?= e($settings['site_title']) ?>">
                        <?php else: ?>
                            <?= e($settings['site_title']) ?>
                        <?php endif; ?>
                    </a>
                </h1>
                <div class="navbar-nav flex-row order-md-last">
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown">
                            <span class="avatar avatar-sm" style="background-image: url(https://ui-avatars.com/api/?name=<?= urlencode($adminUser['full_name'] ?? $adminUser['username']) ?>&background=206bc4&color=fff)"></span>
                            <div class="d-none d-xl-block ps-2">
                                <div><?= e($adminUser['full_name'] ?? $adminUser['username']) ?></div>
                                <div class="mt-1 small text-muted"><?= e($adminUser['username']) ?></div>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                            <a href="<?= adminUrl('profile.php') ?>" class="dropdown-item">
                                <i class="ti ti-user me-2"></i> Profilim
                            </a>
                            <a href="<?= adminUrl('settings.php') ?>" class="dropdown-item">
                                <i class="ti ti-settings me-2"></i> Ayarlar
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="<?= adminUrl('logout.php') ?>" class="dropdown-item text-danger">
                                <i class="ti ti-logout me-2"></i> Çıkış Yap
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <?php include __DIR__ . '/sidebar.php'; ?>

        <div class="page-wrapper">
            <div class="page-header d-print-none">
                <div class="container-xl">
                    <div class="row g-2 align-items-center">
                        <div class="col">
                            <h2 class="page-title"><?= e($pageTitle) ?></h2>
                            <?php if (isset($breadcrumbs)): ?>
                            <div class="text-muted mt-1">
                                <?php foreach ($breadcrumbs as $i => $crumb): ?>
                                    <?php if ($i > 0): ?> / <?php endif; ?>
                                    <?php if (isset($crumb['url'])): ?>
                                        <a href="<?= e($crumb['url']) ?>" class="text-muted"><?= e($crumb['name']) ?></a>
                                    <?php else: ?>
                                        <?= e($crumb['name']) ?>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php if (isset($headerButtons)): ?>
                        <div class="col-auto ms-auto d-print-none">
                            <?= $headerButtons ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Page body -->
            <div class="page-body">
                <div class="container-xl">
                    <?= showFlash('message') ?>
                    <?= showFlash('success') ?>
                    <?= showFlash('error') ?>
                    <?= showFlash('warning') ?>
