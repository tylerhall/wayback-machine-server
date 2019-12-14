#!/bin/bash

mkdir -p repo/$1
git -C repo/$1 init

for d in websites/$1/*/ ; do
	TS="$(basename $d)"
	WILL_CLOBBER=`diff -qr $d repo/$1 | grep differ | wc -l`

	if (( $WILL_CLOBBER > 0 )); then
		git -C repo/$1 add .
		git -C repo/$1 commit -am "$d"
		GIT_DATE=`date -jf "%Y%m%d%H%M%S" $TS +"%s"`
		GIT_COMMITTER_DATE="$GIT_DATE" git -C repo/$1 commit --amend --no-edit --date="$GIT_DATE"	
	fi

	rsync -a $d repo/$1
done

git -C repo/$1 add .
git -C repo/$1 commit -am "$d"
GIT_DATE=`date -jf "%Y%m%d%H%M%S" $TS +"%s"`
GIT_COMMITTER_DATE="$GIT_DATE" git -C repo/$1 commit --amend --no-edit --date="$GIT_DATE"	
