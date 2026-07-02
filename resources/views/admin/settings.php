<div class="page-header settings-page-header">
    <h1 class="settings-title">
        Settings <i class="fas fa-cog"></i>
    </h1>
    <?php if(isset($_GET['saved'])): ?>
        <div class="alert-success settings-alert">
            Settings saved successfully!
        </div>
    <?php endif; ?>
</div>

<div class="settings-container table edit">
    
    <!-- Sidebar Tabs -->
    <div class="settings-sidebar">
        <ul class="settings-tabs-list">
            <?php 
            $tabIcons = [
                'General' => 'fa-sliders-h',
                'Account' => 'fa-user-circle',
                'Security' => 'fa-shield-alt',
                'System' => 'fa-server',
                'Email' => 'fa-envelope',
                'Appearance' => 'fa-paint-brush',
                'SEO' => 'fa-search',
                'Localization' => 'fa-globe',
                'Performance' => 'fa-tachometer-alt',
                'Uploads' => 'fa-cloud-upload-alt',
                'Developer' => 'fa-code',
                'Integrations' => 'fa-plug',
            ];
            
            $first = true;
            foreach ($settingsByGroup as $group => $settings): 
                $icon = $tabIcons[$group] ?? 'fa-cog';
            ?>
                <li>
                    <a href="#tab-<?= md5($group) ?>" class="settings-tab <?= $first ? 'active' : '' ?>" onclick="switchTab(event, 'tab-<?= md5($group) ?>')">
                        <i class="fas <?= $icon ?> fa-fw" style="margin-right: 8px; opacity: 0.8;"></i> <?= htmlspecialchars($group) ?>
                    </a>
                </li>
            <?php $first = false; endforeach; ?>
            
            <li>
                <a href="#tab-system" class="settings-tab" onclick="switchTab(event, 'tab-system')">
                    <i class="fas fa-server fa-fw" style="margin-right: 8px; opacity: 0.8;"></i> System
                </a>
            </li>
        </ul>
    </div>

    <!-- Content Area -->
    <div class="settings-content">
        <form method="POST" action="/admin/settings/save" enctype="multipart/form-data">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars(\App\Core\Session::token()) ?>">
            
            <?php 
            $first = true;
            foreach ($settingsByGroup as $group => $settings): ?>
                <div id="tab-<?= md5($group) ?>" class="tab-pane" style="display: <?= $first ? 'block' : 'none' ?>;">
                    <h2 class="settings-tab-title">
                        <?= htmlspecialchars($group) ?> Settings
                    </h2>
                    
                    <?php foreach ($settings as $s): ?>
                        <div class="form-group settings-form-group">
                            <label class="form__label settings-label">
                                <?= htmlspecialchars($s['setting_label']) ?>
                            </label>
                            
                            <?php if ($s['setting_type'] === 'text'): ?>
                                <input type="text" name="settings[<?= htmlspecialchars($s['setting_key']) ?>]" value="<?= htmlspecialchars((string)$s['setting_value']) ?>">
                            
                            <?php elseif ($s['setting_type'] === 'password'): ?>
                                <input type="password" name="settings[<?= htmlspecialchars($s['setting_key']) ?>]" value="<?= htmlspecialchars((string)$s['setting_value']) ?>" class="settings-password">
                            
                            <?php elseif ($s['setting_type'] === 'integer'): ?>
                                <input type="number" name="settings[<?= htmlspecialchars($s['setting_key']) ?>]" value="<?= htmlspecialchars((string)$s['setting_value']) ?>" class="settings-number">
                            
                            <?php elseif ($s['setting_type'] === 'color'): ?>
                                <input type="color" name="settings[<?= htmlspecialchars($s['setting_key']) ?>]" value="<?= htmlspecialchars((string)$s['setting_value']) ?>" class="settings-color">
                            
                            <?php elseif ($s['setting_type'] === 'boolean'): ?>
                                <div class="settings-switch-wrapper">
                                    <label class="switch">
                                        <input type="checkbox" name="settings[<?= htmlspecialchars($s['setting_key']) ?>]" value="1" <?= $s['setting_value'] == '1' ? 'checked' : '' ?>>
                                        <span class="slider"></span>
                                    </label>
                                    <span class="settings-switch-label">Enable</span>
                                </div>
                            
                            <?php elseif ($s['setting_type'] === 'file'): ?>
                                <div class="settings-file-upload">
                                    <?php if ($s['setting_value']): ?>
                                        <img src="<?= htmlspecialchars((string)$s['setting_value']) ?>" class="settings-file-preview" id="preview-<?= md5($s['setting_key']) ?>">
                                    <?php else: ?>
                                        <div class="settings-file-preview" id="preview-<?= md5($s['setting_key']) ?>" style="display:flex;align-items:center;justify-content:center;color:var(--text-muted);"><i class="fas fa-image"></i></div>
                                    <?php endif; ?>
                                    
                                    <div>
                                        <label class="settings-file-btn">
                                            <i class="fas fa-cloud-upload-alt" style="margin-right: 8px;"></i> Upload Image
                                            <input type="file" name="settings_file[<?= htmlspecialchars($s['setting_key']) ?>]" accept="image/png, image/jpeg, image/svg+xml, image/x-icon" 
                                                onchange="handleFileUploadPreview(this, '<?= md5($s['setting_key']) ?>')">
                                        </label>
                                        <div class="settings-file-name" id="name-<?= md5($s['setting_key']) ?>">No file chosen</div>
                                    </div>
                                </div>
                                <?php if ($s['setting_value']): ?>
                                    <input type="hidden" name="settings[<?= htmlspecialchars($s['setting_key']) ?>]" value="<?= htmlspecialchars((string)$s['setting_value']) ?>">
                                <?php endif; ?>
                            
                            <?php elseif ($s['setting_type'] === 'textarea'): ?>
                                <textarea name="settings[<?= htmlspecialchars($s['setting_key']) ?>]" rows="4"><?= htmlspecialchars((string)$s['setting_value']) ?></textarea>
                            
                            <?php elseif ($s['setting_type'] === 'select'): ?>
                                <select name="settings[<?= htmlspecialchars($s['setting_key']) ?>]">
                                    <?php 
                                    $options = explode(',', $s['setting_options'] ?? '');
                                    foreach ($options as $opt): 
                                        $opt = trim($opt);
                                    ?>
                                        <option value="<?= htmlspecialchars($opt) ?>" <?= $s['setting_value'] === $opt ? 'selected' : '' ?>>
                                            <?= htmlspecialchars(ucfirst($opt)) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php $first = false; endforeach; ?>
            
            <div id="tab-system" class="tab-pane" style="display: none;">
                <h2 class="settings-tab-title">
                    System Information
                </h2>
                <div class="settings-sysinfo-container">
                    <?php foreach ($systemInfo as $key => $val): ?>
                        <div class="settings-sysinfo-row">
                            <div class="settings-sysinfo-key">
                                <?= htmlspecialchars($key) ?>
                            </div>
                            <div class="settings-sysinfo-val">
                                <?= htmlspecialchars((string)$val) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="settings-submit-wrapper">
                <button type="submit" class="btn btn-success settings-submit-btn">
                    <i class="fas fa-save"></i> Save Settings
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.settings-page-header {
    justify-content: space-between;
}
.settings-title {
    display: flex;
    align-items: center;
    gap: 10px;
}
.settings-title i {
    color: var(--text-muted);
    font-size: 0.9em;
}
.settings-alert {
    margin: 0;
    padding: 10px 20px;
}
.settings-container {
    display: flex;
    gap: 30px;
    margin-top: 20px;
    align-items: flex-start;
    background: transparent !important;
    box-shadow: none !important;
    border: none !important;
    padding: 0 !important;
}
.settings-sidebar {
    flex: 0 0 250px;
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 6px -1px #0000000d;
}
.settings-tabs-list {
    list-style: none;
    padding: 0;
    margin: 0;
}
.settings-tab {
    display: block;
    padding: 14px 20px;
    text-decoration: none;
    color: var(--text-main);
    border-bottom: 1px solid var(--border-color);
    cursor: pointer;
    font-weight: 500;
    transition: background 0.2s;
}
.settings-tab:hover {
    background: #f8fafc;
}
.settings-tab.active {
    background: var(--primary-focus);
    color: var(--primary) !important;
    border-left: 4px solid var(--primary);
    padding-left: 16px !important;
}
.settings-content {
    flex: 1;
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 4px 6px -1px #0000000d;
}
.settings-tab-title {
    margin-top: 0;
    border-bottom: 1px solid var(--border-color);
    padding-bottom: 15px;
    margin-bottom: 25px;
    color: var(--text-main);
    font-size: 20px;
}
.settings-form-group {
    padding: 15px 0;
}
.settings-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: var(--text-main);
}
.settings-password {
    width: 100%;
    max-width: 800px;
    box-sizing: border-box;
    padding: 14px 16px;
    font-size: 15px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    background-color: #f8fafc;
    outline: none;
    transition: all 0.15s ease-in-out;
}
.settings-password:focus {
    background-color: #fff;
    border-color: var(--primary);
    box-shadow: 0 0 0 4px var(--primary-focus);
}
.settings-number {
    max-width: 250px;
}
.settings-color {
    height: 45px;
    width: 70px;
    padding: 0;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    cursor: pointer;
}
.settings-switch-wrapper {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-top: 8px;
}
.settings-switch-label {
    font-weight: 500;
    color: var(--text-muted);
}
.settings-file-upload {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 10px;
    margin-top: 5px;
}
.settings-file-upload input[type="file"] {
    display: none;
}
.settings-file-btn {
    display: inline-flex;
    align-items: center;
    padding: 10px 16px;
    background: #f8fafc;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    color: var(--text-main);
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 0.95em;
}
.settings-file-btn:hover {
    background: #f1f5f9;
    border-color: var(--primary);
    color: var(--primary);
}
html[data-theme="dark"] .settings-file-btn {
    background: #1e293b;
}
html[data-theme="dark"] .settings-file-btn:hover {
    background: #334155;
}
.settings-file-preview {
    width: 52px;
    height: 52px;
    border-radius: 8px;
    object-fit: contain;
    background: #f8fafc;
    border: 1px solid var(--border-color);
    padding: 6px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}
