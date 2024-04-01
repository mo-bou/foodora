# FoodMarketplace

Based on Symfony-Docker: https://github.com/dunglas/symfony-docker

Useful commands :

``composer run-tests`` runs unit tests

``composer analyse`` runs phpstan

``composer start-messenger-worker`` starts the messenger workers

``php bin/console app:product:import-csv <supplierName> <filePath>`` Imports the given csv file to the supplier products

``symfony console workflow:dump <workflow> | dot -Tpng -o docs/<workflow>.png`` dumps the specified workflow configuration in a png
(you might need to install [graphviz](https://www.graphviz.org/]))

You can run the project with docker and access it through https://localhost:4443/

You can access the admin at : https://localhost:4443/admin/
