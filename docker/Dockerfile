ARG ubuntu_image=ubuntu:latest
ARG bitnami_image=public.ecr.aws/bitnami/wordpress:6.4.2-debian-11-r2

FROM $ubuntu_image as deps
RUN apt-get update \
    && apt-get install --no-install-recommends --yes \
    ca-certificates \
    curl \
    unzip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*
# Downlod jq & aws cli install sources
RUN curl -Lo /usr/local/bin/jq https://github.com/stedolan/jq/releases/download/jq-1.6/jq-linux64 \
    && chmod +0755 /usr/local/bin/jq \
    && curl -Lo awscliv2.zip "https://awscli.amazonaws.com/awscli-exe-linux-x86_64.zip" \
    && unzip awscliv2.zip -x "aws/dist/awscli/examples/*"

WORKDIR /app
COPY docker/version.sh docker/version.sh
RUN bash docker/version.sh

WORKDIR /app
# Copy sources
COPY scripts/ scripts/
COPY packages/ packages/
COPY vendor/ vendor/
COPY wp-admin/ wp-admin/
COPY wp-content/mu-plugins/ wp-content/mu-plugins/
COPY wp-content/plugins/ wp-content/plugins/
COPY wp-content/themes/ wp-content/themes/
COPY wp-content/db.php wp-content/db.php
# TODO: add Redis support for the local docker-composer.yml.
# Comment out the following line for the local build.
COPY wp-content/plugins/redis-cache/includes/object-cache.php wp-content/object-cache.php
COPY db-config.php db-config.php
# Persisted files
COPY robots.txt robots.txt
# Remove redundant .htaccess files
RUN find . -name "*.htaccess" -type f -delete

FROM $bitnami_image as base_runner
## Change user to perform privileged actions
USER 0
RUN apt-get update \
    && apt-get install --no-install-recommends --yes \
    bash-completion \
    ca-certificates \
    fzf \
    git \
    vim \
    zsh \
    default-mysql-client \
    pv \
    libnss-wrapper \
    build-essential \
    libtool \
    autoconf \
    unzip \
    wget \
    && apt-get clean  \
    && rm -rf /var/lib/apt/lists/*

# Install extra PHP extensions.
RUN pecl install --onlyreqdeps redis igbinary
RUN echo "extension = redis" >> /opt/bitnami/php/etc/php.ini
RUN echo "extension = igbinary.so" >> /opt/bitnami/php/etc/php.ini

# Fix `zend_mm_heap corrupted` error
# RUN echo "output_buffering = 4096" >> /opt/bitnami/php/etc/php.ini
RUN echo "max_execution_time = 300" >> /opt/bitnami/php/etc/php.ini
RUN mkdir -p /home/blemmyae \
    && useradd -u 1001 -g 0 blemmyae -p blemmyae \
    && chmod 751 /home/blemmyae \
    && chsh blemmyae -s $(which zsh) \
    && echo ZSH_THEME="agnoster" >> ~/.zshrc \
    && echo exec zsh >> /.bashrc \
    && sh -c "$(wget https://raw.github.com/ohmyzsh/ohmyzsh/master/tools/install.sh -O -)"

FROM base_runner as runner
## Change user to perform privileged actions
USER 0
COPY --from=deps /usr/local/bin/jq /usr/bin/jq
COPY --from=deps /aws /aws
RUN /aws/install \
    && rm -r /aws

## Copilot - deploy script & dependencies
COPY --from=deps --chown=1001 /app/scripts /opt/copilot/scripts
## Custom wordpress content
COPY --from=deps /app/packages /opt/bitnami/wordpress/packages
COPY --from=deps /app/vendor /opt/bitnami/wordpress/vendor
COPY --from=deps /app/wp-admin /opt/bitnami/wordpress/wp-admin
RUN rm -rf /opt/bitnami/wordpress/wp-content
COPY --from=deps /app/wp-content /opt/bitnami/wordpress/wp-content
COPY --from=deps /app/robots.txt /opt/bitnami/wordpress/robots.txt
COPY --from=deps /app/db-config.php /opt/bitnami/wordpress/db-config.php
RUN mkdir /opt/bitnami/wordpress/wp-content/uploads \
    && chmod 755 -R /opt/bitnami/wordpress/wp-content \
    && chmod 644 /opt/bitnami/wordpress/wp-config.php \
    && chmod 644 /opt/bitnami/wordpress/db-config.php \
    && chown -R 1001 /opt/bitnami/wordpress

RUN export RUNTIME_PWD=$(pwd) && cd /opt/bitnami/wordpress \
    && ls -d packages/* | cut -f2 -d '/' | xargs -I % sh -c '(rm wp-content/plugins/% || echo "[%] symlink was already purged or does not exist") && ln -s /opt/bitnami/wordpress/packages/% wp-content/plugins/%' \
    && cd $RUNTIME_PWD && unset RUNTIME_PWD

## Use bash instead of default shell
RUN ln -sf /bin/bash /bin/sh

## Revert to the original non-root user
USER 1001

ENTRYPOINT ["/bin/sh", "-c"]
CMD ["/opt/copilot/scripts/startup.sh"]
EXPOSE 8080
