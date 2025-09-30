import requests
import json

cookies = {
    'cna': 'Otj2IFpvThICAXZEFZHzamBw',
    '_gcl_au': '1.1.748147921.1752164922',
    '_bl_uid': 'F7m8wcyax06lU4rm47q1eIez62R5',
    'acw_tc': '0a03e54a17567353768315086e1e907eca27d4985fc55cbb39fcdb553fc54e',
    'x-ap': 'ap-southeast-1',
    'sca': 'd9943fb1',
    'xlly_s': '1',
    'atpsida': '1701f5bb0ee163766aa65847_1756736456_5',
    'tfstk': 'gXE-LamowsfortdyeWbcx359F_BciZ2PE7y6xXckRSFYO76yxJgkOHFxiTyoV_Pv9RV9qHqLLHHQdWWrtT70U8oEA1fg9G2zUQ6Rz2E-O-gb3vlBNZ0715Qci1fGjiVckcEAsWDbG1ujLjMSdHt5HmMnBDMSO26xGvHwRQNIAtBxdAkSOvGIcqMrCDGQOD6YhjkKAYNIAtejgvGmkLhHFXKLPu_29Y5-PHtQDY3fj-GbJPBnF4h_FjQfloKZyfwSMH1Steln9jr1iZe4OzNo3WIOMcaaMk3svgdZerwtcbm1WEn8n7rtVlCJqx0KwrZSkptQemzS7yFRwUl7rS3Zhq9dbxViG8r7k9RbF5c-VxgcfOejRrqr7ufX2caaE04Q1sYoNPHC48EgXgowsfHHPtBv8euS3d-HyOIoj4diHfXDoe8EoxkxstBv8euS3xhGniYe8qDV.',
    'isg': 'BLm5V_9-9R2soalToaW-yEHCyCWTxq14XV61YNvsJ-BfYtj0JxcvSRT04H6UYkWw',
    'ssxmod_itna': 'YqUxuD2D0D90frx4qxRhDQqWui43qT4q0dGMlDeq7tDRDFqApxDHQIrK75rYn7t7DGK=YlEOWiKMD0H2rzDBKQDax7fr04ti2Us3YbjOh3Y9E2DwpD0r7uuEawY6+pnd8iZ8K8Cwz6lrK7eDU4GnGRDx2OeD44DvDBYD74G+DDeDixGmG4DSlDD9DGPdglTi2eDEDYPdv4DmDGYdheDgmDDBDD64x7QaRWxD0Th+aYoK=Ac7djFNS6Tq+PxejeDMWxGXnYkPjeH6gFxbwa8fH1oYxB=cxBQiZAWAS1IQ==naqQApKYArB8DfOelxxoi=aYYiiYM0De7DK0D807w0DonDa7N8YY4jQCEwwDDi=iVoYxKeD6tyxNgrXr02gWv7RpKtG5BqciG3eA1nwsGIhWxofe=WxQKe5RhVPA3YD',
    'ssxmod_itna2': 'YqUxuD2D0D90frx4qxRhDQqWui43qT4q0dGMlDeq7tDRDFqApxDHQIrK75rYn7t7DGK=YlEOWiKwDDcYq5L0DDtDFrTpfNx05jGYA3aqc3xcfhDxfE=YPh9eWhOGSWUqWWhSyGxaA17CY70C8WFe2gGaal9HREDak=ii2=0Di87aRppK8=iz1paOBe5xg6PQQevWF8iOR=OHu1SDuxqNiREQxzE1WfHeC=f6RSvQ0tYXq5HLiRi0N35LU97AXr3mP5eCy6K5TVYLPwiajtDHKmymDyafYla4CgcX75j7fTiXN9H0WZYqKCtMrNgYIPQ0/QIxIAxYIcgqZ7f+OGnmp+2xZjKoF0yaj3rPFgKOnUxtf5xN1ORCZPTSKAZtvp37mPD9AWODvaTCEpKEty7oObXC4XKat6BbcAeKQDEiG1ANPeUnAWIUuZDajr23Q4Y0Djju3jYYjWDfDxOteeeEjus7n4jYdCeEjcgtG1nwsCuhWx=nGgfG1D7PYD',
}

