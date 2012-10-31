#/bin/bash

pages="index.html screenshots.html compatibility_matrix.html developpement.html faq.html download.html installation.html installation_agent.html installation_server.html installation_frontend.html"

vars=$(cat << EOF
COLLECTDPW_VERSION=5.1.0-20121031
COLLECTDPW_PACKAGE=collectd-5.1.0-20121031.tar.gz
PERFWATCHER_VERSION=1.0
PERFWATCHER_PACKAGE=perfwatcher-/1.0.20121026.tgz
EOF
)

substitute_cmd=$(
echo "sed "
for kv in $vars; do
	k=$(echo "$kv" | cut -d= -f 1)
	v=$(echo "$kv" | cut -d= -f 2- | sed -e 's/\//\\\//g')
	echo "-e s/@$k@/$v/g"
done
)

for page in $pages
do
	(
		cat tpl/header.html
		cat tpl/$page | $substitute_cmd
		cat tpl/footer.html
		) > $page
done
