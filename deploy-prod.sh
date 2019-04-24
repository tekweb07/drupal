# Récuperer les sources
git pull origin master

# Récuperer les librairies
composer install

# Mettre à jour la BDD
drush updb -y

# Export des configs de prod
drush csex prod -y

#Ajout des congfig de prod
git add config/prod
git commit -m 'Mise à jour des configs de prod.'
git push origin master

# Importer les configurations
drush cim -y

# Vider les caches
drush cr