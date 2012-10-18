#/bin/bash

pages="index.html screenshots.html compatibility_matrix.html developpement.html faq.html download.html"

for page in $pages
do
	cat tpl/header.html tpl/$page tpl/footer.html > $page
done
