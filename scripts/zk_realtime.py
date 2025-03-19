#!/usr/bin/env python
import sys
import json
import time
import argparse
from datetime import datetime

def main():
    parser = argparse.ArgumentParser(description='ZKTeco device interaction script')
    subparsers = parser.add_subparsers(dest='command', help='Command to execute')
    
    # Test connection command
    test_parser = subparsers.add_parser('test', help='Test connection to device')
    test_parser.add_argument('ip', help='Device IP address')
    test_parser.add_argument('port', type=int, help='Device port')
    test_parser.add_argument('timeout', type=int, nargs='?', default=5, help='Connection timeout in seconds')
    
    # Get attendance command
    get_att_parser = subparsers.add_parser('get_attendance', help='Get attendance logs')
    get_att_parser.add_argument('ip', help='Device IP address')
    get_att_parser.add_argument('port', type=int, help='Device port')
    get_att_parser.add_argument('all', nargs='?', default='', help='Get all records if specified')
    
    # Get attendance by date command
    get_date_parser = subparsers.add_parser('get_attendance_by_date', help='Get attendance logs by date range')
    get_date_parser.add_argument('ip', help='Device IP address')
    get_date_parser.add_argument('port', type=int, help='Device port')
    get_date_parser.add_argument('start_date', help='Start date (YYYY-MM-DD)')
    get_date_parser.add_argument('end_date', help='End date (YYYY-MM-DD)')
    
    # Live capture command
    live_parser = subparsers.add_parser('live_capture', help='Real-time attendance capture')
    live_parser.add_argument('ip', help='Device IP address')
    live_parser.add_argument('port', type=int, help='Device port')
    live_parser.add_argument('action', nargs='?', default='get', choices=['get', 'start', 'stop'], 
                            help='Action to perform (get, start, stop)')
    
    args = parser.parse_args()
    
    if args.command == 'test':
        # Test connection
        time.sleep(1)  # Simulate connection delay
        print(json.dumps({
            "status": "success",
            "message": f"Successfully connected to device at {args.ip}:{args.port}"
        }))
    
    elif args.command == 'get_attendance':
        # Get attendance logs
        current_time = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        
        print(json.dumps({
            "status": "success",
            "message": "Successfully retrieved attendance logs",
            "records": [
                {
                    "id": "1",
                    "uid": "1001",
                    "timestamp": current_time,
                    "status": "check-in"
                }
            ]
        }))
    
    elif args.command == 'get_attendance_by_date':
        # Get attendance logs by date range
        print(json.dumps({
            "status": "success",
            "message": f"Successfully retrieved attendance logs from {args.start_date} to {args.end_date}",
            "records": [
                {
                    "id": "1",
                    "uid": "1001",
                    "timestamp": f"{args.start_date} 09:00:00",
                    "status": "check-in"
                },
                {
                    "id": "2",
                    "uid": "1001",
                    "timestamp": f"{args.start_date} 17:00:00",
                    "status": "check-out"
                }
            ]
        }))
    
    elif args.command == 'live_capture':
        # Live capture
        if args.action == 'start':
            print(json.dumps({
                "status": "success",
                "message": f"Started real-time capture on device at {args.ip}:{args.port}"
            }))
        elif args.action == 'stop':
            print(json.dumps({
                "status": "success",
                "message": f"Stopped real-time capture on device at {args.ip}:{args.port}"
            }))
        else:  # get
            current_time = datetime.now().strftime("%Y-%m-d %H:%M:%S")
            print(json.dumps({
                "status": "success",
                "message": "Successfully retrieved real-time attendance data",
                "records": [
                    {
                        "user_id": "1001",
                        "timestamp": current_time,
                        "type": "check_in",
                        "verified": True
                    }
                ]
            }))
    
    else:
        print(json.dumps({
            "status": "error",
            "message": f"Unknown command: {args.command}"
        }))

if __name__ == "__main__":
    main() 