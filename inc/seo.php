<?php
// Dosya: /inc/seo.php
// SEO Meta Tag ve Schema.org Fonksiyonları

/**
 * SEO meta taglarını render et
 * @param array $item Sayfa/Ürün/Post verisi
 * @param string $type Tip: 'page', 'product', 'post'
 * @return string
 */
function renderSeoTags($item, $type = 'page') {
    $settings = getSettings();

    // Fallback değerler
    $title = !empty($item['seo_title'])
        ? $item['seo_title']
        : $item['title'] . ' | ' . $settings['site_title'];

    $description = !empty($item['seo_description'])
        ? $item['seo_description']
        : truncate(strip_tags($item['content'] ?? $item['description'] ?? $item['excerpt'] ?? ''), 160);

    $keywords = $item['seo_keywords'] ?? '';

    $ogImage = !empty($item['og_image'])
        ? siteUrl('uploads/' . $item['og_image'])
        : (!empty($item['featured_image'])
            ? siteUrl('uploads/' . $item['featured_image'])
            : (!empty($settings['default_og_image'])
                ? siteUrl('uploads/' . $settings['default_og_image'])
                : ''));

    $robots = $item['seo_robots'] ?? 'index,follow';
    $canonical = !empty($item['seo_canonical']) ? $item['seo_canonical'] : currentUrl();

    // OG için fallback
    $ogTitle = !empty($item['og_title']) ? $item['og_title'] : $title;
    $ogDescription = !empty($item['og_description']) ? $item['og_description'] : $description;

    // Escape değerleri
    $title = e($title);
    $description = e($description);
    $keywords = e($keywords);
    $ogTitle = e($ogTitle);
    $ogDescription = e($ogDescription);
    $ogImage = e($ogImage);
    $canonical = e($canonical);
    $robots = e($robots);

    $output = <<<HTML
    <title>{$title}</title>
    <meta name="description" content="{$description}">
HTML;

    if ($keywords) {
        $output .= "\n    <meta name=\"keywords\" content=\"{$keywords}\">";
    }

    $output .= <<<HTML

    <meta name="robots" content="{$robots}">
    <link rel="canonical" href="{$canonical}">

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="{$ogTitle}">
    <meta property="og:description" content="{$ogDescription}">
HTML;

    if ($ogImage) {
        $output .= "\n    <meta property=\"og:image\" content=\"{$ogImage}\">";
    }

    $output .= <<<HTML

    <meta property="og:url" content="{$canonical}">
    <meta property="og:site_name" content="{$settings['site_title']}">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{$ogTitle}">
    <meta name="twitter:description" content="{$ogDescription}">
HTML;

    if ($ogImage) {
        $output .= "\n    <meta property=\"twitter:image\" content=\"{$ogImage}\">";
    }

    return $output;
}

/**
 * Schema.org JSON-LD render et
 * @param string $type Schema tipi: 'organization', 'product', 'article', 'breadcrumb'
 * @param array $data Data
 * @return string
 */
