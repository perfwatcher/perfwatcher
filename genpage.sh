#/bin/bash

pages="index.html screenshots.html"

for page in $pages
do
	cat tpl/header.html tpl/$page tpl/footer.html > $page
done
