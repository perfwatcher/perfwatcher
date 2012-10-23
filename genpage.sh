#/bin/bash

pages="index.html screenshots.html compatibility_matrix.html developpement.html faq.html download.html installation.html installation_agent.html installation_server.html installation_frontend.html"

for page in $pages
do
	cat tpl/header.html tpl/$page tpl/footer.html > $page
done
