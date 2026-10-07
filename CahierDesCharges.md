# CAHIER DES CHARGES
Victoria Ançay Ludivine Rouiller

## Fonctionnalités

### 1. Fonctionnalités principales 
Dans cette partie vous trouverez les fonctions principales de l'application.
#### 1.1 Authentification et gestion de session

**Création de compte** : l'élève crée son compte avec un identifiant unique, un mot de passe et une adresse e-mail (utile pour la récupération du mot de passe). 

**Connexion** : l'élève accède à son espace personnel avec son identifiant et son mot de passe. 

**Déconnexion** : un bouton de déconnexion est accessible depuis toutes les pages. 

**Sécurité** : les mots de passe sont stockés de manière chiffrée (hachage) et jamais en clair. Un mot de passe minimum de 8 caractères est exigé. 

**Isolation des données** : chaque session est privée. Une élève ne peut voir ni modifier les cours et résumés d'une autre élève.
 
**Mot de passe oublié** :réinitialisation par e-mail pour en créer un nouveau. 

#### 1.2 Page d'accueil
La page d'accueil est la **première** page affichée après la connexion. 
Elle contient :
- Le nom de l'élève en en-tête (par exemple « Bonjour Victoria »).
- La liste des cours déjà créés, affichés sous forme de cartes ou de cases colorées.
- Un bouton « Ajouter un cours », un accès à l'onglet Profil via un menu de navigation.
- Un bouton de déconnexion.


#### 1.3 Gestion des cours 
**Créer un cours** : l'élève saisit le nom de la branche (ex.:
Mathématiques, Français, Histoire) et choisit une couleur. 

**Choisir la couleur** : une palette de couleurs prédéfinies est proposée, pour que chaque branche soit facilement reconnaissable. La couleur s'applique à la case du cours sur la page d'accueil et à la page du cours. 

**Modifier un cours** : le nom et la couleur peuvent être changés à tout moment grâce aux trois petits points en haut à droite.

**Supprimer un cours** : cliquez sur les trois petits points et sélectionner "supprimer le cours" une confirmation est demandée avant la suppression, car elle entraîne celle des résumés qui y sont rattachés. 

**Nombre de cours illimité** : l'élève crée autant de cours que nécessaire dont il a besoin. 


#### 1.4 Gestion des résumés
Chaque case de cours ouvre une page dédiée aux résumés de cette branche.

**Transformer un résumé** : l'élève ajoute un fichier depuis son appareil. Les formats acceptés sont principalement PDF, Word (.docx) et images (.jpg, .png). 

**Afficher la liste des résumés** : chaque résumé apparaît avec son nom, sa date d'ajout et son type de fichier.

**Option des résumés** : Consulter ou télécharger un résumé, renommer un résumé, supprimer un résumé, avec confirmation. Tout ça en allant sur le résumé et sélectionnant les trois petits points.


#### 1.5 Profil
L'onglet « Profil » permet à l'élève de saisir et de modifier ses données personnelles : nom et prénom, adresse e-mail, établissement et année ou classe (facultatif) ainsi que changement de mot de passe. 
**Les données** du profil ne sont visibles que par l'élève.

### 2. Fonctionnalités supplémentaires (options) 

#### 2.1 Accès visiteur
Cette option permet à l'élève de **partager ses résumés** avec d'autres personnes (copains, camarades, famille) sans leur donner son mot de passe.

**Principe** : depuis son profil, l'élève génère un accès visiteur, sous la forme d'un lien ou d'un code unique, qu'elle peut transmettre à qui elle veut.


### Fonctionnement et règles :
**Gérer l'accès** : l'élève crée un ou plusieurs accès visiteur et leur donne un nom pour les repérer (ex. : « Accès Léa », « Classe 10B »). 

**Lecture seule** : le visiteur peut consulter les cours et télécharger les résumés, mais ne peut ni ajouter, ni modifier, ni supprimer quoi que ce soit. 

**Pas d'accès aux données personnelles** : l'onglet "Profil" l'adresse e-mail et le mot de passe restent invisibles pour le visiteur. 

**Gestion des accès** : l'élève voit la liste de ses accès visiteur actifs et peut révoquer (désactiver) n'importe lequel à tout moment. Le lien ne fonctionne alors plus. 

**Durée de validité (optionnel)** : l'élève peut fixer une date d'expiration (ex. : 7 jours, 1 mois, illimité).

**Affichage pour le visiteur** : il voit une version simplifiée de la page d'accueil avec les cours et leurs couleurs, et un bandeau indiquant « Mode visiteur ». 

#### 2.2 Idées d'améliorations futures (à valider)
Ces fonctionnalités ne sont pas indispensables et ne seront développées que s'il reste du temps.

**Choix des cours partagés** : l'accès visiteur ne donne accès qu'à certains cours sélectionnés. 

**Recherche d'un cours ou d'un résumé par mot-clé** : si beaucoup de cours possibilité de mettre des mots clés pour gain de temps.

**Tri des résumés** : par date ou par nom. 

**Créer un mode sombre** : pour plus de confort pour l'utilisateur selon les préférences.
