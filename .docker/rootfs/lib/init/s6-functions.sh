

is_unprivileged() {
    USER_ID=${PUID:-0}
    GROUP_ID=${PGID:-0}
    if ${SSL}; then
        LISTEN_PORT=${HTTP_PORT:-443}
    else
        LISTEN_PORT=${HTTP_PORT:-80}
    fi

    if ! [[ "${USER_ID}" =~ ^[0-9]+$ ]]; then
        echo "ERROR: USER_ID=${USER_ID} is not valid." >&2
        exit 1
    fi

    if ! [[ "${GROUP_ID}" =~ ^[0-9]+$ ]]; then
        echo "ERROR: GROUP_ID=${GROUP_ID} is not valid." >&2
        exit 1
    fi

    if ! [[ "${LISTEN_PORT}" =~ ^[0-9]+$ ]]; then
        echo "ERROR: LISTEN_PORT=${LISTEN_PORT} is not valid." >&2
        exit 1
    fi

    USER_ID=$((10#${USER_ID:-0}))
    GROUP_ID=$((10#${GROUP_ID:-0}))
    LISTEN_PORT=$((10#${LISTEN_PORT:-80}))
    export USER_ID GROUP_ID LISTEN_PORT

    if (( USER_ID == 0 && GROUP_ID == 0 )); then
        return 1
    fi

    if (( USER_ID == 0 || GROUP_ID == 0 )); then
        echo "ERROR: PUID=${USER_ID} and PGID=${GROUP_ID} must both be 0 or both be greater than 0." >&2
        exit 1
    fi

    if (( LISTEN_PORT <= 1024 )); then
        echo "ERROR: Cannot bind to privileged port ${LISTEN_PORT} under non-root UID ${USER_ID}." >&2
        exit 1
    fi

    return 0
}
