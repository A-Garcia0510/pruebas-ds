#!/usr/bin/env python3
import requests
import json

def test_api():
    try:
        # Test the redeem endpoint
        url = "http://localhost:8000/api/v1/loyalty/redeem-reward"
        data = {
            "user_id": 1,
            "reward_id": 1
        }
        
        print("Testing API endpoint:", url)
        print("Sending data:", json.dumps(data, indent=2))
        
        response = requests.post(url, json=data, timeout=10)
        
        print(f"Status Code: {response.status_code}")
        print(f"Response Headers: {dict(response.headers)}")
        print(f"Response Text: {response.text}")
        
        if response.status_code == 200:
            try:
                json_response = response.json()
                print(f"JSON Response: {json.dumps(json_response, indent=2)}")
                return True
            except json.JSONDecodeError as e:
                print(f"Error decoding JSON: {e}")
                return False
        else:
            print(f"HTTP Error: {response.status_code}")
            return False
            
    except requests.exceptions.ConnectionError:
        print("Connection Error: Could not connect to the API server")
        print("Make sure the Python FastAPI server is running on localhost:8000")
        return False
    except Exception as e:
        print(f"Unexpected error: {e}")
        return False

if __name__ == "__main__":
    success = test_api()
    if success:
        print("\n✅ API test completed successfully")
    else:
        print("\n❌ API test failed") 