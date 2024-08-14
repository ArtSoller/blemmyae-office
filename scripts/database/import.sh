#!/bin/bash

# Directories.
SCRIPTNAME="$(basename "${BASH_SOURCE[0]}")"
SCRIPTPATH="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT="$(dirname "${SCRIPTPATH}")"
NAME="${SCRIPTPATH:${#ROOT}+1}"

# Read configs.
source "${SCRIPTPATH}/config.sh"

SRC_ENVIRONMENT="$1"
FILE_NAME="$2"
DB_NAME="$3"
REINDEX="$4"

# Exit if the dump environment wasn't provided.
[ -z "$SRC_ENVIRONMENT" ] && echo "ERROR: Argument SRC_ENVIRONMENT not provided to script" && exit 1
# Exit if the dump name wasn't provided.
[ -z "$FILE_NAME" ] && echo "ERROR: Argument FILE_NAME not provided to script" && exit 1
# Check if the database name is provided.
if [ -z "$DB_NAME" ]; then
  echo "NOTICE: Argument WORDPRESS_DATABASE_NAME not provided to script"
else
  echo "NOTICE: Argument WORDPRESS_DATABASE_NAME provided to script"
  WORDPRESS_DATABASE_NAME="$DB_NAME"
fi

# Show which database will be used in a script.
echo "Using database: $WORDPRESS_DATABASE_NAME"

TEMP_DIR="${SCRIPTPATH}/temp"
mkdir "$TEMP_DIR"

FILEPATH="$TEMP_DIR"/"$FILE_NAME"
FILEPATH_S3=s3://"$S3_BACKUP_BUCKET"/"$SRC_ENVIRONMENT"/"$FILE_NAME"

echo "$FILE_NAME download - Start"
aws s3 cp "$FILEPATH_S3" "$FILEPATH"
[ ! -f "$FILEPATH" ] && echo "ERROR: $FILE_NAME was not downloaded" && exit 1
echo "$FILE_NAME download - End"

echo "$WORDPRESS_DATABASE_NAME import - Start"
pv "$FILEPATH" | gzip -dc | pv | mariadb -h "$WORDPRESS_DATABASE_HOST" -u "$WORDPRESS_DATABASE_USER" -p"$WORDPRESS_DATABASE_PASSWORD" "$WORDPRESS_DATABASE_NAME"
echo "$WORDPRESS_DATABASE_NAME import - End"

echo "Run Post DB reset WP commands - Start"
# @todo: Strip allow-root params.
wp cache flush --allow-root
wp ri run --allow-root
wp cache flush --allow-root
if [ -z "$REINDEX" ]; then
  echo "NOTICE: Argument REINDEX not provided to script"
else
  echo "NOTICE: Argument REINDEX provided to script"
  wp elasticpress index --setup --yes --allow-root
  wp cache flush --allow-root
fi
echo "Run Post DB reset WP commands - End"

# Cleanup.
rm -r "$TEMP_DIR"

exit 0
