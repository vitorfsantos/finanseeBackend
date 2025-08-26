#!/bin/bash

# Script para regenerar a documentação Swagger da API FinanSee

echo "🔄 Regenerando documentação Swagger..."

# Limpar cache do Laravel
php artisan config:clear
php artisan cache:clear

# Gerar documentação Swagger
php artisan l5-swagger:generate

echo "✅ Documentação regenerada com sucesso!"
echo "📖 Acesse: http://localhost:8000/api/documentation"
echo ""
echo "📁 Arquivo gerado: storage/api-docs/api-docs.json"
