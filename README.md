# AgroAmigo-web
AgroAmigo is a software designed to help users track every single detail of the incredible process of growing plants.

## Ejecutar en otro Codespace

Estas instrucciones permiten que otra instancia de desarrollo prepare y muestre la aplicación sin asumir que existe una configuración local previa.

1. Clona el repositorio y entra en su carpeta:

```bash
git clone URL_DEL_REPOSITORIO
cd AgroAmigo-web
```

2. Comprueba que PHP esté disponible y que tenga las extensiones `pgsql` y `pdo_pgsql`. La aplicación usa PostgreSQL mediante `pg_connect()`; no usa MySQL. La versión del binario y la versión de las extensiones deben coincidir:

```bash
php8.3 --version
php8.3 -m | grep -E 'pgsql|pdo_pgsql'
```

En el Codespace de referencia, las extensiones están instaladas para PHP 8.3. Si no aparecen, instala el runtime y sus extensiones:

```bash
sudo apt update
sudo apt install -y php8.3-cli php8.3-pgsql
```

Después, vuelve a comprobar `php8.3 -m`. No mezcles un PHP 8.4 con extensiones compiladas para PHP 8.3, porque `pg_connect()` no estará disponible.

3. Crea el archivo local de variables de entorno. No subas `.env` a GitHub:

```bash
cp .env.example .env
```

Completa `.env` con los datos actuales de **Supabase > Connect > Session pooler**. Son necesarias `SUPA_HOST`, `SUPA_DBNAME`, `SUPA_USERNAME`, `SUPA_PASSWORD` y `SUPA_PORT`. La contraseña debe ser la real de la base de datos y nunca debe quedar escrita en `README.md`, `.env.example` ni en el código.

4. Prepara la base de datos en Supabase. Abre **SQL Editor**, ejecuta el contenido de `database.sql` y verifica que las tablas `usuarios` y `cultivos` existan. El archivo `.env` solo configura la conexión; no contiene ni crea los datos de la base de datos.

5. Inicia el servidor desde la raíz del proyecto:

```bash
php8.3 -S 0.0.0.0:8080
```

6. En VS Code abre la pestaña **Ports**, busca el puerto `8080`, hazlo público si es necesario y abre la URL generada. La página inicial es `index.html`.

7. Verifica el flujo básico: crear una cuenta, iniciar sesión y agregar un cultivo. Si aparece un error de conexión, revisa primero las variables de `.env`, la extensión `pgsql` y que Supabase permita la conexión mediante el Session pooler.

### Diagnóstico de errores 500

Antes de cambiar el código, comprueba que las versiones del entorno sean compatibles. Un Codespace puede tener una versión de PHP o de sus extensiones distinta a la de otro equipo:

```bash
php8.3 --version
php8.3 -m | grep -E 'pgsql|pdo_pgsql'
php8.3 --ini
```

La aplicación necesita PHP CLI con `pgsql` habilitado. Si alguna extensión no aparece, instala el paquete correspondiente a la misma versión de PHP y reinicia el servidor. Comprueba también que `.env` tenga todos los nombres de variables esperados y que la contraseña de Supabase sea válida. Para ver el error concreto durante una prueba, observa la salida de la terminal donde se ejecuta `php8.3 -S 0.0.0.0:8080`; no expongas las credenciales al compartir los logs.

## Comportamiento de la aplicación

El flujo de prueba es: crear una cuenta, iniciar sesión y agregar un cultivo. Las contraseñas se guardan con hash. Las fotos se guardan como `BYTEA` en PostgreSQL junto con su nombre y tipo MIME.
