<?php
/**
 * Shared <head> partial. Expects $page_title to be set by the including page.
 */
if (!isset($page_title)) {
    $page_title = 'Forces Academy LMS';
}
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($page_title); ?> — Forces Academy LMS</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600;700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<?php if (!empty($use_bootstrap)): ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<?php endif; ?>
<link rel="stylesheet" href="css/style.css">
