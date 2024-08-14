#!/bin/bash

# Exit if the secret wasn't populated by the ECS agent
[ -z "$WP_SECRET" ] && echo "Secret WP_SECRET not populated in environment" && exit 1

# Wordpress
export WORDPRESS_DATABASE_HOST=$(echo "$WP_SECRET" | jq -r '.host')
export WORDPRESS_DATABASE_PORT_NUMBER=$(echo "$WP_SECRET" | jq -r '.port')
export WORDPRESS_DATABASE_NAME=$(echo "$WP_SECRET" | jq -r '.dbname')
export WORDPRESS_DATABASE_USER=$(echo "$WP_SECRET" | jq -r '.username')
export WORDPRESS_DATABASE_PASSWORD=$(echo "$WP_SECRET" | jq -r '.password')

# S3 DB Backups account
export AWS_ACCESS_KEY_ID=$(echo "$WP_SECRET" | jq -r '.s3AwsAccessKeyId')
export AWS_SECRET_ACCESS_KEY=$(echo "$WP_SECRET" | jq -r '.s3AwsSecretAccess')
export AWS_DEFAULT_REGION=$(echo "$WP_SECRET" | jq -r '.s3AwsDefaultRegion')

export S3_BACKUP_BUCKET=cra-portal-backend-backup

# Define current environment.
ENVIRONMENT_NAME="$COPILOT_ENVIRONMENT_NAME"
if [ -z "$ENVIRONMENT_NAME" ]; then
  ENVIRONMENT_NAME=undefined
fi
export ENVIRONMENT_NAME
