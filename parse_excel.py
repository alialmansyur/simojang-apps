import openpyxl
import json

wb = openpyxl.load_workbook("IKK KANREG.xlsx", data_only=True)
sheet = wb.active

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

for i, row in enumerate(sheet.iter_rows(min_row=5, values_only=True)):
    if not any(row): continue
    
    sas_no = row[1]
    sas_text = row[2]
    ikk_no = row[3]
    ikk_text = row[4]
    keg_no = row[5]
    keg_text = row[6]
    
    unit = row[8]
    satuan = row[9]
    target = row[10]
    
    # Process Sasaran
    if sas_no is not None and sas_text is not None:
        current_sasaran = {
            "no_urut": int(sas_no) if isinstance(sas_no, (int, float)) else sas_no,
            "nama_sasaran": str(sas_text).strip(),
            "indikators": []
        }
        sasarans.append(current_sasaran)
        
    # Process IKK Header (the row with IKK no but maybe without text)
    if ikk_no is not None:
        current_indikator = {
            "no_urut": int(ikk_no) if isinstance(ikk_no, (int, float)) else ikk_no,
            "nama_indikator": "", # Will be filled later if None
            "pengampu_ids": map_pengampu(unit),
            "satuan": str(satuan).strip() if satuan else "",
            "target_volume": str(target).strip() if target else "",
            "kegiatans": []
        }
        if current_sasaran:
            current_sasaran["indikators"].append(current_indikator)
            
    # Process IKK Text if it appears on a row (usually along with Kegiatan 1)
    if ikk_text is not None and str(ikk_text).strip() != "":
        if current_indikator and current_indikator["nama_indikator"] == "":
            current_indikator["nama_indikator"] = str(ikk_text).strip()
            
    # Process Kegiatan
    if keg_no is not None and keg_text is not None:
        keg = {
            "no_urut": int(keg_no) if isinstance(keg_no, (int, float)) else keg_no,
            "nama_kegiatan": str(keg_text).strip(),
            "pengampu_ids": map_pengampu(unit),
            "satuan": str(satuan).strip() if satuan else "",
            "target_volume": str(target).strip() if target else ""
        }
        if current_indikator:
            current_indikator["kegiatans"].append(keg)

# Calculate counts
s_count = len(sasarans)
i_count = sum(len(s["indikators"]) for s in sasarans)
k_count = sum(len(i["kegiatans"]) for s in sasarans for i in s["indikators"])

print(f"Sasaran: {s_count}, Indikator: {i_count}, Kegiatan: {k_count}")

with open("apps/admin/clean_data.json", "w", encoding="utf-8") as f:
    json.dump(sasarans, f, indent=4, ensure_ascii=False)
