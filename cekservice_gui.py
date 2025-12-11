"""
Laravel FrankenPHP Service Manager - Flask Web GUI
Versi GUI berbasis Flask untuk manajemen service SystemD
"""

from flask import Flask, render_template_string, jsonify, request, session, redirect, url_for
import subprocess
import os
import secrets

app = Flask(__name__)
app.secret_key = secrets.token_hex(16)

# Konfigurasi
DEFAULT_USERNAME = "admin"
DEFAULT_PASSWORD = "sinara123"

def load_services_config(config_file="services.txt"):
    """Membaca konfigurasi service dari file eksternal."""
    services = {}
    script_dir = os.path.dirname(os.path.abspath(__file__))
    config_path = os.path.join(script_dir, config_file)
    
    if not os.path.exists(config_path):
        return {"staging": "laravel-frankenphp-staging", "production": "laravel-frankenphp-production"}
    
    try:
        with open(config_path, 'r') as f:
            for line in f:
                line = line.strip()
                if not line or line.startswith('#'):
                    continue
                if '=' in line:
                    key, value = line.split('=', 1)
                    services[key.strip()] = value.strip()
    except Exception:
        return {"staging": "laravel-frankenphp-staging", "production": "laravel-frankenphp-production"}
    
    return services if services else {"staging": "laravel-frankenphp-staging", "production": "laravel-frankenphp-production"}

def run_command(command):
    """Menjalankan perintah shell."""
    try:
        result = subprocess.run(command, shell=True, capture_output=True, text=True)
        return result.stdout, result.stderr, result.returncode
    except Exception as e:
        return "", str(e), 1

def check_service_status(service_name):
    """Memeriksa status layanan."""
    command = f"systemctl status {service_name}"
    stdout, stderr, returncode = run_command(command)
    
    if returncode == 0:
        for line in stdout.splitlines():
            if "Active:" in line:
                if "active (running)" in line:
                    return {"status": "running", "message": line.strip(), "class": "success"}
                elif "inactive" in line or "dead" in line:
                    return {"status": "stopped", "message": line.strip(), "class": "danger"}
                else:
                    return {"status": "unknown", "message": line.strip(), "class": "warning"}
    
    if "could not be found" in stderr:
        return {"status": "not_found", "message": f"Service {service_name} tidak ditemukan", "class": "secondary"}
    
    return {"status": "error", "message": stderr or "Unknown error", "class": "danger"}

def manage_service(service_name, action):
    """Mengelola layanan (start, stop, restart)."""
    command = f"sudo systemctl {action} {service_name}"
    stdout, stderr, returncode = run_command(command)
    
    if returncode == 0:
        return {"success": True, "message": f"Berhasil {action} layanan {service_name}"}
    return {"success": False, "message": f"Gagal {action} layanan {service_name}: {stderr}"}

