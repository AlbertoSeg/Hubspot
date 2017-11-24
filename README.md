# Integración Hubspot - Fotocasa/Tucasa

Integración con Hubspot de los leads de Fotocasa y Tucasa. 

## Prerequisitos
Para poder instalar las librerías, tiene que estar instalado en el servidor **composer**.


## Instalación
    
  1. Modificar en el archivo [parameters](config/parameters.php) las siguientes variables:
     - api_key_hubspot: API Key Hubspot
     - fotocasa_url_contacts: Es la URL de la API de contactos de fotocasa
     - fotocasa_platform_channel: Valor que se utilizará como contact_channel
     - fotocasa_owner_id: Es el ID de usuario de Hubspot que se será quien escriba las notas en los contactos
     - fotocasa_auth_user: Usuario de autenticación de la API de fotocasa
     - fotocasa_auth_password: Contraseña de autenticación de la API de fotocasa
     - tucasa_url: Es la URL de la API de tucasa
     - tucasa_client_id: ID de cliente de tucasa
     - tucasa_platform_channel: Valor que se utilizará como contact_channel
     - tucasa_owner_id: Es el ID de usuario de Hubspot que será quien escriba las notas en los contactos, el ID actual es de Clara
    
  2. Ejecutar composer install
  
  3. Comprobar que existe la carpeta tmp en la raiz (a la misma altura que public, lib, vendor, etc...). Puede ser que si no está creada la carpeta de error de escritura.
  
## Ejecución

### Fotocasa
Hay que ejecutar el archivo [fotocasa.php](public/fotocasa.php). Se debe introducir en un CRON, y la repetición del script debe ser 1 vez cada día, ya que hace la comprobación revisando el día anterior. Si se aumenta la frecuencia, se tendría que modificar el tiempo que se le envía a fotocasa como parámetro.

### Tucasa
Hay que ejecutar el archivo [tucasa.php](public/tucasa.php). Se debe introducir en un CRON, y la repetición del script puede ser las veces que se quiera, ya que fotocasa proporcionará los leads nuevos que hayan habido desde la última vez que se hizo la petición. Por ejemplo: una frecuencia asumible sería que se repitiera cada hora.
