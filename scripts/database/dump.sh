#!/bin/bash

# Directories.
SCRIPTNAME="$(basename "${BASH_SOURCE[0]}")"
SCRIPTPATH="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT="$(dirname "${SCRIPTPATH}")"
NAME="${SCRIPTPATH:${#ROOT}+1}"

# Read configs.
source "${SCRIPTPATH}/config.sh"

TEMP_DIR="${SCRIPTPATH}/temp"
mkdir "$TEMP_DIR"

FILENAME="$WORDPRESS_DATABASE_NAME"_"$(date +%s)".sql.gz
FILEPATH=./"$TEMP_DIR"/"$FILENAME"

echo "$WORDPRESS_DATABASE_NAME dump - Start"
mariadb-dump -h "$WORDPRESS_DATABASE_HOST" -u "$WORDPRESS_DATABASE_USER" -p"$WORDPRESS_DATABASE_PASSWORD" --single-transaction --quick "$WORDPRESS_DATABASE_NAME" | pv | gzip -9 >"$FILEPATH"
echo "$WORDPRESS_DATABASE_NAME dump - End"

FILEPATH_S3=s3://"$S3_BACKUP_BUCKET"/"$ENVIRONMENT_NAME"/"$FILENAME"

echo "$FILENAME offload - Start"
aws s3 cp "$FILEPATH" "$FILEPATH_S3"
aws s3 presign "$FILEPATH_S3"
echo "$FILENAME offload - End"

# Cleanup.
rm -r "$TEMP_DIR"

exit 0
