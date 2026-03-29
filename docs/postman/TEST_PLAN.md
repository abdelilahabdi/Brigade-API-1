# RestaurantAI-Brigade API V2 - Postman Test Plan

## 1) Collection structure

- Auth
  - Register Client
  - Register Admin
  - Login Client
  - Login Admin
  - Me
  - Logout Client
  - Access Without Token
- Profile
  - Get Profile
  - Update Dietary Tags
  - Invalid Dietary Tags
- Categories
  - List Categories
  - Create Category (Admin)
  - Update Category (Admin)
  - Delete Category (Admin)
  - Get Plates By Category
  - Client Forbidden On Category Write Routes
  - Reject Delete Category With Active Plates
- Plates
  - List Plates
  - Show Plate
  - Create Plate (Admin)
  - Update Plate (Admin)
  - Delete Plate (Admin)
  - Client Forbidden On Plate Write Routes
  - Invalid Category ID
  - Invalid Ingredient IDs
- Ingredients
  - List Ingredients (Admin)
  - Create Ingredient (Admin)
  - Update Ingredient (Admin)
  - Delete Ingredient (Admin)
  - Client Forbidden On Ingredient Routes
  - Invalid Ingredient Tags
- Recommendations
  - Analyze Plate
  - Check Analyze Immediate 202
  - Get Recommendation By Plate
  - Get Recommendation History
  - Verify Ready Status After Queue Job
  - Verify Score Label Warning Conflicts
- Admin Stats
  - Get Admin Stats (Admin)
  - Client Forbidden On Admin Stats
  - Validate Admin Stats JSON Shape
- Error Cases
  - 401 Unauthenticated
  - 403 Forbidden
  - 404 Not Found
  - 422 Validation Error

## 2) Environment variables

| Variable | Example | Usage |
|---|---|---|
| `base_url` | `http://127.0.0.1:8000/api` | Base API URL |
| `token_client` | empty | Saved after client login |
| `token_admin` | empty | Saved after admin login |
| `category_id` | empty | Saved after create category |
| `plate_id` | empty | Saved after create plate |
| `ingredient_id` | empty | Saved after create ingredient |
| `recommendation_plate_id` | empty | Plate target for recommendation analyze |
| `recommendation_poll_attempts` | `10` | Optional polling script |

## 3) Scenario details

### Auth

| Test name | Objective | Method | URL | Headers | Body | Expected result | HTTP |
|---|---|---|---|---|---|---|---|
| Register Client | Create a client account | POST | `{{base_url}}/register` | `Content-Type: application/json` | `{ "name":"Client One", "email":"client1@example.com", "password":"password123", "password_confirmation":"password123", "role":"client", "dietary_tags":["vegan"] }` | User + token returned | 201 |
| Register Admin | Create an admin account | POST | `{{base_url}}/register` | `Content-Type: application/json` | `{ "name":"Admin One", "email":"admin1@example.com", "password":"password123", "password_confirmation":"password123", "role":"admin" }` | User + token returned | 201 |
| Login Client | Login client user | POST | `{{base_url}}/login` | `Content-Type: application/json` | `{ "email":"client1@example.com", "password":"password123" }` | Token client saved | 200 |
| Login Admin | Login admin user | POST | `{{base_url}}/login` | `Content-Type: application/json` | `{ "email":"admin1@example.com", "password":"password123" }` | Token admin saved | 200 |
| Me | Get current user | GET | `{{base_url}}/me` | `Authorization: Bearer {{token_client}}` | - | Returns authenticated user | 200 |
| Logout | Delete current token | POST | `{{base_url}}/logout` | `Authorization: Bearer {{token_client}}` | - | Success message | 200 |
| Access Without Token | Check auth guard | GET | `{{base_url}}/me` | none | - | Unauthenticated error | 401 |

### Profile

| Test name | Objective | Method | URL | Headers | Body | Expected result | HTTP |
|---|---|---|---|---|---|---|---|
| Get Profile | Read dietary profile | GET | `{{base_url}}/profile` | `Authorization: Bearer {{token_client}}` | - | Profile object returned | 200 |
| Update Dietary Tags | Update restrictions | PUT | `{{base_url}}/profile` | `Authorization: Bearer {{token_client}}`, `Content-Type: application/json` | `{ "dietary_tags":["gluten_free","no_lactose"] }` | Profile updated | 200 |
| Invalid Dietary Tags | Validate tags enum | PUT | `{{base_url}}/profile` | `Authorization: Bearer {{token_client}}`, `Content-Type: application/json` | `{ "dietary_tags":["invalid_tag"] }` | Validation error | 422 |

### Categories

