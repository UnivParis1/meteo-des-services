# Meteo_des_services_P1

## Architecture
Le projet est un projet <b>Symfony 6</b> en architecture MVC. <br>
Les échanges avec la base de données sont effectuées par l'ORM <b>Doctrine</b>.<br>
Javascript a été utilisé pour l'affichage de la pop-up de Détail dans la page principale.

## Rôles
Le projet distingue trois rôles avec héritage:
- <b>ROLE_USER</b> : n'accède à la météo des services qu'en lecture
- <b>ROLE_ADMIN</b> : accède à la météo des services en lecture et en écriture (ajout/modification/suppression d'application et maintenance)
- <b>ROLE_SUPER_ADMIN</b> : attribue les droits

Un seul rôle est attribué aux users, les droits sont hérités du rôle précedent (ROLE_SUPER_ADMIN hérite de ROLE_ADMIN, gestion standard Symfony)

## Structure technique
### Diagramme de classe

- <i>Application.state, Maintenance.ApplicationState</i> : {"operational", "pertubed", "unavailable", "default"}
- <i>ApplicationHistory.historyType, MaintenanceHistory.historyType</i> : {"creation", "update", "deletion"}

### Précisions techniques
La suppression d'une application depuis la météo des services ne supprime pas l'application en base de données mais l'archive avec la propriété <i>isArchived</i> (pour conserver son historique).<br>

## Partie visuelle

Utilise le bundle Symfony "encore" : inclus webpack au projet

Parmi les librairies de style, le projet utilise <b>Bootstrap</b>.

### Commande frontend compilation dans public/build

- yarn run build || npm run build

En dev pour debugger dans un browser: npm run watch

- Necéssite de faire un rsync pour mettre à jour public/build (ce repertoire est ignoré par git)

ex: rsync -avn public/build/ USER@HOST:~/www/public/build/

