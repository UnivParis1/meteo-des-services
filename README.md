# Meteo_des_services_P1

## Architecture
Le projet est un projet <b>Symfony 7.2 maintenue </b>
Les échanges avec la base de données sont effectuées par l'ORM <b>Doctrine</b>.<br>
Javascript a été utilisé pour l'affichage de la pop-up de Détail dans la page principale.

## Rôles
Pour la gestion utilisateur, Le projet distingue trois rôles avec héritage:
- <b>ROLE_USER</b> : n'accède à la météo des services qu'en lecture
- <b>ROLE_ADMIN</b> : accède à la météo des services en lecture et en écriture (ajout/modification/suppression d'application et maintenance)
- <b>ROLE_SUPER_ADMIN</b> : attribue les droits

Un seul rôle est attribué aux users, les droits sont hérités du rôle précedent (ROLE_SUPER_ADMIN hérite de ROLE_ADMIN, gestion standard Symfony)

## Structure technique
### Diagramme de classe

- <i>Application.state, Maintenance.ApplicationState</i> : {"operational", "pertubed", "unavailable", "default"}
- <i>ApplicationHistory.historyType, MaintenanceHistory.historyType</i> : {"creation", "update", "deletion"}

### Vue MySQL hors doctrine

Une vue MySQL en bdd est indispensable pour sélectionner les applications en maintenance (Erreur sur l'index si elle n'est pas présente).
Celle ci est hors fichier de configuration

```
CREATE
VIEW view_maintenance_encours AS
    SELECT
        maintenance.id AS id,
        maintenance.application_id AS application_id,
        maintenance.starting_date AS starting_date,
        maintenance.ending_date AS ending_date,
        maintenance.application_state AS application_state,
        maintenance.is_archived AS is_archived
    FROM
        maintenance
    WHERE
        maintenance.starting_date <= CURRENT_TIMESTAMP()
            AND maintenance.ending_date >= CURRENT_TIMESTAMP()
```

### Import des données json

- Import json des applications ent avec EsupUserApps/admin/config-apps.tml (projet github EsupUserApps)

- Commande Symfony: bin/console app:import-json config-apps.json

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

