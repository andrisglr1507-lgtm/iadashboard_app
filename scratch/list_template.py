import os
import json

path = r"C:\Users\andri\Downloads\free-tailwind-admin-dashboard-template-main\free-tailwind-admin-dashboard-template-main"

def explore_dir(dir_path):
    if not os.path.exists(dir_path):
        return {"error": "Path does not exist"}
    
    result = {}
    for root, dirs, files in os.walk(dir_path):
        rel_path = os.path.relpath(root, dir_path)
        if rel_path == ".":
            rel_path = "root"
        
        # skip node_modules or large folders
        if "node_modules" in rel_path or ".git" in rel_path:
            continue
            
        result[rel_path] = files
        
    return result

print(json.dumps(explore_dir(path), indent=2))
