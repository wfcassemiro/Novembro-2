# 🔌 Guia de Integração AJAX

## Visão Geral

Este documento explica como integrar o endpoint `ajax_provider_rates.php` ao frontend para buscar taxas de fornecedores dinamicamente.

---

## 📡 Endpoint Disponível

### GET /ajax_provider_rates.php

**Parâmetros:**
- `provider_id` (int, obrigatório) - ID do fornecedor

**Response:**
```json
{
  "success": true,
  "provider": {
    "id": 5,
    "name": "João Silva",
    "currency": "BRL"
  },
  "services": [
    "Tradução",
    "Revisão",
    "Legendagem"
  ],
  "rates": {
    "Tradução": {
      "rate": 0.25,
      "unit": "palavra",
      "currency": "BRL",
      "lang_from": "EN",
      "lang_to": "PT-BR"
    },
    "Revisão": {
      "rate": 0.15,
      "unit": "palavra",
      "currency": "BRL",
      "lang_from": null,
      "lang_to": null
    }
  }
}
```

**Erro:**
```json
{
  "error": "Fornecedor não encontrado"
}
```

---

## 💻 Implementação no Frontend

### Opção 1: JavaScript Vanilla

```javascript
// Adicione este código ao final do budget_c.php, dentro da tag <script>

document.addEventListener('DOMContentLoaded', function() {
    const providerSelect = document.getElementById('provider_id');
    const costServiceInput = document.getElementById('cost_service');
    const costValueInput = document.getElementById('cost_value');
    
    if (providerSelect) {
        providerSelect.addEventListener('change', function() {
            const providerId = this.value;
            
            // Ignora opções especiais
            if (providerId === 'interno' || providerId === 'outro' || !providerId) {
                costServiceInput.value = '';
                costValueInput.value = '0,00';
                return;
            }
            
            // Busca dados do fornecedor
            fetch(`ajax_provider_rates.php?provider_id=${providerId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        handleProviderData(data);
                    } else {
                        console.error('Erro:', data.error);
                        costValueInput.value = '0,20'; // Valor padrão
                    }
                })
                .catch(error => {
                    console.error('Erro na requisição:', error);
                    costValueInput.value = '0,20'; // Valor padrão
                });
        });
    }
    
    function handleProviderData(data) {
        // Se houver apenas um serviço, preenche automaticamente
        if (data.services.length === 1) {
            const service = data.services[0];
            costServiceInput.value = service;
            
            // Preenche taxa se disponível
            if (data.rates[service]) {
                const rate = data.rates[service].rate;
                costValueInput.value = formatBRL(rate);
            }
        } else {
            // Múltiplos serviços: pode implementar datalist ou select
            createServiceDatalist(data.services, data.rates);
        }
    }
    
    function createServiceDatalist(services, rates) {
        // Remove datalist existente se houver
        let existingDatalist = document.getElementById('services_datalist');
        if (existingDatalist) {
            existingDatalist.remove();
        }
        
        // Cria novo datalist
        const datalist = document.createElement('datalist');
        datalist.id = 'services_datalist';
        
        services.forEach(service => {
            const option = document.createElement('option');
            option.value = service;
            datalist.appendChild(option);
        });
        
        costServiceInput.setAttribute('list', 'services_datalist');
        document.body.appendChild(datalist);
        
        // Listener para atualizar taxa quando selecionar serviço
        costServiceInput.addEventListener('input', function() {
            const selectedService = this.value;
            if (rates[selectedService]) {
                const rate = rates[selectedService].rate;
                costValueInput.value = formatBRL(rate);
            }
        });
    }
    
    function formatBRL(value) {
        return parseFloat(value).toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 4
        });
    }
});
```

---

### Opção 2: jQuery (se disponível)

```javascript
$(document).ready(function() {
    $('#provider_id').on('change', function() {
        const providerId = $(this).val();
        
        if (providerId === 'interno' || providerId === 'outro' || !providerId) {
            $('#cost_service').val('');
            $('#cost_value').val('0,00');
            return;
        }
        
        $.ajax({
            url: 'ajax_provider_rates.php',
            method: 'GET',
            data: { provider_id: providerId },
            dataType: 'json',
            success: function(data) {
                if (data.success) {
                    handleProviderData(data);
                } else {
                    console.error('Erro:', data.error);
                    $('#cost_value').val('0,20');
                }
            },
            error: function(xhr, status, error) {
                console.error('Erro na requisição:', error);
                $('#cost_value').val('0,20');
            }
        });
    });
    
    function handleProviderData(data) {
        if (data.services.length === 1) {
            const service = data.services[0];
            $('#cost_service').val(service);
            
            if (data.rates[service]) {
                const rate = data.rates[service].rate;
                $('#cost_value').val(formatBRL(rate));
            }
        } else {
            createServiceAutocomplete(data.services, data.rates);
        }
    }
    
    function createServiceAutocomplete(services, rates) {
        // jQuery UI Autocomplete (se disponível)
        $('#cost_service').autocomplete({
            source: services,
            select: function(event, ui) {
                const selectedService = ui.item.value;
                if (rates[selectedService]) {
                    const rate = rates[selectedService].rate;
                    $('#cost_value').val(formatBRL(rate));
                }
            }
        });
    }
    
    function formatBRL(value) {
        return parseFloat(value).toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 4
        });
    }
});
```

---

## 🎯 Fluxo de Interação Completo

```
1. Usuário seleciona fornecedor
   ↓
