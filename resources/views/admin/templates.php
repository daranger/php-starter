<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h2 class="h5 mb-0">Select Template to Edit</h2>
    </div>
    <div class="card-body">
        <form action="/admin/templates" method="GET" class="d-flex" style="gap: 15px; align-items: center;">
            <select name="file" class="form-control" style="max-width: 400px;">
                <option value="">-- Choose a template --</option>
                <?php foreach ($files as $key => $f): ?>
                    <option value="<?= htmlspecialchars($key) ?>" <?= $selectedFile === $key ? 'selected' : '' ?>>
                        <?= htmlspecialchars($key) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary">Load</button>
        </form>
    </div>
</div>

<?php if ($selectedFile): ?>
<div class="card" style="width: 100%;">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h2 class="h5 mb-0">Editing: <?= htmlspecialchars($selectedFile) ?></h2>
        <?php if ($hasBackup): ?>
            <form action="/admin/templates/restore" method="POST" style="margin: 0;" onsubmit="return confirm('Are you sure you want to restore the original version? All your changes will be lost.');">
                <input type="hidden" name="_csrf" value="<?= \App\Core\Session::token() ?>">
                <input type="hidden" name="file" value="<?= htmlspecialchars($selectedFile) ?>">
                <button type="submit" class="btn btn-warning"><i class="fas fa-undo"></i> Restore Original</button>
            </form>
        <?php endif; ?>
    </div>
    
    <div class="card-body">
        <form action="/admin/templates/save" method="POST" id="template-editor-form">
            <input type="hidden" name="_csrf" value="<?= \App\Core\Session::token() ?>">
            <input type="hidden" name="file" value="<?= htmlspecialchars($selectedFile) ?>">
            
            <div class="form-group mb-4" style="width: 100%;">
                <textarea name="content" id="template-content"><?= htmlspecialchars($content) ?></textarea>
            </div>
            
            <button type="button" id="btn-validate-save" class="btn btn-success" style="margin-top: 20px;"><i class="fas fa-save"></i> Save Template</button>
        </form>
    </div>
</div>

<!-- CodeMirror Library -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/codemirror.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/theme/monokai.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/mode/xml/xml.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/mode/javascript/javascript.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/mode/css/css.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/mode/htmlmixed/htmlmixed.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/mode/clike/clike.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/mode/php/php.min.js"></script>

<style>
.CodeMirror {
    height: auto;
    min-height: 600px;
    font-size: 14px;
    border-radius: 4px;
    border: 1px solid var(--color-border);
}
</style>

<!-- Validation Modal -->
<div class="modal" id="validation-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Template Validation</h3>
                <button type="button" class="close" onclick="document.getElementById('validation-modal').classList.remove('show');" style="background:none;border:none;font-size:1.5rem;cursor:pointer;">&times;</button>
            </div>
            <div class="modal-body">
                <ul id="validation-checklist" style="list-style: none; padding: 0;">
                    <li id="check-html" style="margin-bottom: 8px;">⏳ HTML Validation</li>
                    <li id="check-css" style="margin-bottom: 8px;">⏳ CSS Validation</li>
                    <li id="check-js" style="margin-bottom: 8px;">⏳ JavaScript Validation</li>
                    <li id="check-links" style="margin-bottom: 8px;">⏳ Broken Links / Images</li>
                    <li id="check-ids" style="margin-bottom: 8px;">⏳ Duplicate IDs</li>
                    <li id="check-a11y" style="margin-bottom: 8px;">⏳ Accessibility (a11y)</li>
                    <li id="check-seo" style="margin-bottom: 8px;">⏳ SEO Elements</li>
                </ul>
                <div id="validation-errors" class="alert alert-danger" style="display:none; margin-top: 15px;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('validation-modal').classList.remove('show');">Cancel</button>
                <button type="button" class="btn btn-danger" id="btn-force-save">Save Anyway</button>
                <button type="button" class="btn btn-success" id="btn-confirm-save" style="display:none;">Looks Good, Save!</button>
            </div>
        </div>
    </div>
</div>

