# FoodMarketplace

Based on Symfony-Docker: https://github.com/dunglas/symfony-docker

Useful commands :

``composer run-tests`` runs unit tests

``composer start-messenger-worker`` starts the messenger workers

``php bin/console app:product:import-csv <supplierName> <filePath>`` Imports the given csv file to the supplier products

You can run the project with docker and access it through https://localhost:4443/
