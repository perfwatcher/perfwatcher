#! /bin/bash

#
# vim: expandtab sw=4 ts=4 sts=4:
#

DEFAULT_VERSION=git
COMPRESSIONS="tgz tbz"
RELEASEDIR=releases

usage() {
	echo "Usage:"
	echo "  $0 <VERSION> <git branch>"
	echo ""
	echo "Example:"
	echo "  $0  1.0-20121029  master"
	echo "  $0  1.0  release/1.0"
	echo ""
	echo "Create a tar.gz release file"
	echo ""
	exit 0
}

if [ $# -lt 2 ]; then
	usage;
fi

if [ "x$1" = "x--help" -o "x$1" = "x-h" ]; then
	usage
fi

VERSION=$1
shift
branch=$1
shift

# Checks wether remote branch has locak tracking branch
ensure_local_branch() {
	if ! git branch | grep -q '^..'"$1"'$'; then
	    git branch --track $1 origin/$1
    fi
}

##

ensure_local_branch $branch

WORKDIR=${RELEASEDIR}/perfwatcher-${VERSION}
if [ -e ${WORKDIR} ]; then
    echo "${WORKDIR} already exists."
    echo "Please check what's wrong, then remove ${WORKDIR}"
    echo ""
    exit 1
fi

mkdir -p ${WORKDIR}
git clone --local . ${WORKDIR}
cd ${WORKDIR}
ensure_local_branch $branch
git checkout $branch

# Removing unneeded files
rm -rf .git
find . -name .gitignore -print0 | wargs -0 -r rm -f
cd ..

name=perfwatcher-${VERSION}

for comp in $COMPRESSIONS ; do
    case $comp in
        tbz|tgz|txz)
            if [ ! -f $name.tar ] ; then
                echo "* Creating $name.tar"
                tar cf $name.tar $name
            fi
            if [ $comp = tbz ] ; then
                echo "* Creating $name.tar.bz2"
                bzip2 -9k $name.tar
            fi
            if [ $comp = txz ] ; then
                echo "* Creating $name.tar.xz"
                xz -9k $name.tar
            fi
            if [ $comp = tgz ] ; then
                echo "* Creating $name.tar.gz"
                gzip -9c $name.tar > $name.tar.gz
            fi
            ;;
            zip)
                echo "* Creating $name.zip"
                zip -q -9 -r $name.zip $name
            ;;
            zip-7z)
                echo "* Creating $name.zip"
                7za a -bd -tzip $name.zip $name > /dev/null
            ;;
            7z)
                echo "* Creating $name.7z"
                7za a -bd $name.7z $name > /dev/null
            ;;
            *)
                echo "WARNING: ignoring compression '$comp', not known!"
            ;;
        esac

# Cleanup
    rm -f $name.tar
done

# Remove directory with current dist set
rm -rf perfwatcher-${VERSION}
