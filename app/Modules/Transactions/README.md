# Módulo de Transações

Este módulo gerencia transações financeiras (receitas e despesas) com controle de permissões baseado no nível do usuário.

## Funcionalidades

- **CRUD completo** de transações financeiras
- **Controle de permissões** baseado no nível do usuário
- **Filtros avançados** para listagem (tipo, categoria, data, valor, usuário, empresa)
- **Validação robusta** de dados
- **Documentação OpenAPI** completa

## Estrutura de Permissões

### adminMaster
- Pode criar, visualizar, atualizar e excluir transações para **qualquer usuário** e **qualquer empresa**
- Acesso total a todas as funcionalidades

### companyAdmin
- Pode gerenciar transações apenas da **sua empresa**
- Pode criar transações para qualquer usuário da empresa
- Pode visualizar, atualizar e excluir transações da empresa

### companyUser
- Pode gerenciar transações apenas da **sua empresa**
- Pode criar transações para si mesmo ou outros usuários da empresa
- Pode visualizar, atualizar e excluir transações da empresa

### user
- Pode gerenciar apenas **suas próprias transações pessoais**
- Não pode criar transações para empresas
- Acesso limitado às funcionalidades básicas

## Endpoints da API

### Listar Transações
```
GET /api/transactions
```
**Parâmetros de filtro:**
- `type`: income/expense
- `user_id`: ID do usuário (apenas adminMaster)
- `company_id`: ID da empresa (apenas adminMaster)
- `category`: Categoria da transação
- `start_date`: Data inicial (YYYY-MM-DD)
- `end_date`: Data final (YYYY-MM-DD)
- `min_amount`: Valor mínimo
- `max_amount`: Valor máximo
- `per_page`: Itens por página (padrão: 15)

### Criar Transação
```
POST /api/transactions
```
**Campos obrigatórios:**
- `type`: income/expense
- `amount`: Valor (mínimo 0.01)
- `date`: Data (não pode ser futura)

**Campos opcionais:**
- `category`: Categoria
- `description`: Descrição
- `user_id`: ID do usuário (definido automaticamente conforme permissões)
- `company_id`: ID da empresa (definido automaticamente conforme permissões)

### Visualizar Transação
```
GET /api/transactions/{id}
```

### Atualizar Transação
```
PUT /api/transactions/{id}
```

### Excluir Transação
```
DELETE /api/transactions/{id}
```

## Modelo de Dados

### Transaction
- `id`: UUID (chave primária)
- `user_id`: UUID (usuário da transação)
- `company_id`: UUID (empresa da transação, opcional)
- `type`: Enum (income/expense)
- `category`: String (categoria da transação)
- `description`: Text (descrição detalhada)
- `amount`: Decimal (valor com 2 casas decimais)
- `date`: Date (data da transação)
- `created_at`: Timestamp
- `updated_at`: Timestamp
- `deleted_at`: Timestamp (soft delete)

## Relacionamentos

- **User**: Uma transação pertence a um usuário
- **Company**: Uma transação pode pertencer a uma empresa (opcional)

## Scopes do Modelo

- `forUser($userId)`: Filtra por usuário
- `forCompany($companyId)`: Filtra por empresa
- `ofType($type)`: Filtra por tipo (income/expense)
- `inDateRange($startDate, $endDate)`: Filtra por período

## Validações

- **Tipo**: Deve ser 'income' ou 'expense'
- **Valor**: Deve ser maior que 0.01 e menor que 999.999,99
- **Data**: Não pode ser futura
- **Categoria**: Máximo 255 caracteres
- **Descrição**: Máximo 1000 caracteres
- **Usuário**: Deve existir na base de dados
- **Empresa**: Deve existir na base de dados (se fornecida)

## Exemplos de Uso

### Criar transação pessoal (usuário comum)
```json
{
  "type": "expense",
  "category": "Alimentação",
  "description": "Almoço no restaurante",
  "amount": 25.50,
  "date": "2024-01-15"
}
```

### Criar transação para empresa (companyAdmin)
```json
{
  "type": "income",
  "category": "Vendas",
  "description": "Venda de produtos",
  "amount": 1500.00,
  "date": "2024-01-15",
  "user_id": "550e8400-e29b-41d4-a716-446655440000"
}
```

### Criar transação para qualquer usuário/empresa (adminMaster)
```json
{
  "type": "expense",
  "category": "Marketing",
  "description": "Campanha publicitária",
  "amount": 5000.00,
  "date": "2024-01-15",
  "user_id": "550e8400-e29b-41d4-a716-446655440000",
  "company_id": "550e8400-e29b-41d4-a716-446655440001"
}
```

## Middleware

O módulo utiliza o middleware `CheckRole` para controlar o acesso baseado no nível do usuário. As rotas são protegidas conforme as permissões definidas.

## Testes

Para executar os testes do módulo:
```bash
php artisan test --filter=Transaction
```

## Seeder

Para popular o banco com dados de exemplo:
```bash
php artisan db:seed --class=App\\Modules\\Transactions\\Models\\Seeders\\TransactionSeeder
```

## Factory

O módulo inclui um factory para facilitar a criação de dados de teste:
```php
// Transação básica
Transaction::factory()->create();

// Transação de receita
Transaction::factory()->income()->create();

// Transação de despesa
Transaction::factory()->expense()->create();

// Transação pessoal (sem empresa)
Transaction::factory()->personal()->create();

// Transação para usuário específico
Transaction::factory()->forUser($user)->create();

// Transação para empresa específica
Transaction::factory()->forCompany($company)->create();
```
