import re

file_path = "app/Database/Migrations/2026-09-08-022132_CreateIkkTables.php"
with open(file_path, "r") as f:
    content = f.read()

# find data_ikk_kegiatan block
idx = content.find("// 3. data_ikk_kegiatan")
if idx != -1:
    before = content[:idx]
    after = content[idx:]
    
    # replace target_volume in after
    pattern = r"('target_volume'\s*=>\s*\[\s*)'type'\s*=>\s*'VARCHAR',\s*'constraint'\s*=>\s*100,"
    replacement = r"\1'type' => 'INT',"
    after = re.sub(pattern, replacement, after, count=1)
    
    with open(file_path, "w") as f:
        f.write(before + after)
