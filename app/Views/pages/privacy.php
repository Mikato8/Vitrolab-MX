<section class="page-hero">
    <div class="container">
        <nav class="breadcrumb" aria-label="Migas de pan"><a href="<?= e(url('/')) ?>">Inicio</a> <?= icon('chevron-right', 'icon icon-xs') ?> <span>Privacidad</span></nav>
        <div class="page-hero-grid">
            <div>
                <span class="eyebrow">Aviso de privacidad</span>
                <h1>Tus datos, con el mínimo necesario.</h1>
                <p>Este aviso explica qué datos recabamos, para qué los usamos, quién puede verlos y cómo ejercer tus derechos.</p>
            </div>
        </div>
    </div>
</section>
<section class="section">
    <div class="container prose-page-grid">
        <article class="prose-page">
            <h2>Responsable</h2>
            <p>VitroLab MX es un proyecto editorial independiente con domicilio de contacto en México. Para asuntos de datos personales: <a href="mailto:privacidad@vitrolab.mx">privacidad@vitrolab.mx</a>.</p>

            <h2>Datos que recabamos</h2>
            <ul>
                <li><strong>Cuenta:</strong> nombre, correo, contraseña cifrada, rol, institución, formación, semestre, estado, semblanza y ORCID opcional.</li>
                <li><strong>Verificación:</strong> método, dominio del correo y, si lo subes, constancia o credencial institucional.</li>
                <li><strong>Contenido:</strong> casos, reportes, imágenes, archivos, comentarios, reacciones y favoritos.</li>
                <li><strong>Uso:</strong> fecha de acceso, dirección IP, intentos de inicio de sesión y acciones editoriales.</li>
                <li><strong>Comunicaciones:</strong> mensajes que nos envíes por correo o formularios.</li>
            </ul>

            <h2>Finalidades</h2>
            <p>Usamos los datos para operar la cuenta, verificar pertenencia institucional, publicar contenido, moderar la comunidad, prevenir abuso, emitir PDF, responder solicitudes y mantener respaldos. No vendemos datos personales ni los usamos para publicidad de terceros.</p>

            <h2>Qué es público</h2>
            <p>Son públicos tu nombre, institución, formación, semblanza, ORCID, foto de perfil y las publicaciones y comentarios que decidas compartir. No son públicos tu correo, matrícula, constancia, IP, historial de acceso ni borradores. El correo se usa para autenticación y notificaciones del servicio.</p>

            <h2>Encargados y transferencias</h2>
            <p>El sitio se aloja en Hostinger y la base de datos en Neon (PostgreSQL administrado). Estos proveedores procesan datos por cuenta del proyecto. No transferimos datos a terceros para fines comerciales. Podemos divulgar información si una autoridad competente lo requiere conforme a la ley.</p>

            <h2>Conservación</h2>
            <p>Conservamos la cuenta mientras esté activa. Los registros de seguridad se conservan por periodos limitados. Si eliminas tu cuenta, anonimizamos tus datos personales y conservamos el contenido publicado con autoría anonimizada para no romper las discusiones. Los respaldos pueden tardar en rotar.</p>

            <h2>Derechos</h2>
            <p>Puedes solicitar acceso, rectificación, cancelación u oposición al tratamiento, así como retirar tu consentimiento, escribiendo a <a href="mailto:privacidad@vitrolab.mx">privacidad@vitrolab.mx</a>. También puedes actualizar tus datos desde <a href="<?= e(url('/perfil')) ?>">tu perfil</a> y eliminar tu cuenta desde la misma página. Responderemos en los plazos aplicables.</p>

            <h2>Seguridad</h2>
            <p>Usamos contraseñas con hash, CSRF en formularios, cookies HttpOnly y SameSite, validación de archivos, control de intentos de acceso y separación de documentos privados. Ningún sistema es infalible: si detectas una vulnerabilidad, escríbenos con responsabilidad y sin divulgar datos de terceros.</p>

            <h2>Cambios</h2>
            <p>Si cambiamos este aviso de forma sustancial, lo anunciaremos en el sitio y actualizaremos la fecha. La versión vigente es la publicada en esta página.</p>
            <p class="muted">Última actualización: octubre de 2026.</p>
        </article>
        <aside class="prose-aside">
            <div class="sidebar-card">
                <h2 class="sidebar-title"><?= icon('lock', 'icon icon-sm') ?> En corto</h2>
                <ul class="check-list check-list-small">
                    <li>No vendemos datos.</li>
                    <li>Correo y matrícula no son públicos.</li>
                    <li>Las constancias son privadas.</li>
                    <li>Puedes eliminar tu cuenta.</li>
                </ul>
            </div>
            <div class="sidebar-card">
                <h2 class="sidebar-title"><?= icon('mail', 'icon icon-sm') ?> Contacto</h2>
                <a class="btn btn-outline btn-block btn-sm" href="mailto:privacidad@vitrolab.mx">privacidad@vitrolab.mx</a>
            </div>
        </aside>
    </div>
</section>
