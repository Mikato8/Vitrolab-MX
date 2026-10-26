<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\CatalogRepository;

final class PageController extends BaseController
{
    public function about(Request $request): Response
    {
        $stats = (new CatalogRepository())->stats();
        return $this->view('pages/about', [
            'title' => 'Nosotros · VitroLab MX',
            'metaDescription' => 'Una comunidad abierta para documentar el cultivo in vitro de material vegetal en México: casos, protocolos y reportes con revisión editorial.',
            'stats' => $stats,
        ]);
    }

    public function ethics(Request $request): Response
    {
        return $this->view('pages/ethics', [
            'title' => 'Ética y criterios editoriales · VitroLab MX',
            'metaDescription' => 'Criterios de publicación, trazabilidad, reproducibilidad, conflicto de interés y manejo de material vegetal.',
        ]);
    }

    public function privacy(Request $request): Response
    {
        return $this->view('pages/privacy', [
            'title' => 'Aviso de privacidad · VitroLab MX',
            'metaDescription' => 'Cómo tratamos tus datos personales y qué información es pública en VitroLab MX.',
        ]);
    }

    public function terms(Request $request): Response
    {
        return $this->view('pages/terms', [
            'title' => 'Términos de uso · VitroLab MX',
            'metaDescription' => 'Reglas de convivencia, licencias y responsabilidad sobre el contenido publicado.',
        ]);
    }

    public function guides(Request $request): Response
    {
        return $this->view('pages/guides', [
            'title' => 'Guías para documentar · VitroLab MX',
            'metaDescription' => 'Método para escribir un caso de cultivo in vitro, diseñar un reporte de laboratorio y reportar resultados con trazabilidad.',
        ]);
    }

    public function library(Request $request): Response
    {
        return $this->view('pages/library', [
            'title' => 'Biblioteca y fuentes · VitroLab MX',
            'metaDescription' => 'Repositorios, datasets, manuales y literatura científica sobre cultivo in vitro de plantas, con enlaces verificables.',
            'resources' => $this->resources(),
        ]);
    }

    public function glossary(Request $request): Response
    {
        return $this->view('pages/glossary', [
            'title' => 'Glosario de cultivo in vitro · VitroLab MX',
            'metaDescription' => 'Términos esenciales de cultivo de tejidos vegetales: explante, callo, organogénesis, PGR, vitrificación y más.',
            'terms' => $this->glossaryTerms(),
        ]);
    }

