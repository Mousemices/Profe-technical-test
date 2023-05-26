# Profe-technical-test

This project is using Laravel 10.12.0 with PHP 8.2.4.

**PS**: Took advantage of named arguments that introduced in the PHP 8.

Clone the repository.

```bash
git clone <repository>
```

Install dependencies.
```bash
composer install
```

Copy `.env.example` to `.env`. 

Execute command to generate `APP_KEY`.
```bash
php artisan key:generate
```

Launch server.

```bash
php artisan serve
```

We have only one route, supported by **POST** method with required parameter: `txt_file`
```bash
http://127.0.0.1:8000/api/football-competition-outcome
```

You can use Postman or Insomnia to test the API.

You have the sample `Football_competition.txt` under folder `/tests/Stubs/`.

Just don't forget to put ***Accept application/json*** to the headers [Postman, Insomnia], since they are not set by default.
