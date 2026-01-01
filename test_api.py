import requests
import json

url = "http://127.0.0.1:8000/api/v1/auth/login"
payload = {
    "email": "admin@digiskul.app",
    "password": "password"
}
headers = {
    "Content-Type": "application/json",
    "Accept": "application/json"
}

try:
    response = requests.post(url, data=json.dumps(payload), headers=headers)
    print(f"Status Code: {response.status_code}")
    print(f"Response Body: {response.text}")
except Exception as e:
    print(f"Error: {e}")
