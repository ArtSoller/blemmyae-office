# Reverse proxy:

```shell
export AWS_ACCESS_KEY_ID=XXXXX
export AWS_SECRET_ACCESS_KEY=XXXXX
export AWS_DEFAULT_REGION=us-east-2

aws ecr get-login-password --region us-east-2 | docker login --username AWS --password-stdin 361493004146.dkr.ecr.us-east-2.amazonaws.com

cd docker/nginx

docker build --no-cache --build-arg ENVIRONMENT=aws -t cra-portal-backend/proxy .
docker tag cra-portal-backend/proxy:latest 361493004146.dkr.ecr.us-east-2.amazonaws.com/cra-portal-backend/proxy:latest
docker push 361493004146.dkr.ecr.us-east-2.amazonaws.com/cra-portal-backend/proxy:latest
```
