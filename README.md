# Mobile Application Subscription Managment API

Teknik Detaylar:

1) Laravel
    - Stabil 7.0 sürümü tercih edilmiştir.
2) Cache - Driver=Database
    - Performans için gerekli görülmüştür.
    - Redis, Memcache vb. kullanılabilir. Localhost ortamında veritabanı üzerinde kullanılması uygun görülmüştür.
    - Standart cache süreleri 1 gün, client_token ve uid bilgilerini tutan cache süreleri 30 gün, check subscription için kullanılan cache süresi 2 dakika olarak belirlenmiştir. Süreler değiştirilebilir.
3) Database - Migrations
    - Veritabanı migration kullanılarak oluşturulmuştur.
    - BTREE kullanılarak tablolarda indeksleme yapılmıştır.
    - Tablolarda, uzun bir string değer olan client_token ile arama yapmak yerine, her cihaz için unique bir integer değer olan u_id temel alınmıştır. Aramalarda performans artışı için kullanılmıştır.
4) Middleware
    - API route için ClientTokenAuth isimli bir middleware oluşturulmuştur. Purchase ve check subscription işlemlerinde token kontrolü sağlanmaktadır. 
5) Postman Collection
    - Proje dosyalarında "*masm.postman_collection.json*" isimli postman collection yer almaktadır.
    
---

### REGISTER

##### İstek (Request) : POST

Parametre   |Açıklama           |Tür        |İçerik
:-----------|:-----------------:|:---------:|:----:|
uid         |Device ID          |Integer    |43
appId       |Application ID     |Integer    |12
language    |Language           |String     |en
os          |Operating System   |Integer    |andriod

##### Süreç (Process) :
 - Sistem register isteği ile çalışır.
 - İstek geldiğinde, Cache üzerinde mevcut u_id=client_token eşleşmesi var mı kontrol edilir? 
 - Varsa cevap olarak döndürülür. Yoksa veritabanı üzerinde uid kaydı var mı kontrol edilir? 
 - Varsa eşleşen client_token cevap olarak döndürülür. Yoksa Devices, Register tablolarına kayıt yapılır.
 - Register işlemine giren u_id ve app_id kontrolü cache üzerinden yapılır. Eğer app_id değişiyorsa yeni kayıt olarak Device tablosuna kaydedilir.
 - client_token Cache'e yazılır. Ardından client_token döndürülür.

##### Veritabanı (Database) :
1. Devices
    - Index ataması; u_id, app_id alanlarındadır. (BTREE)
    - Tabloya kaydedilenler => id, u_id, app_id, language, os, created_date, updated_date
2. Register
    - Index ataması; u_id, client_token alanlarındadır. (BTREE)
    - Tabloya kaydedilenler => id, u_id, client_token, created_date, updated_date

##### Koşullar (Conditions) :
1. Her u_id kendisine ait sadece bir client_token barındırabilir.
2. Her u_id kendisine bağlı birden fazla app_id barındırabilir.

##### Cevap (Response) : JSON

Parametre       |Açıklama       |Tür    |İçerik
:--------------:|:-------------:|:-----:|:----:|
result          |Cevap Değeri   |String |true - false
message         |Açıklama Metni |String |account created - account already exists
client_token    |API Token      |String |$2y$10$xI6bSlU5CEgL6FHgVv1wY.Im4mt7.kmrNX28eJlH1JaYIyELhiNMG

---

### PURCHASE

##### İstek (Request) : GET|POST

Parametre       |Açıklama           |Tür    |İçerik
:--------------:|:-----------------:|:-----:|:----:|
client_token    |API Token          |String |$2y$10$xI6bSlU5CEgL6FHgVv1wY.Im4mt7.kmrNX28eJlH1JaYIyELhiNMG
receipt         |Satın Alma Kodu    |String |0664129e82ascd4dc351235190e53ed11

##### Süreç (Process) : 
 - Sistem purchase isteği ile çalışır.
 - İstek geldiğinde, client_token daha önce oluşturulmuş clientTokenAuth isimli middleware ile kontrol edilir.  
 - Token eşleşmesi yoksa sonuç "false" ve mesaj "unauthorized access" olarak döndürülür. Token eşleşmesi varsa devam edilir.
 - Google/Apple API'a istek gönderilir. İstek içerisinde parametre olarak sadece receipt bulunur. Receipt string içindeki son karakter tek bir sayı ise status:true, expire_date:UTC-6 döndürülür. Değilse status:false döner. 
 - Status:true ise veritabanına kayıt yapılır ve başarılı mesaj içeren cevap döndürülür. Status:false ise direkt başarısız mesaj içeren cevap döndürülür.

##### Veritabanı (Database) : 
1. Purchases
    - Index ataması; u_id, status, expire_date alanlarındadır. (BTREE)
    - Tabloya kaydedilenler => id, receipt, u_id, status, expire_date, created_date, updated_date

##### Koşullar (Conditions) :
1. Mock API proje içerisinde farklı bir endpoint olarak yazılacaktır.
2. expire_date UTC-6 olacaktır.

##### Cevap (Response) : JSON

Parametre   |Açıklama       |Tür    |İçerik
:----------:|:-------------:|:-----:|:----:|
result      |Cevap Değeri   |String |true - false
message     |Açıklama Metni |String |purchase successful - purchase fail

---

### CHECK SUBSCRIPTION

##### İstek (Request) : GET

Parametre       |Açıklama   |Tür    |İçerik
:--------------:|:---------:|:-----:|:----:|
client_token    |API Token  |String |$2y$10$xI6bSlU5CEgL6FHgVv1wY.Im4mt7.kmrNX28eJlH1JaYIyELhiNMG

##### Süreç (Process) : 
 - Sistem check subscription isteği ile çalışır. Çift method olarak kullanılabilir.
  - İstek geldiğinde, client_token daha önce oluşturulmuş clientTokenAuth isimli middleware ile kontrol edilir.  Eşleşme varsa devam edilir, yoksa sonuç "false" ve mesaj "unauthorized access" olarak döndürülür. 
 - Cache üzerinde mevcut client_token=purchase_list eşleşmesi var mı kontrol edilir? 
 - Varsa döndürülür, yoksa veritabanında status=1 olan ve expire_date>NOW() olan kayıtlar getirilir. Cache'e yazılır ve cevap döndürülür.
 - Yeni bir purchase isteği olur ve başarılı sonuç dönerse, client_token=purchase_list cache'i silinir ve check subscription isteği ilk çağırıldığında cache yeniden oluşturulur.

##### Veritabanı (Database) : 
1. Purchases
    - Veri için bu tablo kullanılır.
    

##### Cevap (Response) : JSON

Parametre   |Açıklama           |Tür    |İçerik
:----------:|:-----------------:|:-----:|:----:|
result      |Cevap Değeri       |String |true - false
list        |Aktif Abonelikler  |Array  |[] - [{id:1, receipt:abcde, status:1, expire_date:..., created_at:...}]

---
