#!/bin/bash

# Coprime IT Landing Page - Quick Start Script

echo "🚀 Запуск лендинг-сторінки Coprime IT..."

# Перевіряємо чи встановлений Docker
if ! command -v docker &> /dev/null; then
    echo "❌ Docker не встановлений. Будь ласка, встановіть Docker спочатку."
    exit 1
fi

# Перевіряємо чи встановлений Docker Compose
if ! command -v docker-compose &> /dev/null; then
    echo "❌ Docker Compose не встановлений. Будь ласка, встановіть Docker Compose спочатку."
    exit 1
fi

# Створюємо директорію для логів
mkdir -p logs/nginx

echo "📦 Збірка Docker образу..."
docker-compose build

echo "🌐 Запуск контейнера..."
docker-compose up -d

echo "✅ Лендинг-сторінка запущена!"
echo "🌍 Відкрийте браузер та перейдіть на: http://localhost:8080"
echo ""
echo "📋 Корисні команди:"
echo "  - Переглянути логи: docker-compose logs -f"
echo "  - Зупинити: docker-compose down"
echo "  - Перезапустити: docker-compose restart"
echo ""
echo "🎨 Сторінка містить:"
echo "  - Сучасний дизайн з анімаціями"
echo "  - Адаптивний інтерфейс"
echo "  - Форму зворотного зв'язку"
echo "  - SEO-оптимізацію"
echo ""
echo "📧 Контакти: info@coprime.it"
echo "🌐 Сайт: https://coprime.it"

