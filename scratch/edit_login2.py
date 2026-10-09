import os

filepath = r"D:\Andri\Flutter Project\ia_sodc_sys\lib\ui\auth\login_screen.dart"

with open(filepath, "r", encoding="utf-8") as f:
    content = f.read()

# Fix Laragon public path
content = content.replace(
    "ref.read(baseUrlProvider.notifier).setBaseUrl('http://$ip/classic-dashboard/api');",
    "ref.read(baseUrlProvider.notifier).setBaseUrl('http://$ip/classic-dashboard/public/api');"
)

with open(filepath, "w", encoding="utf-8") as f:
    f.write(content)

print("File patched for Laragon /public/ segment successfully!")
