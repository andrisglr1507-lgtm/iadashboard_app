import os

filepath = r"D:\Andri\Flutter Project\ia_sodc_sys\lib\ui\auth\login_screen.dart"

with open(filepath, "r", encoding="utf-8") as f:
    content = f.read()

# Add trailing slash to baseUrl
content = content.replace(
    "setBaseUrl('http://$ip/api')",
    "setBaseUrl('http://$ip/api/')"
)

with open(filepath, "w", encoding="utf-8") as f:
    f.write(content)

print("login_screen.dart patched to add trailing slash!")