| Test name | Objective | Method | URL | Headers | Body | Expected result | HTTP |
|---|---|---|---|---|---|---|---|
| List Categories | Read list as authenticated | GET | `{{base_url}}/categories` | `Authorization: Bearer {{token_client}}` | - | Categories array | 200 |
| Create Category Admin | Create category | POST | `{{base_url}}/categories` | `Authorization: Bearer {{token_admin}}`, `Content-Type: application/json` | `{ "name":"Healthy", "description":"Healthy dishes", "color":"#22C55E", "is_active":true }` | Category created + save `category_id` | 201 |
| Update Category Admin | Edit category | PUT | `{{base_url}}/categories/{{category_id}}` | `Authorization: Bearer {{token_admin}}`, `Content-Type: application/json` | `{ "name":"Healthy Updated", "description":"Updated", "color":"#16A34A", "is_active":true }` | Category updated | 200 |
| Delete Category Admin | Delete category | DELETE | `{{base_url}}/categories/{{category_id}}` | `Authorization: Bearer {{token_admin}}` | - | Category deleted if allowed | 200 |
| Client Forbidden Category Write | Enforce admin role | POST/PUT/DELETE | Category write URLs | `Authorization: Bearer {{token_client}}` | valid body | Forbidden | 403 |
| Get Plates By Category | Category->plates relation | GET | `{{base_url}}/categories/{{category_id}}/plates` | `Authorization: Bearer {{token_client}}` | - | Plate list by category | 200 |
| Reject Delete Active Category | Prevent deletion with active plates | DELETE | `{{base_url}}/categories/{{category_id}}` | `Authorization: Bearer {{token_admin}}` | - | Conflict message | 409 |

### Plates

| Test name | Objective | Method | URL | Headers | Body | Expected result | HTTP |
|---|---|---|---|---|---|---|---|
| List Plates | Paginated plates list | GET | `{{base_url}}/plates?per_page=10` | `Authorization: Bearer {{token_client}}` | - | Pagination + eager-loaded relations | 200 |
| Show Plate | Get one plate | GET | `{{base_url}}/plates/{{plate_id}}` | `Authorization: Bearer {{token_client}}` | - | Plate details | 200 |
| Create Plate Admin | Create plate with ingredients | POST | `{{base_url}}/plates` | `Authorization: Bearer {{token_admin}}`, `Content-Type: application/json` | `{ "name":"Protein Bowl", "description":"Lean meal", "price":18.0, "category_id":{{category_id}}, "ingredient_ids":[{{ingredient_id}}], "is_available":true }` | Plate created + save `plate_id` and `recommendation_plate_id` | 201 |
| Update Plate Admin | Update plate fields | PUT | `{{base_url}}/plates/{{plate_id}}` | `Authorization: Bearer {{token_admin}}`, `Content-Type: application/json` | `{ "name":"Protein Bowl XL", "price":19.5, "ingredient_ids":[{{ingredient_id}}] }` | Plate updated | 200 |
| Delete Plate Admin | Delete plate | DELETE | `{{base_url}}/plates/{{plate_id}}` | `Authorization: Bearer {{token_admin}}` | - | Plate deleted | 200 |
| Client Forbidden Plate Write | Enforce admin role | POST/PUT/DELETE | Plate write URLs | `Authorization: Bearer {{token_client}}` | valid body | Forbidden | 403 |
| Invalid Category ID | Validate FK | POST | `{{base_url}}/plates` | `Authorization: Bearer {{token_admin}}` | `{ "name":"Bad Plate", "price":12.0, "category_id":999999 }` | Validation error | 422 |
| Invalid Ingredient IDs | Validate ingredient IDs | POST | `{{base_url}}/plates` | `Authorization: Bearer {{token_admin}}` | `{ "name":"Bad Plate", "price":12.0, "category_id":{{category_id}}, "ingredient_ids":[999999] }` | Validation error | 422 |

### Ingredients

| Test name | Objective | Method | URL | Headers | Body | Expected result | HTTP |
|---|---|---|---|---|---|---|---|
| List Ingredients Admin | Read ingredients | GET | `{{base_url}}/ingredients?per_page=10` | `Authorization: Bearer {{token_admin}}` | - | Paginated list | 200 |
| Create Ingredient Admin | Create ingredient with tags | POST | `{{base_url}}/ingredients` | `Authorization: Bearer {{token_admin}}`, `Content-Type: application/json` | `{ "name":"Milk", "tags":["contains_lactose"] }` | Ingredient created + save `ingredient_id` | 201 |
| Update Ingredient Admin | Update name/tags | PUT | `{{base_url}}/ingredients/{{ingredient_id}}` | `Authorization: Bearer {{token_admin}}`, `Content-Type: application/json` | `{ "name":"Whole Milk", "tags":["contains_lactose"] }` | Ingredient updated | 200 |
| Delete Ingredient Admin | Delete ingredient | DELETE | `{{base_url}}/ingredients/{{ingredient_id}}` | `Authorization: Bearer {{token_admin}}` | - | Ingredient deleted | 200 |
| Client Forbidden Ingredient Routes | Enforce admin role | GET/POST/PUT/DELETE | ingredient URLs | `Authorization: Bearer {{token_client}}` | - | Forbidden | 403 |
| Invalid Tags | Validate tags enum | POST | `{{base_url}}/ingredients` | `Authorization: Bearer {{token_admin}}` | `{ "name":"Bad Ingredient", "tags":["invalid_tag"] }` | Validation error | 422 |

