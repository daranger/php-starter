<div style="margin-top: 40px;">
    <div style="text-align: center; margin-bottom: 3rem;">
        <h1 style="font-size: 2.5rem; margin-bottom: 1rem;">UI Components</h1>
        <p style="font-size: 1.2rem; color: var(--color-text-muted);">Витрина доступных UI компонентов для PHP Starter Kit.</p>
    </div>

    <!-- Buttons -->
    <section class="ui-section">
        <h2>Buttons</h2>
        <div class="ui-showcase">
            <button class="btn">Default</button>
            <button class="btn btn--primary">Primary</button>
            <button class="btn btn--danger">Danger</button>
            <button class="btn btn--outline">Outline</button>
        </div>
    </section>

    <!-- Inputs -->
    <section class="ui-section">
        <h2>Inputs</h2>
        <div class="ui-showcase" style="display: flex; flex-direction: column; gap: 1rem; max-width: 400px;">
            <input type="text" class="form__input" placeholder="Text Input">
            <input type="email" class="form__input" placeholder="Email Input">
            <input type="password" class="form__input" placeholder="Password Input">
        </div>
    </section>

    <!-- Forms -->
    <section class="ui-section">
        <h2>Forms</h2>
        <div class="ui-showcase" style="max-width: 400px;">
            <div class="form__group">
                <label class="form__label">Example Label</label>
                <input type="text" class="form__input" placeholder="Enter something">
                <div class="form-error">Error message example:</div>
            </div>
        </div>
    </section>

    <!-- Cards -->
    <section class="ui-section">
        <h2>Cards</h2>
        <div class="ui-showcase" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; width: 100%;">
            <div class="card">
                <h3>Simple Card</h3>
                <p style="color: var(--color-text-muted); margin-top: 0.5rem;">Basic content inside a card.</p>
            </div>
            <div class="card" style="display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center;">
                <h3>Centered Card</h3>
                <p style="color: var(--color-text-muted); margin-top: 0.5rem; margin-bottom: 1rem;">Content can be organized flexibly.</p>
                <button class="btn btn--primary">Action</button>
            </div>
        </div>
    </section>

    <!-- Alerts -->
    <section class="ui-section">
        <h2>Alerts</h2>
        <div class="ui-showcase" style="display: flex; flex-direction: column; gap: 1rem; width: 100%;">
            <div class="alert alert--info"><strong>Info:</strong> This is an informational alert.</div>
            <div class="alert alert--success"><strong>Success:</strong> Your action was completed successfully!</div>
            <div class="alert alert--warning"><strong>Warning:</strong> Be careful with this action.</div>
            <div class="alert alert--error"><strong>Error:</strong> Something went wrong!</div>
        </div>
    </section>

    <!-- Badges -->
    <section class="ui-section">
        <h2>Badges</h2>
        <div class="ui-showcase">
            <span class="badge badge--default">Default</span>
            <span class="badge badge--primary">Primary</span>
            <span class="badge badge--success">Success</span>
            <span class="badge badge--warning">Warning</span>
            <span class="badge badge--danger">Danger</span>
        </div>
    </section>

    <!-- Tables -->
    <section class="ui-section">
        <h2>Tables</h2>
        <div class="ui-showcase" style="width: 100%;">
            <div class="table-responsive" style="width: 100%;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Role</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>John Doe</td>
                            <td><span class="badge badge--success">Active</span></td>
                            <td>Admin</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>Jane Smith</td>
                            <td><span class="badge badge--warning">Pending</span></td>
                            <td>User</td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>Bob Johnson</td>
                            <td><span class="badge badge--danger">Banned</span></td>
                            <td>User</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Modals -->
    <section class="ui-section">
        <h2>Modals</h2>
        <div class="ui-showcase">
            <button class="btn btn--primary" data-modal-open="example-modal">Open Example Modal</button>
        </div>
    </section>

    <!-- Dropdowns -->
    <section class="ui-section">
        <h2>Dropdowns</h2>
        <div class="ui-showcase" style="min-height: 200px; align-items: flex-start;">
            <div class="dropdown">
                <button class="btn btn--outline js-dropdown-toggle">Dropdown Menu ▼</button>
                <div class="dropdown-menu">
                    <a href="#" class="dropdown-item">Profile</a>
                    <a href="#" class="dropdown-item">Settings</a>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item" style="color: var(--color-error);">Logout</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Tabs -->
    <section class="ui-section">
        <h2>Tabs</h2>
        <div class="ui-showcase" style="width: 100%;">
            <div class="tabs" style="width: 100%;">
                <div class="tabs-header">
                    <button class="tab-btn active" data-tab="tab1">Account</button>
                    <button class="tab-btn" data-tab="tab2">Security</button>
                    <button class="tab-btn" data-tab="tab3">Notifications</button>
                </div>
                <div class="tabs-content">
                    <div class="tab-pane active" id="tab1">Account settings content goes here.</div>
                    <div class="tab-pane" id="tab2">Security settings content goes here.</div>
                    <div class="tab-pane" id="tab3">Notification preferences go here.</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Tooltips -->
    <section class="ui-section">
        <h2>Tooltips</h2>
        <div class="ui-showcase">
            <span class="tooltip-trigger" data-tooltip="This is a tooltip!">Hover over me</span>
            <button class="btn tooltip-trigger" data-tooltip="Action tooltip">Hover Button</button>
        </div>
    </section>

    <!-- Pagination -->
    <section class="ui-section">
        <h2>Pagination</h2>
        <div class="ui-showcase">
            <ul class="pagination">
                <li class="page-item disabled"><a href="#" class="page-link">Prev</a></li>
                <li class="page-item active"><a href="#" class="page-link">1</a></li>
                <li class="page-item"><a href="#" class="page-link">2</a></li>
                <li class="page-item"><a href="#" class="page-link">3</a></li>
                <li class="page-item"><a href="#" class="page-link">Next</a></li>
            </ul>
        </div>
    </section>

    <!-- Toasts -->
    <section class="ui-section">
        <h2>Toasts</h2>
        <div class="ui-showcase">
            <button class="btn btn--outline js-show-toast" data-type="success" data-message="Successfully saved!">Show Success Toast</button>
            <button class="btn btn--outline js-show-toast" data-type="error" data-message="An error occurred!">Show Error Toast</button>
        </div>
    </section>

</div>

<!-- Example Modal -->
<div class="modal" id="example-modal">
    <div class="modal-header">
        <h2>Example Modal</h2>
        <button class="modal-close" data-modal-close>&times;</button>
    </div>
    <div class="modal-body">
        <p>This is the content of the modal. You can put forms, messages, or anything else here.</p>
        <div style="margin-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
            <button class="btn" data-modal-close>Cancel</button>
            <button class="btn btn--primary" data-modal-close>Confirm</button>
        </div>
    </div>
</div>

<style>
    .ui-section {
        margin-bottom: 3rem;
    }
    
    .ui-section h2 {
        font-size: 1.5rem;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid var(--color-border);
    }
    
    .ui-showcase {
        padding: 2rem;
        border: 1px solid var(--color-border);
        border-radius: var(--radius-lg);
        display: flex;
        flex-wrap: wrap;
        gap: 1.5rem;
        align-items: center;
        background: var(--color-surface);
        box-shadow: var(--shadow-sm);
    }
</style>


