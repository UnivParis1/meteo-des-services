# Meteo_des_services_P1

## Architecture
Le projet est un projet <b>Symfony 6</b> en architecture MVC. <br>
Les échanges avec la base de données sont effectuées par l'ORM <b>Doctrine</b>.<br>
Javascript a été utilisé pour l'affichage de la pop-up de Détail dans la page principale.

## Source des données
Le projet s'appuie sur un <b>fichier json</b> (public/json/applications.json) pour récupérer les applications (fname + titre) de Paris 1. <br>
Au premier lancement du projet, les applications sont enregistrées dans une <b>base de données MySQL</b> qui enregistre les informations sur la météo des services (voir diagramme). <br>
Une application non référencée dans le fichier .json peut être ajoutée directement depuis la météo des services.

## Rôles
Le projet distingue deux rôles :
- <b>User</b> : n'accède à la météo des services qu'en lecture
- <b>Admin</b> : accède à la météo des services en lecture et en écriture (ajout/modification/suppression d'application et maintenance)
Le paramétrage du rôle s'effectue dans le fichier .env du projet (variable GLOBAL_VARIABLE, valeur : {"user", "admin"})

## Structure technique
### Diagramme de classe

![image](https://github.com/pierreLouisClv/Meteo_des_services_P1/assets/113671168/3ef8d87d-9c6f-4ecb-af50-c109e114550a)

- <i>Application.state, Maintenance.ApplicationState</i> : {"operational", "pertubed", "unavailable", "default"}
- <i>ApplicationHistory.historyType, MaintenanceHistory.historyType</i> : {"creation", "update", "deletion"}

### Précisions techniques
La suppression d'une application depuis la météo des services ne supprime pas l'application en base de données mais l'archive avec la propriété <i>isArchived</i> (pour conserver son historique).<br>
La propriété isFromJson permet de différencier les applications issus du fichier .json des applications créées au sein de la météo des services.<br>
La structure de la base de données a été conservée dans les migrations du projet.
## Partie visuelle
Parmi les librairies de style, le projet utilise <b>Bootstrap</b>.



