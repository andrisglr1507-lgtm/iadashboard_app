import os
import json

path = r"C:\Users\andri\Downloads\gentelella-master\gentelella-master"

def explore_dir(dir_path):
    if not os.path.exists(dir_path):
        return {"error": "Path does not exist"}
    
    result = []
    for root, dirs, files in os.walk(dir_path):
        rel_path = os.path.relpath(root, dir_path)
        if rel_path == ".":
            rel_path = "root"
        
        if "vendors" in rel_path or ".git" in rel_path or "node_modules" in rel_path:
            continue
            
        result.append({rel_path: files[:10]}) # Limit to 10 files per dir
        
    return result

print(json.dumps(explore_dir(path), indent=2))
