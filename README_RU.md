# Тестовое задание: Bitrix + Laravel

Решение тестового задания для позиции Fullstack-разработчик Bitrix + Laravel.

## Структура

```text
part1-laravel-products-api/
  Laravel REST API: GET /api/products

part2-bitrix-order-sync/
  D7-обработчик события сохранения заказа в Bitrix
```

## Часть 1. Laravel

Реализован эндпоинт `GET /api/products` с постраничной выдачей, фильтрами по категории, диапазону цены и наличию. В решении есть миграции, модели Eloquent, FormRequest-валидация, сервис выборки, Resource для JSON-ответа и пример feature-теста.

Ключевые моменты:

- индексы под фильтрацию большого каталога;
- `simplePaginate()` вместо дорогого полного подсчёта;
- `with()` для загрузки категории и остатка без N+1;
- ограничение `per_page`;
- понятный JSON при ошибках валидации.

Подробнее: [part1-laravel-products-api/README_RU.md](part1-laravel-products-api/README_RU.md)

## Часть 2. Bitrix

Реализован D7-обработчик события `sale:OnSaleOrderSaved`. При сохранении заказа обработчик собирает данные заказа, свойства и товары корзины, затем отправляет JSON во внешний Laravel API.

Ключевые моменты:

- используется событийная модель Bitrix D7;
- сбой внешнего сервиса не ломает сохранение заказа;
- ошибки логируются через `AddMessage2Log`;
- в комментариях описано, как вынести отправку в очередь/cron.

Подробнее: [part2-bitrix-order-sync/README_RU.md](part2-bitrix-order-sync/README_RU.md)

## Примечание

Часть Laravel была также развёрнута на тестовой BitrixVM рядом с Bitrix через `/laravel`, при этом наружу отдана только папка Laravel `public`.
