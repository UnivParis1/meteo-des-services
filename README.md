# Meteo_des_services_P1

## Architecture
Le projet est un projet <b>Symfony 7.3 maintenue </b>
Les échanges avec la base de données sont effectuées par l'ORM <b>Doctrine</b>.<br>
Javascript a été utilisé pour l'affichage de la pop-up de Détail dans la page principale.

## Rôles

### Gestion utilisateurs et permission/droits ACL

Pour la gestion utilisateur, Le projet distingue 6 rôles avec héritage:

- <b>niveau 0</b> : <b>PUBLIC_ACCESS</b> : anonyme
- <b>niveau 1</b> : <b>ROLE_STUDENT</b> : rôle de base, assigné automatiquement si aucun rôle associé à l'utilisateur
- <b>niveau 2</b> : <b>ROLE_TEACHER</b> : correspond à enseignant
- <b>niveau 3</b> : <b>ROLE_STAFF</b> : personnel de l'université (remplace le précédent ROlE_USER)
- <b>niveau 4</b> : <b>ROLE_ADMIN</b> : accède à la météo des services en lecture et en écriture (ajout/modification/suppression d'application et maintenance)
- <b>niveau 5</b> : <b>ROLE_SUPER_ADMIN</b> : attribue les droits utilisateurs, accède au back-office

Un seul rôle est attribué aux users, les droits sont hérités du rôle précedent (ROLE_SUPER_ADMIN hérite de ROLE_ADMIN, gestion standard Symfony)

#### Gestion droits des Applications

Dans la partie back-office, il est possible de restreindre l'affichage d'une application en séléctionnant le profil de droit minimum en sélectionnant le niveau d'accès (niveau 1,2...)

Exemple: si on séléctionne ROLE_STAFF (niveau 3), seul les personnes assignés à ROLE_STAFF, ROLE_ADMIN et ROLE_SUPER_ADMIN verront l'application dans l'index

##### Tâche de maintenance

Pour assigner les rôles aux utilisateurs en fonction de l'attribut LDAP eduAffiliations automatiquement aux utilisateurs existant, on peut lancer la commande:

- <code>bin/console app:update-user-wsgroups</code>

Cette commande assignera les roles en fonction des affiliations et mettra à jour l'utilisateur si celui-ci n'a pas les permissions liées à son groupe.

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

- Commande Symfony: `bin/console app:import-json config-apps.json`

### Précisions techniques
La suppression d'une application depuis la météo des services ne supprime pas l'application en base de données mais l'archive avec la propriété <i>isArchived</i> (pour conserver son historique).<br>

## Partie visuelle

Utilise le bundle Symfony "encore" : inclus webpack au projet

Parmi les librairies de style, le projet utilise <b>Bootstrap</b>.

### Commande frontend compilation dans public/build

- `yarn run build` ou `npm run build`

En dev pour debugger dans un browser:

- `npm run watch`

## Api : webservice pour récupérer l'état d'une application

### Path / Chemin d'accès

L'api est cassifiée, elle reprend la présentation similaire à l'index web de l'application.

  * sur : /meteo/api : récupération de l'état des applications

### Filtre

Paramètres de filtre pour selectionner les applications :

  * fname : filtre sur le fname des applications ex: /meteo/api?fname=Ibis
  * state : filre le statut ex: /meteo/api?fname=Ibis&state=unavailable
