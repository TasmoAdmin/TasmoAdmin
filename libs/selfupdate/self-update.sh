#! /bin/bash

USER_NAME=reloxx13
REPO_NAME=SonWEB

if [ ! $1 ]; then
    echo 'Usage:'
    echo "$0 <github's username> <github's repository name> [stable ref] [destination dir] [excluded patterns]"
    echo
    echo 'The stable ref is optional and defaults to heads/master.'
    echo 'The destination dir is optional and defaults to current dir.'
    echo 'The excluded patterns are a list (enclosed in quotes) of patterns of files to be not overwritten.'
    echo
    exit 0
fi

if [ $3 ]; then
    STABLE_REF="$3"
else
    STABLE_REF='heads/master'
fi

LAST_COMMIT_FILE=/tmp/$USER_NAME.$REPO_NAME.`echo $STABLE_REF | tr '/' '_'`

if [ "$4" ]; then
    DEST_DIR="$4"
else
    DEST_DIR='.'
fi

if [ "$5" ]; then
    EXCLUDED_PAT="$5"
else
    EXCLUDED_PAT=''
fi

COMMIT_HASH=`curl https://api.github.com/repos/$USER_NAME/$REPO_NAME/git/refs/$STABLE_REF | grep 'sha'` || exit 1

function fetch_and_update() {
    # fetch new files
    curl "http://nodeload.github.com/$USER_NAME/$REPO_NAME/tarball/master" | tar -x || exit 1

    # remove patterns
    [ "$EXCLUDED_PAT" ] && cd $USER_NAME-$REPO_NAME-* && rm -rvf $EXCLUDED_PAT && cd ..

    # copy all files form repo to destination
    cp -rv $USER_NAME-$REPO_NAME-*/* $DEST_DIR || exit 1

    # delete temporary fetched repo
    rm -rf $USER_NAME-$REPO_NAME-*/ || exit 1

    # updates file with last commit
    echo "$COMMIT_HASH" > $LAST_COMMIT_FILE || exit 1
}

if [ -f $LAST_COMMIT_FILE ]; then
    LAST_COMMIT=`cat $LAST_COMMIT_FILE`

    if [ "$LAST_COMMIT" != "$COMMIT_HASH" ]; then
        fetch_and_update
    else
        echo "repo up to date."
    fi
else
    fetch_and_update
fi

