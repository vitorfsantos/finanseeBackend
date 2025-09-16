# FinanSee Backend

<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
</p>

<p align="center">
  <a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
  <a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
  <a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
  <a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Sobre o FinanSee

O **FinanSee Backend** é uma API RESTful desenvolvida em Laravel para gerenciamento financeiro empresarial e pessoal. O sistema oferece controle completo sobre transações financeiras, usuários e empresas, com um sistema robusto de permissões baseado em níveis de acesso.

## 🚀 Funcionalidades Principais

### 🔐 Sistema de Autenticação
- Autenticação baseada em tokens (Laravel Sanctum)
- Login/logout seguro
- Controle de sessões

### 👥 Gerenciamento de Usuários
- CRUD completo de usuários
- Sistema de níveis de permissão
- Controle de acesso baseado em roles
- Soft delete com restauração

### 🏢 Gerenciamento de Empresas
- Cadastro e gestão de empresas
- Controle de CNPJ único
- Associação de usuários a empresas

### 💰 Módulo de Transações
- Controle de receitas e despesas
- Categorização de transações
- Filtros avançados por data, valor, categoria
- Relatórios mensais automáticos
- Controle de permissões por nível de usuário

### 📊 Relatórios
- Relatórios mensais de transações
- Análise de receitas vs despesas
- Filtros por período e categoria

## 🏗️ Arquitetura

O projeto utiliza uma arquitetura modular, organizando as funcionalidades em módulos independentes:

```
app/Modules/
├── Auth/           # Autenticação e autorização
├── Users/          # Gerenciamento de usuários
├── Companies/      # Gerenciamento de empresas
├── Transactions/   # Transações financeiras
└── Addresses/      # Endereços (módulo auxiliar)
```

## 🔑 Sistema de Permissões

O sistema implementa 4 níveis de usuário com diferentes permissões:

| Nível | Descrição | Permissões |
|-------|-----------|------------|
| **Admin Master** | Administrador do sistema | Acesso total a todos os recursos |
| **Company Admin** | Administrador da empresa | Gerencia usuários e transações da empresa |
| **Company User** | Usuário da empresa | Gerencia transações da empresa |
| **User** | Usuário pessoal | Gerencia apenas suas próprias transações |

## 🛠️ Tecnologias Utilizadas

- **Laravel 12** - Framework PHP
- **Laravel Sanctum** - Autenticação API
- **SQLite** - Banco de dados (desenvolvimento)
- **L5-Swagger** - Documentação automática da API
- **PHPUnit** - Testes automatizados
- **Laravel Pint** - Code style fixer

## 📋 Pré-requisitos

- PHP 8.2 ou superior
- Composer
- Node.js e NPM (para assets)

## 🚀 Instalação

1. **Clone o repositório**
```bash
git clone <repository-url>
cd finanSeeBackend
```

2. **Instale as dependências**
```bash
composer install
npm install
```

3. **Configure o ambiente**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configure o banco de dados**
```bash
# SQLite (desenvolvimento)
touch database/database.sqlite

# Ou configure MySQL/PostgreSQL no .env
```

5. **Execute as migrações e seeders**
```bash
php artisan migrate
php artisan db:seed
```

6. **Gere a documentação da API**
```bash
php artisan l5-swagger:generate
```

7. **Inicie o servidor**
```bash
php artisan serve
```

## 🧪 Executando Testes

```bash
# Executar todos os testes
php artisan test

# Executar testes por módulo
php artisan test --testsuite=Auth
php artisan test --testsuite=Users
php artisan test --testsuite=Companies
php artisan test --testsuite=Transactions

# Executar testes específicos
php artisan test app/Modules/Auth/Tests/
```

## 📚 Documentação da API

A documentação interativa da API está disponível em:
- **URL**: `http://localhost:8000/api/documentation`
- **Formato**: Swagger UI

### Endpoints Principais

#### Autenticação
- `POST /api/auth/login` - Fazer login
- `POST /api/auth/logout` - Fazer logout

#### Usuários
- `GET /api/users` - Listar usuários
- `POST /api/users` - Criar usuário
- `GET /api/users/{id}` - Exibir usuário
- `PUT /api/users/{id}` - Atualizar usuário
- `DELETE /api/users/{id}` - Excluir usuário

#### Empresas
- `GET /api/companies` - Listar empresas
- `POST /api/companies` - Criar empresa
- `GET /api/companies/{id}` - Exibir empresa
- `PUT /api/companies/{id}` - Atualizar empresa
- `DELETE /api/companies/{id}` - Excluir empresa

#### Transações
- `GET /api/transactions` - Listar transações
- `POST /api/transactions` - Criar transação
- `GET /api/transactions/{id}` - Exibir transação
- `PUT /api/transactions/{id}` - Atualizar transação
- `DELETE /api/transactions/{id}` - Excluir transação
- `GET /api/transactions/reports/monthly` - Relatório mensal

## 🔧 Comandos Úteis

```bash
# Gerar dados fake para testes
php artisan generate:fake-data

# Limpar cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Executar em modo desenvolvimento
composer run dev

# Executar testes
composer run test
```

## 📁 Estrutura do Projeto

```
finanSeeBackend/
├── app/
│   ├── Http/
│   │   ├── Controllers/     # Controllers principais
│   │   ├── Middleware/      # Middlewares customizados
│   │   └── Requests/        # Form Requests
│   ├── Modules/            # Módulos da aplicação
│   │   ├── Auth/
│   │   ├── Users/
│   │   ├── Companies/
│   │   ├── Transactions/
│   │   └── Addresses/
│   └── Services/           # Serviços compartilhados
├── database/
│   ├── migrations/         # Migrações do banco
│   └── seeders/           # Seeders para dados iniciais
├── routes/
│   └── api.php            # Rotas da API
├── tests/                 # Testes automatizados
└── storage/
    └── api-docs/          # Documentação gerada
```

## 🔒 Segurança

- Autenticação baseada em tokens Bearer
- Middleware de autorização por roles
- Validação robusta de dados de entrada
- Soft delete para preservar histórico
- Sanitização de dados sensíveis

## 🤝 Contribuição

1. Fork o projeto
2. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

## 📄 Licença

Este projeto está licenciado sob a licença MIT - veja o arquivo [LICENSE](LICENSE) para detalhes.

## 📞 Suporte

Para dúvidas ou suporte:
- **Email**: vitorxfelippe@gmail.com
- **Documentação**: Acesse `/api/documentation` após iniciar o servidor
- **Issues**: Use o sistema de issues do GitHub

---

**Desenvolvido com ❤️ usando Laravel**