headers = {
    'Accept': '*/*',
    'Accept-Language': 'en-US,en;q=0.9,vi-VN;q=0.8,vi;q=0.7,fr-FR;q=0.6,fr;q=0.5',
    'Connection': 'keep-alive',
    'Origin': 'https://chat.qwen.ai',
    'Referer': 'https://chat.qwen.ai/c/guest',
    'Sec-Fetch-Dest': 'empty',
    'Sec-Fetch-Mode': 'cors',
    'Sec-Fetch-Site': 'same-origin',
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36',
    'authorization': 'Bearer',
    'bx-ua': '231!fpp3SkmU3z3+joFAE43GpLEjUq/YvqY2leOxacSC80vTPuB9lMZY9mRWFzrwLEV0PmcfY4rL2JFHeKTCyUQdACIzKZBlsbAyXt6Iz8TQ7ck9CIZgeOBOVaRK66GQqw/G9pMRBb28c0I6pXxeCmvMXDUPkF/DRcC0GsGCA3FByoiok+G3N4s6OH8QSBzKvL6zZPjueqOKAshjdmkMbyWWls5WbVbp5PBl2ZakxDprleD+zu8e+Zd++6WF1cs+5ie9HJBh+++j+ygU3+jOQqGIRMkgFkk3k2BBI8q9X4runFE3IoVRf/d1V4uolvkvbtVVGy3qQaz18OAErmHM7l7vXb6kw7xAc3+lglcxnLU1n3pohAniCbldVSuP9SZUNYZKk7mqYtGessVZW7Rx7xMxro7oGK/lScFHXOINQEsb4fcd0Rb+KP4LZ1YJBHzPPN3AoTYBv8NkUtfIx62EubLze/xLsNCpsZweEY5z7uPeZE4tna/H0jQ1YL3lCqKLYAIhijL1MwDc7ZEs+ER7EYR5Tof8E4y1GpM2ppJUt/iYXKTqTgyBbfdGV/sL0y3Qy30vfP7MwZhrdKxCHS9uw1EYaTwOjPgasd+hmQTSHlWUWW0HrnFkZbsRLzu9abhWYiYcsNa85ZhNaNc6Mk4HbgyrJKAN4I7KRNU5xtYuQyuI3GcLfMtiZLYDhYl+SnTgiOZLaWAFOnoVnztZV4vpR7hMuj1PGfhaNZIVnKakTwLyl7hIJJvRKGtnyWPJOIpJ0heLQCBDV/QlZWRaFfk9xD8UspSk+h1L09249DdMqC/4BCntToGqb09q7hpScSU9BvV/zt1dsB5naVfjW+l+YJy9eSBKQIeH1uYu/ssaBfCdM8lYeb0lFDFqsVDyj90qoRsm3LN2A6zaOWDMjVVYz5JmSGX5ZXzpBFG4rqPm7kE4+7lnNvf65kARSUzsoW39KgpzEieMMLWuJslN8h1GdFuDlY0akkYiLkToK5rvzECEQNZr7EPmPeKytwrh0Nsiz8YAevccE9XnvKClT7imbSNPCgYU5n2RXG4S6LETn4mfAp3GVrvSwk6jMivACIAEAYU9YWaJksZH0tKXXTXRw8YxlEtzdnThNh2+4uVrEx4Sig4sBb/eMet2GdLp7+j8kc8MNYOa+qbxIx+k8ADSM7jxzAsrutJ9YxhuePePEnV9rJcCeFsKOHnbcuBNEa84R/Kr9lATj2liLROwcBhmy0tKj/U9scvMqZoWa1mDeXPBj+lR8D802XGZ4F8+nYOWwmuSHRYVw8eDxoPyDbkuOJgn+68h8rR6dEg7m550ntYUEnoj2u7XdudrfZ8Dixps67B9t0jS771KQXTbclFLYaizKQcLryCBWXFK2F1KeqpHbEYNYEfux2ARTUTNl0De/pUR/jUo1hCRc6ZxD6tVi1UNqFqVUgOy2BDbQBO6Jri/HLAjqHEw4UiBcFeVaJ9NnPYd9+o06E+9tCLMDBTVOFD5rqL2XuaniB3FrE6KoSAITh9KJuf+hCKpENjn65Lgi+==',
    'bx-umidtoken': 'T2gAXgvxaymN41YDo4Rva3A3FxaMttFAXO0hD0B76QEBeulG-F0eCPrdY_JJAC_bdYY=',
    'bx-v': '2.5.31',
    'content-type': 'application/json; charset=UTF-8',
    'sec-ch-ua': '"Not;A=Brand";v="99", "Google Chrome";v="139", "Chromium";v="139"',
    'sec-ch-ua-mobile': '?0',
    'sec-ch-ua-platform': '"Windows"',
    'source': 'web',
    'timezone': 'Mon Sep 01 2025 21:20:59 GMT+0700',
    'x-accel-buffering': 'no',
    'x-request-id': '001f2bc4-380c-47bf-b4f2-ae2361c019f8',
    # 'Cookie': 'cna=Otj2IFpvThICAXZEFZHzamBw; _gcl_au=1.1.748147921.1752164922; _bl_uid=F7m8wcyax06lU4rm47q1eIez62R5; acw_tc=0a03e54a17567353768315086e1e907eca27d4985fc55cbb39fcdb553fc54e; x-ap=ap-southeast-1; sca=d9943fb1; xlly_s=1; atpsida=1701f5bb0ee163766aa65847_1756736456_5; tfstk=gXE-LamowsfortdyeWbcx359F_BciZ2PE7y6xXckRSFYO76yxJgkOHFxiTyoV_Pv9RV9qHqLLHHQdWWrtT70U8oEA1fg9G2zUQ6Rz2E-O-gb3vlBNZ0715Qci1fGjiVckcEAsWDbG1ujLjMSdHt5HmMnBDMSO26xGvHwRQNIAtBxdAkSOvGIcqMrCDGQOD6YhjkKAYNIAtejgvGmkLhHFXKLPu_29Y5-PHtQDY3fj-GbJPBnF4h_FjQfloKZyfwSMH1Steln9jr1iZe4OzNo3WIOMcaaMk3svgdZerwtcbm1WEn8n7rtVlCJqx0KwrZSkptQemzS7yFRwUl7rS3Zhq9dbxViG8r7k9RbF5c-VxgcfOejRrqr7ufX2caaE04Q1sYoNPHC48EgXgowsfHHPtBv8euS3d-HyOIoj4diHfXDoe8EoxkxstBv8euS3xhGniYe8qDV.; isg=BLm5V_9-9R2soalToaW-yEHCyCWTxq14XV61YNvsJ-BfYtj0JxcvSRT04H6UYkWw; ssxmod_itna=YqUxuD2D0D90frx4qxRhDQqWui43qT4q0dGMlDeq7tDRDFqApxDHQIrK75rYn7t7DGK=YlEOWiKMD0H2rzDBKQDax7fr04ti2Us3YbjOh3Y9E2DwpD0r7uuEawY6+pnd8iZ8K8Cwz6lrK7eDU4GnGRDx2OeD44DvDBYD74G+DDeDixGmG4DSlDD9DGPdglTi2eDEDYPdv4DmDGYdheDgmDDBDD64x7QaRWxD0Th+aYoK=Ac7djFNS6Tq+PxejeDMWxGXnYkPjeH6gFxbwa8fH1oYxB=cxBQiZAWAS1IQ==naqQApKYArB8DfOelxxoi=aYYiiYM0De7DK0D807w0DonDa7N8YY4jQCEwwDDi=iVoYxKeD6tyxNgrXr02gWv7RpKtG5BqciG3eA1nwsGIhWxofe=WxQKe5RhVPA3YD; ssxmod_itna2=YqUxuD2D0D90frx4qxRhDQqWui43qT4q0dGMlDeq7tDRDFqApxDHQIrK75rYn7t7DGK=YlEOWiKwDDcYq5L0DDtDFrTpfNx05jGYA3aqc3xcfhDxfE=YPh9eWhOGSWUqWWhSyGxaA17CY70C8WFe2gGaal9HREDak=ii2=0Di87aRppK8=iz1paOBe5xg6PQQevWF8iOR=OHu1SDuxqNiREQxzE1WfHeC=f6RSvQ0tYXq5HLiRi0N35LU97AXr3mP5eCy6K5TVYLPwiajtDHKmymDyafYla4CgcX75j7fTiXN9H0WZYqKCtMrNgYIPQ0/QIxIAxYIcgqZ7f+OGnmp+2xZjKoF0yaj3rPFgKOnUxtf5xN1ORCZPTSKAZtvp37mPD9AWODvaTCEpKEty7oObXC4XKat6BbcAeKQDEiG1ANPeUnAWIUuZDajr23Q4Y0Djju3jYYjWDfDxOteeeEjus7n4jYdCeEjcgtG1nwsCuhWx=nGgfG1D7PYD',
}

