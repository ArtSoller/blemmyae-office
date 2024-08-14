FROM varnish:7.4

# Graphql Api configuration
COPY default.vcl /etc/varnish/

# Install build dependencies
USER root
RUN set -e; \
    apt-get update; \
    apt-get -y install $VMOD_DEPS /pkgs/*.deb; \
    install-vmod https://github.com/varnish/varnish-modules/releases/download/0.23.0/varnish-modules-0.23.0.tar.gz;

## Install varnish-exporter to expose mapped for prometheus varnishstat output
RUN mkdir -p /opt/prometheus_varnish_exporter \
    && cd /opt/prometheus_varnish_exporter \
    && curl -L -O https://github.com/jonnenauha/prometheus_varnish_exporter/releases/download/1.6.1/prometheus_varnish_exporter-1.6.1.linux-amd64.tar.gz \
    && tar zxfv prometheus_varnish_exporter-1.6.1.linux-amd64.tar.gz \
    && ln -s /opt/prometheus_varnish_exporter/prometheus_varnish_exporter-1.6.1.linux-amd64/prometheus_varnish_exporter /usr/local/bin/;

RUN set -e; \
    apt-get -y purge --auto-remove $VMOD_DEPS varnish-dev; \
    rm -rf /var/lib/apt/lists/*

COPY entrypoint.sh /usr/local/bin/

RUN chmod +x /usr/local/bin/entrypoint.sh

USER varnish

# Port for prometheus exporter
EXPOSE 9131

## Override max req/response sizes
# - headers may have a lot of tags(x-key)
# - body size may be big due to LQIPs inside response body
# - api aws ilb timeouts

# Provide args to entrypoint (/usr/local/bin/docker-varnish-entrypoint), @see: https://github.com/varnish/docker-varnish/blob/master/next/alpine/scripts/docker-varnish-entrypoint
# Docs: https://varnish-cache.org/docs/5.1/reference/varnishd.html
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

CMD [ \
    "-p", "http_req_hdr_len=128k", \
    "-p", "http_req_size=5M", \
    "-p", "workspace_client=5M", \
    "-p", "http_resp_hdr_len=128k", \
    "-p", "http_resp_size=5M", \
    "-p", "workspace_backend=5M", \
    "-p", "backend_idle_timeout=900s", \
    "-p", "between_bytes_timeout=900s", \
    "-p", "first_byte_timeout=900s" \
]
