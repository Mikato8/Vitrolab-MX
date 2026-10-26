# Arquitectura

## Capas

```text
HTTP
 │
 ├─ public/index.php            front controller
 ├─ app/Core/App.php             arranque, CSRF, router, errores
 ├─ app/Core/Router.php          rutas declarativas
 ├─ app/Controllers              validación, autorización, respuestas
 ├─ app/Repositories             SQL parametrizado
 ├─ app/Services                 verificación institucional
 ├─ app/Support                  PDF, helpers, iconos
 ├─ app/Views                    PHP + layouts
 └─ app/Core/Database            PDO o HTTP Neon
```

No hay ORM ni Composer. El autoloader de `app/bootstrap.php` resuelve `App\` contra `app/`.

## Flujo de una petición

1. `public/index.php` carga `app/bootstrap.php`.
2. `App::run()` captura `Request`, valida CSRF en métodos no GET, carga `routes/web.php` y despacha.
3. El controlador valida, consulta repositorios y renderiza una vista con `View::render`.
4. `Response::send()` aplica cabeceras de seguridad y envía HTML, JSON, PDF o un archivo.

## Transporte de datos

`PdoDatabase` convierte los marcadores `$1, $2, …` a marcadores nombrados de PDO, conserva el orden y castea tipos. `HttpDatabase` usa el endpoint SQL de Neon con la cadena de conexión en la cabecera; se usa cuando no hay `pdo_pgsql` o cuando `DB_DRIVER=http`.

El driver se elige en `Database::configure`:

- `pdo`: fuerza PDO.
- `http`: fuerza HTTPS.
- `auto`: intenta PDO y cae a HTTPS si falla.

En Hostinger el plan Business tiene `pdo_pgsql`, así que producción usa `DB_DRIVER=pdo` con el endpoint en `options` para que libpq encuentre la rama de Neon.

## Contenido y estados

Los dos tipos editoriales (`cases`, `reports`) comparten estados:

```text
borrador → en_revision → publicado
                │
                ├→ cambios_solicitados → en_revision
                └→ rechazado
```

- El autor puede editar en `borrador`, `cambios_solicitados` y `rechazado`.
- Un editor o administrador puede editar y revisar cualquier estado.
- Publicar asigna `published_at`; cualquier otro estado lo limpia.
- Cada decisión se registra en `reviews` y `audit_log`.

Las especies y las instituciones son catálogos editoriales, no contenido con flujo de revisión. Se siembran desde `database/seeds/catalog.php`.

## Verificación de miembros

`MemberVerifier` recibe el correo y la institución seleccionada:

1. Busca el dominio en `institutions.email_domains`.
2. Si no está y el dominio termina en `.edu.mx`, verifica automáticamente.
3. Si no, deja la cuenta en `pendiente` con método `manual`.
4. El administrador puede verificarla manualmente o suspenderla.

La constancia o credencial se guarda en `storage/constancias/{user_id}/` y se sirve sólo desde `/admin/constancias/...` a administradores autenticados.

## Métricas del caso

Las métricas se capturan como porcentajes o razones y se muestran sólo si existen:

- Contaminación: explantes contaminados / totales × 100.
- Supervivencia: explantes vivos al final de la fase / iniciales × 100.
- Multiplicación: brotes o propágulos / explante sembrado.
- Enraizamiento: brotes con raíz / brotes transferidos × 100.
- Aclimatación: plántulas vivas a cuatro semanas / trasplantadas × 100.

El portal no calcula resultados experimentales: sólo los presenta y valida rangos.

## Reportes y tratamientos

`reports` guarda el texto del reporte y `report_treatments` la tabla estructurada. Al guardar, el repositorio reemplaza las filas del reporte dentro de una operación de escritura. Los archivos viven en `report_files` y se almacenan en `public/uploads/archivos/reporte-{id}/`.

La gráfica de la ficha se genera en el navegador con CSS a partir de las respuestas; no se guarda una imagen derivada. El PDF usa `SimplePdf::table` para reconstruir la tabla.

## PDF

`SimplePdf` implementa un generador PDF mínimo:

- Fuentes Helvetica/Helvetica-Bold y codificación Windows-1252.
- Saltos de página y pie con numeración.
- Imágenes JPEG incrustadas.
- Tabla con anchos proporcionales para los tratamientos.

`CasePdf` y `ReportPdf` componen el documento con folio, autoría, institución, licencia y cita sugerida.

## Búsqueda

`cases.search_vector` y `reports.search_vector` son columnas `tsvector` actualizadas por trigger con `to_tsvector('spanish', …)`. El buscador usa `websearch_to_tsquery`, complementado con `pg_trgm` para títulos y resúmenes. Los filtros por categoría, especie, estado y año se aplican con SQL parametrizado.

## Seguridad

- CSRF en todos los POST; token regenerado al iniciar sesión.
- Contraseñas con `PASSWORD_DEFAULT`.
- Cookies `HttpOnly`, `SameSite=Lax` y `Secure` cuando `SESSION_SECURE=true`.
- Validación de MIME real con `finfo`, tamaño, dimensiones y lista blanca.
- Documentos privados fuera de `public/` y servidos por controlador autenticado.
- Límite de intentos de acceso por correo e IP.
- `.htaccess` raíz bloquea `app/`, `config/`, `database/`, `storage/`, `bin/`, `docs/`, `tools/` y `.env`.

## Despliegue

Ver [`DESPLIEGUE-HOSTINGER.md`](DESPLIEGUE-HOSTINGER.md).
