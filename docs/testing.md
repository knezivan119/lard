# Testing

## Unit

## Feature

## Artisan and stub
| Outcome                                  | Command                                                           | File created                                   | Stub used (after `stub:publish`)                                        |
| ---------------------------------------- | ----------------------------------------------------------------- | ---------------------------------------------- | ----------------------------------------------------------------------- |
| Feature test (Pest)                      | `php artisan make:test Users/IndexTest --pest`                    | `tests/Feature/Users/IndexTest.php`            | `stubs/pest.stub` ([Artisan Cheatsheet][1])                             |
| Feature test (PHPUnit)                   | `php artisan make:test Users/IndexTest --phpunit`                 | `tests/Feature/Users/IndexTest.php`            | `stubs/test.stub` ([Artisan Cheatsheet][1])                             |
| Unit test (Pest)                         | `php artisan make:test Services/UserServiceTest --unit --pest`    | `tests/Unit/Services/UserServiceTest.php`      | `stubs/pest.unit.stub` ([Artisan Cheatsheet][1])                        |
| Unit test (PHPUnit)                      | `php artisan make:test Services/UserServiceTest --unit --phpunit` | `tests/Unit/Services/UserServiceTest.php`      | `stubs/test.unit.stub` ([Artisan Cheatsheet][1])                        |
| Generate a view + a test (Pest)          | `php artisan make:view dashboard --test --pest`                   | `resources/views/dashboard.blade.php` + a test | `stubs/view.pest.stub` ([Artisan Cheatsheet][2])                        |
| Generate a view + a test (PHPUnit)       | `php artisan make:view dashboard --test --phpunit`                | `resources/views/dashboard.blade.php` + a test | `stubs/view.test.stub` ([Artisan Cheatsheet][2])                        |
| Generate a controller + a test (Pest)    | `php artisan make:controller PostController --test --pest`        | Controller + a test                            | Uses controller stubs + a test stub (Pest) ([Artisan Cheatsheet][3])    |
| Generate a controller + a test (PHPUnit) | `php artisan make:controller PostController --test --phpunit`     | Controller + a test                            | Uses controller stubs + a test stub (PHPUnit) ([Artisan Cheatsheet][3]) |

[1]: https://artisan.page/12.x/maketest?utm_source=chatgpt.com "php artisan make:test - Laravel 12.x - The Laravel Artisan Cheatsheet"
[2]: https://artisan.page/11.x/makeview?utm_source=chatgpt.com "php artisan make:view - Laravel 11.x - The Laravel Artisan Cheatsheet"
[3]: https://artisan.page/12.x/makecontroller?utm_source=chatgpt.com "php artisan make:controller - Laravel 12.x - The Laravel Artisan Cheatsheet"
