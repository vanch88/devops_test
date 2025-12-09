#!/bin/sh
set -e

# Fix permissions for grafana data directory
if [ -d /var/lib/grafana ]; then
    chown -R 472:472 /var/lib/grafana 2>/dev/null || true
    chmod -R 777 /var/lib/grafana 2>/dev/null || true
fi

# Create necessary directories
mkdir -p /var/lib/grafana/plugins 2>/dev/null || true
chown -R 472:472 /var/lib/grafana/plugins 2>/dev/null || true
chmod -R 777 /var/lib/grafana/plugins 2>/dev/null || true

# Run original grafana entrypoint which will switch to grafana user
exec /run.sh "$@"

