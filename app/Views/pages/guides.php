<section class="page-hero">
    <div class="container">
        <nav class="breadcrumb" aria-label="Migas de pan"><a href="<?= e(url('/')) ?>">Inicio</a> <?= icon('chevron-right', 'icon icon-xs') ?> <span>Guías</span></nav>
        <div class="page-hero-grid">
            <div>
                <span class="eyebrow">Método de trabajo</span>
                <h1>Guías para documentar con criterio</h1>
                <p>Dos formatos, una misma exigencia: material de partida, condiciones, evidencia y límites. Usa estas guías antes de enviar a revisión.</p>
            </div>
        </div>
    </div>
</section>

<section class="section section-tight">
    <div class="container guide-grid">
        <article class="guide-card">
            <span class="guide-number">01</span>
            <h2><?= icon('flask', 'icon icon-sm') ?> Caso de cultivo in vitro</h2>
            <p>Sirve para documentar un protocolo completo: qué material se usó, cómo se desinfectó, qué medio se preparó y qué ocurrió.</p>
            <ol class="guide-steps">
                <li><strong>Ficha.</strong> Especie, variedad o genotipo, procedencia y estado sanitario del material.</li>
                <li><strong>Explante.</strong> Tipo, tamaño, posición, edad y número de explantes por tratamiento.</li>
                <li><strong>Desinfección.</strong> Agentes, concentraciones, tiempos, enjuagues y condiciones de la campana.</li>
                <li><strong>Medio.</strong> Sales, vitaminas, sacarosa, agar, pH, esterilización y recipientes.</li>
                <li><strong>Reguladores.</strong> Tipo, concentración, fase y orden de aplicación.</li>
                <li><strong>Condiciones.</strong> Temperatura, fotoperiodo, luz, humedad y subcultivos.</li>
                <li><strong>Resultados.</strong> Contaminación, supervivencia, multiplicación, enraizamiento y aclimatación.</li>
                <li><strong>Límites.</strong> Qué falló, qué se ajustó y qué no se puede concluir.</li>
            </ol>
            <a class="btn btn-outline btn-sm" href="<?= e(url('/casos/nuevo')) ?>">Empezar un caso</a>
        </article>

        <article class="guide-card">
            <span class="guide-number">02</span>
            <h2><?= icon('file-text', 'icon icon-sm') ?> Reporte de laboratorio</h2>
            <p>Sirve para comparar tratamientos y sostener una conclusión con datos, no con impresiones.</p>
            <ol class="guide-steps">
                <li><strong>Objetivo e hipótesis.</strong> Qué se comparó y qué se esperaba.</li>
                <li><strong>Diseño.</strong> Factores, niveles, aleatorización, unidad experimental y número de repeticiones.</li>
                <li><strong>Tratamientos.</strong> Una fila por tratamiento, con concentración y control.</li>
                <li><strong>Variables.</strong> Unidad, método y momento de medición.</li>
                <li><strong>Resultados.</strong> Números, dispersión, resultados negativos y observaciones.</li>
                <li><strong>Análisis.</strong> Pruebas, supuestos y qué parte es interpretación.</li>
                <li><strong>Conclusiones.</strong> Respuesta al objetivo sin extrapolar más allá del diseño.</li>
                <li><strong>Limitaciones.</strong> Tamaño de muestra, estación, genotipo, sesgos.</li>
            </ol>
            <a class="btn btn-outline btn-sm" href="<?= e(url('/reportes/nuevo')) ?>">Subir un reporte</a>
        </article>
    </div>
</section>