params = {
    'chat_id': 'b2f16867-7de3-48b0-95c5-f39d6216a627',
}

json_data = {
    'stream': True,
    'incremental_output': True,
    'chat_id': 'b2f16867-7de3-48b0-95c5-f39d6216a627',
    'chat_mode': 'guest',
    'model': 'qwen3-235b-a22b',
    'parent_id': None,
    'messages': [
        {
            'fid': '5120e064-70cd-4721-911a-3707afc32a15',
            'parentId': None,
            'childrenIds': [
                'a13204be-2d41-4595-a837-9ffab842b691',
            ],
            'role': 'user',
            'content': 'giới thiệu bản thân bạn',
            'user_action': 'chat',
            'files': [],
            'timestamp': 1756736459,
            'models': [
                'qwen3-235b-a22b',
            ],
            'chat_type': 't2t',
            'feature_config': {
                'thinking_enabled': False,
                'output_schema': 'phase',
            },
            'extra': {
                'meta': {
                    'subChatType': 't2t',
                },
            },
            'sub_chat_type': 't2t',
            'parent_id': None,
        },
    ],
    'timestamp': 1756736459,
}

response = requests.post(
    'https://chat.qwen.ai/api/v2/chat/completions',
    params=params,
    cookies=cookies,
    headers=headers,
    json=json_data,
)

