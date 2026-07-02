<div class="page-header">
    <h1>Edit <?= htmlspecialchars($table_name) ?> (#<?= htmlspecialchars($id) ?>)</h1>
    <a href="/admin/<?= urlencode($table_name) ?>" class="btn btn-success" style="margin-left: auto;">&larr; Back to List</a>
</div>

<?= $ok ?? '' ?>

<?php if($row): ?>
    <div class="table edit">
        <form method="POST" action="">
            <input type="hidden" name="_csrf" value="<?= \App\Core\Session::token() ?>">
            <?php 
            $columns->data_seek(0);
            while($col = $columns->fetch_assoc()): 
                $field = $col['Field'];
                $val = $row->$field ?? '';
                if(in_array($field, ['id', 'password', 'password_hash'], true)) continue;
            ?>
                <div class="form-group">
                    <label class="dashboard"><b><?= htmlspecialchars($field) ?></b></label>
                    <div style="margin-top: 8px;">
                    <?php if($col['Type'] === 'text' || strpos($col['Type'], 'varchar(255)') !== false && strlen($val) > 100): ?>
                        <textarea name="<?= htmlspecialchars($field) ?>" class="edit-textarea"><?= htmlspecialchars($val) ?></textarea>
                    <?php elseif(strpos($col['Type'], 'enum') !== false): 
                        preg_match("/^enum\(\'(.*)\'\)$/", $col['Type'], $matches);
                        $options = explode("','", $matches[1] ?? '');
                    ?>
                        <select name="<?= htmlspecialchars($field) ?>">
                            <?php foreach($options as $opt): ?>
                                <option value="<?= htmlspecialchars($opt) ?>" <?= $val === $opt ? 'selected' : '' ?>><?= htmlspecialchars($opt) ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <input type="text" name="<?= htmlspecialchars($field) ?>" value="<?= htmlspecialchars($val) ?>">
                    <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
            
            <div class="form-actions" style="margin-top: 24px;">
                <button type="submit" name="submit" value="1" class="btn btn-success">Save Changes</button>
            </div>
        </form>
    </div>
<?php else: ?>
    <div class="table">
        <p>Record not found.</p>
    </div>
<?php endif; ?>