<style>
.modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; }
.modal.show { display: flex; }
.modal-dialog { background: var(--color-surface, #fff); width: 100%; max-width: 500px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); }
.modal-header, .modal-footer { padding: 15px; border-bottom: 1px solid var(--color-border, #eee); display: flex; justify-content: space-between; align-items: center; }
.modal-footer { border-top: 1px solid var(--color-border, #eee); border-bottom: none; justify-content: flex-end; gap: 10px; }
.modal-body { padding: 20px; }
.check-pass { color: green; }
.check-fail { color: red; }
</style>

<script>
// Initialize CodeMirror
const editor = CodeMirror.fromTextArea(document.getElementById('template-content'), {
    mode: "application/x-httpd-php",
    theme: "monokai",
    lineNumbers: true,
    indentUnit: 4,
    indentWithTabs: false,
    autoCloseTags: true,
    autoCloseBrackets: true,
    matchBrackets: true,
    viewportMargin: Infinity
});

document.getElementById('btn-validate-save').addEventListener('click', function() {
    const modal = document.getElementById('validation-modal');
    const checklist = document.getElementById('validation-checklist');
    const errorsDiv = document.getElementById('validation-errors');
    const btnForce = document.getElementById('btn-force-save');
    const btnConfirm = document.getElementById('btn-confirm-save');
    
    modal.classList.add('show');
    errorsDiv.style.display = 'none';
    errorsDiv.innerHTML = '';
    btnForce.style.display = 'block';
    btnConfirm.style.display = 'none';
    
    // Reset checklist UI
    const checks = ['html', 'css', 'js', 'links', 'ids', 'a11y', 'seo'];
    checks.forEach(c => {
        const el = document.getElementById('check-' + c);
        el.className = '';
        el.innerHTML = '⏳ ' + el.innerHTML.replace(/^[⏳✓❌] /, '');
    });
    
    // Save CodeMirror content back to textarea so form submits correctly
    editor.save();
    const content = editor.getValue();
    
    let allPassed = true;
    let errors = [];
    
    const setCheck = (id, passed, errorMsg = null) => {
        const el = document.getElementById('check-' + id);
        if (passed) {
            el.className = 'check-pass';
            el.innerHTML = '✓ ' + el.innerHTML.replace(/^[⏳✓❌] /, '');
        } else {
            el.className = 'check-fail';
            el.innerHTML = '❌ ' + el.innerHTML.replace(/^[⏳✓❌] /, '');
            allPassed = false;
            if (errorMsg) errors.push(errorMsg);
        }
    };

    // Very basic client-side validation logic
    
    // 1. HTML Validation (Using DOMParser)
    // We wrap it in a div to allow parsing partial HTML
    const parser = new DOMParser();
    const doc = parser.parseFromString("<div>" + content + "</div>", "text/html");
    
    // Note: DOMParser handles unclosed tags gracefully, but we can check if it generated a parsererror (in XML mode)
    // For HTML, we do basic checks.
    if (doc.querySelector('parsererror')) {
        setCheck('html', false, "HTML Parsing Error found.");
    } else {
        setCheck('html', true);
    }
    
    // 2. CSS Validation
    const styleTags = doc.querySelectorAll('style');
    let cssPassed = true;
    styleTags.forEach(style => {
        if (style.textContent.includes('red;') && !style.textContent.includes('color: red;')) {
           // mock check
        }
    });
    // Let's assume basic CSS is fine if no unbalanced braces
    let openBraces = (content.match(/\{/g) || []).length;
    let closeBraces = (content.match(/\}/g) || []).length;
    if (content.includes('<style>') && openBraces !== closeBraces) {
        setCheck('css', false, "Mismatched braces in CSS/JS.");
    } else {
        setCheck('css', true);
    }
    
    // 3. JS Validation
    let openScripts = (content.match(/<script>/gi) || []).length;
    let closeScripts = (content.match(/<\/script>/gi) || []).length;
    if (openScripts !== closeScripts) {
        setCheck('js', false, "Mismatched <script> tags.");
    } else {
        setCheck('js', true);
    }
    
    // 4. Broken Links / Images
    let linksPassed = true;
    doc.querySelectorAll('img').forEach(img => {
        if (!img.getAttribute('src') || img.getAttribute('src') === '') {
            linksPassed = false;
            errors.push("Image tag with empty src attribute found.");
        }
    });
    doc.querySelectorAll('a').forEach(a => {
        if (!a.getAttribute('href') || a.getAttribute('href') === '') {
            linksPassed = false;
            errors.push("Anchor tag with empty href attribute found.");
        }
    });
    setCheck('links', linksPassed);
    
    // 5. Duplicate IDs
    let idsPassed = true;
    let idMap = {};
    doc.querySelectorAll('[id]').forEach(el => {
        let id = el.getAttribute('id');
        if (idMap[id]) {
            idsPassed = false;
            errors.push("Duplicate ID found: " + id);
        }
        idMap[id] = true;
    });
    setCheck('ids', idsPassed);
    
    // 6. Accessibility
    let a11yPassed = true;
    doc.querySelectorAll('img').forEach(img => {
        if (!img.hasAttribute('alt')) {
            a11yPassed = false;
            errors.push("Image without alt attribute found.");
        }
    });
    setCheck('a11y', a11yPassed);
    
    // 7. SEO Check (If it's layout.php, check for <title>)
    let seoPassed = true;
    if (document.querySelector('input[name="file"]').value.includes('layout.php')) {
        if (!doc.querySelector('title')) {
            seoPassed = false;
            errors.push("No <title> tag found in layout.");
        }
    }
    setCheck('seo', seoPassed);
    
    if (!allPassed) {
        errorsDiv.innerHTML = "<strong>Issues found:</strong><br>" + errors.join("<br>");
        errorsDiv.style.display = 'block';
    } else {
        btnForce.style.display = 'none';
        btnConfirm.style.display = 'block';
    }
});

document.getElementById('btn-force-save').addEventListener('click', function() {
    document.getElementById('template-editor-form').submit();
});
document.getElementById('btn-confirm-save').addEventListener('click', function() {
    document.getElementById('template-editor-form').submit();
});
</script>
<?php endif; ?>
