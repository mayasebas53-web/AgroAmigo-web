# AgroAmigo-web
AgroAmigo is a software designed to help users track every single detail of the incredible process of growing plants.

## Probar en Codespaces

1. En Supabase, abre **SQL Editor** y ejecuta `database.sql`.
2. Rota la contraseña de la base de datos si la que estaba en el repositorio fue expuesta.
3. Copia `.env.example` como `.env` y reemplaza los valores con los datos de **Connect > Session pooler**:

```bash
cp .env.example .env
```

Edita `.env` y completa `SUPA_USERNAME` y `SUPA_PASSWORD`.

4. Inicia el servidor con el PHP que tiene soporte PostgreSQL:

```bash
php8.3 -S 0.0.0.0:8080
```

5. En VS Code abre **Ports**, haz público el puerto `8080` y abre la URL generada.

El flujo de prueba es: crear cuenta, iniciar sesión y agregar un cultivo. Las contraseñas se guardan con hash. La foto se guarda como `BYTEA` en PostgreSQL junto con su nombre y tipo MIME.
