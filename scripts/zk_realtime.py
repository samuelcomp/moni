#!/usr/bin/env python3
import sys
import json
import time
import argparse
from datetime import datetime
from zk import ZK, const

def main():
    parser = argparse.ArgumentParser(description='ZKTeco Device Interface')
    parser.add_argument('command', choices=['test', 'get_attendance', 'get_attendance_by_date', 'live_capture'], 
                        help='Command to execute')
    parser.add_argument('ip', help='Device IP address')
    parser.add_argument('port', type=int, help='Device port')
    parser.add_argument('args', nargs='*', help='Additional arguments')
    
    args = parser.parse_args()
    
    if args.command == 'test':
        test_connection(args.ip, args.port)
    elif args.command == 'get_attendance':
        get_all = len(args.args) > 0 and args.args[0] == 'all'
        get_attendance(args.ip, args.port, get_all)
    elif args.command == 'get_attendance_by_date':
        if len(args.args) >= 2:
            get_attendance_by_date(args.ip, args.port, args.args[0], args.args[1])
        else:
            print(json.dumps({
                'status': 'error',
                'message': 'Start and end dates are required'
            }))
    elif args.command == 'live_capture':
        live_capture(args.ip, args.port)

def test_connection(ip, port):
    try:
        zk = ZK(ip, port=port, timeout=5)
        conn = zk.connect()
        
        if conn:
            info = {
                'status': 'success',
                'message': 'Connection successful',
                'firmware_version': conn.get_firmware_version(),
                'serial_number': conn.get_serialnumber(),
                'platform': conn.get_platform(),
                'device_name': conn.get_device_name(),
                'face_algorithm_version': conn.get_face_version() if hasattr(conn, 'get_face_version') else None
            }
            conn.disconnect()
            print(json.dumps(info))
        else:
            print(json.dumps({
                'status': 'error',
                'message': 'Failed to connect to device'
            }))
    except Exception as e:
        print(json.dumps({
            'status': 'error',
            'message': str(e)
        }))

def get_attendance(ip, port, get_all=False):
    try:
        zk = ZK(ip, port=port, timeout=5)
        conn = zk.connect()
        
        if conn:
            attendance = conn.get_attendance()
            
            # Convert attendance records to JSON-serializable format
            records = []
            for record in attendance:
                records.append({
                    'id': record.uid,
                    'uid': record.user_id,
                    'timestamp': record.timestamp.strftime('%Y-%m-%d %H:%M:%S'),
                    'status': record.status,
                    'punch': record.punch
                })
            
            conn.disconnect()
            
            print(json.dumps({
                'status': 'success',
                'message': 'Successfully retrieved attendance logs',
                'records': records
            }))
        else:
            print(json.dumps({
                'status': 'error',
                'message': 'Failed to connect to device'
            }))
    except Exception as e:
        print(json.dumps({
            'status': 'error',
            'message': str(e)
        }))

def get_attendance_by_date(ip, port, start_date, end_date):
    try:
        zk = ZK(ip, port=port, timeout=5)
        conn = zk.connect()
        
        if conn:
            attendance = conn.get_attendance()
            
            # Parse start and end dates
            start_dt = datetime.strptime(start_date, '%Y-%m-%d')
            end_dt = datetime.strptime(end_date + ' 23:59:59', '%Y-%m-%d %H:%M:%S')
            
            # Filter records by date range
            filtered_records = []
            for record in attendance:
                if start_dt <= record.timestamp <= end_dt:
                    filtered_records.append({
                        'id': record.uid,
                        'uid': record.user_id,
                        'timestamp': record.timestamp.strftime('%Y-%m-%d %H:%M:%S'),
                        'status': record.status,
                        'punch': record.punch
                    })
            
            conn.disconnect()
            
            print(json.dumps({
                'status': 'success',
                'message': 'Successfully retrieved attendance logs',
                'records': filtered_records,
                'total_records': len(filtered_records)
            }))
        else:
            print(json.dumps({
                'status': 'error',
                'message': 'Failed to connect to device'
            }))
    except Exception as e:
        print(json.dumps({
            'status': 'error',
            'message': str(e)
        }))

def live_capture(ip, port):
    try:
        zk = ZK(ip, port=port, timeout=5)
        conn = zk.connect()
        
        if conn:
            # Enable real-time events
            conn.enable_device()
            
            # Print connection success message
            print(json.dumps({
                'status': 'connected',
                'message': 'Successfully connected to device'
            }))
            sys.stdout.flush()  # Ensure the message is sent immediately
            
            # Start monitoring real-time events
            for event in conn.live_capture():
                if event:
                    # Format the event data
                    event_data = {
                        'uid': event.user_id,
                        'timestamp': event.timestamp.strftime('%Y-%m-%d %H:%M:%S'),
                        'status': event.status,
                        'punch': event.punch,
                        'event_type': 'attendance'
                    }
                    
                    # Print the event as JSON
                    print(json.dumps({
                        'status': 'event',
                        'data': event_data
                    }))
                    sys.stdout.flush()  # Ensure the event is sent immediately
                
                # Small delay to prevent CPU hogging
                time.sleep(0.1)
            
            # Disable the device when done
            conn.disable_device()
            conn.disconnect()
            
            # Print completion message
            print(json.dumps({
                'status': 'completed',
                'message': 'Live capture completed'
            }))
        else:
            print(json.dumps({
                'status': 'error',
                'message': 'Failed to connect to device'
            }))
    except KeyboardInterrupt:
        # Handle manual interruption
        if conn:
            conn.disable_device()
            conn.disconnect()
        print(json.dumps({
            'status': 'completed',
            'message': 'Live capture stopped by user'
        }))
    except Exception as e:
        # Handle other exceptions
        if conn:
            try:
                conn.disable_device()
                conn.disconnect()
            except:
                pass
        print(json.dumps({
            'status': 'error',
            'message': str(e)
        }))

if __name__ == "__main__":
    main() 