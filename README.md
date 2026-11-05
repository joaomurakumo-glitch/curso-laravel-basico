<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Manutenção dos formulários do CRUD financeiro

### Antes

Os formulários de lançamento e categoria não tinham um limite mínimo de caracteres nos textos. A validação dos valores e das datas precisava de cobertura para situações inválidas e para operações de edição.

### Depois

- Descrição do lançamento e nome da categoria são obrigatórios, aceitam de 3 a 100 caracteres e são normalizados sem espaços nas extremidades.
- Valores de lançamentos são obrigatórios, positivos, numéricos, limitados a duas casas decimais e ao limite suportado pelo banco.
- Datas de lançamento precisam corresponder a uma data válida no formato `AAAA-MM-DD`.
- As regras do servidor são aplicadas tanto na criação quanto na edição de lançamentos; categorias também são validadas no servidor na criação.
- O navegador complementa a validação com limites HTML e normalização JavaScript. As mensagens do servidor continuam sendo a garantia de integridade.
- Testes cobrem limites de texto, valores inválidos, datas inexistentes, normalização e rejeição de alterações inválidas.

### Limite do escopo atual

O CRUD existente registra receitas, despesas e categorias. Ele não possui cadastro de pessoas, produtos ou vendas nem campos de e-mail, idade, nascimento, CPF, CEP, telefone, estoque, desconto ou período inicial/final. Essas regras não foram associadas artificialmente a um lançamento financeiro; para validá-las e armazená-las, é necessário criar os cadastros correspondentes.

### Verificação

Execute `php artisan test --compact tests/Feature/ExpenseManagerTest.php` para validar os cenários do CRUD financeiro.
