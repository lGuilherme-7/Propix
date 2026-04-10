# Propix — Sistema de Orçamentos Profissionais

> Crie, compartilhe e acompanhe propostas comerciais com link único por cliente.

---

## 📋 Sobre o Projeto

O **Propix** é um sistema web completo de gestão de orçamentos com mini CRM integrado. Permite que prestadores de serviço criem propostas profissionais, compartilhem via link exclusivo e acompanhem a aprovação ou recusa em tempo real — tudo em uma interface limpa e moderna.

---

## 🌐 Fluxo de Uso

```
Login / Cadastro
      ↓
Dashboard — visão geral dos orçamentos
      ↓
Criar orçamento — preenche dados do cliente e serviço
      ↓
Link único gerado — visualizar.php?token=HASH
      ↓
Cliente recebe o link — aprova ou recusa
      ↓
Status atualizado — acompanhe em tempo real
```

---

## ✨ Funcionalidades

- **Cadastro e login** com senha criptografada (bcrypt)
- **Dashboard** com métricas: total, aprovados, pendentes e recusados
- **Criar orçamentos** com campo de valor formatado (R$ 1.000,00)
- **Link único por orçamento** gerado via hash de 32 caracteres
- **Página pública do cliente** — limpa, sem menu, foco na proposta
- **Aprovação e recusa** com modal de confirmação
- **Listagem completa** com filtro por status e busca em tempo real
- **Multi-tenant** — cada usuário vê apenas seus próprios orçamentos
- **Design responsivo** — mobile first com menu hambúrguer

---

## 🗂️ Estrutura do Projeto

```
propix/
│
├── banco.sql                   # Banco de dados completo com dados de teste
├── migration_multitenant.sql   # Migração para adicionar multi-tenant
│
├── config/
│   ├── config.php              # Conexão PDO e constantes
│   └── auth.php                # Proteção de rotas (include nas páginas internas)
│
├── actions/
│   └── acao.php                # Todo o backend: login, cadastro, criar, aprovar, recusar, excluir, logout
│
├── public/                     # Páginas acessíveis sem login
│   ├── index.php               # Login
│   ├── cadastro.php            # Cadastro público
│   └── visualizar.php          # Página da proposta (cliente)
│
└── app/                        # Páginas protegidas (requer login)
    ├── dashboard.php           # Painel principal com métricas
    ├── criar.php               # Criar novo orçamento
    └── orcamentos.php          # Listagem completa com filtros
```

---

## 🚀 Como Instalar

**1. Clone o repositório:**
```bash
git clone https://github.com/seu-usuario/propix.git
```

**2. Importe o banco de dados:**
```bash
mysql -u root -p < banco.sql
```
Ou importe o arquivo `banco.sql` diretamente pelo phpMyAdmin.

**3. Configure a conexão** em `config/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'propix');
define('DB_USER', 'root');    // seu usuário MySQL
define('DB_PASS', '');        // sua senha MySQL
define('APP_URL',  'http://localhost/propix');
```

**4. Acesse no navegador:**
```
https://lguilherme-7.github.io/Propix/
```

**5. Credenciais de teste:**
```
E-mail: admin@propix.com
Senha:  Admin@123
```

> ⚠️ Após o primeiro acesso, troque a senha do admin pelo painel ou diretamente no banco.

---

## ⚙️ Configuração

### Ambiente

No `config/config.php`, altere o ambiente para produção antes de subir:

```php
define('APP_ENV', 'production'); // Oculta erros na tela
```

### URL da aplicação

Ajuste `APP_URL` para refletir o domínio real em produção:

```php
define('APP_URL', 'https://seudominio.com.br');
```

### Geração de senha (se necessário)

Crie um arquivo temporário `gerar_senha.php` na raiz e acesse pelo navegador:

```php
<?php echo password_hash('SuaSenha123', PASSWORD_BCRYPT);
```

Use o hash gerado para atualizar o usuário no banco:

```sql
UPDATE usuarios SET senha = 'HASH_AQUI' WHERE email = 'admin@propix.com';
```

> Apague o arquivo `gerar_senha.php` após o uso.

