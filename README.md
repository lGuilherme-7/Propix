# Propix — Sistema de Orçamentos Profissionais

> Crie, compartilhe e acompanhe propostas comerciais com link único por cliente.

---

## 📋 Sobre o Projeto

O **Propix** é um sistema web completo de gestão de orçamentos com mini CRM integrado. Permite que prestadores de serviço criem propostas profissionais, compartilhem via link exclusivo e acompanhem a aprovação ou recusa em tempo real — tudo em uma interface limpa e moderna.

---


## 🌐 Link para acessar

```
https://propix.xo.je/
```

---

## ✨ Funcionalidades

- Cadastro e login com autenticação segura
- Dashboard com métricas em tempo real
- Criação de orçamentos com valor formatado
- Link único e seguro por orçamento
- Página pública da proposta para o cliente
- Aprovação e recusa com confirmação
- Listagem com filtro por status e busca
- Cada usuário vê apenas seus próprios orçamentos
- Design responsivo — funciona em qualquer dispositivo

---

## 🌐 Fluxo de Uso

```
Cadastro / Login
      ↓
Dashboard — visão geral dos orçamentos
      ↓
Criar orçamento — preenche dados do cliente e serviço
      ↓
Link único gerado e compartilhado com o cliente
      ↓
Cliente acessa o link — aprova ou recusa a proposta
      ↓
Status atualizado — acompanhe em tempo real
```

---

## 🚀 Como Instalar

**1. Clone o repositório:**
```bash
git clone https://github.com/seu-usuario/propix.git
```

**2. Importe o banco de dados** pelo phpMyAdmin ou terminal MySQL.

**3. Configure as credenciais do banco** no arquivo de configuração da aplicação.

**4. Acesse no navegador e crie sua conta.**

---

## 🎨 Tecnologias

| Tecnologia | Uso |
|---|---|
| PHP 8+ | Backend e validações |
| MySQL | Banco de dados |
| HTML5 + CSS3 | Interface responsiva |
| JavaScript ES6+ | Interatividade |
| Google Fonts (Poppins) | Tipografia |

Sem frameworks, sem dependências externas.

---

## 🔐 Segurança

O sistema foi desenvolvido com boas práticas de segurança:

- Senhas armazenadas com hash seguro (bcrypt)
- Proteção contra SQL Injection via prepared statements
- Proteção contra XSS em todos os outputs
- Validação de dados inteiramente no backend
- Sessões seguras com regeneração de ID no login
- Links de orçamentos com tokens impossíveis de adivinhar
- Isolamento de dados por usuário

---

## 📱 Responsividade

Compatível com todos os tamanhos de tela — desktop, tablet e mobile — com menu adaptado para dispositivos móveis.

---

## 🔧 Personalização

As cores, nome do sistema e informações de contato podem ser ajustados diretamente nos arquivos do projeto. A paleta usa variáveis CSS centralizadas para facilitar a customização.

---

## 🚀 Melhorias Futuras

- [ ] Exportar proposta em PDF
- [ ] Envio automático por e-mail ao cliente
- [ ] Notificação via WhatsApp ao aprovar
- [ ] Tema escuro
- [ ] Planos por usuário (SaaS)

---

## 📄 Licença

Este projeto está sob a licença MIT. Sinta-se livre para usar, modificar e distribuir.

---

Feito com 💜 e muito PHP.
