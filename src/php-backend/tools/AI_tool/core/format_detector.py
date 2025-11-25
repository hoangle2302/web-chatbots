import re
from typing import Optional

from .ai_client import call_ai

SUPPORTED_FORMATS = {
    "docx": ["word", "docx", "microsoft word", "báo cáo word"],
    "pdf": ["pdf", "file pdf"],
    "md": ["markdown", "md", ".md"],
    "json": ["json", ".json", "dữ liệu json", "định dạng json"],
    "csv": ["csv", ".csv", "file csv", "bảng tính"],
    "xml": ["xml", ".xml"],
    "yaml": ["yaml", "yml", ".yaml", ".yml"],
    "py": ["python", "py", ".py", "script python", "file python"],
    "js": ["javascript", "js", ".js", "file js", "mã javascript"],
    "ts": ["typescript", "ts", ".ts"],
    "html": ["html", ".html", "trang web", "file html"],
    "css": ["css", ".css"],
    "sh": ["shell", "bash", "sh", ".sh", "script shell"],
    "sql": ["sql", ".sql", "truy vấn sql"],
    "txt": ["text", "plain text", "txt", ".txt"],
}

KEYWORD_TO_FORMAT = {}
for fmt, keywords in SUPPORTED_FORMATS.items():
    for kw in keywords:
        KEYWORD_TO_FORMAT[kw.lower()] = fmt

def detect_format_from_prompt(prompt: str, api_key: Optional[str] = None) -> str:
    prompt_lower = prompt.lower()
    for keyword, fmt in KEYWORD_TO_FORMAT.items():
        if keyword in prompt_lower:
            return fmt
    ai_guess = _ask_ai_to_detect_format(prompt, api_key=api_key)
    return ai_guess if ai_guess in SUPPORTED_FORMATS else "txt"

def _ask_ai_to_detect_format(prompt: str, api_key: Optional[str] = None) -> str:
    system_prompt = """
Bạn là chuyên gia phân tích yêu cầu. Hãy xác định **định dạng file đầu ra** mà người dùng muốn, dựa trên yêu cầu sau.

Chỉ trả lời bằng **một từ duy nhất** trong danh sách sau:
docx, pdf, md, json, csv, xml, yaml, py, js, ts, html, css, sh, sql, txt

Nếu không chắc, trả lời: txt
"""
    try:
        response = call_ai(
            prompt=f"Yêu cầu: {prompt}",
            system_prompt=system_prompt,
            json_mode=False,
            temperature=0.0,
            api_key=api_key
        )
        return response.strip().lower()
    except:
        return "txt"