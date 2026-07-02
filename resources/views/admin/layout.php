<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Nix-Cloud</title>
    <link rel="stylesheet" href="/css/admin.css">
    <link rel="icon" href="/assets/settings.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <main>
        <div class="admin">
            <div class="nav-side-menu">
                <span>Admin Panel</span>
                <ul>
                    <li>
                        <a href="/admin"><i class="fas fa-tachometer-alt" style="margin-right: 8px;"></i> Dashboard</a>
                    </li>
                    <li>
                        <a href="/admin/settings"><i class="fas fa-cog" style="margin-right: 8px;"></i> Settings</a>
                    </li>
                    <li>
                        <a href="/admin/templates"><i class="fas fa-code" style="margin-right: 8px;"></i> Templates</a>
                    </li>
                    <?php if(isset($db_tables)): ?>
                        <?php foreach($db_tables as $t): ?>
                            <?php 
                                if($t->table_name == 'settings') continue;
                                
                                $icon = 'fa-table';
                                if ($t->table_name === 'users') $icon = 'fa-users';
                                elseif ($t->table_name === 'password_resets') $icon = 'fa-key';
                            ?>
                            <li>
                                <a href="/admin/<?= htmlspecialchars((string)$t->table_name) ?>">
                                    <i class="fas <?= $icon ?>" style="margin-right: 8px;"></i>
                                    <?= htmlspecialchars(ucfirst(str_replace('_', ' ', (string)$t->table_name))) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <li><a href="/" class="back-link">&larr; Back to Site</a></li>
                </ul>
            </div>
            <div class="flex-column">
                <?php require_once __DIR__ . '/' . $view . '.php'; ?>
            </div>
        </div>
    </main>
</body>
</html>