### Recommendations

| Test name | Objective | Method | URL | Headers | Body | Expected result | HTTP |
|---|---|---|---|---|---|---|---|
| Analyze Plate | Start async scoring | POST | `{{base_url}}/recommendations/analyze/{{recommendation_plate_id}}` | `Authorization: Bearer {{token_client}}` | - | Processing started | 202 |
| Analyze Immediate Check | Verify required payload | POST | same | same | - | Has `status=processing` and `plate_id` | 202 |
| Get Recommendation By Plate | Read one recommendation | GET | `{{base_url}}/recommendations/{{recommendation_plate_id}}` | `Authorization: Bearer {{token_client}}` | - | Processing or ready payload | 200 |
| Get Recommendation History | Read user history | GET | `{{base_url}}/recommendations?per_page=10` | `Authorization: Bearer {{token_client}}` | - | Paginated recommendation history | 200 |
| Verify Ready After Job | Validate async completion | GET (poll) | `{{base_url}}/recommendations/{{recommendation_plate_id}}` | `Authorization: Bearer {{token_client}}` | - | Eventually `status=ready` | 200 |
| Verify Scoring Fields | Validate scoring contract | GET | same | same | - | `score,label,warning_message,conflicting_tags,status` exist | 200 |

### Admin Stats

| Test name | Objective | Method | URL | Headers | Body | Expected result | HTTP |
|---|---|---|---|---|---|---|---|
| Admin Stats Allowed | Read global stats | GET | `{{base_url}}/admin/stats` | `Authorization: Bearer {{token_admin}}` | - | Structured global stats | 200 |
| Admin Stats Forbidden Client | Role restriction | GET | `{{base_url}}/admin/stats` | `Authorization: Bearer {{token_client}}` | - | Forbidden | 403 |
| Admin Stats JSON Shape | Contract validation | GET | `{{base_url}}/admin/stats` | `Authorization: Bearer {{token_admin}}` | - | Has totals + recommendations + categories nodes | 200 |

## 4) Postman scripts examples

### Save `token_client` after login

```javascript
const json = pm.response.json();
pm.test('HTTP 200', () => pm.response.to.have.status(200));
pm.environment.set('token_client', json.token);
```

### Save `token_admin` after login

```javascript
const json = pm.response.json();
pm.test('HTTP 200', () => pm.response.to.have.status(200));
pm.environment.set('token_admin', json.token);
```

### Save IDs from create responses

```javascript
const json = pm.response.json();
pm.test('Resource created', () => pm.expect(pm.response.code).to.be.oneOf([200, 201]));
if (json.data?.id) {
  pm.environment.set('category_id', json.data.id);
  pm.environment.set('plate_id', json.data.id);
  pm.environment.set('ingredient_id', json.data.id);
}
```

### Check HTTP code

```javascript
pm.test('Expected status code', () => {
  pm.response.to.have.status(200);
});
```

### Check JSON field exists

```javascript
const json = pm.response.json();
pm.test('Field exists', () => {
  pm.expect(json).to.have.property('message');
});
```

### Poll recommendation until `ready`

```javascript
const json = pm.response.json();
const attempts = Number(pm.environment.get('recommendation_poll_attempts') || 10);
const current = Number(pm.environment.get('recommendation_poll_current') || 0);

if (json.status === 'processing' && current < attempts) {
  pm.environment.set('recommendation_poll_current', current + 1);
  postman.setNextRequest(pm.info.requestName);
} else {
  pm.environment.unset('recommendation_poll_current');
  postman.setNextRequest(null);
}
```

## 5) Recommended execution order

1. Register/Login Admin
2. Register/Login Client
3. Profile tests
4. Ingredients create (admin)
5. Categories create (admin)
6. Plates create/update/show/list (admin + client)
7. Category protections and delete conflict case
8. Recommendations analyze + poll + history
9. Admin Stats
10. Error Cases sweep (401/403/404/422)

## 6) Queue & Jobs local validation

1. Ensure `.env` has `QUEUE_CONNECTION=database`.
2. Run migrations (`jobs`, `failed_jobs` must exist): `php artisan migrate`.
3. Start worker in terminal A: `php artisan queue:work --queue=recommendations,default`.
4. Trigger analyze endpoint in Postman.
5. Poll recommendation endpoint until `status=ready`.
6. If needed inspect failures: `php artisan queue:failed`.
