# Panel de administración — Diálogo y Desarrollo (conectado a `revista_digital`)

## 1. Instalación en XAMPP

1. Copia toda esta carpeta (`dyd-admin`) dentro de `C:\xampp\htdocs\` (o `/Applications/XAMPP/htdocs/` en Mac).
2. Abre XAMPP y arranca **Apache** y **MySQL**.
3. En phpMyAdmin, confirma que la base `revista_digital` ya existe con las tablas del docente (el script que te pasó).
4. Importa `sql/seed_datos.sql` (pestaña "Importar" en phpMyAdmin, o pegarlo en la pestaña SQL). Esto crea un autor **"Redacción"**, que necesitas porque la tabla `reportajes` exige un autor (no acepta vacío) — es el que usarás cuando un reportaje no tenga autor externo.
5. Abre en el navegador: `http://localhost/dyd-admin/setup_admin.php` y crea tu primer usuario (admin). **Borra o renombra `setup_admin.php` después de usarlo**, para que nadie más pueda crear usuarios desde ahí.
6. Entra a `http://localhost/dyd-admin/login.php` con el correo y contraseña que acabas de crear.

Si `config/database.php` no logra conectar, revisa esas 3 líneas (usuario/contraseña de tu MySQL — por defecto en XAMPP es `root` sin contraseña).

## 2. Qué se conectó y cómo

- **`config/database.php`**: conexión PDO a `revista_digital`.
- **`includes/auth.php`**: sesiones + `requireLogin()` (protege cada página).
- **`login.php` / `logout.php` / `setup_admin.php`**: login real contra la tabla `usuarios`, con `password_hash()` / `password_verify()` (nunca se guardan contraseñas en texto plano).
- Cada módulo del sidebar ahora es un `.php` con **CRUD real** (crear, leer, actualizar, eliminar) usando PDO con *prepared statements* (protegido contra inyección SQL):
  - `reportajes.php` (+ `reportajes_fotos.php` para la galería, la entidad débil `reportajes_fotos`)
  - `noticias.php`, `boletines.php`, `podcasts.php`, `videos.php`
  - `autores.php`, `usuarios.php`, `perfil.php`
  - `index.php`: dashboard con conteos reales y actividad reciente desde la BD.
- Los archivos que subes (fotos, PDFs) se guardan en `uploads/fotos` y `uploads/pdfs`, y solo se guarda la ruta en la base de datos (así es como se hace en la práctica; el archivo pesado no va dentro de la BD).

## 3. Cosas que ajusté frente al mockup original (por el esquema real del docente)

- El HTML de ejemplo mostraba una columna **"Estado"** (Publicado/Borrador) en reportajes/boletines, pero esa columna **no existe** en las tablas del docente. La quité y usé lo que sí existe: `es_destacado` en reportajes (columna "Destacado").
- El documento del docente dice que el autor de un reportaje es **opcional**, pero el script SQL define `autor_id` como `NOT NULL`. Por eso creé el autor **"Redacción"** (paso 4 arriba): así el reportaje siempre tiene un `autor_id` válido, y semánticamente "Redacción" representa "sin autor externo", tal como describe el modelo conceptual.
- `imagenes.html` y `archivos.html` quedaron sin conectar porque no corresponden a ninguna tabla del modelo (son una librería de medios genérica, no parte del modelo conceptual que te dio el docente). Si tu docente las pide, dime y las armamos con una tabla nueva.

## 4. Seguridad mínima que ya está incluida

- Contraseñas con hash (`password_hash`), nunca en texto plano.
- Consultas con *prepared statements* (`$pdo->prepare(...)->execute([...])`), no concatenación de strings — evita inyección SQL.
- Todas las páginas del panel llaman `requireLogin()` al inicio: sin sesión, te manda a `login.php`.
- `uploads/.htaccess` bloquea que se ejecute PHP dentro de esa carpeta.

## 5. Siguientes pasos sugeridos

- Prueba crear/editar/eliminar en cada módulo con datos reales.
- Si tu docente pide subir esto a un hosting (no XAMPP local), habrá que cambiar `config/database.php` con los datos del hosting.