    /** @return array<int, array<string, mixed>> */
    private function resources(): array
    {
        return [
            [
                'group' => 'Repositorios de código y datos',
                'items' => [
                    [
                        'title' => 'tissue-culture-andropogon',
                        'author' => 'avahoffman · GitHub',
                        'description' => 'Código y datos del estudio «Thinking inside the Box: Tissue Culture for Plant Propagation in a Key Ecological Species». Micropropagación de Andropogon gerardii para restauración ecológica.',
                        'url' => 'https://github.com/avahoffman/tissue-culture-andropogon',
                        'tag' => 'GitHub',
                        'lang' => 'R · datos',
                    ],
                    [
                        'title' => 'GMOnotebook',
                        'author' => 'BigPhenomics · GitHub',
                        'description' => 'Flujos de trabajo en Jupyter para estudiar tasas de transformación de plantas con imágenes hiperespectrales y RGB de callos y brotes de Populus trichocarpa.',
                        'url' => 'https://github.com/BigPhenomics/GMOnotebook',
                        'tag' => 'GitHub',
                        'lang' => 'Python · Jupyter',
                    ],
                    [
                        'title' => 'HADES research resources',
                        'author' => 'NPEC-NL · GitHub',
                        'description' => 'Recursos de la plataforma HADES para fenotipado estéril de raíces in vitro, siembra robótica, control climático e imagen multimodal.',
                        'url' => 'https://github.com/NPEC-NL/hades-research-resources',
                        'tag' => 'GitHub',
                        'lang' => 'Hardware · datos',
                    ],
                    [
                        'title' => 'exp-plant-tissue-culture-au',
                        'author' => 'virtual-labs · GitHub',
                        'description' => 'Experimento virtual para enseñar transferencia aséptica de explantes y propagación in vitro. Útil como material didáctico de laboratorio.',
                        'url' => 'https://github.com/virtual-labs/exp-plant-tissue-culture-au',
                        'tag' => 'GitHub',
                        'lang' => 'Docencia',
                    ],
                    [
                        'title' => 'Caduceus-Dataset',
                        'author' => 'Kquant03 · Hugging Face',
                        'description' => 'Incluye un protocolo para generar plantas axénicas de Sporobolus alterniflorus a partir de semillas, con esterilización y establecimiento en medio MS modificado.',
                        'url' => 'https://huggingface.co/datasets/Kquant03/Caduceus-Dataset',
                        'tag' => 'Hugging Face',
                        'lang' => 'Dataset',
                    ],
                    [
                        'title' => 'agri_data',
                        'author' => 'parthrautV · Hugging Face',
                        'description' => 'Cuestionario de preguntas y respuestas sobre micropropagación, etapas del cultivo de tejidos, semillas artificiales y micropropagación fotoautotrófica.',
                        'url' => 'https://huggingface.co/datasets/parthrautV/agri_data',
                        'tag' => 'Hugging Face',
                        'lang' => 'Dataset',
                    ],
                ],
            ],
            [
                'group' => 'Literatura, manuales y protocolos',
                'items' => [
                    [
                        'title' => 'Murashige & Skoog (1962)',
                        'author' => 'Physiologia Plantarum 15: 473-497',
                        'description' => 'El medio MS original. Base de la mayoría de los protocolos de micropropagación citados en el portal.',
                        'url' => 'https://doi.org/10.1111/j.1399-3054.1962.tb08052.x',
                        'tag' => 'Artículo',
                        'lang' => 'DOI',
                    ],
                    [
                        'title' => 'Plant Propagation by Tissue Culture',
                        'author' => 'George, Hall & De Klerk (2008) · Springer',
                        'description' => 'Referencia integral sobre medios, reguladores, organogénesis, embriogénesis y aclimatación. Nuestro manual de cabecera para revisar protocolos.',
                        'url' => 'https://doi.org/10.1007/978-1-4020-5005-3',
                        'tag' => 'Libro',
                        'lang' => 'DOI',
                    ],
                    [
                        'title' => 'Plant Tissue Culture: An Introductory Text',
                        'author' => 'Bhojwani & Dantu (2013) · Springer',
                        'description' => 'Texto introductorio con protocolos de laboratorio y explicaciones de los fundamentos del cultivo de tejidos.',
                        'url' => 'https://doi.org/10.1007/978-81-322-1026-9',
                        'tag' => 'Libro',
                        'lang' => 'DOI',
                    ],
                    [
                        'title' => 'Plant Cell Culture Protocols',
                        'author' => 'Loyola-Vargas & Ochoa-Alejo (2018) · Humana Press',
                        'description' => 'Protocolos detallados de embriogénesis somática, organogénesis, transformación y conservación en distintas especies.',
                        'url' => 'https://doi.org/10.1007/978-1-4939-8594-4',
                        'tag' => 'Protocolos',
                        'lang' => 'DOI',
                    ],
                    [
                        'title' => 'Coffee somatic embryogenesis',
                        'author' => 'Etienne et al. (2018) · Frontiers in Plant Science',
                        'description' => 'Revisión sobre la embriogénesis somática del café y su transferencia a la propagación comercial de clones élite.',
                        'url' => 'https://doi.org/10.3389/fpls.2018.01630',
                        'tag' => 'Artículo',
                        'lang' => 'DOI',
                    ],
                    [
                        'title' => 'FAO · Banana tissue culture',
                        'author' => 'Organización de las Naciones Unidas para la Alimentación y la Agricultura',
                        'description' => 'Guía práctica para producir y manejar material de siembra de plátano y banano libre de patógenos.',
                        'url' => 'https://www.fao.org/3/i2250en/i2250en.pdf',
                        'tag' => 'Manual',
                        'lang' => 'PDF',
                    ],
                ],
            ],
            [
                'group' => 'Repositorios institucionales y datos experimentales',
                'items' => [
                    [
                        'title' => 'Repositorio UAAAN',
                        'author' => 'Universidad Autónoma Agraria Antonio Narro',
                        'description' => 'Tesis y publicaciones sobre micropropagación de pitaya, agave y otras especies de zonas áridas.',
                        'url' => 'https://repositorio.uaaan.mx',
                        'tag' => 'Repositorio',
                        'lang' => 'Institucional',
                    ],
                    [
                        'title' => 'LOCUS · Repositório Institucional UFV',
                        'author' => 'Universidade Federal de Viçosa',
                        'description' => 'Tesis y artículos con protocolos de micropropagación, organogénesis y conservación de Corymbia y otras especies forestales.',
                        'url' => 'https://www.locus.ufv.br',
                        'tag' => 'Repositorio',
                        'lang' => 'Institucional',
                    ],
                    [
                        'title' => 'Repositorio CIATEJ',
                        'author' => 'Centro de Investigación y Asistencia en Tecnología y Diseño del Estado de Jalisco',
                        'description' => 'Publicaciones y tesis sobre biotecnología vegetal, micropropagación y metabolitos secundarios.',
                        'url' => 'https://ciatej.repositorioinstitucional.mx',
                        'tag' => 'Repositorio',
                        'lang' => 'Institucional',
                    ],
                    [
                        'title' => 'Datos de optimización de medios',
                        'author' => 'Conjunto experimental citado en la recopilación',
                        'description' => '729 observaciones de combinaciones de BAP, IBA, floroglucinol y sacarosa para medir tasas de proliferación. Útil para planear dosis-respuesta.',
                        'url' => 'https://github.com/search?q=tissue+culture+BAP+IBA+phloroglucinol',
                        'tag' => 'Datos',
                        'lang' => 'Búsqueda',
                    ],
                    [
                        'title' => 'Segmentación de callos de lenteja',
                        'author' => 'Conjunto experimental citado en la recopilación',
                        'description' => 'Imágenes de callos de Lens culinaris de tres genotipos para tareas de segmentación y fenotipado.',
                        'url' => 'https://huggingface.co/datasets?search=lentil+callus',
                        'tag' => 'Datos',
                        'lang' => 'Búsqueda',
                    ],
                    [
                        'title' => 'Kiya-SFT y PMC',
                        'author' => 'NaolBM · hatakeyama-llm-team',
                        'description' => 'Corpus de texto científico sobre reguladores del crecimiento y manipulación in vitro de especies modelo como Populus. Útil para búsqueda semántica.',
                        'url' => 'https://huggingface.co/datasets/hatakeyama-llm-team/PMC',
                        'tag' => 'Hugging Face',
                        'lang' => 'Corpus',
                    ],
                ],
            ],
        ];
    }

