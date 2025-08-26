# Organização das Rotas da API - FinanSee

## Estrutura Atual

### **Rotas Públicas** (sem autenticação)
```php
// Login público
Route::post('/auth/login', [\App\Modules\Auth\Controllers\AuthController::class, 'login'])->name('auth.login');
```

### **Rotas Autenticadas** (com auth:sanctum)
```php
Route::middleware(['auth:sanctum'])->group(function () {
  // Rota para obter usuário atual
  Route::get('/user', function (Request $request) {
    return $request->user();
  });

  // Auth module - rotas autenticadas (logout)
  Route::group([], base_path('app/Modules/Auth/routes.php'));

  // Users module - rotas autenticadas
  Route::group([], base_path('app/Modules/Users/routes.php'));
});
```

## Permissionamento por Módulo

### **Módulo Auth** (`app/Modules/Auth/routes.php`)
```php
Route::prefix('auth')->group(function () {
  Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
});
```

### **Módulo Users** (`app/Modules/Users/routes.php`)
```php
Route::prefix('users')->group(function () {
  // Rotas para todos os usuários autenticados
  Route::get('/', ListUsersController::class)->name('users.index');
  Route::get('/{user}', ShowUserController::class)->name('users.show');
  
  // Rotas que precisam de permissão de admin
  Route::middleware(['role:admin'])->group(function () {
    Route::post('/', CreateUserController::class)->name('users.store');
    Route::put('/{user}', UpdateUserController::class)->name('users.update');
    Route::delete('/{user}', DeleteUserController::class)->name('users.destroy');
  });
  
  // Rotas que precisam de permissão de manager
  Route::middleware(['role:manager'])->group(function () {
    // Route::put('/{user}/status', UpdateUserStatusController::class)->name('users.update-status');
  });
});
```

## Middleware de Role

### **CheckRole Middleware** (`app/Http/Middleware/CheckRole.php`)
```php
// Registrado como 'role' no bootstrap/app.php
Route::middleware(['role:admin'])->group(function () {
  // Rotas que precisam de permissão de admin
});

Route::middleware(['role:manager'])->group(function () {
  // Rotas que precisam de permissão de manager
});
```

### **Lógica de Verificação de Role**
```php
private function userHasRole($user, string $role): bool
{
  if ($role === 'admin') {
    return in_array($user->email, ['admin@finansee.com']);
  }
  
  if ($role === 'manager') {
    return in_array($user->email, ['admin@finansee.com', 'manager@finansee.com']);
  }
  
  return false;
}
```

## Vantagens desta Organização

### **1. Controle Centralizado no api.php**
- Login público definido diretamente
- Agrupamento claro de rotas autenticadas
- Fácil visualização da estrutura

### **2. Permissionamento Granular por Módulo**
- Cada módulo define seus próprios níveis de acesso
- Middleware de role aplicado onde necessário
- Flexibilidade para diferentes permissões

### **3. Separação de Responsabilidades**
- `api.php`: Controle geral de autenticação
- `routes.php` dos módulos: Controle de permissões específicas
- Middleware: Lógica de verificação de roles

## Como Adicionar Novos Módulos

### **1. Módulo Simples (apenas autenticação)**
```php
// Em routes/api.php
Route::middleware(['auth:sanctum'])->group(function () {
  Route::group([], base_path('app/Modules/NewModule/routes.php'));
});

// Em app/Modules/NewModule/routes.php
Route::prefix('new-module')->group(function () {
  Route::get('/', ListController::class)->name('new-module.index');
  Route::post('/', CreateController::class)->name('new-module.store');
});
```

### **2. Módulo com Permissionamento**
```php
// Em app/Modules/NewModule/routes.php
Route::prefix('new-module')->group(function () {
  // Rotas para todos os usuários autenticados
  Route::get('/', ListController::class)->name('new-module.index');
  
  // Rotas para admin
  Route::middleware(['role:admin'])->group(function () {
    Route::post('/', CreateController::class)->name('new-module.store');
    Route::delete('/{id}', DeleteController::class)->name('new-module.destroy');
  });
  
  // Rotas para manager
  Route::middleware(['role:manager'])->group(function () {
    Route::put('/{id}', UpdateController::class)->name('new-module.update');
  });
});
```

## Fluxo de Autenticação e Autorização

1. **Usuário acessa rota pública** (`POST /api/auth/login`)
2. **Faz login** e recebe token
3. **Usa token** para acessar rotas autenticadas
4. **Middleware auth:sanctum** valida o token
5. **Middleware role** verifica permissões específicas
6. **Acesso permitido** ou negado conforme permissões

## Rotas Disponíveis

### **Públicas:**
- `POST /api/auth/login` - Login de usuário

### **Autenticadas (todos os usuários):**
- `GET /api/user` - Obter usuário atual
- `POST /api/auth/logout` - Logout de usuário
- `GET /api/users` - Listar usuários
- `GET /api/users/{id}` - Mostrar usuário

### **Autenticadas (apenas admin):**
- `POST /api/users` - Criar usuário
- `PUT /api/users/{id}` - Atualizar usuário
- `DELETE /api/users/{id}` - Excluir usuário

### **Autenticadas (apenas manager):**
- (Exemplo comentado para futuras implementações)
