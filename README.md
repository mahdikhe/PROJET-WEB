# CityPulse - Plateforme de Gestion de Projets Urbains

## Description
CityPulse est une plateforme web permettant aux citoyens et aux professionnels de collaborer sur des projets urbains durables et inclusifs. L'application facilite la gestion des tâches, la visualisation des contributions géographiques et la coordination des activités de projet.

## Fonctionnalités
- Tableau de bord Kanban pour la gestion des tâches
- Visualisation des contributeurs sur une carte interactive
- Chatbot d'assistance pour la suggestion de tâches
- Gestion des contributions
- Formulaire de participation aux projets

## Technologies utilisées
- Frontend: HTML, CSS, JavaScript, Leaflet.js
- Backend: PHP, MySQL
- Base de données: MySQL avec PDO

## Installation
1. Cloner le dépôt
2. Importer les fichiers SQL dans votre base de données MySQL
3. Configurer les paramètres de connexion dans db.php et db_contributors.php
4. Déployer sur un serveur web avec PHP

## Structure du projet
- `tasks.php`: Gestion des tâches avec interface Kanban
- `get_contributor_locations.php`: API pour récupérer les emplacements des contributeurs
- `contribute.html`: Formulaire pour participer aux projets
- `creative_dashboard.php`: Tableau de bord administratif

## Captures d'écran

![image](https://github.com/user-attachments/assets/2bc97c8a-8985-45d3-8e04-f3c5c368e83b)

Gestion des taches:

![image](https://github.com/user-attachments/assets/53e3f67a-3529-482b-a87c-f4c7cd0d21fe)

Calendrier:

![image](https://github.com/user-attachments/assets/e4784480-5865-456a-98b9-b50eba639dcb)

DASHBOARD :
![image](https://github.com/user-attachments/assets/250423b5-0206-4d19-980f-add734eefce0)








## Licence
MIT License

Copyright (c) 2025 

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights  
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell      
copies of the Software, and to permit persons to whom the Software is         
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all  
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR     
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,       
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE    
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER         
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,  
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE  
SOFTWARE.