<section class="section">
    <div class="container prose-page-grid">
        <article class="prose-page">
            <h2>Anatomía de un protocolo repetible</h2>
            <p>Un protocolo no es una lista de pasos. Es una descripción de condiciones que otra persona puede reconstruir. Si falta la concentración, el tiempo o la unidad, el paso no es repetible.</p>
            <div class="ficha-grid">
                <div class="ficha-item"><span>Material</span><p>Especie, genotipo, edad, procedencia y estado sanitario. No basta con «explante de hoja».</p></div>
                <div class="ficha-item"><span>Desinfección</span><p>Agente, concentración, tiempo, tensoactivo, enjuagues y número de explantes por lote.</p></div>
                <div class="ficha-item"><span>Medio</span><p>Sales, vitaminas, sacarosa, agar, pH antes de esterilizar y método de esterilización.</p></div>
                <div class="ficha-item"><span>PGR</span><p>Tipo, concentración (mg/L o µM), fase y si se filtró o se autoclavó.</p></div>
                <div class="ficha-item"><span>Ambiente</span><p>Temperatura, fotoperiodo, densidad de flujo, humedad, recipiente y sellado.</p></div>
                <div class="ficha-item"><span>Evaluación</span><p>Qué se midió, cuándo, con qué instrumento y cómo se definió cada categoría.</p></div>
            </div>

            <h2>Checklist antes de enviar a revisión</h2>
            <ul class="check-list">
                <li><?= icon('check', 'icon icon-sm') ?> El título describe el material, el método y el resultado principal.</li>
                <li><?= icon('check', 'icon icon-sm') ?> El resumen permite entender el hallazgo sin leer todo.</li>
                <li><?= icon('check', 'icon icon-sm') ?> Las concentraciones tienen unidad y las temperaturas, escala.</li>
                <li><?= icon('check', 'icon icon-sm') ?> El número de explantes y de repeticiones está explícito.</li>
                <li><?= icon('check', 'icon icon-sm') ?> Los resultados negativos o inesperados también se reportan.</li>
                <li><?= icon('check', 'icon icon-sm') ?> No hay nombres, teléfonos o direcciones sin consentimiento.</li>
                <li><?= icon('check', 'icon icon-sm') ?> Las imágenes no muestran personas identificables ni documentos privados.</li>
                <li><?= icon('check', 'icon icon-sm') ?> Las referencias permiten verificar lo afirmado.</li>
            </ul>

            <h2>Cómo se calculan las métricas del caso</h2>
            <p>El portal no calcula por ti los resultados experimentales: los reportas. Pero las fichas usan definiciones consistentes para que puedas comparar:</p>
            <ul>
                <li><strong>Contaminación:</strong> explantes con crecimiento microbiano visible / explantes totales × 100.</li>
                <li><strong>Supervivencia:</strong> explantes vivos al final de la fase / explantes iniciales × 100.</li>
                <li><strong>Multiplicación:</strong> brotes o propágulos obtenidos / explante sembrado.</li>
                <li><strong>Enraizamiento:</strong> brotes con al menos una raíz / brotes transferidos × 100.</li>
                <li><strong>Aclimatación:</strong> plántulas vivas a las cuatro semanas / plántulas trasplantadas × 100.</li>
            </ul>
            <p>Si usas otra definición, declárala en el texto. La comparación sólo es válida cuando las definiciones coinciden.</p>

            <h2>Plantilla mínima de un caso</h2>
            <ol>
                <li>¿Qué especie y qué material de partida?</li>
                <li>¿Cuál era el objetivo y por qué importaba?</li>
                <li>¿Cómo se desinfectó y qué controles se usaron?</li>
                <li>¿Qué medio, reguladores y condiciones?</li>
                <li>¿Qué pasó, con qué números y en cuánto tiempo?</li>
                <li>¿Qué falló y qué se ajustó?</li>
                <li>¿Qué se aprendió y qué queda abierto?</li>
            </ol>
        </article>
        <aside class="prose-aside">
            <div class="sidebar-card">
                <h2 class="sidebar-title"><?= icon('shield', 'icon icon-sm') ?> Ética express</h2>
                <p>Si dudas si un dato es sensible, anonimízalo. Si no tienes autorización para publicar una imagen, no la subas. Si no puedes sustentar una cifra, declárala como estimación.</p>
                <a class="btn btn-outline btn-block btn-sm" href="<?= e(url('/etica')) ?>">Leer criterios completos</a>
            </div>
            <div class="sidebar-card">
                <h2 class="sidebar-title"><?= icon('book-open', 'icon icon-sm') ?> Lecturas</h2>
                <p>Antes de escribir, revisa dos o tres casos publicados y observa cómo estructuran la ficha técnica y los límites.</p>
                <a class="btn btn-primary btn-block btn-sm" href="<?= e(url('/casos')) ?>">Ver casos</a>
            </div>
            <div class="sidebar-card">
                <h2 class="sidebar-title"><?= icon('download', 'icon icon-sm') ?> Plantillas</h2>
                <p>Descarga una estructura base para tu caso o tu tabla de datos.</p>
                <a class="btn btn-outline btn-block btn-sm" href="<?= e(url('/descargas/plantilla-caso-vitrolab.md')) ?>" download>Plantilla de caso (MD)</a>
                <a class="btn btn-outline btn-block btn-sm" href="<?= e(url('/descargas/plantilla-reporte-vitrolab.csv')) ?>" download>Plantilla de datos (CSV)</a>
            </div>
        </aside>
    </div>
</section>
