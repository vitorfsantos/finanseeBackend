# Configuração do Scheduler para Reset Diário do Banco

## Visão Geral

Este projeto está configurado para executar automaticamente `php artisan migrate:fresh --seed --force` diariamente à meia-noite (00:00) em ambiente de produção.

## Arquivos Criados/Modificados

### 1. Comando Personalizado
- **Arquivo**: `app/Console/Commands/DatabaseResetDailyCommand.php`
- **Comando**: `database:reset-daily`
- **Funcionalidade**: Executa o reset do banco com verificações de segurança

### 2. Configuração do Agendamento
- **Arquivo**: `bootstrap/app.php`
- **Configuração**: Adicionado `->withSchedule()` com o comando agendado

## Configuração no Servidor de Produção

### 1. Configurar o Cron Job

Adicione a seguinte linha ao crontab do servidor:

```bash
# Editar crontab
crontab -e

# Adicionar esta linha (substitua /path/to/your/project pelo caminho real)
* * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
```

### 2. Verificar se o Scheduler está Funcionando

```bash
# Listar tarefas agendadas
php artisan schedule:list

# Executar o scheduler manualmente (para teste)
php artisan schedule:run

# Verificar logs
tail -f storage/logs/database-reset.log
```

## Características de Segurança

### 1. Verificação de Ambiente
- O comando só executa em ambiente de produção
- Retorna erro se executado em outros ambientes

### 2. Logs Detalhados
- Logs de início e fim da operação
- Logs de erro em caso de falha
- Output do comando salvo em `storage/logs/database-reset.log`

### 3. Prevenção de Sobreposição
- `withoutOverlapping()` previne execuções simultâneas
- `runInBackground()` executa em background

## Comandos Disponíveis

### Executar Manualmente (apenas em produção)
```bash
php artisan database:reset-daily
```

### Verificar Status do Scheduler
```bash
php artisan schedule:list
```

### Testar o Scheduler
```bash
php artisan schedule:run
```

## Monitoramento

### Logs do Sistema
- **Laravel Logs**: `storage/logs/laravel.log`
- **Scheduler Logs**: `storage/logs/database-reset.log`

### Verificar Execução
```bash
# Verificar se o cron está rodando
ps aux | grep "schedule:run"

# Verificar logs recentes
tail -f storage/logs/database-reset.log
```

## Troubleshooting

### 1. Scheduler não está executando
- Verificar se o cron job está configurado corretamente
- Verificar permissões do usuário
- Verificar se o PHP está no PATH

### 2. Comando falha
- Verificar logs em `storage/logs/laravel.log`
- Verificar se o ambiente está configurado como 'production'
- Verificar permissões do banco de dados

### 3. Verificar Configuração
```bash
# Verificar ambiente
php artisan env

# Verificar configuração do banco
php artisan config:show database

# Testar conexão com banco
php artisan tinker
>>> DB::connection()->getPdo();
```

## Importante

⚠️ **ATENÇÃO**: Este comando irá **APAGAR TODOS OS DADOS** do banco de dados e recriar as tabelas com dados de seed. Use apenas em ambientes onde isso é desejado (como ambientes de desenvolvimento/teste que precisam ser resetados diariamente).
