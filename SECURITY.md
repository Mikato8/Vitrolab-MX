# Seguridad

## Reportar una vulnerabilidad

Escribe a **seguridad@vitrolab.mx** con:

- Descripción del problema y pasos para reproducirlo.
- URL o endpoint afectado.
- Impacto estimado.
- Si es posible, una prueba de concepto sin datos de terceros.

No publiques la vulnerabilidad ni datos personales antes de que podamos responder. Confirmamos recepción en un plazo razonable y coordinamos la divulgación.

## Alcance

Este repositorio contiene una aplicación PHP sin framework y su esquema PostgreSQL. Quedan fuera de alcance:

- La infraestructura de Hostinger y Neon (se reportan a sus respectivos proveedores).
- Ataques de denegación de servicio.
- Ingeniería social.
- Pruebas que modifiquen o eliminen contenido de producción sin autorización.

## Medidas implementadas

- CSRF en formularios y acciones POST.
- Contraseñas con `PASSWORD_DEFAULT`.
- Cookies `HttpOnly`, `SameSite=Lax` y `Secure` en HTTPS.
- Validación de MIME real, tamaño y dimensiones.
- Lista blanca de documentos y bloqueo de ejecución de PHP en uploads.
- Límite de intentos de acceso por correo e IP.
- Constancias privadas fuera del árbol público.
- Auditoría de acciones editoriales y de cuenta.

## Credenciales

No publiques `.env`, cadenas de conexión ni contraseñas. Rota las credenciales de Neon y del administrador después de cada despliegue compartido. Si una credencial se expone, revócala de inmediato.
