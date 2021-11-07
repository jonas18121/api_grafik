# Mettre en place un système d'authentification, partie 1

Ici on va voir `l'authentification très simple`, via un `Json et un système de cookie`.

C'est un `système d'authentification qui est statefull`, car c'est le serveur qui va devoir valider systématiquement la requête.

Et ça va fonctionner que si l'api est sur le même domaine que le reste de l'application.

## Authentification

On va crée l'entité user via la commande :

    > php bin/console make:user

### secrity.yaml

Le fichier `secrity.yaml` été prérempli grace à la commande ci-dessus.

Dans `secrity.yaml`

- `json_login:` est le moyen qui va nous permettre d'authentifier l'utilisateur dans le cadre d'une api

- `check_path:` va nous permettre de précisé le chemin qui va nous permettre d'authentifier l'utilisateur

- `api_login` est le nom de la route `/login` qui est dans le controller SecurityController.php

- Maintenant il faut qu'on crée le controller SecurityController.php

Dans `secrity.yaml`

    security:
        encoders:
            App\Entity\User:
                algorithm: auto

        # https://symfony.com/doc/current/security.html#where-do-users-come-from-user-providers
        providers:
            # used to reload user from session & other features (e.g. switch_user)
            app_user_provider:
                entity:
                    class: App\Entity\User
                    property: email
        firewalls:
            dev:
                pattern: ^/(_(profiler|wdt)|css|images|js)/
                security: false
            main:
                anonymous: lazy
                provider: app_user_provider
                json_login:
                    check_path: api_login

                # activate different ways to authenticate
                # https://symfony.com/doc/current/security.html#firewalls-authentication

                # https://symfony.com/doc/current/security/impersonating_user.html
                # switch_user: true

        # Easy way to control access for large sections of your site
        # Note: Only the *first* access control that matches will be used
        access_control:
            # - { path: ^/admin, roles: ROLE_ADMIN }
            # - { path: ^/profile, roles: ROLE_USER }


### SecurityController.php

On va créer manuellement le controller SecurityController.php et on va le faire extends de `AbstractController`

Dans `SecurityController.php`

- On importe : use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

- On va créer la fonction public `login()`

- Dans `login()`

    - `$user = $this->getUser();` on récupère l'utilisateur
    - puis on retourne au Json qui aura le username et le roles de l'utilisateur
    - La particularité de cette authentification est qu'elle va ce faire a travers un cookie. Donc, il ne va pas renvoyer un token

- On importe : use Symfony\Component\Routing\Annotation\Route;
- `@Route("/api/login", name="api_login", methods="POST")`, on construit la route pour la fonction `login()`

- On va testé l'authentification de l'utilisateur via (`Postman` ou `insoumnia` https://insomnia.rest/)

Dans `SecurityController.php`


    <?php 

    namespace App\Controller;

    use Symfony\Component\Routing\Annotation\Route;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

    class SecurityController extends AbstractController
    {

        /**
        * 
        * Cette , va nous permettre de récupéré les informations a travers un cookies
        * Donc, il ne va pas renvoyer un token
        * 
        * @Route("/api/login", name="api_login", methods="POST")
        *
        * @return void
        */
        public function login() 
        {
            $user = $this->getUser();
            return $this->json([
                'username' => $user->getUsername(),
                'roles' => $user->getRoles()
            ]);
        }
    }

### CLI

En CLI on peut généré un mot de passe hashé via la commande:

    > php bin/console security:encode-password

### test

après avoir créer un user dans ma base de données et bien configuré Postman, je peut testé mon api sur Postman