# Edit the file `VERSION`
# make & push --tags
upgradeVersion:
	cat algoliasearch.php | sed -E "s/[0-9]\.[0-9]\.[0-9]+/`head -1 VERSION`/" > algoliasearch.php.new && mv algoliasearch.php.new algoliasearch.php
	cat composer.json | sed -E "s/[0-9]\.[0-9]\.[0-9]+/`head -1 VERSION`/" > composer.json.new && mv composer.json.new composer.json
	git tag `head -1 VERSION`