html[data-theme="dark"] .settings-file-preview {
    background: #1e293b;
}
.settings-file-name {
    font-size: 0.85em;
    color: var(--text-muted);
    margin-top: 6px;
}
.settings-sysinfo-container {
    background: var(--bg-main);
    border-radius: 8px;
    border: 1px solid var(--border-color);
    overflow: hidden;
}
.settings-sysinfo-row {
    display: flex;
    border-bottom: 1px solid var(--border-color);
    padding: 16px 20px;
    align-items: center;
    background: #f8fafc;
}
.settings-sysinfo-row:last-child {
    border-bottom: none;
}
.settings-sysinfo-key {
    flex: 0 0 40%;
    font-weight: 600;
    color: var(--text-main);
    font-size: 15px;
}
.settings-sysinfo-val {
    flex: 1;
    color: var(--text-muted);
    font-size: 15px;
}
.settings-submit-wrapper {
    margin-top: 30px;
    border-top: 1px solid var(--border-color);
    padding-top: 25px;
}
.settings-submit-btn {
    font-size: 16px;
}
.settings-submit-btn i {
    margin-right: 8px;
}
</style>

<script>
function handleFileUploadPreview(input, keyHash) {
    const file = input.files[0];
    document.getElementById('name-' + keyHash).textContent = file ? file.name : 'No file chosen'; 
    if (file) { 
        const reader = new FileReader();
        reader.onload = function(e) {
            const p = document.getElementById('preview-' + keyHash); 
            if (p && p.tagName === 'IMG') { p.src = e.target.result; }
            else if (p) { p.outerHTML = '<img src="' + e.target.result + '" class="settings-file-preview" id="preview-' + keyHash + '">'; }
        };
        reader.readAsDataURL(file);

        // Instant AJAX Upload
        const formData = new FormData();
        formData.append('favicon', file);
        const csrfInput = input.closest('form').querySelector('input[name="_csrf"]');
        if (csrfInput) {
            formData.append('_csrf', csrfInput.value);
        }
        
        const btn = input.closest('.settings-file-btn');
        const oldHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin" style="margin-right: 8px;"></i> Uploading...';
        
        fetch('/admin/settings/upload-favicon', {
            method: 'POST',
            body: formData
        }).then(res => res.json()).then(data => {
            btn.innerHTML = '<i class="fas fa-check" style="margin-right: 8px; color: #10b981;"></i> Saved!';
            setTimeout(() => btn.innerHTML = oldHtml, 2000);
        }).catch(err => {
            btn.innerHTML = '<i class="fas fa-times" style="margin-right: 8px; color: #ef4444;"></i> Failed';
            setTimeout(() => btn.innerHTML = oldHtml, 2000);
            console.error(err);
        });
    }
}

function switchTab(e, tabId) {
    if (e) e.preventDefault();
    
    document.querySelectorAll('.tab-pane').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.settings-tab').forEach(el => el.classList.remove('active'));
    
    document.getElementById(tabId).style.display = 'block';
    
    if (e && e.currentTarget) {
        e.currentTarget.classList.add('active');
    } else {
        const activeLink = document.querySelector(`.settings-tab[onclick*="${tabId}"]`);
        if (activeLink) activeLink.classList.add('active');
    }
    
    localStorage.setItem('activeSettingsTab', tabId);
}

document.addEventListener('DOMContentLoaded', () => {
    const savedTab = localStorage.getItem('activeSettingsTab');
    if (savedTab && document.getElementById(savedTab)) {
        switchTab(null, savedTab);
    }
});
</script>
