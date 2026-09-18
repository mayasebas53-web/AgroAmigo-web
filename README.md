# AgroAmigo-web
AgroAmigo is a software designed to help users track every single detail of the incredible process of growing plants.

## Ejecutar en otro Codespace

Estas instrucciones permiten que otra instancia de desarrollo prepare y muestre la aplicación sin asumir que existe una configuración local previa.

1. Clona el repositorio y entra en su carpeta:

```bash
git clone URL_DEL_REPOSITORIO
cd AgroAmigo-web
```

2. Comprueba que PHP esté disponible y que tenga las extensiones `pgsql` y `pdo_pgsql`. La aplicación usa PostgreSQL mediante `pg_connect()`; no usa MySQL. Estas comprobaciones se hacen dentro del Codespace, que es un entorno remoto independiente de tu computadora:

```bash
php --version
php -m | grep -E 'pgsql|pdo_pgsql'
```

Si el comando `php` no muestra las extensiones, instala el runtime y sus extensiones en el Codespace, no en tu máquina local:

```bash
sudo apt update
sudo apt install -y php-cli php-pgsql
```

Después, vuelve a comprobar `php -m`. El script de arranque selecciona automáticamente el primer PHP instalado que tenga ambas extensiones, para que no tengas que cambiar la versión manualmente.

3. Crea el archivo local de variables de entorno. No subas `.env` a GitHub:

```bash
cp .env.example .env
```

Completa `.env` con los datos actuales de **Supabase > Connect > Session pooler**. Son necesarias `SUPA_HOST`, `SUPA_DBNAME`, `SUPA_USERNAME`, `SUPA_PASSWORD` y `SUPA_PORT`. La contraseña debe ser la real de la base de datos y nunca debe quedar escrita en `README.md`, `.env.example` ni en el código.

4. Prepara la base de datos en Supabase. Abre **SQL Editor**, ejecuta el contenido de `database.sql` y verifica que las tablas `usuarios` y `cultivos` existan. El archivo `.env` solo configura la conexión; no contiene ni crea los datos de la base de datos.

5. Inicia el servidor desde la raíz del proyecto. El script detecta automáticamente la versión compatible y usa el puerto `8080`:

```bash
./start-local.sh
```

Para usar otro puerto, ejecuta `PORT=8081 ./start-local.sh`. Para forzar un binario concreto, usa `PHP_BIN=php8.3 ./start-local.sh`.

6. En VS Code abre la pestaña **Ports**, busca el puerto `8080`, hazlo público si es necesario y abre la URL generada. La página inicial es `index.html`.

7. Verifica el flujo básico: crear una cuenta, iniciar sesión y agregar un cultivo. Si aparece un error de conexión, revisa primero las variables de `.env`, la extensión `pgsql` y que Supabase permita la conexión mediante el Session pooler.

### Diagnóstico de errores 500

Antes de cambiar el código, comprueba que el selector encuentre un PHP compatible. Un Codespace puede tener una versión de PHP o de sus extensiones distinta a la de otro equipo:

```bash
./start-local.sh
php --version
php --ini
```

La aplicación necesita PHP CLI con `pgsql` habilitado. Si ninguna versión es compatible, instala el paquete correspondiente dentro del Codespace y vuelve a ejecutar el script. Comprueba también que `.env` tenga todos los nombres de variables esperados y que la contraseña de Supabase sea válida. Para ver el error concreto durante una prueba, observa la salida de la terminal donde se ejecuta el script; no expongas las credenciales al compartir los logs.

## Publicar en Render

Render es una plataforma reconocida de alojamiento web. Para esta aplicación usaremos Docker, de modo que Render ejecute PHP 8.3 con las extensiones de PostgreSQL dentro de un contenedor reproducible. Render proporciona HTTPS para el dominio público; las credenciales de Supabase deben configurarse como variables secretas del servicio y no deben subirse al repositorio.

1. Sube los cambios a GitHub:

```bash
git add Dockerfile docker-entrypoint.sh README.md start-local.sh
git commit -m "Prepara despliegue en Render"
git push origin main
```

2. En Render selecciona **New > Web Service**, conecta el repositorio `AgroAmigo-web` y elige **Docker**. Render detectará el archivo `Dockerfile`. No necesitas definir un comando de inicio adicional.

3. En **Environment** agrega estas variables con los valores de tu proyecto de Supabase:

```text
SUPA_HOST
SUPA_DBNAME
SUPA_USERNAME
SUPA_PASSWORD
SUPA_PORT
```

4. Crea el servicio y espera a que finalice el despliegue. Render asignará una URL `onrender.com` para compartir.

5. Ejecuta `database.sql` una sola vez en el SQL Editor de Supabase y prueba desde la URL pública: crear cuenta, iniciar sesión, agregar cultivo y subir una imagen.

El plan gratuito de Render puede suspender servicios sin tráfico y tardar unos segundos en responder al primer acceso. Revisa sus límites y precios actuales antes de usarlo para producción. La URL pública no debe compartirse junto con las variables de Supabase.

## Comportamiento de la aplicación

El flujo de prueba es: crear una cuenta, iniciar sesión y agregar un cultivo. Las contraseñas se guardan con hash. Las fotos se guardan como `BYTEA` en PostgreSQL junto con su nombre y tipo MIME.
