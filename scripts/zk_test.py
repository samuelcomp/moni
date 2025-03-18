#!/usr/bin/env python
import argparse
import json
import sys
import time
from datetime import datetime

# Parse command line arguments
parser = argparse.ArgumentParser(description='Test ZKTeco connection')
parser.add_argument('--ip', required=True, help='Device IP address')
parser.add_argument('--port', required=True, help='Device port')
args = parser.parse_args()

try:
    # Try to import the zk library
    try:
        from zk import ZK, const
    except ImportError:
        print(json.dumps({
            "error": "ZK library not installed. Please install it with: pip install pyzk"
        }))
        sys.exit(1)

    # Connect to device
    conn = None
    zk = ZK(args.ip, port=int(args.port), timeout=5)
    
    try:
        # Connect to device
        conn = zk.connect()
        
        # Get device info
        device_info = {
            "firmware_version": conn.get_firmware_version(),
            "serial_number": conn.get_serialnumber(),
            "platform": conn.get_platform(),
            "device_name": conn.get_device_name(),
            "face_algorithm_version": conn.get_face_version() if hasattr(conn, 'get_face_version') else None,
            "fingerprint_algorithm_version": conn.get_fp_version() if hasattr(conn, 'get_fp_version') else None
        }
        
        # Get users
        users_data = []
        users = conn.get_users()
        for user in users:
            users_data.append({
                "user_id": user.user_id,
                "name": user.name,
                "role": user.privilege,
                "password": user.password,
                "card": user.card,
                "group_id": user.group_id
            })
        
        # Get attendance records
        attendance_data = []
        attendances = conn.get_attendance()
        for attendance in attendances:
            attendance_data.append({
                "user_id": attendance.user_id,
                "timestamp": attendance.timestamp.strftime('%Y-%m-%d %H:%M:%S'),
                "status": attendance.status,
                "punch": attendance.punch
            })
        
        # Return data as JSON
        result = {
            "device_info": device_info,
            "users": users_data,
            "attendance": attendance_data
        }
        
        print(json.dumps(result, indent=2))
        
    except Exception as e:
        print(json.dumps({
            "error": str(e)
        }))
    finally:
        if conn:
            conn.disconnect()

except Exception as e:
    print(json.dumps({
        "error": str(e)
    }))