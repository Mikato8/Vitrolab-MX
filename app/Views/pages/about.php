<?php /** @var array<string, int> $stats */ ?>
<section class="page-hero">
    <div class="container">
        <nav class="breadcrumb" aria-label="Migas de pan"><a href="<?= e(url('/')) ?>">Inicio</a> <?= icon('chevron-right', 'icon icon-xs') ?> <span>Nosotros</span></nav>
        <div class="page-hero-grid">
            <div>
                <span class="eyebrow">Quiénes somos</span>
                <h1>Un archivo abierto del cultivo in vitro en México.</h1>
                <p>VitroLab MX reúne casos, reportes de laboratorio y fuentes sobre cultivo de tejidos vegetales. No es un consultorio ni una autoridad sanitaria: es un espacio para documentar métodos, comparar resultados y discutir límites con evidencia.</p>
            </div>
            <div class="page-hero-aside">
                <span class="big-number"><?= number_format($stats['members'] ?? 0) ?></span>
                <span>miembros verificados</span>
                <span class="muted"><?= number_format($stats['institutions'] ?? 0) ?> instituciones registradas</span>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container prose-page-grid">
        <article class="prose-page">
            <h2>Por qué existe</h2>
            <p>En muchos laboratorios, los protocolos viven en cuadernos, presentaciones o en la memoria de quien estuvo en la campana. Cuando esa persona se va, el conocimiento se va con ella. Este portal existe para convertir ese trabajo en un archivo consultable, con condiciones explícitas y resultados comparables.</p>
            <p>También existe porque publicar no debería depender de tener un título de posgrado o una cédula. Un estudiante puede observar, medir y documentar. Lo que sostiene la calidad no es el cargo, sino la trazabilidad del método, la revisión entre pares y la honestidad sobre lo que no funcionó.</p>

            <h2>Qué puedes hacer aquí</h2>
            <ul>
                <li><strong>Casos:</strong> documenta especie, explante, desinfección, medio, reguladores, condiciones, protocolo y resultados.</li>
                <li><strong>Reportes:</strong> publica ensayos, validaciones, protocolos, diagnósticos y seguimientos con diseño y datos.</li>
                <li><strong>Especies e instituciones:</strong> encuentra quién trabaja con qué material y desde dónde.</li>
                <li><strong>Biblioteca:</strong> consulta repositorios, datasets, manuales y literatura científica con enlaces verificables.</li>
            </ul>

            <h2>Cómo trabajamos</h2>
            <p>Un comité editorial revisa cada publicación antes de hacerla pública. Verifica que el protocolo tenga condiciones suficientes para repetirse, que las cifras sean coherentes, que no haya datos personales sin consentimiento y que el autor distinga resultados de interpretación. La revisión no certifica la veracidad absoluta de un experimento: certifica que cumple los criterios editoriales y que puede discutirse con evidencia.</p>
            <p>La verificación es institucional. Si tu correo pertenece a una institución del directorio o termina en <code>.edu.mx</code>, la cuenta se verifica automáticamente. Si usas un correo personal, puedes subir una constancia o credencial y un editor la revisará. <strong>Nunca pedimos cédula profesional.</strong></p>

            <h2>Lo que no somos</h2>
            <p>No somos una consultoría, un laboratorio certificado, una autoridad sanitaria ni una plataforma de inversión. No auditamos resultados ni garantizamos que un protocolo funcione en otra especie, genotipo o laboratorio. Las publicaciones son trabajos documentales y deben leerse como tales. Para una decisión sanitaria, regulatoria o comercial, acude a un especialista y verifica la normativa aplicable.</p>

            <h2>Licencias y crédito</h2>
            <p>El código del portal está bajo licencia MIT. Cada caso y reporte declara su propia licencia. La cita sugerida aparece en la ficha y en el PDF. Si reutilizas una figura o una tabla, atribuye al autor y respeta la licencia declarada.</p>
        </article>
        <aside class="prose-aside">
            <div class="sidebar-card">
                <h2 class="sidebar-title"><?= icon('activity', 'icon icon-sm') ?> En números</h2>
                <dl class="plot-data">
                    <div><dt>Casos publicados</dt><dd><?= number_format($stats['cases'] ?? 0) ?></dd></div>
                    <div><dt>Reportes</dt><dd><?= number_format($stats['reports'] ?? 0) ?></dd></div>
                    <div><dt>Especies</dt><dd><?= number_format($stats['species'] ?? 0) ?></dd></div>
                    <div><dt>Estados</dt><dd><?= number_format($stats['states'] ?? 0) ?></dd></div>
                    <div><dt>Instituciones</dt><dd><?= number_format($stats['institutions'] ?? 0) ?></dd></div>
                </dl>
            </div>
            <div class="sidebar-card">
                <h2 class="sidebar-title"><?= icon('shield', 'icon icon-sm') ?> Principios</h2>
                <ul class="check-list check-list-small">
                    <li>Transparencia sobre el método y los límites.</li>
                    <li>Consentimiento y anonimización.</li>
                    <li>Crédito y licencia para cada autor.</li>
                    <li>Revisión editorial con retroalimentación.</li>
                    <li>Ninguna promesa de resultado.</li>
                </ul>
            </div>
            <div class="sidebar-card">
                <h2 class="sidebar-title"><?= icon('mail', 'icon icon-sm') ?> Contacto</h2>
                <p>¿Quieres proponer una colaboración, una corrección o una visita de laboratorio?</p>
                <a class="btn btn-outline btn-block btn-sm" href="mailto:hola@vitrolab.mx">hola@vitrolab.mx</a>
            </div>
        </aside>
    </div>
</section>
