# Guia de Testes

Este documento descreve como executar e entender os testes criados para os módulos do sistema.

## Estrutura dos Testes

Os testes estão organizados por módulo dentro de `app/Modules/{ModuleName}/Tests/`:

### Módulo Auth
- `AuthControllerTest.php` - Testa o controlador de autenticação
- `AuthServiceTest.php` - Testa o serviço de autenticação
- `LoginRequestTest.php` - Testa a validação da requisição de login
- `AuthIntegrationTest.php` - Testes de integração para autenticação

### Módulo Companies
- `CompanyTest.php` - Testa o model Company
- `CreateCompanyControllerTest.php` - Testa o controlador de criação de empresa
- `CreateCompanyServiceTest.php` - Testa o serviço de criação de empresa
- `ListCompaniesServiceTest.php` - Testa o serviço de listagem de empresas
- `CreateCompanyRequestTest.php` - Testa a validação da requisição de criação
- `CompaniesIntegrationTest.php` - Testes de integração para empresas

### Módulo Users
- `UserTest.php` - Testa o model User
- `UserLevelTest.php` - Testa o model UserLevel
- `CreateUserControllerTest.php` - Testa o controlador de criação de usuário
- `CreateUserServiceTest.php` - Testa o serviço de criação de usuário
- `CreateUserRequestTest.php` - Testa a validação da requisição de criação
- `UsersIntegrationTest.php` - Testes de integração para usuários

## Como Executar os Testes

### Executar todos os testes
```bash
php artisan test
```

### Executar testes por módulo

#### Opção 1: Por diretório
```bash
php artisan test app/Modules/Auth/Tests/
php artisan test app/Modules/Companies/Tests/
php artisan test app/Modules/Users/Tests/
```

#### Opção 2: Por testsuite (recomendado)
```bash
php artisan test --testsuite=Auth
php artisan test --testsuite=Companies
php artisan test --testsuite=Users
php artisan test --testsuite=Modules  # Todos os módulos
```

### Executar testes específicos

#### Executar um arquivo de teste específico
```bash
php artisan test app/Modules/Auth/Tests/AuthServiceTest.php
```

#### Executar um método de teste específico
```bash
php artisan test --filter=it_can_login_with_valid_credentials
```

### Executar com cobertura de código
```bash
php artisan test --coverage
```

### Executar com relatório detalhado
```bash
php artisan test --verbose
```

## Configuração para Testes

### Banco de Dados de Teste
Os testes utilizam um banco SQLite em memória por padrão. Para configurar:

1. Certifique-se de que a extensão SQLite está instalada no PHP
2. O arquivo `phpunit.xml` já está configurado para usar SQLite em memória

### Variáveis de Ambiente para Teste
As seguintes variáveis são configuradas automaticamente durante os testes:
- `DB_CONNECTION=sqlite`
- `DB_DATABASE=:memory:`
- `CACHE_DRIVER=array`
- `SESSION_DRIVER=array`
- `QUEUE_CONNECTION=sync`

## Tipos de Testes

### Unit Tests (Testes Unitários)
Testam componentes individuais isoladamente:
- Models
- Services
- Request validations
- Métodos específicos

### Integration Tests (Testes de Integração)
Testam a integração entre componentes:
- Fluxos completos de API
- Autenticação e autorização
- Persistência de dados
- Validações end-to-end

## Cobertura dos Testes

### Auth Module
- ✅ Login com credenciais válidas
- ✅ Login com credenciais inválidas
- ✅ Logout de usuário autenticado
- ✅ Validação de campos obrigatórios
- ✅ Validação de formato de email
- ✅ Geração e revogação de tokens

### Companies Module
- ✅ Criação de empresa (CRUD)
- ✅ Listagem paginada de empresas
- ✅ Busca por nome/CNPJ
- ✅ Validação de campos obrigatórios
- ✅ Validação de CNPJ único
- ✅ Soft delete
- ✅ Autorização por role

### Users Module
- ✅ Criação de usuário (CRUD)
- ✅ Listagem paginada de usuários
- ✅ Níveis de usuário (Admin Master, Company Admin, Regular)
- ✅ Hash de senhas
- ✅ Validação de campos obrigatórios
- ✅ Validação de email único
- ✅ Relacionamentos entre modelos
- ✅ Autorização por role

## Mocking e Stubs

Os testes utilizam Mockery para criar mocks de dependências:
- Services são mockados nos testes de Controllers
- Requests são mockados para validação
- Modelos são criados com Factories

## Factories

### UserFactory
Cria usuários para testes com dados fake realistas.

### UserLevelFactory
Cria níveis de usuário com métodos auxiliares:
- `adminMaster()` - Cria Admin Master (ID: 1)
- `companyAdmin()` - Cria Company Admin (ID: 2)
- `regularUser()` - Cria Regular User (ID: 3)

### CompanyFactory
Cria empresas para testes com CNPJs válidos.

## Test Suites Disponíveis

O PHPUnit está configurado com os seguintes test suites:

- **Unit** - Testes unitários tradicionais (tests/Unit)
- **Feature** - Testes de feature tradicionais (tests/Feature)
- **Auth** - Testes do módulo de autenticação
- **Companies** - Testes do módulo de empresas
- **Users** - Testes do módulo de usuários
- **Modules** - Todos os testes de módulos (usando wildcard)

### Executar por test suite
```bash
php artisan test --testsuite=Auth
php artisan test --testsuite=Companies
php artisan test --testsuite=Users
php artisan test --testsuite=Modules
```

### Combinar test suites
```bash
php artisan test --testsuite=Auth,Companies
```

## Comandos Úteis

### Executar apenas testes que falharam
```bash
php artisan test --stop-on-failure
```

### Executar testes em paralelo (mais rápido)
```bash
php artisan test --parallel
```

### Gerar relatório de cobertura HTML
```bash
php artisan test --coverage-html reports/
```

### Executar test suite com cobertura
```bash
php artisan test --testsuite=Modules --coverage
```

## Debugging de Testes

### Ver saída detalhada
```bash
php artisan test --verbose
```

### Debuggar teste específico
```bash
php artisan test --filter=nome_do_teste --verbose
```

### Log durante testes
Use `dump()` ou `dd()` nos testes para debug:
```php
public function test_example()
{
    $result = $this->service->method();
    dump($result); // Para debug
    $this->assertEquals('expected', $result);
}
```

## Boas Práticas

1. **Use nomes descritivos**: `it_can_create_user_with_valid_data`
2. **Arrange, Act, Assert**: Organize seus testes nessa estrutura
3. **Um teste, uma responsabilidade**: Cada teste deve verificar apenas uma coisa
4. **Use factories**: Para criar dados de teste consistentes
5. **Mock dependências**: Para testes unitários isolados
6. **Teste casos de erro**: Não teste apenas o caminho feliz

## Troubleshooting

### Erro de permissão no SQLite
```bash
sudo chmod 777 storage/
```

### Erro de namespace
Certifique-se de que o autoload está atualizado:
```bash
composer dump-autoload
```

### Testes lentos
Use `--parallel` para executar em paralelo ou `--stop-on-failure` para parar no primeiro erro.
