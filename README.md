# Hubspot

Integración Hubspot Fotocasa

## Configuración
    
  1. Modificar en la clase [Fotocasa](lib/Fotocasa.php) las siguientes constantes:
     - URL_CONTACTS: Es la URL de la API de contactos de fotocasa 
     - OWNER_ID: El la ID del usuario de hubspot al que se le asignarán los contactos. Pasará a ser el owner del contacto.
     - AUTH_USER: Usuario de autenticación de la API de fotocasa
     - AUTH_PASSWORD: Contraseña de autenticación de la API de fotocasa
    
  2. Ejecutar composer install
  
  3. Comprobar que existe la carpeta tmp en la raiz (a la misma altura que public, lib, vendor, etc...). Puede ser que si no está creada la carpeta de error de escritura.
  
