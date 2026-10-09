import os

filepath = r"D:\Andri\Flutter Project\ia_sodc_sys\lib\data\repositories\auth_repository.dart"

with open(filepath, "r", encoding="utf-8") as f:
    content = f.read()

print("--- auth_repository.dart ---")
print(content)

filepath_dio = r"D:\Andri\Flutter Project\ia_sodc_sys\lib\data\remote\dio_client.dart"

with open(filepath_dio, "r", encoding="utf-8") as f:
    content = f.read()

print("--- dio_client.dart ---")
print(content)
