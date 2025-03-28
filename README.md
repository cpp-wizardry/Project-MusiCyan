Installation
Prérequis
PHP >= 8.2
Composer
Symfony
Symfony CLI
SQLite

Installation du projet Symfony
Cloner le repository :
git clone https://github.com/cpp-wizardry/Project-MusiCyan.git
cd Project-MusiCyan/symfony

php --ini
Installer les dépendances :
composer install
Exécuter les migrations :
php bin/console doctrine:migrations:migrate
Lancer le serveur Symfony :
symfony server:start
Accède à l'application en visitant http://127.0.0.1:8000 dans ton navigateur.
