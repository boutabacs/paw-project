import json
import os
from http.server import SimpleHTTPRequestHandler, HTTPServer

DATA_FILE = "students.json"

def load_students():
    if not os.path.exists(DATA_FILE):
        return []
    try:
        with open(DATA_FILE, "r", encoding="utf-8") as f:
            return json.load(f)
    except Exception:
        return []

def save_students(students):
    with open(DATA_FILE, "w", encoding="utf-8") as f:
        json.dump(students, f, ensure_ascii=False, indent=2)

class Handler(SimpleHTTPRequestHandler):
    def _send_json(self, obj, status=200):
        data = json.dumps(obj).encode("utf-8")
        self.send_response(status)
        self.send_header("Content-Type", "application/json; charset=utf-8")
        self.send_header("Content-Length", str(len(data)))
        self.end_headers()
        self.wfile.write(data)

    def do_POST(self):
        if self.path == "/api/students":
            length = int(self.headers.get("Content-Length", 0))
            raw = self.rfile.read(length)
            try:
                payload = json.loads(raw.decode("utf-8"))
            except Exception:
                return self._send_json({"error": "Invalid JSON"}, status=400)

            required = ["studentId", "lastName", "firstName", "email"]
            if not all(k in payload and str(payload[k]).strip() for k in required):
                return self._send_json({"error": "Missing required fields"}, status=400)

            # Ensure course field exists for table rendering
            if not payload.get("course"):
                payload["course"] = "CS101"

            students = load_students()
            # prevent duplicates by studentId
            existing_ids = {s.get("studentId") for s in students}
            if payload["studentId"] in existing_ids:
                # update existing record
                students = [payload if s.get("studentId") == payload["studentId"] else s for s in students]
            else:
                students.append(payload)
            save_students(students)
            return self._send_json({"ok": True, "student": payload})
        else:
            return super().do_POST()

def run(addr="127.0.0.1", port=8000):
    httpd = HTTPServer((addr, port), Handler)
    print(f"Server running at http://{addr}:{port}/")
    try:
        httpd.serve_forever()
    except KeyboardInterrupt:
        pass
    finally:
        httpd.server_close()

if __name__ == "__main__":
    run()