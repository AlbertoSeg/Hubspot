# Hubspot

Integración Hubspot Fotocasa. 

El script que hay que ejecutar es [index.php](public/index.php). Se debe introducir en un CRON, y un tiempo adecuado para la repetición del script sería cada día, ya que hace la comprobación revisando el día anterior. Si se aumenta la frecuencia, se tendría que modificar el tiempo que se le envía a fotocasa como parámetro.

## Configuración
    
  1. Modificar en el archivo [parameters](config/parameters.php) las siguientes variables:
     - api_key_hubspot: API Key Hubspot
     - fotocasa_url_contacts: Es la URL de la API de contactos de fotocasa
     - platform_channel: Valor que se utilizará como contact_channel
     - owner_id: Es el ID de usuario de Hubspot que se será quien escriba las notas en los contactos
     - auth_user: Usuario de autenticación de la API de fotocasa
     - auth_password: Contraseña de autenticación de la API de fotocasa
    
  2. Ejecutar composer install
  
  3. Comprobar que existe la carpeta tmp en la raiz (a la misma altura que public, lib, vendor, etc...). Puede ser que si no está creada la carpeta de error de escritura.
  
