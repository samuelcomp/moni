import sys
import socket
import json

def test_connection(ip, port, timeout=5):
    try:
        # Create a socket object
        s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        s.settimeout(timeout)
        
        # Connect to the device
        result = s.connect_ex((ip, int(port)))
        
        if result == 0:
            print(json.dumps({
                "status": "success",
                "message": "Connection successful"
            }))
        else:
            print(json.dumps({
                "status": "error",
                "message": f"Connection failed (error code: {result})"
            }))
    except socket.timeout:
        print(json.dumps({
            "status": "error",
            "message": "Connection timed out"
        }))
    except Exception as e:
        print(json.dumps({
            "status": "error",
            "message": f"Error: {str(e)}"
        }))
    finally:
        s.close()

if __name__ == "__main__":
    if len(sys.argv) < 3:
        print(json.dumps({
            "status": "error",
            "message": "Usage: python test_device.py IP_ADDRESS PORT [TIMEOUT]"
        }))
        sys.exit(1)
    
    ip = sys.argv[1]
    port = sys.argv[2]
    timeout = int(sys.argv[3]) if len(sys.argv) > 3 else 5
    
    test_connection(ip, port, timeout) 