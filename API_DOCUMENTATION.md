# Documentação da API FinanSee

## Visão Geral

A API FinanSee é uma API RESTful desenvolvida em Laravel para gerenciamento financeiro e de usuários. Esta documentação foi gerada automaticamente usando Swagger/OpenAPI 3.0.

## Acesso à Documentação

A documentação interativa da API está disponível em:
- **URL**: `http://localhost:8000/api/documentation`
- **Formato**: Interface web interativa do Swagger UI

## Autenticação

A API utiliza autenticação baseada em tokens Bearer (Laravel Sanctum). Para acessar endpoints protegidos:

1. Faça login usando o endpoint `/api/auth/login`
2. Use o token retornado no header `Authorization: Bearer {token}`

## Módulos Documentados

### 1. Módulo de Autenticação (`/api/auth`)

#### Endpoints:
- **POST** `/api/auth/login` - Autenticar usuário
- **POST** `/api/auth/logout` - Fazer logout (requer autenticação)

### 2. Módulo de Usuários (`/api/users`)

#### Endpoints:
- **GET** `/api/users` - Listar usuários (requer permissão admin master)
- **POST** `/api/users` - Criar usuário (requer permissão admin da empresa)
- **GET** `/api/users/{user}` - Exibir usuário específico
- **PUT** `/api/users/{user}` - Atualizar usuário (requer permissão admin da empresa)
- **DELETE** `/api/users/{user}` - Excluir usuário (requer permissão admin da empresa)

## Níveis de Permissão

A API implementa um sistema de níveis de usuário:

- **Admin Master**: Acesso total a todos os endpoints
- **Admin da Empresa**: Pode gerenciar usuários da sua empresa
- **Usuário Comum**: Acesso limitado aos próprios dados

## Códigos de Resposta

- **200**: Sucesso
- **201**: Criado com sucesso
- **401**: Não autorizado
- **403**: Acesso negado
- **404**: Não encontrado
- **422**: Dados de entrada inválidos

## Exemplos de Uso

### Login
```bash
curl -X POST "http://localhost:8000/api/auth/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "usuario@exemplo.com",
    "password": "123456"
  }'
```

### Listar Usuários (com autenticação)
```bash
curl -X GET "http://localhost:8000/api/users" \
  -H "Authorization: Bearer {seu_token_aqui}" \
  -H "Content-Type: application/json"
```

### Criar Usuário
```bash
curl -X POST "http://localhost:8000/api/users" \
  -H "Authorization: Bearer {seu_token_aqui}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "João Silva",
    "email": "joao@exemplo.com",
    "password": "123456",
    "phone": "(11) 99999-9999"
  }'
```

## Regenerar Documentação

Para regenerar a documentação após alterações nos controllers:

```bash
php artisan l5-swagger:generate
```

## Estrutura do Projeto

```
app/
├── Http/
│   └── Controllers/
│       └── Api/
│           └── SwaggerController.php  # Configuração base do Swagger
└── Modules/
    ├── Auth/
    │   └── Controllers/
    │       └── AuthController.php     # Documentado
    └── Users/
        └── Controllers/
            ├── ListUsersController.php    # Documentado
            ├── CreateUserController.php   # Documentado
            ├── ShowUserController.php     # Documentado
            ├── UpdateUserController.php   # Documentado
            └── DeleteUserController.php   # Documentado
```

## Tecnologias Utilizadas

- **Laravel 12**: Framework PHP
- **Laravel Sanctum**: Autenticação API
- **L5-Swagger**: Geração de documentação Swagger
- **OpenAPI 3.0**: Especificação da documentação

## Suporte

Para dúvidas ou problemas com a API, entre em contato:
- **Email**: contato@finansee.com
- **Documentação**: Acesse a interface Swagger em `/api/documentation`
