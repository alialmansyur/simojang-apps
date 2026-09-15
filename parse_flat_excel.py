import openpyxl
import json
import re

wb = openpyxl.load_workbook("IKK KANREG.xlsx", data_only=True)
sheet = wb["RENCANA AKSI (FLAT)"]

sasarans = {}
indikators = {}

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

def parse_target_volume(text):
    if not text: return None
    # Remove dots (thousand separators in ID)
    text_clean = str(text).replace(".", "")
    # Find first number sequence
    match = re.search(r'\d+', text_clean)
    if match:
        return int(match.group())
    return None

for i, row in enumerate(sheet.iter_rows(min_row=2, values_only=True)):
    if not any(row): continue
    
    sas_no = row[0]
    sas_text = str(row[1]).strip()
    ikk_no = row[2]
    ikk_text = str(row[3]).strip()
    keg_no = row[4]
    keg_text = str(row[5]).strip()
    unit = row[7]
    satuan = row[8]
    target = row[9]
    
    sas_key = f"{sas_no}_{sas_text}"
    ikk_key = f"{sas_key}_{ikk_no}_{ikk_text}"
    
    if sas_key not in sasarans:
        sasarans[sas_key] = {
            "no_urut": int(sas_no) if isinstance(sas_no, (int, float)) else sas_no,
            "nama_sasaran": sas_text,
            "indikators": []
        }
        
    if ikk_key not in indikators:
        ind = {
            "no_urut": int(ikk_no) if isinstance(ikk_no, (int, float)) else ikk_no,
            "nama_indikator": ikk_text,
            "pengampu_ids": [], 
            "satuan": "",
            "target_volume": str(target).strip() if target else "", # Indikator uses VARCHAR
            "kegiatans": []
        }
        indikators[ikk_key] = ind
        sasarans[sas_key]["indikators"].append(ind)
        
    keg = {
        "no_urut": int(keg_no) if isinstance(keg_no, (int, float)) else keg_no,
        "nama_kegiatan": keg_text,
        "pengampu_ids": map_pengampu(unit),
        "satuan": str(satuan).strip() if satuan else "",
        "target_volume": parse_target_volume(target) # Kegiatan uses INT
    }
    indikators[ikk_key]["kegiatans"].append(keg)

sas_list = list(sasarans.values())

with open("apps/admin/clean_data.json", "w", encoding="utf-8") as f:
    json.dump(sas_list, f, indent=4, ensure_ascii=False)