# HTML Template dengan CSS modern
HTML_TEMPLATE = '''
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel FrankenPHP Manager</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --secondary: #6b7280;
            --bg: #0f172a;
            --card: #1e293b;
            --text: #f1f5f9;
            --text-muted: #94a3b8;
        }
        
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            padding: 2rem;
        }
        
        .container { max-width: 900px; margin: 0 auto; }
        
        .header {
            text-align: center;
            margin-bottom: 2rem;
            padding: 2rem;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(99, 102, 241, 0.3);
        }
        
        .header h1 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .header p { color: rgba(255,255,255,0.8); font-size: 0.95rem; }
        
        .card {
            background: var(--card);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border: 1px solid rgba(255,255,255,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.3);
        }
        
        .service-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .service-name {
            font-size: 1.1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .service-key {
            background: var(--primary);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            text-transform: uppercase;
            font-weight: 700;
        }
        
        .status-badge {
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }
        
        .status-badge.success { background: rgba(16, 185, 129, 0.2); color: var(--success); }
        .status-badge.danger { background: rgba(239, 68, 68, 0.2); color: var(--danger); }
        .status-badge.warning { background: rgba(245, 158, 11, 0.2); color: var(--warning); }
        .status-badge.secondary { background: rgba(107, 114, 128, 0.2); color: var(--secondary); }
        
        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: currentColor;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        .service-info {
            color: var(--text-muted);
            font-size: 0.85rem;
            margin-bottom: 1rem;
            font-family: monospace;
            background: rgba(0,0,0,0.2);
            padding: 0.5rem 1rem;
            border-radius: 6px;
        }
        
        .actions { display: flex; gap: 0.5rem; flex-wrap: wrap; }
        
        .btn {
            padding: 0.6rem 1.2rem;
            border: none;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }
        
        .btn:disabled { opacity: 0.5; cursor: not-allowed; }
        
        .btn-success { background: var(--success); color: white; }
        .btn-success:hover:not(:disabled) { background: #059669; }
        
        .btn-danger { background: var(--danger); color: white; }
        .btn-danger:hover:not(:disabled) { background: #dc2626; }
        
        .btn-warning { background: var(--warning); color: white; }
        .btn-warning:hover:not(:disabled) { background: #d97706; }
        
        .btn-secondary { background: var(--secondary); color: white; }
        .btn-secondary:hover:not(:disabled) { background: #4b5563; }
        
        .toast {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            padding: 1rem 1.5rem;
            border-radius: 10px;
            color: white;
            font-weight: 500;
            transform: translateX(150%);
            transition: transform 0.3s ease;
            z-index: 1000;
            max-width: 350px;
        }
        
        .toast.show { transform: translateX(0); }
        .toast.success { background: var(--success); }
        .toast.error { background: var(--danger); }
        
        .refresh-all {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 1rem;
        }
        
        .loading { opacity: 0.6; pointer-events: none; }
        
        .spinner {
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            display: none;
        }
        
        .loading .spinner { display: inline-block; }
        .loading .btn-text { display: none; }
        
        @keyframes spin { to { transform: rotate(360deg); } }
        
        .login-container {
            max-width: 400px;
            margin: 4rem auto;
        }
        
        .login-card {
            background: var(--card);
            border-radius: 16px;
            padding: 2rem;
            border: 1px solid rgba(255,255,255,0.1);
        }
        
        .login-card h2 {
            text-align: center;
            margin-bottom: 1.5rem;
            color: var(--primary);
        }
        
        .form-group { margin-bottom: 1rem; }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-muted);
            font-size: 0.9rem;
        }
        
        .form-group input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px;
            background: var(--bg);
            color: var(--text);
            font-size: 1rem;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
        }
        
        .btn-login {
            width: 100%;
            padding: 0.85rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 1rem;
        }
        
        .btn-login:hover { background: var(--primary-dark); }
        
        .error-msg {
            background: rgba(239, 68, 68, 0.2);
            color: var(--danger);
            padding: 0.75rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
        }
        
        .logout-btn {
            position: fixed;
            top: 1rem;
            right: 1rem;
            background: rgba(255,255,255,0.1);
            color: var(--text-muted);
            padding: 0.5rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.85rem;
            transition: all 0.2s;
        }
        
        .logout-btn:hover { background: rgba(255,255,255,0.2); color: var(--text); }
    </style>
</head>
<body>
    {% if not authenticated %}
    <div class="login-container">
        <div class="login-card">
            <h2>🔐 Login</h2>
            {% if error %}
            <div class="error-msg">{{ error }}</div>
            {% endif %}
            <form method="POST" action="/login">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" required autofocus>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" class="btn-login">Masuk</button>
            </form>
        </div>
    </div>
    {% else %}
    <a href="/logout" class="logout-btn">🚪 Logout</a>
    <div class="container">
        <div class="header">
            <h1>⚡ Laravel FrankenPHP Manager</h1>
            <p>Manajemen Service SystemD via Web</p>
        </div>
        
        <div class="refresh-all">
            <button class="btn btn-secondary" onclick="refreshAll()">
                <span class="btn-text">🔄 Refresh Semua</span>
                <span class="spinner"></span>
            </button>
        </div>
        
        <div id="services-container">
            {% for key, service in services.items() %}
            <div class="card" id="card-{{ key }}">
                <div class="service-header">
                    <div class="service-name">
                        <span class="service-key">{{ key }}</span>
                        {{ service }}
                    </div>
                    <div class="status-badge secondary" id="status-{{ key }}">
                        <span class="status-dot"></span>
                        <span>Loading...</span>
                    </div>
                </div>
                <div class="service-info" id="info-{{ key }}">Memuat status...</div>
                <div class="actions">
                    <button class="btn btn-success" onclick="manageService('{{ key }}', '{{ service }}', 'start')">
                        <span class="btn-text">▶ Start</span>
                        <span class="spinner"></span>
                    </button>
                    <button class="btn btn-danger" onclick="manageService('{{ key }}', '{{ service }}', 'stop')">
                        <span class="btn-text">⏹ Stop</span>
                        <span class="spinner"></span>
                    </button>
                    <button class="btn btn-warning" onclick="manageService('{{ key }}', '{{ service }}', 'restart')">
                        <span class="btn-text">🔄 Restart</span>
                        <span class="spinner"></span>
                    </button>
                    <button class="btn btn-secondary" onclick="checkStatus('{{ key }}', '{{ service }}')">
                        <span class="btn-text">📊 Status</span>
                        <span class="spinner"></span>
                    </button>
                </div>
            </div>
            {% endfor %}
        </div>
    </div>
    
    <div class="toast" id="toast"></div>
    
    <script>
        const services = {{ services_json|safe }};
        
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.className = 'toast ' + type + ' show';
            setTimeout(() => toast.classList.remove('show'), 3000);
        }
        
        async function checkStatus(key, service) {
            const badge = document.getElementById('status-' + key);
            const info = document.getElementById('info-' + key);
            
            try {
                const res = await fetch('/api/status/' + encodeURIComponent(service));
                const data = await res.json();
                
                badge.className = 'status-badge ' + data.class;
                badge.innerHTML = '<span class="status-dot"></span><span>' + 
                    (data.status === 'running' ? 'Running' : 
                     data.status === 'stopped' ? 'Stopped' : 
                     data.status === 'not_found' ? 'Not Found' : 'Unknown') + '</span>';
                info.textContent = data.message;
            } catch (e) {
                badge.className = 'status-badge danger';
                badge.innerHTML = '<span class="status-dot"></span><span>Error</span>';
                info.textContent = 'Gagal mengambil status';
            }
        }
        
        async function manageService(key, service, action) {
            const card = document.getElementById('card-' + key);
            card.classList.add('loading');
            
            try {
                const res = await fetch('/api/manage', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ service, action })
                });
                const data = await res.json();
                
                showToast(data.message, data.success ? 'success' : 'error');
                
                setTimeout(() => checkStatus(key, service), 500);
            } catch (e) {
                showToast('Gagal menjalankan aksi', 'error');
            } finally {
                card.classList.remove('loading');
            }
        }
        
        function refreshAll() {
            for (const [key, service] of Object.entries(services)) {
                checkStatus(key, service);
            }
        }
        
        // Load status saat halaman dimuat
        document.addEventListener('DOMContentLoaded', refreshAll);
    </script>
    {% endif %}
</body>
</html>
'''


