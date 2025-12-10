# Technology Stack

## Language & Runtime

- Python 3.8+ (recommended: 3.10+ for full Streamlit compatibility)
- Requires Linux OS with SystemD

## Core Dependencies

### CLI Libraries

- `colorama` (>=0.4.6) - Colored terminal output
- `tabulate` (>=0.9.0) - ASCII table formatting

### Web Interface

- `streamlit` (>=1.31.0) - Web application framework
- `pillow` (>=10.0.0) - CAPTCHA image generation

### System Utilities

- `psutil` (>=5.9.0) - Process and port monitoring
- `subprocess` (built-in) - Shell command execution
- `argparse` (built-in) - CLI argument parsing

## Common Commands

### Installation

```bash
pip install -r requirements.txt
```

### Running CLI

```bash
# Check all services status
python3 cekservice.py

# Check specific service
python3 cekservice.py --service staging

# Restart production service
python3 cekservice.py --service production --action restart
```

### Running Web Interface

```bash
streamlit run cekservice_streamlit.py

# Custom port
streamlit run cekservice_streamlit.py --server.port 8502
```

### Port Scanner

```bash
# Scan all open ports
python3 cekport.py

# Check specific port
python3 cekport.py -p 8080
```

## Configuration

### Web Authentication

Default credentials:

- Username: `admin`
- Password: `sinara123`

### Optional Streamlit Secrets

Create `.streamlit/secrets.toml` for advanced features:

```toml
[auth]
redirect_uri = "http://localhost:8501/oauth2callback"
cookie_secret = "your_random_secret"
client_id = "your_client_id"
client_secret = "your_client_secret"

[connections.db]
type = "sql"
url = "sqlite:///database.db"
```

## System Requirements

- Linux with SystemD
- sudo access for systemctl commands
- Services must be named: `laravel-frankenphp-staging` and `laravel-frankenphp-production`
