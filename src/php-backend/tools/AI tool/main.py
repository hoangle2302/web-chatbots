from fastapi import FastAPI, File, UploadFile, Form, Header
from typing import Optional
from fastapi.responses import JSONResponse, FileResponse
import os
import tempfile
from core.tasks import process_request

app = FastAPI(title="AI File Worker")

@app.post("/process-file")
async def process_file(
    file: UploadFile = File(...),
    user_prompt: str = Form(...),
    output_format: str = Form("auto"),  # "auto", "py", "json", "docx", v.v.
    authorization: Optional[str] = Header(default=None),
    internal_key: Optional[str] = Header(default=None, alias="X-Internal-Key")
):
    # Ưu tiên lấy API key từ header Authorization/X-Internal-Key do backend PHP gửi sang
    api_key: Optional[str] = None
    if authorization:
        scheme_split = authorization.split(" ", 1)
        if len(scheme_split) == 2 and scheme_split[0].lower() == "bearer":
            api_key = scheme_split[1].strip()
        else:
            api_key = authorization.strip()
    elif internal_key:
        api_key = internal_key.strip()

    with tempfile.NamedTemporaryFile(delete=False) as tmp:
        # Lưu nội dung upload vào file tạm để xử lý
        content = await file.read()
        tmp.write(content)
        tmp_path = tmp.name

    try:
        # Gọi core.tasks.process_request để parse + gọi API mô hình
        result = process_request(
            file_path=tmp_path,
            filename=file.filename,
            prompt=user_prompt,
            output_format=output_format,
            api_key=api_key
        )

        if isinstance(result, dict) and "file_path" in result:
            # Trả về file (docx/pdf/...) cho backend PHP
            return FileResponse(
                path=result["file_path"],
                filename=result["filename"],
                media_type="application/octet-stream"
            )
        else:
            return JSONResponse({"result": str(result)})

    except Exception as e:
        return JSONResponse({"error": str(e)}, status_code=500)
    finally:
        # Dọn file tạm dù xử lý thành công hay thất bại
        if os.path.exists(tmp_path):
            os.unlink(tmp_path)