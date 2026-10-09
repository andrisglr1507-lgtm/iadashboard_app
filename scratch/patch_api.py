import os

filepath = r"D:\Andri\Flutter Project\ia_sodc_sys\lib\core\constants\api_constants.dart"

with open(filepath, "r", encoding="utf-8") as f:
    content = f.read()

# Update localBaseUrl to use port 8000
import re
content = re.sub(
    r"static const String localBaseUrl = '.*?';",
    "static const String localBaseUrl = 'http://10.87.231.194:8000/api';",
    content
)

with open(filepath, "w", encoding="utf-8") as f:
    f.write(content)

print("api_constants.dart patched!")
