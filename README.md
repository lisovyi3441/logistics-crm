# Logistics CRM

CRM-система для логістики на базі Laravel 12. Керування клієнтами, замовленнями, історією доставок та системою доступу на основі ролей (RBAC).

## Технології
- **Фреймворк:** Laravel 12
- **Фронтенд:** Vue 3 + Inertia.js v2
- **Стилі:** Tailwind CSS v4
- **Тестування:** Pest 4
- **Авторизація:** Spatie Laravel Permission
- **Контейнеризація:** Docker (Laravel Sail)

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
- **Менеджер логістики** (доступ своєї компанії): `manager@gmail.com`
- **Клієнт** (лише власні замовлення): `customer@gmail.com`

*Пароль для всіх акаунтів: `password`*

---

## 💻 Розробка та Тестування

**Запуск тестів (100% покриття):**
```bash
./vendor/bin/sail bin pest
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

*   **RBAC:** Точкове налаштування політик (Policies) для розмежування доступу Admin, Manager та Customer.
*   **Динамічні замовлення:** Можливість додавати кілька товарів (Order Items) до одного замовлення з динамічною валідацією на клієнті та бекенді.
*   **Історія статусів:** Трекінг кожної зміни стану замовлення з відображенням часової стрічки.
*   **UI/UX:** Адаптивний інтерфейс на базі Radix Vue (доступні діалоги, випадаючі списки) та Tailwind CSS v4.
*   **Строга типізація:** Спеціалізовані класи-екшени та сувора типізація (`declare(strict_types=1);`).
