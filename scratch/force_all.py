import os
import re

filepath = r"D:\Andri\Flutter Project\ia_sodc_sys\lib\ui\auth\login_screen.dart"

with open(filepath, "r", encoding="utf-8") as f:
    content = f.read()

# Let's just FORCE it to be exactly what we need, replacing the whole dropdown/input logic or just finding all setBaseUrl and forcefully appending /
content = re.sub(r"setBaseUrl\('http://(.*?)'\)", r"setBaseUrl('http://10.87.231.194:8000/api/')", content)

with open(filepath, "w", encoding="utf-8") as f:
    f.write(content)

print("login_screen.dart fully forced to http://10.87.231.194:8000/api/!")
