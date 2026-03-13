# Payment Gateway API

API REST para gerenciar pagamentos multi-gateway com fallback por prioridade.

**Nivel:** 2 (Junior)

## Requisitos
- PHP 8.2+
- Composer
- MySQL
- Docker (opcional, para subir o ambiente completo)

## Subindo com Docker
```bash
docker compose up -d
docker compose exec app php artisan migrate:fresh --seed
```

## Subindo local (sem Docker)
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

## Seed de teste
O seeder cria:
- 1 usuario admin (`admin@local.test` / `password`)
- 2 gateways
- 1 cliente
- 2 produtos
- 1 transacao + itens

Rodar manualmente:
```bash
php artisan db:seed
```

## Autenticacao
Base URL (local): `http://localhost:8000/api`

### Login (publica)
- Metodo: `POST`
- URL: `/login`
- Headers:
  - `Content-Type: application/json`
- Body:
```json
{
  "email": "admin@local.test",
  "password": "password"
}
```
Retorna token para usar nas rotas privadas:
```
Authorization: Bearer <token>
```

### Compra (publica)
- Metodo: `POST`
- URL: `/transactions`
- Headers:
  - `Content-Type: application/json`
- Body:
```json
{
  "client_id": 1,
  "products": [
    { "id": 1, "quantity": 1 }
  ],
  "card_number": "5569000000006063",
  "cvv": "010"
}
```

## Rotas principais
### Rotas privadas (Bearer Token)
Headers padrao:
- `Authorization: Bearer <token>`

#### Users
- `GET /users`
- `POST /users`
Body:
```json
{
  "name": "Manager Teste",
  "email": "manager@local.test",
  "role": "manager",
  "password": "password"
}
```
- `GET /users/{id}`
- `PATCH /users/{id}`
Body:
```json
{
  "name": "Novo Nome"
}
```
- `DELETE /users/{id}`

#### Products
- `GET /products`
- `POST /products`
Body:
```json
{
  "name": "Produto X",
  "quantity": 10,
  "amount": 19.90
}
```
- `GET /products/{id}`
- `PATCH /products/{id}`
Body:
```json
{
  "amount": 29.90
}
```
- `DELETE /products/{id}`

#### Clients
- `GET /clients`
- `GET /clients/{id}`
- `GET /clients/{id}/details`
- `GET /clients/{id}/purchases`
- `GET /clients/purchases/{transaction_id}`

#### Transactions
- `GET /transactions`
- `GET /transactions/{id}`
- `POST /transactions/{id}/refund`

#### Clients Refund
- `POST /clients/purchases/{transaction_id}/refund`

#### Gateways
- `PATCH /gateways/{id}/activate`
- `PATCH /gateways/{id}/deactivate`
- `PATCH /gateways/{id}/priority`
Body:
```json
{
  "priority": 1
}
```

## Gateways mock
O compose ja sobe os mocks:
- Gateway 1: `http://localhost:3001`
- Gateway 2: `http://localhost:3002`

Variaveis de ambiente usadas:
- `GATEWAY1_URL`
- `GATEWAY1_EMAIL`
- `GATEWAY1_TOKEN`
- `GATEWAY2_URL`
- `GATEWAY2_TOKEN`
- `GATEWAY2_SECRET`

## Postman
Login:
![Requisicao de login](public/Requisicao-de-login.png)

Listagem de produtos:
![Requisicao de listagem de produtos](public/Requisicao-de-listagem-produtos.png)

Criacao de produto:
![Requisicao cria produto](public/Requisicao-cria-produto.png)

Compra (transactions):
![Requisicao transactions](public/Requisicao-transactions.png)
