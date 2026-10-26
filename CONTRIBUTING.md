# Contribuir

## Contenido editorial

1. Crea una cuenta con correo institucional o sube una constancia.
2. Documenta un caso o reporte siguiendo [`/guias`](https://lightcoral-hare-545659.hostingersite.com/guias).
3. Revisa que las concentraciones tengan unidad, que el tamaño de muestra sea explícito y que los resultados negativos también se reporten.
4. Envía a revisión. El comité puede publicar, pedir cambios o rechazar con notas.

## Código

- PHP 8.2+, `declare(strict_types=1)` y sin Composer.
- Respeta la separación `Controllers → Repositories → Database`.
- SQL parametrizado con marcadores `$1, $2, …`; nunca concatenes entrada del usuario.
- Vistas en `app/Views`, estilos en `public/assets/css/app.css`.
- No agregues dependencias de JavaScript si se resuelve con CSS o JS nativo.

### Verificación local

```bash
php -l app/Controllers/*.php
php bin/check.php
php -S 127.0.0.1:8085 -t public bin/router.php
```

### Ramas y commits

- `main` debe quedar desplegable.
- Usa ramas cortas: `fix/pdf-tabla`, `feat/filtro-especies`.
- Un commit por cambio coherente; explica el qué y el por qué.

## Datos y privacidad

No incluyas nombres, correos, teléfonos, matrículas ni documentos de personas reales sin consentimiento. Para imágenes de laboratorio, verifica que no aparezcan rostros ni identificadores.

## Licencia

Al contribuir aceptas que el código se publique bajo MIT y que el contenido editorial conserve la licencia que declare cada autor.
