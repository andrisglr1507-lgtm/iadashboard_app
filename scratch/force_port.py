import os
import re

filepath = r"D:\Andri\Flutter Project\ia_sodc_sys\lib\ui\auth\login_screen.dart"

with open(filepath, "r", encoding="utf-8") as f:
    content = f.read()

# Force the text field to use IP + Port
content = content.replace(
    "_ipController.text = uri.host;",
    "_ipController.text = '${uri.host}:${uri.port == 80 ? '' : uri.port}';"
)

content = content.replace(
    "final ip = _ipController.text.trim().isEmpty ? '192.168.1.5' : _ipController.text.trim();",
    "final ip = '10.87.231.194:8000';"
)

content = content.replace(
    "final ip = val.trim().isEmpty ? '192.168.1.5' : val.trim();",
    "final ip = '10.87.231.194:8000';"
)

content = content.replace(
    "ref.read(baseUrlProvider.notifier).setBaseUrl('http://$ip/classic-dashboard/public/api');",
    "ref.read(baseUrlProvider.notifier).setBaseUrl('http://$ip/api');"
)

with open(filepath, "w", encoding="utf-8") as f:
    f.write(content)

print("login_screen.dart forcefully patched for port 8000!")
