import pdfplumber
import json

pdf_path = r"d:\2. Project\WebApps Simojang\IKK KANREG.pdf"
try:
    with pdfplumber.open(pdf_path) as pdf:
        all_tables = []
        for page in pdf.pages:
            tables = page.extract_tables()
            for table in tables:
                all_tables.extend(table)
                
    with open("pdf_tables.json", "w", encoding="utf-8") as f:
        json.dump(all_tables, f, indent=4, ensure_ascii=False)
    print("Success")
except Exception as e:
    print(e)
