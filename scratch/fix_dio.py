import os
import re

filepath = r"D:\Andri\Flutter Project\ia_sodc_sys\lib\core\constants\api_constants.dart"

with open(filepath, "r", encoding="utf-8") as f:
    content = f.read()

# Make sure base URLs end with /
content = content.replace("http://10.87.231.194:8000/api", "http://10.87.231.194:8000/api/")
content = content.replace("https://iacsa.biz.id/api", "https://iacsa.biz.id/api/")

# Remove leading slashes from endpoints
content = content.replace("= '/login'", "= 'login'")
content = content.replace("= '/master", "= 'master")
content = content.replace("= '/sodc", "= 'sodc")
content = content.replace("= '/bins", "= 'bins")
content = content.replace("= '/products", "= 'products")
content = content.replace("= '/opname", "= 'opname")

with open(filepath, "w", encoding="utf-8") as f:
    f.write(content)

print("api_constants.dart fixed for Dio path resolution!")
