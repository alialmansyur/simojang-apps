import openpyxl

wb = openpyxl.load_workbook("IKK KANREG.xlsx", data_only=True)
sheet = wb.active

for i, row in enumerate(sheet.iter_rows(min_row=5, max_row=20, values_only=True)):
    print(f"Row {i+5}:", row[:11])
