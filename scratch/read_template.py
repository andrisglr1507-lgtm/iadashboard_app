import os

paths = [
    r"C:\Users\andri\Downloads\free-tailwind-admin-dashboard-template-main\free-tailwind-admin-dashboard-template-main\tailwind-admin-vuejs-free\package\src\layouts\full\FullLayout.vue",
    r"C:\Users\andri\Downloads\free-tailwind-admin-dashboard-template-main\free-tailwind-admin-dashboard-template-main\tailwind-admin-vuejs-free\package\src\layouts\full\vertical-sidebar\VerticalSidebar.vue"
]

for p in paths:
    print(f"--- {p} ---")
    if os.path.exists(p):
        with open(p, "r", encoding="utf-8") as f:
            print(f.read())
    else:
        print("File not found.")
