<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup - PHP Starter Kit</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        :root {
            --bg-color: #f8fafc;
            --surface-color: #ffffff;
            --text-color: #1e293b;
            --text-muted: #64748b;
            --primary-color: #3b82f6;
            --primary-hover: #2563eb;
            --border-color: #e2e8f0;
            --radius: 12px;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }

        .installer-container {
            background: var(--surface-color);
            width: 100%;
            max-width: 500px;
            padding: 40px;
            border-radius: var(--radius);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        }

        .installer-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .installer-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }

        .installer-header p {
            color: var(--text-muted);
            margin-top: 8px;
            font-size: 15px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 15px;
            box-sizing: border-box;
            transition: all 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .row {
            display: flex;
            gap: 16px;
        }

        .col {
            flex: 1;
        }

        .btn {
            display: block;
            width: 100%;
            padding: 14px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn:hover {
            background: var(--primary-hover);
        }

        .btn:disabled {
            background: var(--text-muted);
            cursor: not-allowed;
        }

        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            display: none;
        }

        .alert.error {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
            display: block;
        }
        
        .alert.success {
            background: #f0fdf4;
            color: #16a34a;
            border: 1px solid #bbf7d0;
            display: block;
        }
    </style>
</head>
<body>

    <div class="installer-container">
        <div class="installer-header">
            <h1>Initial Setup</h1>
            <p>Welcome! Please provide your database and admin details to get started.</p>
        </div>

        <div id="message" class="alert"></div>

        <form id="install-form">
            <h3 style="font-size: 16px; margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 8px;">Database Setup</h3>
            <div class="row">
                <div class="form-group col" style="flex: 2;">
                    <label>DB Host</label>
                    <input type="text" id="db_host" class="form-control" value="localhost" required>
                </div>
                <div class="form-group col" style="flex: 1;">
                    <label>DB Port</label>
                    <input type="text" id="db_port" class="form-control" value="3306" required>
                </div>
            </div>

            <div class="form-group">
                <label>Database Name</label>
                <input type="text" id="db_name" class="form-control" value="php-starter" required>
            </div>

            <div class="row">
                <div class="form-group col">
                    <label>Database User</label>
                    <input type="text" id="db_user" class="form-control" value="root" required>
                </div>
                <div class="form-group col">
                    <label>Database Password</label>
                    <input type="password" id="db_pass" class="form-control" autocomplete="new-password">
                </div>
            </div>

            <h3 style="font-size: 16px; margin-bottom: 15px; margin-top: 10px; border-bottom: 1px solid #eee; padding-bottom: 8px;">Admin Account</h3>
            
            <div class="form-group">
                <label>Admin Email</label>
                <input type="email" id="admin_email" class="form-control" placeholder="admin@example.com" required>
            </div>

            <div class="form-group">
                <label>Admin Password</label>
                <input type="password" id="admin_password" class="form-control" required autocomplete="new-password">
            </div>

            <button type="submit" id="install-btn" class="btn">
                <i class="fas fa-rocket"></i> Install & Setup
            </button>
        </form>
    </div>

    <script>
        document.getElementById('install-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const btn = document.getElementById('install-btn');
            const msg = document.getElementById('message');
            
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Installing...';
            msg.className = 'alert';
            
            const formData = new URLSearchParams();
            formData.append('db_host', document.getElementById('db_host').value);
            formData.append('db_port', document.getElementById('db_port').value);
            formData.append('db_name', document.getElementById('db_name').value);
            formData.append('db_user', document.getElementById('db_user').value);
            formData.append('db_pass', document.getElementById('db_pass').value);
            formData.append('admin_email', document.getElementById('admin_email').value);
            formData.append('admin_password', document.getElementById('admin_password').value);

            try {
                const response = await fetch('/', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: formData.toString()
                });
                
                const data = await response.json();
                
                if (data.success) {
                    msg.className = 'alert success';
                    msg.innerHTML = '<i class="fas fa-check-circle"></i> Installation complete! Redirecting...';
                    setTimeout(() => window.location.reload(), 2000);
                } else {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-rocket"></i> Install & Setup';
                    msg.className = 'alert error';
                    msg.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + data.error;
                }
            } catch (err) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-rocket"></i> Install & Setup';
                msg.className = 'alert error';
                msg.innerHTML = '<i class="fas fa-exclamation-circle"></i> Network error occurred.';
            }
        });
    </script>
</body>
</html>
