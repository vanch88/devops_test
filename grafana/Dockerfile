FROM grafana/grafana:latest

COPY --chmod=755 ./grafana/entrypoint.sh /entrypoint-custom.sh

ENTRYPOINT ["/entrypoint-custom.sh"]

