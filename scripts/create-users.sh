#!/bin/bash

ROLE=("$1")
shift
USERS=("$@")

# Exit if users role wasn't provided.
[ -z "$ROLE" ] && echo "ERROR: Argument ROLE not provided to script" && exit 1
# Exit if users array wasn't provided.
[ -z "$USERS" ] && echo "ERROR: Argument USERS array not provided to script" && exit 1

# shellcheck disable=SC2068
for USER in ${USERS[@]}; do
  echo "$USER"
  login=$(echo "$USER" | cut -d'@' -f1)
  echo "$login"
  wp user create "${login}" "${USER}" --role="${ROLE}" --send-email --allow-root

  # Check the exit status of the 'wp user create' command.
  if [ $? -eq 0 ]; then
      echo "User created successfully."
  else
      echo "Failed to create user. Trying to update user role."
      wp user set-role "${USER}" "${ROLE}" --allow-root
  fi
done