2. JavaScript detecta mudança (event 'change')
   ↓
3. Faz requisição AJAX para ajax_provider_rates.php
   ↓
4. Backend busca dados no BD
   ↓
5. Retorna JSON com serviços e taxas
   ↓
6. Frontend processa resposta:
   
   Caso A: Apenas 1 serviço
   ├─ Preenche campo "Serviço" automaticamente
   └─ Preenche campo "Valor" com taxa cadastrada
   
   Caso B: Múltiplos serviços
   ├─ Cria datalist/autocomplete com opções
   ├─ Usuário seleciona serviço
   └─ Preenche campo "Valor" com taxa correspondente
   
   Caso C: Nenhuma taxa cadastrada
   └─ Preenche valor padrão: 0,20
```

---

## 📝 Exemplo de Uso Real

### Cenário 1: Fornecedor com taxa cadastrada

```
Usuário seleciona: "João Silva" (ID: 5)
                      ↓
        AJAX GET ajax_provider_rates.php?provider_id=5
                      ↓
              Backend consulta BD
                      ↓
              Encontra taxas:
              - Tradução: R$ 0,25/palavra
              - Revisão: R$ 0,15/palavra
                      ↓
              Retorna JSON
                      ↓
        Frontend preenche campos:
        ┌────────────────────────────────────┐
        │ Fornecedor: João Silva             │
        │ Serviço: [Tradução ▼] (datalist)  │
        │ Valor: 0,25                        │
        │ [+ Adicionar]                      │
        └────────────────────────────────────┘
```

### Cenário 2: Fornecedor sem taxa cadastrada

```
Usuário seleciona: "Maria Costa" (ID: 8)
                      ↓
        AJAX GET ajax_provider_rates.php?provider_id=8
                      ↓
              Backend consulta BD
                      ↓
              Não encontra taxas
              Verifica services_offered: "Interpretação, Legendagem"
                      ↓
              Retorna JSON com serviços, mas sem rates
                      ↓
        Frontend preenche campos:
        ┌────────────────────────────────────┐
        │ Fornecedor: Maria Costa            │
        │ Serviço: [Interpretação ▼]        │
        │ Valor: 0,20 (padrão)               │
        │ [+ Adicionar]                      │
        └────────────────────────────────────┘
        
        Usuário pode editar valor manualmente
```

---

## 🔧 Personalização Avançada

### Adicionar Loading Spinner

```javascript
providerSelect.addEventListener('change', function() {
    const providerId = this.value;
    
    if (providerId === 'interno' || providerId === 'outro' || !providerId) {
        return;
    }
    
    // Mostra spinner
    costValueInput.value = 'Carregando...';
    costValueInput.disabled = true;
    
    fetch(`ajax_provider_rates.php?provider_id=${providerId}`)
        .then(response => response.json())
        .then(data => {
            // Remove spinner
            costValueInput.disabled = false;
            
            if (data.success) {
                handleProviderData(data);
            } else {
                costValueInput.value = '0,20';
            }
        })
        .catch(error => {
            costValueInput.disabled = false;
            costValueInput.value = '0,20';
        });
});
```

### Cachear Requisições

```javascript
const providerCache = {};

