#!/bin/sh
set -e

# Fix permissions for prometheus data directory
if [ -d /prometheus ]; then
    chmod -R 777 /prometheus 2>/dev/null || true
    # Create queries.active file with proper permissions before Prometheus starts
    touch /prometheus/queries.active 2>/dev/null || true
    chmod 666 /prometheus/queries.active 2>/dev/null || true
    # Ensure directory is writable
    chmod 777 /prometheus 2>/dev/null || true
fi

# Run prometheus as root (for development)
exec /bin/prometheus "$@"

