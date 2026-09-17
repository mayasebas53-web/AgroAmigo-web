CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo_electronico VARCHAR(150) NOT NULL UNIQUE,
    contraseña VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS cultivos (
    id_cultivo  SERIAL PRIMARY KEY,
    id_usuario INT NOT NULL,
    nombre_cultivo VARCHAR(100) NOT NULL,
    tipo_cultivo VARCHAR(100) NOT NULL,
    fecha_siembra DATE NOT NULL,
    tamaño_terreno DECIMAL(10, 2),
    ubicacion_cultivo VARCHAR(255) NOT NULL,
    zona VARCHAR(50),
    estado_cultivo VARCHAR(50) NOT NULL,
    foto BYTEA,
    foto_nombre VARCHAR(255),
    foto_tipo VARCHAR(100),
    fecha_creacion TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    CONSTRAINT fk_usuario_cultivo 
        FOREIGN KEY (id_usuario) 
        REFERENCES usuarios(id_usuario) 
        ON DELETE CASCADE
);

    ALTER TABLE cultivos ADD COLUMN IF NOT EXISTS foto BYTEA;
    ALTER TABLE cultivos ADD COLUMN IF NOT EXISTS foto_nombre VARCHAR(255);
    ALTER TABLE cultivos ADD COLUMN IF NOT EXISTS foto_tipo VARCHAR(100);
    ALTER TABLE cultivos ADD COLUMN IF NOT EXISTS fecha_creacion TIMESTAMPTZ NOT NULL DEFAULT NOW();