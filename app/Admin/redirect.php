<?php
use App\Core\Db;
header("X-Robots-Tag: noindex, nofollow");
if (admin()) {
    $page_id = (int)($_GET['id'] ?? 0);
    if ($page_id > 0) {
        $page = Db::query("SELECT url FROM pages WHERE id = $page_id")->fetch_object();
        if ($page && !empty($page->url)) {
            header("Location: " . $page->url);
            exit;
        }
    }
}
die('page not found');
