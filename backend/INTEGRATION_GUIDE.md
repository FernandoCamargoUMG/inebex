# 🔗 Guía de Integración Multi-API INEBEX

## 🎯 Objetivo
Integrar 3 sistemas independientes:
- **INEBEX API** (MySQL) - Sistema administrativo
- **Inventarios API** (PostgreSQL/MySQL) - Gestión de inventarios
- **Contable API** (SQL Server/MySQL) - Sistema contable

## 🏗️ Arquitecturas Recomendadas

### 1. **🌐 API Gateway con Laravel (Recomendado)**

#### Ventajas:
- ✅ Un solo punto de entrada
- ✅ Autenticación centralizada
- ✅ Rate limiting unificado
- ✅ Logging centralizado
- ✅ Transformación de datos consistente

#### Estructura:
```
Frontend (React/Vue/Angular)
       ↓
Laravel API Gateway
       ├── INEBEX Module (MySQL)
       ├── HTTP Client → Inventarios API (PostgreSQL)
       └── HTTP Client → Contable API (SQL Server)
```

#### Implementación en Laravel:

```php
// config/database.php - Múltiples conexiones
'connections' => [
    'mysql_inebex' => [
        'driver' => 'mysql',
        'host' => env('DB_INEBEX_HOST', '127.0.0.1'),
        'database' => env('DB_INEBEX_DATABASE', 'inebex'),
        // ... resto de configuración
    ],
    'pgsql_inventory' => [
        'driver' => 'pgsql',
        'host' => env('DB_INVENTORY_HOST', '127.0.0.1'),
        'database' => env('DB_INVENTORY_DATABASE', 'inventory'),
        // ... resto de configuración
    ],
    'sqlsrv_accounting' => [
        'driver' => 'sqlsrv',
        'host' => env('DB_ACCOUNTING_HOST', '127.0.0.1'),
        'database' => env('DB_ACCOUNTING_DATABASE', 'accounting'),
        // ... resto de configuración
    ],
],

// app/Services/IntegrationService.php
class IntegrationService
{
    public function __construct(
        private HttpClient $http,
        private InventoryService $inventory,
        private AccountingService $accounting
    ) {}

    // Ejemplo: Crear expediente con productos e invoices
    public function createCompleteRecord(array $data)
    {
        DB::beginTransaction();
        try {
            // 1. Crear expediente en INEBEX
            $record = Record::create($data['record']);
            
            // 2. Crear productos en Inventarios
            if (isset($data['products'])) {
                $inventoryResponse = $this->inventory->createProducts(
                    $data['products'], 
                    $record->id
                );
            }
            
            // 3. Crear factura en Contable
            if (isset($data['invoice'])) {
                $accountingResponse = $this->accounting->createInvoice([
                    'record_id' => $record->id,
                    'inventory_id' => $inventoryResponse['id'] ?? null,
                    'amount' => $data['invoice']['amount']
                ]);
            }
            
            DB::commit();
            return [
                'record' => $record,
                'inventory' => $inventoryResponse ?? null,
                'accounting' => $accountingResponse ?? null
            ];
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
}

// app/Http/Controllers/IntegrationController.php
class IntegrationController extends Controller
{
    public function createCompleteRecord(Request $request, IntegrationService $service)
    {
        try {
            $result = $service->createCompleteRecord($request->all());
            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
```

### 2. **🔗 Comunicación HTTP entre APIs**

#### Implementación:

```php
// app/Services/InventoryService.php
class InventoryService
{
    private $baseUrl;
    private $httpClient;
    
    public function __construct()
    {
        $this->baseUrl = config('services.inventory.url');
        $this->httpClient = new GuzzleHttp\Client();
    }
    
    public function createProduct(array $data)
    {
        $response = $this->httpClient->post("{$this->baseUrl}/api/products", [
            'headers' => [
                'Authorization' => 'Bearer ' . config('services.inventory.token'),
                'Content-Type' => 'application/json'
            ],
            'json' => $data
        ]);
        
        return json_decode($response->getBody(), true);
    }
    
    public function getProductsByRecordId($recordId)
    {
        $response = $this->httpClient->get("{$this->baseUrl}/api/products/record/{$recordId}");
        return json_decode($response->getBody(), true);
    }
}

// app/Services/AccountingService.php
class AccountingService
{
    public function createInvoice(array $data)
    {
        // Similar implementación para API contable
        $response = $this->httpClient->post("{$this->baseUrl}/api/invoices", [
            'json' => $data
        ]);
        
        return json_decode($response->getBody(), true);
    }
}
```

### 3. **🎯 Event-Driven Architecture**

