# Módulo de Endereços

Este módulo gerencia endereços de forma polimórfica, permitindo que tanto empresas quanto usuários tenham endereços associados.

## Estrutura

### Modelo Address
- **Localização**: `app/Modules/Addresses/Models/Address.php`
- **Relacionamento**: Polimórfico com `addressable_id` e `addressable_type`
- **Campos**:
  - `street` (obrigatório): Rua
  - `number` (opcional): Número
  - `complement` (opcional): Complemento
  - `neighborhood` (opcional): Bairro
  - `city` (obrigatório): Cidade
  - `state` (obrigatório): Estado (2 caracteres)
  - `zipcode` (obrigatório): CEP
  - `country` (opcional): País (padrão: Brasil)

### Serviço AddressService
- **Localização**: `app/Modules/Addresses/Services/AddressService.php`
- **Métodos**:
  - `createOrUpdateAddress()`: Cria ou atualiza endereço
  - `updateAddress()`: Atualiza endereço existente
  - `deleteAddress()`: Remove endereço

## Integração com Empresas

### Modelo Company
O modelo Company foi atualizado para incluir o relacionamento com Address:
```php
public function address(): MorphOne
{
    return $this->morphOne(\App\Modules\Addresses\Models\Address::class, 'addressable');
}
```

### Serviços Atualizados
- **CreateCompanyService**: Agora aceita dados de endereço opcionais
- **UpdateCompanyService**: Permite atualização de endereço junto com dados da empresa

### Requests Atualizados
- **CreateCompanyRequest**: Validação de endereço opcional
- **UpdateCompanyRequest**: Validação de endereço opcional

## Uso

### Criar empresa com endereço
```json
{
  "name": "Empresa Exemplo LTDA",
  "cnpj": "12.345.678/0001-90",
  "email": "contato@empresa.com",
  "phone": "(11) 3333-4444",
  "address": {
    "street": "Rua das Flores",
    "number": "123",
    "complement": "Sala 45",
    "neighborhood": "Centro",
    "city": "São Paulo",
    "state": "SP",
    "zipcode": "01234-567",
    "country": "Brasil"
  }
}
```

### Atualizar empresa com endereço
```json
{
  "name": "Empresa Atualizada LTDA",
  "address": {
    "street": "Nova Rua",
    "city": "Rio de Janeiro",
    "state": "RJ",
    "zipcode": "20000-000"
  }
}
```

## Validações

### Campos obrigatórios quando endereço é fornecido:
- `street`: Rua
- `city`: Cidade
- `state`: Estado (exatamente 2 caracteres)
- `zipcode`: CEP

### Campos opcionais:
- `number`: Número
- `complement`: Complemento
- `neighborhood`: Bairro
- `country`: País (padrão: Brasil)

## Comportamento

1. **Endereço opcional**: Empresas podem ser criadas/atualizadas sem endereço
2. **Substituição**: Se um endereço já existe, ele será substituído pelo novo
3. **Relacionamento polimórfico**: Permite que o mesmo sistema gerencie endereços de usuários e empresas
4. **Carregamento automático**: Endereços são carregados automaticamente nas consultas de empresas

