# Project Structure

## File Organization

```
.
├── cekservice.py              # CLI application
├── cekservice_streamlit.py    # Web interface (Streamlit)
├── cekport.py                 # Port scanner utility
├── requirements.txt           # Python dependencies
├── README.md                  # Documentation (Indonesian)
├── LIBRARIES.md              # Library documentation (Indonesian)
└── .streamlit/               # Streamlit configuration (optional)
    └── secrets.toml          # Authentication & DB config
```

## Code Architecture

### CLI Application (`cekservice.py`)

- `run_command()` - Execute shell commands via subprocess
- `check_service_status()` - Query systemctl for service status
- `manage_service()` - Perform start/stop/restart actions
- `print_header()` - Display formatted CLI header
- `main()` - Entry point with argparse handling

### Web Application (`cekservice_streamlit.py`)

- `run_command()` - Shell command execution
- `check_service_status()` - Service status with web-friendly output
- `manage_service()` - Service management with visual feedback
- `generate_captcha_image()` - Create CAPTCHA using PIL
- `login_page()` - Authentication UI with session state
- `main()` - Streamlit app entry point

### Port Scanner (`cekport.py`)

- `get_process_name()` - Retrieve process name from PID
- `check_ports()` - Scan for listening ports using psutil
- `print_header()` - Display scanner header
- `main()` - Entry point with optional port filtering

## Conventions

### Service Names

- Staging: `laravel-frankenphp-staging`
- Production: `laravel-frankenphp-production`

### Color Coding (CLI)

- Green: Active/Success
- Red: Inactive/Error
- Yellow: Warning/Partial status

### Session State (Streamlit)

- `authenticated` - Login status
- `captcha` - Current CAPTCHA value
- `captcha_image` - CAPTCHA image bytes

### Command Patterns

All systemctl commands use sudo:

```python
command = f"sudo systemctl {action} {service_name}"
```

### Error Handling

- Subprocess errors captured via `capture_output=True`
- Return codes checked for success/failure
- User-friendly error messages in Indonesian
