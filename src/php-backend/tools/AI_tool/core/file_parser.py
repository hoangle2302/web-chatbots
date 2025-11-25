import os
from typing import List

from PyPDF2 import PdfReader
from docx import Document
import pandas as pd

try:
    from pdf2image import convert_from_path
    from pytesseract import image_to_string

    OCR_AVAILABLE = True
except ImportError:
    OCR_AVAILABLE = False

def extract_text(file_path: str, original_name: str) -> str:
    ext = os.path.splitext(original_name)[1].lower()
    if ext == ".pdf":
        reader = PdfReader(file_path)
        pdf_text = "\n".join(page.extract_text() or "" for page in reader.pages)

        if pdf_text.strip():
            return pdf_text

        if OCR_AVAILABLE:
            try:
                images: List["Image.Image"] = convert_from_path(file_path)
                ocr_chunks = []
                for idx, image in enumerate(images, start=1):
                    try:
                        ocr_chunks.append(image_to_string(image))
                    except Exception as page_err:  # pragma: no cover - diagnostic path
                        ocr_chunks.append(f"[Lỗi OCR trang {idx}: {page_err}]")

                ocr_text = "\n".join(ocr_chunks)
                if ocr_text.strip():
                    return ocr_text
            except Exception as ocr_err:  # pragma: no cover - diagnostic path
                return f"[Không thể OCR PDF: {ocr_err}]"

        return "[Không thể trích xuất nội dung PDF]"
    elif ext == ".docx":
        doc = Document(file_path)
        return "\n".join(para.text for para in doc.paragraphs)
    elif ext == ".txt":
        with open(file_path, "r", encoding="utf-8") as f:
            return f.read()
    elif ext in [".xlsx", ".xls"]:
        df = pd.read_excel(file_path)
        return df.to_string()
    else:
        return f"[Nội dung file {ext} chưa được hỗ trợ, xử lý như text thô]\n" + str(open(file_path, "rb").read()[:500])