# منبع API Key در صفحه Patterns

## جریان کار:

### 1. وقتی دکمه "همگام‌سازی الگوها" زده می‌شود:

**فایل:** `app/Livewire/Patterns/Index.php`
**متد:** `syncFromApi()`

#### مرحله 1: خواندن API Key برای استفاده در API
```php
// خط 577
$password = \App\Models\ApiKey::getKeyValue('api_key');
```
- از جدول `api_keys` می‌خواند
- نام کلید: `api_key`
- شرط: `is_active = true`
- اگر در جدول نبود، از config استفاده می‌کند

#### مرحله 2: خواندن API Key برای نمایش در مودال
```php
// خط 587
$displayPassword = \App\Models\ApiKey::getKeyValue('api_key');
```
- **مستقیماً از جدول `api_keys` می‌خواند**
- نام کلید: `api_key`
- شرط: `is_active = true`
- اگر در جدول نبود، پیام هشدار نمایش می‌دهد

#### مرحله 3: ذخیره در syncResponseData
```php
// خط 606
'password' => $displayPassword, // از جدول api_keys خوانده شده
```

### 2. متد getKeyValue():

**فایل:** `app/Models/ApiKey.php`
**متد:** `getKeyValue($keyName)`

```php
public static function getKeyValue($keyName)
{
    $apiKey = self::where('key_name', $keyName)
        ->where('is_active', true)
        ->first();
    
    return $apiKey ? $apiKey->key_value : null;
}
```

**نحوه کار:**
1. در جدول `api_keys` جستجو می‌کند
2. شرط: `key_name = 'api_key'` و `is_active = true`
3. اگر پیدا کرد، `key_value` را برمی‌گرداند
4. اگر پیدا نکرد، `null` برمی‌گرداند

### 3. MelipayamakService:

**فایل:** `app/Services/MelipayamakService.php`
**متد:** `getPassword()`

```php
protected function getPassword()
{
    // همیشه از دیتابیس می‌خوانیم (اولویت اول)
    $apiKey = \App\Models\ApiKey::getKeyValue('api_key');
    
    // اگر در دیتابیس نبود، از config می‌خوانیم
    if (empty($apiKey)) {
        $apiKey = config('services.melipayamak.api_key') 
            ?: config('services.melipayamak.password');
    }
    
    return $apiKey;
}
```

## خلاصه:

### برای نمایش در مودال Patterns:
- **منبع:** جدول `api_keys`
- **نام کلید:** `api_key`
- **شرط:** `is_active = true`
- **اگر نبود:** پیام هشدار نمایش می‌دهد

### برای استفاده در API:
- **اولویت 1:** جدول `api_keys` با نام `api_key`
- **اولویت 2:** `config('services.melipayamak.api_key')`
- **اولویت 3:** `config('services.melipayamak.password')`

## نکات مهم:

1. **نام کلید باید دقیقاً `api_key` باشد** (نه `api-key` یا `API_KEY`)
2. **`is_active` باید `true` باشد**
3. **اگر در جدول نبود، از config استفاده می‌شود اما در مودال پیام هشدار نمایش داده می‌شود**

## برای تست:

```sql
-- بررسی اینکه API Key در جدول وجود دارد
SELECT * FROM api_keys WHERE key_name = 'api_key' AND is_active = 1;

-- اگر وجود ندارد، اضافه کنید:
INSERT INTO api_keys (key_name, key_value, is_active, created_at, updated_at)
VALUES ('api_key', 'YOUR_API_KEY_HERE', 1, NOW(), NOW());
```
