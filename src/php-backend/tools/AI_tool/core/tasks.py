from typing import Optional

from .file_parser import extract_text
from .ai_client import call_ai
from .file_generator import generate_file
from .format_detector import detect_format_from_prompt

def process_request(
    file_path: str,
    filename: str,
    prompt: str,
    output_format: str = "auto",
    api_key: Optional[str] = None
):
    requested_format = output_format
    auto_mode = output_format == "auto"

    if auto_mode:
        output_format = detect_format_from_prompt(prompt, api_key=api_key)

    # Nếu auto detect trả về định dạng nhị phân (pdf/docx/zip...), chuyển về text
    binary_formats = {"pdf", "docx"}
    if auto_mode and output_format in binary_formats and requested_format == "auto":
        output_format = "txt"

    text = extract_text(file_path, filename)

    # Tạo prompt tổng hợp: yêu cầu của người dùng + nội dung tài liệu trích xuất
    ai_prompt = f"""
Hãy thực hiện yêu cầu sau dựa trên tài liệu được cung cấp.

Yêu cầu: {prompt}

Tài liệu:
{text[:12000]}  # giới hạn độ dài
"""

    json_mode = output_format in ["json", "csv"]
    # Gọi AI (qua Key4U) với prompt đã xây dựng
    ai_response = call_ai(ai_prompt, json_mode=json_mode, api_key=api_key)

    if output_format in ["json", "csv"]:
        # Với JSON/CSV: sinh file tạm để PHP tải về
        return generate_file(ai_response, output_format)
    else:
        # Trả về plain text cho PHP hiển thị
        return ai_response