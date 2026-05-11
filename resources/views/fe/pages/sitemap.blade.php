<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>

<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <?php foreach ($sites as $p): ?>
        <url>
            <loc><?= $p['link'] ?></loc>
            <lastmod><?= gmdate('Y-m-d\TH:i:s\Z',strtotime($p['time'])) ?></lastmod>
            <changefreq>daily</changefreq>
            <priority>0.6</priority>
        </url>
    <?php endforeach; ?>
</urlset>