providerSelect.addEventListener('change', function() {
    const providerId = this.value;
    
    // Verifica cache
    if (providerCache[providerId]) {
        handleProviderData(providerCache[providerId]);
        return;
    }
    
    // Faz requisição
    fetch(`ajax_provider_rates.php?provider_id=${providerId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Salva no cache
                providerCache[providerId] = data;
                handleProviderData(data);
            }
        });
});
```

### Exibir Mensagem de Erro Amigável

```javascript
fetch(`ajax_provider_rates.php?provider_id=${providerId}`)
    .then(response => {
        if (!response.ok) {
            throw new Error('Erro na requisição');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            handleProviderData(data);
        } else {
            showNotification('Aviso', data.error, 'warning');
            costValueInput.value = '0,20';
        }
    })
    .catch(error => {
        showNotification('Erro', 'Não foi possível carregar dados do fornecedor', 'error');
        costValueInput.value = '0,20';
    });

function showNotification(title, message, type) {
    // Implementação de notificação toast
    // Pode usar bibliotecas como Toastify, SweetAlert, etc.
    alert(`${title}: ${message}`);
}
```

---

## 🎨 UI/UX Melhorias

### Select Melhorado com Imagens

```javascript
function enhanceProviderSelect() {
    // Transforma select em componente customizado
    // com avatar e informações do fornecedor
    
    // Biblioteca sugerida: Select2, Choices.js
    $('#provider_id').select2({
        placeholder: 'Selecione um fornecedor',
        templateResult: formatProvider,
        templateSelection: formatProviderSelection
    });
}

function formatProvider(provider) {
    if (!provider.id) return provider.text;
    
    return $(`
        <span>
            <i class="fas fa-user-circle"></i>
            ${provider.text}
        </span>
    `);
}
```

### Autocomplete de Serviços com Descrição

```javascript
function createEnhancedServiceInput(services, rates) {
    $('#cost_service').autocomplete({
        source: services.map(service => ({
            label: `${service} - ${rates[service] ? formatBRL(rates[service].rate) + '/' + rates[service].unit : 'Taxa não cadastrada'}`,
            value: service
        })),
        select: function(event, ui) {
            const service = ui.item.value;
            if (rates[service]) {
                $('#cost_value').val(formatBRL(rates[service].rate));
            }
        }
    });
}
```

---

## ⚠️ Tratamento de Erros

### Possíveis Erros e Soluções

| Erro | Causa | Solução |
|------|-------|---------|
| 401 Unauthorized | Usuário não logado | Redirecionar para login |
| 404 Not Found | Endpoint não existe | Verificar caminho do arquivo |
| 500 Internal Error | Erro no servidor | Verificar logs PHP, conexão BD |
| Network Error | Problema de rede | Retry com backoff |
| Empty Response | BD vazio | Mostrar mensagem amigável |

### Implementação de Retry

```javascript
async function fetchProviderRatesWithRetry(providerId, maxRetries = 3) {
    for (let i = 0; i < maxRetries; i++) {
        try {
            const response = await fetch(`ajax_provider_rates.php?provider_id=${providerId}`);
            if (!response.ok) throw new Error('Request failed');
            
            const data = await response.json();
            return data;
        } catch (error) {
            if (i === maxRetries - 1) throw error;
            
            // Espera 1s antes de tentar novamente
            await new Promise(resolve => setTimeout(resolve, 1000));
        }
    }
}
```

---

## 📚 Recursos Adicionais

### Bibliotecas Recomendadas

1. **Axios** - Cliente HTTP simplificado
2. **jQuery** - Se já estiver no projeto
3. **Select2** - Selects avançados
4. **Choices.js** - Alternativa leve ao Select2
5. **Toastify** - Notificações toast
6. **SweetAlert2** - Modais bonitos

### Links Úteis

- [Fetch API MDN](https://developer.mozilla.org/pt-BR/docs/Web/API/Fetch_API)
- [jQuery AJAX](https://api.jquery.com/jquery.ajax/)
- [Select2 Documentation](https://select2.org/)

---

## ✅ Checklist de Implementação

- [ ] Copiar `ajax_provider_rates.php` para o diretório correto
- [ ] Ajustar caminhos de `require_once` no endpoint
- [ ] Adicionar código JavaScript ao `budget_c.php`
- [ ] Testar com fornecedor que tem taxas cadastradas
- [ ] Testar com fornecedor sem taxas cadastradas
- [ ] Testar com opções "Interno" e "Outro"
- [ ] Implementar tratamento de erros
- [ ] Adicionar loading spinner (opcional)
- [ ] Implementar cache (opcional)
- [ ] Adicionar notificações toast (opcional)

---

**Última atualização:** 16/11/2024  
**Status:** Documentação completa ✅