# Routes
@app.route('/')
def index():
    if not session.get('authenticated'):
        return render_template_string(HTML_TEMPLATE, authenticated=False, error=None)
    
    services = load_services_config()
    import json
    return render_template_string(
        HTML_TEMPLATE, 
        authenticated=True, 
        services=services,
        services_json=json.dumps(services)
    )

@app.route('/login', methods=['POST'])
def login():
    username = request.form.get('username', '')
    password = request.form.get('password', '')
    
    if username == DEFAULT_USERNAME and password == DEFAULT_PASSWORD:
        session['authenticated'] = True
        return redirect(url_for('index'))
    
    return render_template_string(HTML_TEMPLATE, authenticated=False, error="Username atau password salah!")

@app.route('/logout')
def logout():
    session.clear()
    return redirect(url_for('index'))

@app.route('/api/status/<path:service>')
def api_status(service):
    if not session.get('authenticated'):
        return jsonify({"error": "Unauthorized"}), 401
    return jsonify(check_service_status(service))

@app.route('/api/manage', methods=['POST'])
def api_manage():
    if not session.get('authenticated'):
        return jsonify({"error": "Unauthorized"}), 401
    
    data = request.get_json()
    service = data.get('service')
    action = data.get('action')
    
    if action not in ['start', 'stop', 'restart']:
        return jsonify({"success": False, "message": "Aksi tidak valid"})
    
    return jsonify(manage_service(service, action))

if __name__ == '__main__':
    import argparse
    
    parser = argparse.ArgumentParser(description='Laravel FrankenPHP Manager - Web GUI')
    parser.add_argument('--host', default='0.0.0.0', help='Host address (default: 0.0.0.0)')
    parser.add_argument('--port', type=int, default=5000, help='Port number (default: 5000)')
    parser.add_argument('--debug', action='store_true', help='Enable debug mode')
    
    args = parser.parse_args()
    
    print(f"\n{'='*50}")
    print(f"  Laravel FrankenPHP Manager - Web GUI")
    print(f"{'='*50}")
    print(f"  🌐 Akses di: http://{args.host}:{args.port}")
    print(f"  👤 Username: {DEFAULT_USERNAME}")
    print(f"  🔑 Password: {DEFAULT_PASSWORD}")
    print(f"{'='*50}\n")
    
    app.run(host=args.host, port=args.port, debug=args.debug)