---

## 🗄️ Banco de Dados

### Tabela `usuarios`

| Coluna | Tipo | Descrição |
|---|---|---|
| id | INT UNSIGNED | Chave primária |
| nome | VARCHAR(120) | Nome do usuário |
| email | VARCHAR(120) | E-mail único |
| senha | VARCHAR(255) | Hash bcrypt |
| data_criacao | DATETIME | Data de cadastro |

### Tabela `orcamentos`

| Coluna | Tipo | Descrição |
|---|---|---|
| id | INT UNSIGNED | Chave primária |
| usuario_id | INT UNSIGNED | Dono do orçamento |
| hash | CHAR(32) | Token único do link |
| cliente | VARCHAR(120) | Nome do cliente |
| email | VARCHAR(120) | E-mail do cliente |
| telefone | VARCHAR(20) | Telefone (opcional) |
| servico | VARCHAR(120) | Nome do serviço |
| valor | DECIMAL(10,2) | Valor da proposta |
| descricao | TEXT | Descrição detalhada |
| prazo | VARCHAR(80) | Prazo de entrega |
| status | ENUM | `pendente` / `aprovado` / `recusado` |
| data_criacao | DATETIME | Data de criação |

---

## 🔐 Segurança

- Senhas com `password_hash()` (bcrypt) e verificadas com `password_verify()`
- `session_regenerate_id(true)` no login — previne session fixation
- PDO com prepared statements em todas as queries — sem SQL injection
- `htmlspecialchars()` em todo output — sem XSS
- `filter_var(FILTER_VALIDATE_EMAIL)` para validação de e-mails
- Rotas internas protegidas via `auth.php` — sem acesso sem sessão
- Link do orçamento via `random_bytes(16)` — hash impossível de adivinhar
- Multi-tenant — usuário só acessa e exclui seus próprios orçamentos

---

## 🎨 Tecnologias

| Tecnologia | Uso |
|---|---|
| PHP 8+ | Backend, validações, sessões |
| MySQL | Banco de dados |
| PDO | Acesso seguro ao banco |
| HTML5 | Estrutura semântica |
| CSS3 | Layout responsivo, variáveis, animações |
| JavaScript ES6+ | Interatividade, máscaras, preview |
| Google Fonts (Poppins) | Tipografia |

Sem frameworks, sem dependências externas, sem `node_modules`.

---

## 📱 Responsividade

| Breakpoint | Comportamento |
|---|---|
| `> 768px` | Sidebar fixa, layout em grid |
| `< 768px` | Topbar + menu hambúrguer com overlay |
| `< 520px` | Colunas colapsadas, tabela simplificada |

---

## 🔧 Personalização Rápida

**Alterar as cores** — edite as variáveis CSS no topo do `<style>` de qualquer página:

```css
:root {
  --roxo:       #7C3AED;  /* Cor primária */
  --roxo-dark:  #5B21B6;  /* Hover e estados ativos */
  --roxo-light: #EDE9FE;  /* Fundos suaves */
  --fundo:      #F8F7FF;  /* Background geral */
}
```

**Alterar nome do sistema** — pesquise e substitua `Propix` nos arquivos PHP e no `<title>` de cada página.

---

## 🚀 Melhorias Futuras

- [ ] Exportar orçamento em PDF
- [ ] Envio automático por e-mail ao cliente
- [ ] Integração com WhatsApp ao aprovar
- [ ] Tema escuro
- [ ] Planos e limites por usuário (SaaS)
- [ ] Histórico de alterações por orçamento

---

## 📦 Deploy

Por ser PHP puro sem dependências, o Propix roda em qualquer hospedagem compartilhada:

- **Hospedagem compartilhada** — upload via FTP + importar SQL pelo phpMyAdmin
- **VPS** — instale Apache/Nginx + PHP 8 + MySQL e aponte para a pasta
- **Docker** — utilize uma imagem `php:8-apache` com volume montado

---

## 📄 Licença

Este projeto está sob a licença MIT. Sinta-se livre para usar, modificar e distribuir.

---

Feito com 💜 e muito PHP.

