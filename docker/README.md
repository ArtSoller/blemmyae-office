# Somewhat docs

## Generating local certificates

### Allow to edit hosts file

```sh
sudo -S chmod 777 /etc/hosts
```

#### Add domains

```sh
for domain in cms-local api-local; do sudo echo "127.0.0.1 $domain.cyberriskalliance.com" >> /etc/hosts; done
```

### Restore hosts file permissions and run mkcert

```sh
sudo -S chmod 644 /etc/hosts
mkcert --install
```

#### Create local certificates

```sh
for domain in cms-local api-local; do mkcert -key-file nginx/certs/$domain.cyberriskalliance.com.key -cert-file nginx/certs/$domain.cyberriskalliance.com.crt $domain.cyberriskalliance.com; done
```

## DB Import

1. Find your container id of __mysql__ via _Docker Desktop_ dashboard or `docker ps`
2. Copy files via `docker cp`, ex: `docker cp backup.sql.gz blemmyae-mysql-1:backup.sql.gz`
3. Connect to the container `docker exec -it blemmyae-mysql-1 /bin/bash`
4. Import db `gunzip < backup.sql.gz | mysql -u bn_wordpress -pbitnami blemmyae`. You can find your credentials in __docker-compose.yml__.

or

1. In Dockerfile add `COPY blemmyae_1707384631.sql.gz /opt/bitnami/wordpress/blemmyae_1707384631.sql.gz`
2. Example `pv /opt/bitnami/wordpress/blemmyae_1707384631.sql.gz | gzip -dc | pv | mysql -h mysql -u bn_wordpress -pbitnami blemmyae` from cms/api container

## Test http3 configuration

Ref: https://http3.is/ \
Right now doesn't work due to bug in https://github.com/macbre/docker-nginx-http3/issues/100

```sh
docker pull rmarx/curl-http3:latest
docker run -it --rm rmarx/curl-http3 curl -IL https://cms-local.cyberriskalliance.com/host --http3
docker run -it --rm rmarx/curl-http3 curl -IL https://cms-api.cyberriskalliance.com/host --http3
```
