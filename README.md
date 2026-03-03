# Logistics CRM

CRM-система для логістики на базі Laravel 12. Керування клієнтами, замовленнями, історією доставок та системою доступу на основі ролей (RBAC).

## Технології
- **Фреймворк:** Laravel 12
- **Фронтенд:** Vue 3 + Inertia.js v2
- **Стилі:** Tailwind CSS v4
- **Тестування:** Pest 4
- **Авторизація:** Spatie Laravel Permission (Декларативний RBAC через `Permissions` Enum)
- **Контейнеризація:** Docker (Laravel Sail)
- **Маршрутизація:** OSRM (Open Source Routing Machine)
- **Кешування:** Redis (для маршрутів та статистики)

---

## 🚀 Швидкий запуск (Docker)

Найшвидший спосіб запустити проєкт — використати Docker Compose:

### 1. Клонування репозиторію
```bash
git clone https://github.com/lisovyi3441/logistics-crm.git
cd logistics-crm
```

### 2. Налаштування середовища
```bash
cp .env.example .env
```

### 3. Запуск контейнерів
Встановіть залежності (якщо локально немає PHP) та запустіть контейнери у фоні:
```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php84-composer:latest \
    composer install --ignore-platform-reqs

./vendor/bin/sail up -d
```
*(Примітка: `sail` — це просто зручна обгортка над `docker compose`).*

### 4. Ініціалізація бази та фронтенду
Згенеруйте ключ, наповніть базу тестовими даними та зберіть фронтенд:
```bash
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate:fresh --seed
./vendor/bin/sail npm install
./vendor/bin/sail npm run build
```

---

## 👤 Тестові акаунти

База даних автоматично наповнюється акаунтами для кожної ролі:

- **Адміністратор** (повний доступ): `admin@gmail.com`
- **Менеджер логістики** (доступ до своєї компанії, скасування замовлень): `manager@gmail.com`
- **Спостерігач** (лише перегляд замовлень своєї компанії): `observer@gmail.com`

*Пароль для всіх акаунтів: `password`*

---

## 💻 Розробка та Тестування

**Запуск тестів (100% покриття бізнес-логіки):**
```bash
./vendor/bin/sail artisan test --compact
```

**Форматування коду (Pint):**
```bash
./vendor/bin/sail bin pint
```

**Гаряче перевантаження фронтенду (HMR):**
```bash
./vendor/bin/sail npm run dev
```

---

## 🛠 Основні фічі

*   **RBAC 2.0:** Перехід від простих ролей до гранульованих дозволів (`Permissions` Enum). Політики (Policies) суворо регулюють доступ на рівні моделей та ресурсних контролерів.
*   **Динамічні замовлення:** Додавання позицій вантажу (Order Items) з автоматичним розрахунком об'єму (CBM) та ваги.
*   **Pricing Pipeline:** Розрахунок вартості через ланцюжок правил (База, ADR націнка, Страхування, Податки) з використанням патерна Pipeline.
*   **Transactional Integrity:** Всі критичні бізнес-операції (призначення вантажівки, зміна статусу) захищені DB Transactions.
*   **Semantic URLs:** Використання людських ідентифікаторів (`order_number`) замість числових ID у маршрутах.
*   **UI/UX:** Адаптивний інтерфейс на базі Radix Vue та Tailwind CSS v4.
*   **Строга типізація:** Використання `declare(strict_types=1);` та суворо типізованих Actions для бізнес-логіки.
