# Récuperer les sources
git pull origin master

# Récuperer les librairies
composer install

# Mettre à jour la BDD
drush updb -y

# Importer les configurations
drush cim -y

# Vider les caches
drush cr