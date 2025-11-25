import json
import csv
import tempfile
import os
from docx import Document
from fpdf import FPDF

def generate_file(content: str, fmt: str) -> dict:
    suffix = f".{fmt}"
    if fmt == "json":
        data = json.loads(content)
        with tempfile.NamedTemporaryFile(delete=False, suffix=suffix) as tmp:
            json.dump(data, tmp, ensure_ascii=False, indent=2)
            return {"file_path": tmp.name, "filename": "result.json"}
    elif fmt == "csv":
        data = json.loads(content)
        with tempfile.NamedTemporaryFile(delete=False, suffix=suffix, mode="w", newline="") as tmp:
            if isinstance(data, list) and len(data) > 0:
                writer = csv.DictWriter(tmp, fieldnames=data[0].keys())
                writer.writeheader()
                writer.writerows(data)
            return {"file_path": tmp.name, "filename": "result.csv"}
    elif fmt == "docx":
        doc = Document()
        doc.add_paragraph(content)
        with tempfile.NamedTemporaryFile(delete=False, suffix=suffix) as tmp:
            doc.save(tmp.name)
            return {"file_path": tmp.name, "filename": "result.docx"}
    elif fmt == "pdf":
        pdf = FPDF()
        pdf.add_page()
        pdf.set_auto_page_break(auto=True, margin=15)
        pdf.set_font("Arial", size=12)
        try:
            pdf.multi_cell(0, 10, content)
        except:
            pdf.multi_cell(0, 10, content.encode('latin-1', 'replace').decode('latin-1'))
        with tempfile.NamedTemporaryFile(delete=False, suffix=suffix) as tmp:
            pdf.output(tmp.name)
            return {"file_path": tmp.name, "filename": "result.pdf"}
    elif fmt in ["py", "js", "ts", "html", "css", "sh", "sql", "yaml", "xml", "md", "txt"]:
        with tempfile.NamedTemporaryFile(delete=False, suffix=suffix) as tmp:
            tmp.write(content.encode("utf-8"))
            return {"file_path": tmp.name, "filename": f"result.{fmt}"}
    else:
        with tempfile.NamedTemporaryFile(delete=False, suffix=".txt") as tmp:
            tmp.write(content.encode("utf-8"))
            return {"file_path": tmp.name, "filename": "result.txt"}