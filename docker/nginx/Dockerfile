ARG nginx_image=361493004146.dkr.ecr.us-east-2.amazonaws.com/proxy-http3:latest
ARG alpine_image=alpine:latest
#@todo: Fix mount type for secrets, such as '/certs/'

FROM $alpine_image as builder
WORKDIR app

COPY . .

# Cloudflare real ip.
RUN wget --output-document=ips-v4.map https://www.cloudflare.com/ips-v4 \
    && wget --output-document=ips-v6.map https://www.cloudflare.com/ips-v6 \
    && chmod +x ./cloudflare-ip-whitelist-sync.sh \
    && sh ./cloudflare-ip-whitelist-sync.sh

WORKDIR certs

# Generate cert & key, also replace env from server.conf.
RUN apk add openssl \
    # dirty hack for ecs
    && openssl req -nodes -x509 -newkey rsa:4096 -keyout .key -out .crt -sha256 -days 365 -subj "/C=GB/ST=London/L=London/O=Global Security/OU=IT Department/CN=www.example.com" \
    && for domain in cms cms-preprod cms-qa1 cms-qa2 api api-preprod api-qa1 api-qa2; do openssl req -nodes -x509 -newkey rsa:4096 -keyout $domain.cyberriskalliance.com.key -out $domain.cyberriskalliance.com.crt -sha256 -days 365 -subj "/C=GB/ST=London/L=London/O=Global Security/OU=IT Department/CN=$domain.cyberriskalliance.com"; done
RUN apk add curl \
    && curl https://ssl-config.mozilla.org/ffdhe2048.txt > dhparam

FROM $nginx_image as runner
ARG ENVIRONMENT
ENV ENVIRONMENT=$ENVIRONMENT

COPY --from=builder /app/*.map /etc/nginx/
# Disable ipv6 until elb is replaced with nlb
COPY --from=builder /app/sysctl.conf /etc/sysctl.conf

# Copy configs.
COPY --from=builder /app/nginx.conf /etc/nginx/nginx.conf
COPY --from=builder /app/server.default.conf /home/nginx/server.default.conf
COPY --from=builder /app/server.$ENVIRONMENT.conf /home/nginx/server.conf
COPY --from=builder /app/includes/ /etc/nginx/includes/

# Copy self-signed SSL certificates.
COPY --chown=nginx:nginx certs/ /home/nginx/ssl/
COPY --chown=nginx:nginx --from=builder /app/certs/ /home/nginx/ssl/

USER nginx

RUN ulimit -n 65536

EXPOSE 80 443
