#!/bin/bash
set -e

: "${USERNAME:=postgres}"
: "${PUID:=1000}"
: "${PGID:=1000}"

current_gid=$(id -g $USERNAME)
if [ "${PGID}" != "automatic" ] && [ "$PGID" != "$current_gid" ]; then

  echo "修改 GID: $current_gid => $PGID"

  if getent group $PGID > /dev/null 2>&1; then
    echo "已存在 GID， 修改主GID"
    usermod -g $PGID $USERNAME
  else
    GROUPNAME="$(id -gn $USERNAME)"
    groupmod --gid $PGID ${GROUPNAME}
  fi

  chown -R $PUID:$PGID /var/lib/postgresql/data
fi

current_uid=$(id -u $USERNAME)
if [ "${PUID}" != "automatic" ] && [ "$PUID" != "$current_uid" ]; then

  echo "修改 UID: $current_uid => $PUID"

  usermod --uid $PUID $USERNAME

  chown -R $PUID:$PGID /var/lib/postgresql/data
fi

exec "$@"