    /** @return array<int, array{term: string, definition: string}> */
    private function glossaryTerms(): array
    {
        return [
            ['term' => 'Cultivo in vitro', 'definition' => 'Crecimiento de células, tejidos u órganos vegetales en condiciones controladas y asépticas, fuera de la planta completa.'],
            ['term' => 'Explante', 'definition' => 'Fragmento de tejido vegetal que se separa de la planta madre para iniciar un cultivo in vitro. Puede ser una yema, una hoja, un segmento nodal o un ápice.'],
            ['term' => 'Micropropagación', 'definition' => 'Multiplicación clonal de plantas a partir de explantes en condiciones in vitro. Permite obtener muchos individuos genéticamente idénticos en poco espacio.'],
            ['term' => 'Organogénesis', 'definition' => 'Formación de órganos (brotes o raíces) a partir de tejidos somáticos. Puede ser directa, sin callo visible, o indirecta, a través de callo.'],
            ['term' => 'Embriogénesis somática', 'definition' => 'Formación de embriones a partir de células somáticas, sin fecundación. Los embriones pueden madurar y convertirse en plántulas.'],
            ['term' => 'Callo', 'definition' => 'Masa desorganizada de células en división que se forma en un explante bajo la acción de reguladores del crecimiento. No siempre es regenerable.'],
            ['term' => 'Medio MS', 'definition' => 'Medio de cultivo formulado por Murashige y Skoog (1962). Aporta sales minerales, vitaminas, sacarosa y agua; se ajusta con reguladores según el objetivo.'],
            ['term' => 'Regulador del crecimiento vegetal (PGR)', 'definition' => 'Compuesto que modifica el crecimiento o la diferenciación. En cultivo in vitro se usan auxinas, citocininas, giberelinas y otros.'],
            ['term' => 'Auxina', 'definition' => 'Familia de reguladores asociada al enraizamiento, la elongación y la formación de callo. Ejemplos: AIB (IBA), ANA (NAA) y 2,4-D.'],
            ['term' => 'Citocinina', 'definition' => 'Familia de reguladores que promueve la división celular y la formación de brotes. Ejemplos: BAP, kinetina, zeatina y TDZ.'],
            ['term' => 'BAP', 'definition' => 'Bencilaminopurina. Citocinina sintética muy usada para inducir brotes múltiples en micropropagación.'],
            ['term' => 'AIB (IBA)', 'definition' => 'Ácido indolbutírico. Auxina utilizada para inducir raíces adventicias durante el enraizamiento in vitro.'],
            ['term' => '2,4-D', 'definition' => 'Ácido 2,4-diclorofenoxiacético. Auxina sintética fuerte, usada para inducir callo y embriogénesis somática.'],
            ['term' => 'Cámara de flujo laminar', 'definition' => 'Cabina que filtra el aire para crear un ambiente estéril donde se manipulan explantes y medios.'],
            ['term' => 'Autoclavado', 'definition' => 'Esterilización por vapor a presión (habitualmente 121 °C, 15 psi, 15-20 min) de medios, recipientes e instrumentos.'],
            ['term' => 'Aclimatación', 'definition' => 'Transición gradual de una plántula in vitro a condiciones de invernadero o campo, con control de humedad, luz y sustrato.'],
            ['term' => 'Vitrificación', 'definition' => 'Alteración morfológica y fisiológica de brotes cultivados in vitro, con hojas translúcidas, hiperhidricidad y baja supervivencia al trasplante.'],
            ['term' => 'Hiperhidricidad', 'definition' => 'Exceso de agua en los tejidos, asociado a vitrificación. Se relaciona con humedad alta en el frasco, sellado y concentración de reguladores.'],
            ['term' => 'Contaminación endógena', 'definition' => 'Microorganismos que viven dentro del tejido vegetal y se manifiestan después de la superficie estéril. Es más difícil de eliminar que la contaminación superficial.'],
            ['term' => 'Indexación', 'definition' => 'Prueba de laboratorio (por ejemplo, RT-PCR o ELISA) para detectar patógenos en el material vegetal.'],
            ['term' => 'Variación somaclonal', 'definition' => 'Cambios genéticos o epigenéticos que aparecen en plantas regeneradas a partir de cultivo de tejidos, especialmente tras varios subcultivos.'],
            ['term' => 'Banco de germoplasma', 'definition' => 'Colección organizada de material vegetal con fines de conservación y estudio. Puede mantenerse en campo, in vitro o en criopreservación.'],
            ['term' => 'Crecimiento mínimo', 'definition' => 'Estrategia de conservación in vitro que reduce el crecimiento mediante bajas temperaturas, osmolitos o medios diluidos, para espaciar los subcultivos.'],
            ['term' => 'Criopreservación', 'definition' => 'Conservación de material biológico a temperaturas ultra bajas, generalmente en nitrógeno líquido (-196 °C).'],
            ['term' => 'Inmersión temporal', 'definition' => 'Sistema de cultivo en el que el explante se sumerge periódicamente en medio líquido y luego se expone al aire, favoreciendo la oxigenación y la multiplicación.'],
            ['term' => 'Fotoperiodo', 'definition' => 'Duración de la fase de luz en un ciclo de 24 horas. En cultivo in vitro suele usarse 16 horas de luz y 8 de oscuridad.'],
            ['term' => 'Sustrato', 'definition' => 'Material sólido donde se siembra la plántula durante la aclimatación. Debe tener buena aireación, retención de agua y estar libre de patógenos.'],
        ];
    }
}