function renderSchema($type, $data = []) {
    $schema = [];
    $settings = getSettings();

    switch($type) {
        case 'organization':
            $schema = [
                '@context' => 'https://schema.org',
                '@type' => 'Organization',
                'name' => $settings['site_title'] ?? 'Site',
                'url' => SITE_URL
            ];

            if (!empty($settings['logo'])) {
                $schema['logo'] = siteUrl('uploads/' . $settings['logo']);
            }

            if (!empty($settings['phone'])) {
                $schema['contactPoint'] = [
                    '@type' => 'ContactPoint',
                    'telephone' => $settings['phone'],
                    'contactType' => 'customer service',
                    'areaServed' => 'TR',
                    'availableLanguage' => 'Turkish'
                ];
            }

            if (!empty($settings['address'])) {
                $schema['address'] = [
                    '@type' => 'PostalAddress',
                    'addressCountry' => 'TR',
                    'addressLocality' => strip_tags($settings['address'])
                ];
            }

            // Sosyal medya
            $sameAs = [];
            if (!empty($settings['facebook'])) $sameAs[] = $settings['facebook'];
            if (!empty($settings['instagram'])) $sameAs[] = $settings['instagram'];
            if (!empty($settings['twitter'])) $sameAs[] = $settings['twitter'];
            if (!empty($settings['youtube'])) $sameAs[] = $settings['youtube'];
            if (!empty($settings['linkedin'])) $sameAs[] = $settings['linkedin'];

            if (!empty($sameAs)) {
                $schema['sameAs'] = $sameAs;
            }
            break;

        case 'product':
            $schema = [
                '@context' => 'https://schema.org',
                '@type' => 'Product',
                'name' => $data['title'] ?? '',
                'description' => truncate(strip_tags($data['description'] ?? ''), 200)
            ];

            if (!empty($data['featured_image'])) {
                $schema['image'] = siteUrl('uploads/' . $data['featured_image']);
            }

            if (isset($data['price']) && $data['price'] > 0) {
                $schema['offers'] = [
                    '@type' => 'Offer',
                    'price' => $data['price'],
                    'priceCurrency' => 'TRY',
                    'availability' => 'https://schema.org/InStock'
                ];
            }

            if (!empty($data['slug'])) {
                $schema['url'] = siteUrl('urun/' . $data['slug']);
            }
            break;

        case 'article':
            $schema = [
                '@context' => 'https://schema.org',
                '@type' => 'Article',
                'headline' => $data['title'] ?? '',
                'description' => truncate(strip_tags($data['excerpt'] ?? $data['content'] ?? ''), 200),
                'author' => [
                    '@type' => 'Organization',
                    'name' => $settings['site_title'] ?? 'Site'
                ]
            ];

            if (!empty($data['featured_image'])) {
                $schema['image'] = siteUrl('uploads/' . $data['featured_image']);
            }

            if (!empty($data['published_at'])) {
                $schema['datePublished'] = date('c', strtotime($data['published_at']));
            }

            if (!empty($data['updated_at'])) {
                $schema['dateModified'] = date('c', strtotime($data['updated_at']));
            }

            if (!empty($data['slug'])) {
                $schema['url'] = siteUrl('blog/' . $data['slug']);
            }

            $schema['publisher'] = [
                '@type' => 'Organization',
                'name' => $settings['site_title'] ?? 'Site'
            ];

            if (!empty($settings['logo'])) {
                $schema['publisher']['logo'] = [
                    '@type' => 'ImageObject',
                    'url' => siteUrl('uploads/' . $settings['logo'])
                ];
            }
            break;

        case 'breadcrumb':
            $schema = [
                '@context' => 'https://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => []
            ];

            if (!empty($data['items'])) {
                foreach ($data['items'] as $index => $item) {
                    $schema['itemListElement'][] = [
                        '@type' => 'ListItem',
                        'position' => $index + 1,
                        'name' => $item['name'],
                        'item' => $item['url'] ?? null
                    ];
                }
            }
            break;

        case 'website':
            $schema = [
                '@context' => 'https://schema.org',
                '@type' => 'WebSite',
                'name' => $settings['site_title'] ?? 'Site',
                'url' => SITE_URL
            ];

            // Site içi arama
            $schema['potentialAction'] = [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => SITE_URL . '/ara?q={search_term_string}'
                ],
                'query-input' => 'required name=search_term_string'
            ];
            break;
    }

    if (empty($schema)) {
        return '';
    }

    return '<script type="application/ld+json">' .
           json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) .
           '</script>';
}

/**
 * Breadcrumb HTML render et
 * @param array $items [['name' => 'Ana Sayfa', 'url' => '/'], ['name' => 'Blog', 'url' => '/blog'], ...]
 * @return string
 */
function renderBreadcrumb($items) {
    if (empty($items)) return '';

    $html = '<nav aria-label="breadcrumb"><ol class="breadcrumb">';

    $count = count($items);
    foreach ($items as $index => $item) {
        $isLast = ($index === $count - 1);

        if ($isLast) {
            $html .= sprintf(
                '<li class="breadcrumb-item active" aria-current="page">%s</li>',
                e($item['name'])
            );
        } else {
            $html .= sprintf(
                '<li class="breadcrumb-item"><a href="%s">%s</a></li>',
                e($item['url']),
                e($item['name'])
            );
        }
    }

    $html .= '</ol></nav>';

    return $html;
}

/**
 * Sayfa başlığını döndür (title tag için)
 * @param string $pageTitle
 * @param bool $appendSiteTitle
 * @return string
 */
function getPageTitle($pageTitle = '', $appendSiteTitle = true) {
    $settings = getSettings();
    $siteTitle = $settings['site_title'] ?? 'Site';

    if (empty($pageTitle)) {
        return $siteTitle;
    }

    if ($appendSiteTitle) {
        return $pageTitle . ' | ' . $siteTitle;
    }

    return $pageTitle;
}

/**
 * Meta description döndür
 * @param string $content
 * @param int $length
 * @return string
 */
function getMetaDescription($content, $length = 160) {
    $content = strip_tags($content);
    $content = preg_replace('/\s+/', ' ', $content);
    return truncate($content, $length);
}