# Note: json_data will not be serialized by requests
# exactly as it was in the original request.
#data = '{"stream":true,"incremental_output":true,"chat_id":"b2f16867-7de3-48b0-95c5-f39d6216a627","chat_mode":"guest","model":"qwen3-235b-a22b","parent_id":null,"messages":[{"fid":"5120e064-70cd-4721-911a-3707afc32a15","parentId":null,"childrenIds":["a13204be-2d41-4595-a837-9ffab842b691"],"role":"user","content":"hi","user_action":"chat","files":[],"timestamp":1756736459,"models":["qwen3-235b-a22b"],"chat_type":"t2t","feature_config":{"thinking_enabled":false,"output_schema":"phase"},"extra":{"meta":{"subChatType":"t2t"}},"sub_chat_type":"t2t","parent_id":null}],"timestamp":1756736459}'
#response = requests.post('https://chat.qwen.ai/api/v2/chat/completions', params=params, cookies=cookies, headers=headers, data=data)ai/api/v2/chats/new', cookies=cookies, headers=headers, data=data)
#
# print("Status code:", response.status_code)
response_text = response.text

# Khởi tạo nội dung phản hồi cuối cùng
final_content = ""

# Tách văn bản phản hồi thành các sự kiện SSE riêng lẻ
events = response_text.split("\n\n")

# Xử lý từng sự kiện
for event in events:
    # Bỏ qua các dòng trống hoặc sự kiện không phải dữ liệu
    if not event.startswith("data:"):
        continue

    # Loại bỏ tiền tố "data: " và phân tích cú pháp JSON
    json_str = event.replace("data: ", "").strip()
    try:
        data = json.loads(json_str)

        # Trích xuất choices nếu có
        if "choices" in data:
            for choice in data["choices"]:
                delta = choice.get("delta", {})
                content = delta.get("content", "")
                final_content += content
    except json.JSONDecodeError:
        # Bỏ qua JSON không hợp lệ
        continue

# In ra phản hồi cuối cùng đã được nối
print("", final_content.strip())