```php
// app/Events/RecordCreated.php
class RecordCreated
{
    public function __construct(
        public Record $record,
        public array $additionalData = []
    ) {}
}

// app/Listeners/CreateInventoryItems.php
class CreateInventoryItems
{
    public function handle(RecordCreated $event)
    {
        if (isset($event->additionalData['products'])) {
            $inventoryService = app(InventoryService::class);
            $inventoryService->createProducts(
                $event->additionalData['products'],
                $event->record->id
            );
        }
    }
}

// app/Listeners/CreateInvoice.php  
class CreateInvoice
{
    public function handle(RecordCreated $event)
    {
        if (isset($event->additionalData['invoice'])) {
            $accountingService = app(AccountingService::class);
            $accountingService->createInvoice([
                'record_id' => $event->record->id,
                'amount' => $event->additionalData['invoice']['amount']
            ]);
        }
    }
}

// En el controlador
public function store(Request $request)
{
    $record = Record::create($request->only('record_fields'));
    
    // Dispara eventos para otros sistemas
    event(new RecordCreated($record, $request->all()));
    
    return response()->json($record);
}
```

## 🔧 **Configuración de Múltiples Bases de Datos**

### .env Configuration:
```env
# INEBEX Database
DB_INEBEX_CONNECTION=mysql
DB_INEBEX_HOST=127.0.0.1
DB_INEBEX_PORT=3306
DB_INEBEX_DATABASE=inebex
DB_INEBEX_USERNAME=root
DB_INEBEX_PASSWORD=

# Inventory Database  
DB_INVENTORY_CONNECTION=pgsql
DB_INVENTORY_HOST=127.0.0.1
DB_INVENTORY_PORT=5432
DB_INVENTORY_DATABASE=inventory
DB_INVENTORY_USERNAME=postgres
DB_INVENTORY_PASSWORD=

# Accounting Database
DB_ACCOUNTING_CONNECTION=sqlsrv
DB_ACCOUNTING_HOST=127.0.0.1
DB_ACCOUNTING_PORT=1433
DB_ACCOUNTING_DATABASE=accounting
DB_ACCOUNTING_USERNAME=sa
DB_ACCOUNTING_PASSWORD=
```

### Modelos con Conexiones Específicas:
```php
// app/Models/InventoryItem.php
class InventoryItem extends Model
{
    protected $connection = 'pgsql_inventory';
    protected $table = 'inventory_items';
}

// app/Models/Invoice.php
class Invoice extends Model
{
    protected $connection = 'sqlsrv_accounting';
    protected $table = 'invoices';
}
```

## 🚀 **APIs Endpoints Integrados**

### Ejemplo de Endpoints Unificados:
```php
// routes/api.php
Route::prefix('integration')->group(function () {
    Route::post('/complete-record', [IntegrationController::class, 'createCompleteRecord']);
    Route::get('/record/{id}/full-data', [IntegrationController::class, 'getCompleteRecord']);
    Route::put('/record/{id}/sync-all', [IntegrationController::class, 'syncAllSystems']);
});
```

## 🔐 **Seguridad y Autenticación**

### JWT Token Compartido:
```php
// app/Services/AuthService.php
class AuthService
{
    public function generateSystemToken($systemName)
    {
        return JWT::encode([
            'system' => $systemName,
            'permissions' => $this->getSystemPermissions($systemName),
            'exp' => time() + 3600
        ], config('app.jwt_secret'));
    }
}
```

## 📊 **Sincronización de Datos**

### Comando de Sincronización:
```php
// app/Console/Commands/SyncSystems.php
class SyncSystems extends Command
{
    protected $signature = 'systems:sync {--system=all}';
    
    public function handle()
    {
        $this->info('Iniciando sincronización...');
        
        // Sincronizar inventarios
        $this->syncInventory();
        
        // Sincronizar contabilidad
        $this->syncAccounting();
        
        $this->info('Sincronización completada');
    }
}
```

## 🎯 **Recomendaciones**

### Para tu caso específico:

1. **🌟 Mantén INEBEX como API principal** (ya está desarrollado)
2. **🔌 Agrega servicios de integración** para inventarios y contabilidad
3. **🗄️ Usa múltiples conexiones de DB** en Laravel
4. **🔄 Implementa eventos** para sincronización automática
5. **🛡️ Centraliza la autenticación** en INEBEX
6. **📝 Mantén logs detallados** de todas las integraciones

### Beneficios:
- ✅ **Escalabilidad**: Cada sistema puede crecer independientemente
- ✅ **Mantenimiento**: Cambios en un sistema no afectan otros
- ✅ **Performance**: Bases de datos optimizadas por dominio
- ✅ **Seguridad**: Aislamiento de datos por sistema
- ✅ **Flexibilidad**: Fácil agregar nuevos sistemas

¿Te gustaría que implemente alguna de estas opciones específicamente para tu caso?