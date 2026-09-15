import json
import re

with open("pdf_tables.json", "r", encoding="utf-8") as f:
    rows = json.load(f)

sasarans = []
current_sasaran = None
current_indikator = None

def map_pengampu(text):
    if not text: return []
    text = str(text).lower()
    ids = set()
    if "seluruh tim kerja" in text:
        return [1, 2, 3, 4, 5, 6]
    
    if "pengangkatan" in text or "mutasi" in text or "pdm" in text:
        ids.add(1)
    if "status" in text or "pemberhentian" in text or "sdp" in text:
        ids.add(2)
    if "pembinaan" in text or "pmasn" in text or "manajemen asn" in text:
        ids.add(3)
    if "pengawasan" in text or "pengendalian" in text or "wasdal" in text:
        ids.add(4)
    if "sistem informasi" in text or "digitalisasi" in text or "sidigi" in text:
        ids.add(5)
    if "bagian tu" in text or "tata usaha" in text:
        ids.add(6)
        
    return list(ids)

for i, row in enumerate(rows):
    if i == 0 or not row: continue
    row = [str(x).replace("\n", " ").strip() if x is not None else "" for x in row]
    while len(row) < 9:
        row.append("")
        
    s_no, s_text, i_no, i_text, k_no, k_text, unit, satuan, target = row
    
    if s_no in ["None", ""]: s_no = None
    if s_text in ["None", ""]: s_text = None
    if i_no in ["None", ""]: i_no = None
    if i_text in ["None", ""]: i_text = None
    if k_no in ["None", ""]: k_no = None
    if k_text in ["None", ""]: k_text = None
    if unit in ["None", ""]: unit = None
    if satuan in ["None", ""]: satuan = None
    if target in ["None", ""]: target = None
    
    if s_no and s_text:
        current_sasaran = {
            "no_urut": int(s_no) if str(s_no).isdigit() else s_no,
            "nama_sasaran": s_text,
            "indikators": []
        }
        sasarans.append(current_sasaran)
        
    if i_no and i_text:
        current_indikator = {
            "no_urut": int(i_no) if str(i_no).isdigit() else i_no,
            "nama_indikator": i_text,
            "pengampu_ids": map_pengampu(unit),
            "satuan": satuan,
            "target_volume": target,
            "kegiatans": []
        }
        if current_sasaran:
            current_sasaran["indikators"].append(current_indikator)
            
    if k_no and k_text:
        keg = {
            "no_urut": int(k_no) if str(k_no).isdigit() else k_no,
            "nama_kegiatan": k_text,
            "pengampu_ids": map_pengampu(unit),
            "satuan": satuan,
            "target_volume": target
        }
        if current_indikator:
            current_indikator["kegiatans"].append(keg)

# Generate SQL or rewrite the Seeder to read from this json
with open("clean_data.json", "w", encoding="utf-8") as f:
    json.dump(sasarans, f, indent=4, ensure_ascii=False)
