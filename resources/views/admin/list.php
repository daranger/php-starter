<div class="page-header">
    <h1><?= htmlspecialchars($h1) ?></h1>
</div>

<?php 
// Проверяем наличие записей (DbResult можно посчитать)
$hasRows = false;
if (isset($rows)) {
    if (isset($rows->num_rows)) {
        $hasRows = $rows->num_rows > 0;
    } else {
        $hasRows = true; // Fallback, если num_rows не определен
    }
}
?>

<?php if($hasRows): ?>
    <div class="table">
        <table>
            <thead>
                <tr>
                    <?php 
                    $colNames = [];
                    foreach($columns as $col): 
                        if (in_array($col['Field'], ['password', 'password_hash'], true)) continue;
                        $colNames[] = $col['Field'];
                        $isActive = ($current_sort === $col['Field']);
                    ?>
                        <th class="<?= $isActive ? 'sort-active' : '' ?>">
                            <a href="?sort=<?= urlencode($col['Field']) ?>&order=<?= ($isActive && $current_order === 'asc') ? 'desc' : 'asc' ?>" class="sort-link">
                                <?= htmlspecialchars($col['Field']) ?>
                                <?php if($isActive): ?>
                                    <span><?= $current_order === 'asc' ? '&uarr;' : '&darr;' ?></span>
                                <?php endif; ?>
                            </a>
                        </th>
                    <?php endforeach; ?>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $rows->fetch_object()): ?>
                    <tr>
                        <?php foreach($colNames as $colName): ?>
                            <td class="truncate-cell"><?= htmlspecialchars(mb_substr($row->{$colName} ?? '', 0, 50)) ?></td>
                        <?php endforeach; ?>
                        <td>
                            <a href="/admin/<?= urlencode($h1) ?>/edit/<?= $row->id ?? '' ?>" class="btn mini btn-success">Edit</a>
                            <a href="/admin/<?= urlencode($h1) ?>/delete/<?= $row->id ?? '' ?>" class="btn mini btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    
    <?php if($pages > 1): ?>
        <div class="pagination">
            <?php for($i=1; $i<=$pages; $i++): ?>
                <a href="?page=<?= $i ?>&sort=<?= urlencode($current_sort) ?>&order=<?= urlencode($current_order) ?>" class="<?= ((isset($_GET['page']) && $_GET['page'] == $i) || (!isset($_GET['page']) && $i==1)) ? 'active' : '' ?>">
                    <span><?= $i ?></span>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
<?php else: ?>
    <div class="table">
        <p>No records found.</p>
    </div>
<?php endif; ?>
