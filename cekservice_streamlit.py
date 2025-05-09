import streamlit as st
import subprocess
import sys
import random
import io
from PIL import Image, ImageDraw, ImageFont
from colorama import init, Fore, Style
from tabulate import tabulate

# Initialize colorama for colored output
init(autoreset=True)

# Set session state for authentication
if 'authenticated' not in st.session_state:
    st.session_state.authenticated = False

def run_command(command):
    """Run shell command and return its output."""
    try:
        result = subprocess.run(command, shell=True, capture_output=True, text=True)
        return result.stdout, result.stderr, result.returncode
    except Exception as e:
        return "", str(e), 1

def check_service_status(service_name):
    """Check service status using systemctl."""
    command = f"systemctl status {service_name}"
    stdout, stderr, returncode = run_command(command)
    
    if returncode == 0:
        status_lines = stdout.splitlines()
        for line in status_lines:
            if "Active:" in line:
                status = line.strip()
                if "active (running)" in status:
                    return "🟢 Active (Running)", "green"
                elif "inactive" in status or "dead" in status:
                    return "🔴 Inactive", "red"
                else:
                    return "🟡 Partially Active", "yellow"
    elif "Unit" in stderr and "could not be found" in stderr:
        return "❌ Service Not Found", "red"
    else:
        return f"❌ Failed to check status: {stderr}", "red"

def manage_service(service_name, action):
    """Manage service (start, stop, restart)."""
    command = f"sudo systemctl {action} {service_name}"
    stdout, stderr, returncode = run_command(command)
    
    if returncode == 0:
        return f"✅ Successfully {action}ed service {service_name}", "green"
    else:
        return f"❌ Failed to {action} service {service_name}: {stderr}", "red"

def generate_captcha_image():
    """Generate a 4-digit numeric captcha as an image."""
    # Generate 4-digit captcha
    captcha_text = str(random.randint(1000, 9999))
    
    # Create an image
    width, height = 200, 80
    image = Image.new('RGB', (width, height), color='white')
    draw = ImageDraw.Draw(image)
    
    # Add noise (random lines)
    for _ in range(5):
        x1 = random.randint(0, width)
        y1 = random.randint(0, height)
        x2 = random.randint(0, width)
        y2 = random.randint(0, height)
        draw.line([(x1, y1), (x2, y2)], fill='lightgray')
    
    # Choose a font
    try:
        font = ImageFont.truetype("/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf", 40)
    except IOError:
        font = ImageFont.load_default()
    
    # Calculate text position
    bbox = draw.textbbox((0, 0), captcha_text, font=font)
    text_width = bbox[2] - bbox[0]
    text_height = bbox[3] - bbox[1]
    x = (width - text_width) / 2
    y = (height - text_height) / 2
    
    # Draw text with some rotation and color variation
    draw.text((x, y), captcha_text, font=font, fill='navy')
    
    # Convert image to bytes
    img_byte_arr = io.BytesIO()
    image.save(img_byte_arr, format='PNG')
    img_byte_arr = img_byte_arr.getvalue()
    
    return captcha_text, img_byte_arr

def generate_captcha():
    """Generate a 4-digit numeric captcha."""
    return str(random.randint(1000, 9999))

def login_page():
    """Create a login page with username, password, and captcha."""
    st.title("🔐 Login")
    
    # Generate and store captcha
    if 'captcha' not in st.session_state:
        st.session_state.captcha, st.session_state.captcha_image = generate_captcha_image()
    
    username = st.text_input("Username")
    password = st.text_input("Password", type="password")
    
    # Display captcha image
    st.image(st.session_state.captcha_image, caption="Enter the numbers you see", width=200)
    user_captcha = st.text_input("Enter Captcha")
    
    if st.button("Login"):
        if username == "admin" and password == "sinara123" and user_captcha == st.session_state.captcha:
            st.session_state.authenticated = True
            st.session_state.captcha = None  # Reset captcha after successful login
            st.session_state.captcha_image = None
            st.rerun()
        else:
            st.error("Invalid credentials or captcha. Please try again.")
            st.session_state.captcha, st.session_state.captcha_image = generate_captcha_image()  # Regenerate captcha on failure

def main():
    st.set_page_config(page_title="Laravel FrankenPHP Service Manager", page_icon="🚀")
    
    # Check authentication state
    if not st.session_state.authenticated:
        login_page()
        return
    
    st.title("🚀 Laravel FrankenPHP Service Manager")
    
    # Logout button
    if st.sidebar.button("Logout"):
        st.session_state.authenticated = False
        st.rerun()
    
    # Service definitions
    services = {
        "Staging": "laravel-frankenphp-staging",
        "Production": "laravel-frankenphp-production"
    }
# Sidebar for service selection and actions
    st.sidebar.header("Service Management")
    selected_service = st.sidebar.selectbox(
        "Select Service", 
        list(services.keys()) + ["All Services"]
    )
    
    action = st.sidebar.radio(
        "Select Action", 
        ["Status", "Start", "Stop", "Restart"]
    )
    
    # Process button
    if st.sidebar.button("Execute"):
        st.header(f"{action} Service(s)")
        
        # Determine which services to process
        if selected_service == "All Services":
            service_list = list(services.values())
        else:
            service_list = [services[selected_service]]
        
        # Create results container
        results_container = st.container()
        
        # Process services
        results = []
        for service in service_list:
            if action.lower() == "status":
                status, color = check_service_status(service)
                results.append((service, status, color))
            else:
                result, color = manage_service(service, action.lower())
                results.append((service, result, color))
        
        # Display results
        for service, result, color in results:
            if color == "green":
                st.success(f"{service}: {result}")
            elif color == "red":
                st.error(f"{service}: {result}")
            else:
                st.warning(f"{service}: {result}")

if __name__ == "__main__":
    main()
