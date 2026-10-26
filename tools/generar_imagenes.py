#!/usr/bin/env python3
"""Genera las imágenes editoriales y figuras demostrativas de VitroLab MX.

Todo se dibuja localmente con Pillow: no se descargan fotos de terceros.
Salidas:
  public/assets/img/hero-lab.jpg, og-cover.png, logo.svg, favicon.svg
  public/uploads/casos/demo/*.jpg
  public/uploads/archivos/demo/figura-*.png y datos-*.csv
"""
from __future__ import annotations

import csv
import math
import random
from pathlib import Path

from PIL import Image, ImageDraw, ImageFilter, ImageFont

BASE = Path(__file__).resolve().parent.parent
IMG = BASE / "public" / "assets" / "img"
CASES = BASE / "public" / "uploads" / "casos" / "demo"
FILES = BASE / "public" / "uploads" / "archivos" / "demo"
for directory in (IMG, CASES, FILES):
    directory.mkdir(parents=True, exist_ok=True)

SERIF = "/System/Library/Fonts/Supplemental/Georgia.ttf"
SERIF_ITALIC = "/System/Library/Fonts/Supplemental/Georgia Italic.ttf"
SERIF_BOLD = "/System/Library/Fonts/Supplemental/Georgia Bold.ttf"
SANS = "/System/Library/Fonts/HelveticaNeue.ttc"
SANS_BOLD = "/System/Library/Fonts/HelveticaNeue.ttc"


def font(path: str, size: int, index: int = 0) -> ImageFont.FreeTypeFont:
    return ImageFont.truetype(path, size=size, index=index)


def gradient(size: tuple[int, int], top: tuple[int, int, int], bottom: tuple[int, int, int]) -> Image.Image:
    width, height = size
    image = Image.new("RGB", size, top)
    draw = ImageDraw.Draw(image)
    for y in range(height):
        t = y / max(1, height - 1)
        color = tuple(int(top[i] * (1 - t) + bottom[i] * t) for i in range(3))
        draw.line([(0, y), (width, y)], fill=color)
    return image


def leaf(draw: ImageDraw.ImageDraw, cx: float, cy: float, w: float, h: float, angle: float, color: tuple[int, int, int, int]) -> None:
    points = []
    for i in range(61):
        t = i / 60
        x = (t - 0.5) * w
        y = -math.sin(t * math.pi) * h * 0.5
        xr = x * math.cos(angle) - y * math.sin(angle)
        yr = x * math.sin(angle) + y * math.cos(angle)
        points.append((cx + xr, cy + yr))
    for i in range(60, -1, -1):
        t = i / 60
        x = (t - 0.5) * w
        y = math.sin(t * math.pi) * h * 0.5
        xr = x * math.cos(angle) - y * math.sin(angle)
        yr = x * math.sin(angle) + y * math.cos(angle)
        points.append((cx + xr, cy + yr))
    draw.polygon(points, fill=color)


def dot_grid(draw: ImageDraw.ImageDraw, size: tuple[int, int], color: tuple[int, int, int, int]) -> None:
    width, height = size
    for x in range(40, width, 34):
        for y in range(40, height, 34):
            draw.ellipse((x, y, x + 2, y + 2), fill=color)


def label(draw: ImageDraw.ImageDraw, xy: tuple[int, int], text: str, f: ImageFont.FreeTypeFont, fill: tuple[int, int, int], spacing: int = 0) -> None:
    draw.text(xy, text, font=f, fill=fill, spacing=spacing)


PALETTES = {
    "agave": ((7, 44, 40), (20, 88, 72), (185, 214, 138), "AGAVE AZUL", "Agave tequilana", "Micropropagación · Jalisco"),
    "vanilla": ((46, 42, 24), (125, 107, 49), (244, 231, 194), "VAINILLA", "Vanilla planifolia", "Micropropagación · Veracruz"),
    "musa": ((14, 47, 29), (57, 112, 54), (214, 231, 151), "PLÁTANO", "Musa spp. AAA", "Saneamiento · Nuevo León"),
    "stevia": ((30, 53, 38), (94, 139, 83), (230, 241, 205), "STEVIA", "Stevia rebaudiana", "Aclimatación · Puebla"),
    "pitaya": ((51, 15, 42), (118, 38, 83), (244, 183, 201), "PITAYA", "Hylocereus undatus", "Organogénesis · Yucatán"),
    "cafe": ((43, 28, 18), (103, 69, 39), (232, 208, 170), "CAFÉ ARÁBIGO", "Coffea arabica", "Embriogénesis · Oaxaca"),
    "andropogon": ((49, 39, 16), (122, 98, 38), (232, 210, 145), "BIG BLUESTEM", "Andropogon gerardii", "Restauración · Chihuahua"),
    "lenteja": ((38, 44, 24), (92, 104, 49), (224, 225, 178), "LENTEJA", "Lens culinaris", "Callo · Estado de México"),
    "laelia": ((42, 20, 38), (105, 45, 82), (238, 193, 214), "ORQUÍDEA DE OCTUBRE", "Laelia anceps", "Conservación · Veracruz"),
    "populus": ((19, 33, 33), (57, 83, 76), (198, 214, 203), "ÁLAMO NEGRO", "Populus trichocarpa", "Transformación · Guanajuato"),
}


def case_image(key: str) -> None:
    top, bottom, accent, common, scientific, subtitle = PALETTES[key]
    width, height = 1600, 1000
    base = gradient((width, height), top, bottom)
    overlay = Image.new("RGBA", (width, height), (0, 0, 0, 0))
    draw = ImageDraw.Draw(overlay)
    dot_grid(draw, (width, height), (255, 255, 255, 18))

    # Halo de luz de cámara de cultivo
    for radius, alpha in ((520, 18), (420, 22), (320, 26)):
        draw.ellipse((width - 380 - radius, 120 - radius // 2, width - 380 + radius, 120 + radius + 240), fill=(255, 255, 255, alpha))

    # Hojas y brotes
    random.seed(key)
    for i in range(5):
        leaf(draw, 1180 + i * 38, 340 + i * 44, 360 - i * 26, 150 - i * 10, -0.55 + i * 0.12, (*accent, 46 - i * 5))
    for i in range(3):
        leaf(draw, 1240 + i * 22, 640 - i * 20, 260, 110, 0.6 + i * 0.18, (255, 255, 255, 26))

    # Círculo tipo caja Petri
    draw.ellipse((1030, 250, 1490, 710), outline=(255, 255, 255, 70), width=2)
    draw.ellipse((1064, 284, 1456, 676), outline=(255, 255, 255, 28), width=1)
    for i in range(9):
        x = 1110 + i * 36
        y = 440 + int(60 * math.sin(i * 0.7))
        draw.ellipse((x, y, x + 14, y + 14), fill=(*accent, 160))
        draw.line((x + 7, y - 22, x + 7, y), fill=(*accent, 120), width=2)

    base = Image.alpha_composite(base.convert("RGBA"), overlay)
    draw = ImageDraw.Draw(base)

    f_common = font(SANS_BOLD, 22)
    f_sci = font(SERIF_ITALIC, 64)
    f_sub = font(SANS, 22)
    f_mark = font(SANS_BOLD, 18)
    f_tag = font(SANS_BOLD, 15)

    draw.rectangle((80, 740, 900, 742), fill=(*accent, 200))
    label(draw, (80, 770), common, f_common, accent)
    draw.text((78, 800), scientific, font=f_sci, fill=(255, 255, 255))
    label(draw, (82, 884), subtitle, f_sub, (235, 238, 230))
    label(draw, (80, 60), "VITROLAB MX", f_mark, (255, 255, 255))
    draw.rectangle((80, 92, 330, 122), outline=(255, 255, 255, 80), width=1)
    label(draw, (94, 99), "CASO DEMOSTRATIVO", f_tag, (255, 255, 255))
    label(draw, (width - 250, height - 58), "IMAGEN GENERADA", f_tag, (255, 255, 255))

    base.convert("RGB").save(CASES / f"{key}.jpg", quality=90, optimize=True)


def hero() -> None:
    width, height = 1800, 1200
    base = gradient((width, height), (6, 30, 29), (10, 63, 55))
    overlay = Image.new("RGBA", (width, height), (0, 0, 0, 0))
    draw = ImageDraw.Draw(overlay)
    dot_grid(draw, (width, height), (255, 255, 255, 14))

    # Luz magenta de crecimiento
    glow = Image.new("RGBA", (width, height), (0, 0, 0, 0))
    gd = ImageDraw.Draw(glow)
    gd.ellipse((1040, -180, 1700, 480), fill=(224, 94, 150, 60))
    glow = glow.filter(ImageFilter.GaussianBlur(120))
    overlay = Image.alpha_composite(overlay, glow)

    # Estantería / mesa
    draw.rectangle((920, 690, 1700, 706), fill=(220, 230, 220, 40))
    draw.rectangle((920, 706, 1700, 1120), fill=(255, 255, 255, 6))

    # Frascos con vitroplantas
    for i in range(6):
        x = 980 + i * 112
        h = 130 + (i % 3) * 26
        draw.rounded_rectangle((x, 690 - h, x + 76, 690), radius=14, fill=(210, 232, 216, 32), outline=(220, 240, 225, 86), width=2)
        draw.rectangle((x + 10, 690 - h + 26, x + 66, 680), fill=(120, 168, 92, 70))
        for j in range(3):
            cx = x + 24 + j * 14
            draw.line((cx, 682, cx + 6, 620 + j * 8), fill=(180, 220, 150, 160), width=3)
            leaf(draw, cx + 8, 630 + j * 8, 34, 14, -0.5, (180, 220, 150, 120))

    # Caja Petri grande
    draw.ellipse((260, 520, 760, 1020), fill=(255, 255, 255, 14), outline=(230, 240, 232, 90), width=3)
    draw.ellipse((300, 560, 720, 980), outline=(230, 240, 232, 40), width=2)
    for i in range(16):
        angle = i * 0.42
        x = 510 + math.cos(angle) * (80 + i * 9)
        y = 770 + math.sin(angle) * (70 + i * 7)
        draw.ellipse((x, y, x + 8, y + 8), fill=(212, 232, 168, 130))

    # Gradilla y tubos
    for i in range(7):
        x = 820 + i * 26
        draw.rounded_rectangle((x, 760, x + 14, 980), radius=7, fill=(215, 235, 220, 26), outline=(220, 240, 225, 54), width=1)

    base = Image.alpha_composite(base.convert("RGBA"), overlay)
    base.convert("RGB").save(IMG / "hero-lab.jpg", quality=88, optimize=True)

    # OG cover
    og = base.resize((1200, 630), Image.LANCZOS).convert("RGB")
    od = ImageDraw.Draw(og)
    od.rectangle((0, 0, 1200, 630), fill=(6, 30, 29, 60))
    label(od, (70, 70), "VITROLAB MX", font(SANS_BOLD, 26), (255, 255, 255))
    label(od, (70, 300), "Cultivo in vitro", font(SERIF_BOLD, 68), (255, 255, 255))
    label(od, (70, 380), "de material vegetal", font(SERIF_BOLD, 68), (218, 236, 176))
    label(od, (70, 500), "Casos · protocolos · reportes de laboratorio", font(SANS, 24), (225, 233, 225))
    og.save(IMG / "og-cover.png", optimize=True)


def report_figure(report: dict) -> None:
    width, height = 1100, 680
    image = Image.new("RGB", (width, height), (250, 248, 242))
    draw = ImageDraw.Draw(image)
    draw.rectangle((0, 0, width, 92), fill=(11, 60, 58))
    label(draw, (44, 28), "VITROLAB MX · FIGURA DEMOSTRATIVA", font(SANS_BOLD, 18), (214, 232, 195))
    title = report["title"]
    # wrap title
    words = title.split()
    lines, current = [], ""
    f_title = font(SERIF_BOLD, 28)
    for word in words:
        trial = (current + " " + word).strip()
        if draw.textlength(trial, font=f_title) > width - 90:
            lines.append(current)
            current = word
        else:
            current = trial
    if current:
        lines.append(current)
    y = 120
    for line in lines[:3]:
        draw.text((44, y), line, font=f_title, fill=(20, 50, 44))
        y += 36

    values = [t["response"] for t in report["treatments"]]
    vmax = max(values) * 1.18 if max(values) > 0 else 1
    chart_top, chart_bottom = y + 32, 570
    left, right = 90, width - 70
    draw.line((left, chart_bottom, right, chart_bottom), fill=(120, 132, 124), width=2)
    n = len(values)
    slot = (right - left) / max(1, n)
    bar_w = min(150, slot * 0.54)
    colors = [(31, 111, 88), (78, 139, 92), (168, 188, 104), (199, 150, 57), (178, 92, 120), (92, 131, 146)]
    for i, treatment in enumerate(report["treatments"]):
        value = treatment["response"]
        cx = left + slot * (i + 0.5)
        bar_h = (value / vmax) * (chart_bottom - chart_top)
        x0, y0 = cx - bar_w / 2, chart_bottom - bar_h
        draw.rounded_rectangle((x0, y0, x0 + bar_w, chart_bottom), radius=6, fill=colors[i % len(colors)])
        draw.text((cx, y0 - 32), f"{value:g}", font=font(SANS_BOLD, 22), fill=(25, 55, 48), anchor="mm")
        caption = treatment["label"].split("·")[0].strip()
        draw.text((cx, chart_bottom + 18), caption, font=font(SANS_BOLD, 15), fill=(40, 60, 52), anchor="ma")
        unit = treatment.get("unit", "")
        draw.text((cx, chart_bottom + 40, ), unit, font=font(SANS, 13), fill=(100, 112, 104), anchor="ma")
    draw.text((44, height - 42), f"Unidad: {report['unit']}", font=font(SANS, 15), fill=(100, 112, 104))
    image.save(FILES / f"figura-{report['slug']}.png", optimize=True)

    with (FILES / f"datos-{report['slug']}.csv").open("w", newline="", encoding="utf-8") as fh:
        writer = csv.writer(fh)
        writer.writerow(["tratamiento", "n", "respuesta", "unidad", "notas"])
        for treatment in report["treatments"]:
            writer.writerow([treatment["label"], treatment["n"], treatment["response"], treatment["unit"], treatment["notes"]])


REPORTS = [
    {
        "slug": "bap-iba-proliferacion-agave",
        "title": "Efecto de BAP e IBA en la proliferación de brotes de Agave tequilana",
        "unit": "brotes por explante",
        "treatments": [
            {"label": "T0 · BAP 0", "response": 1.8, "n": 30, "unit": "brotes/explante", "notes": "Testigo"},
            {"label": "T1 · BAP 1.0", "response": 3.1, "n": 30, "unit": "brotes/explante", "notes": "Vitrificación 4%"},
            {"label": "T2 · BAP 2.0", "response": 4.2, "n": 30, "unit": "brotes/explante", "notes": "Tratamiento adoptado"},
            {"label": "T3 · BAP 4.0", "response": 4.0, "n": 30, "unit": "brotes/explante", "notes": "Vitrificación 22%"},
        ],
    },
    {
        "slug": "desinfeccion-vainilla-tres-protocolos",
        "title": "Comparación de tres protocolos de desinfección en segmentos nodales de vainilla",
        "unit": "% de contaminación",
        "treatments": [
            {"label": "D1 · NaOCl 1.0%", "response": 24.0, "n": 60, "unit": "% contaminación", "notes": "Supervivencia 76%"},
            {"label": "D2 · NaOCl 1.5%", "response": 12.0, "n": 60, "unit": "% contaminación", "notes": "Recomendado"},
            {"label": "D3 · NaOCl 2.0%", "response": 9.0, "n": 60, "unit": "% contaminación", "notes": "Menor brotación"},
        ],
    },
    {
        "slug": "ms-vs-wpm-musa",
        "title": "Respuesta de Musa AAA a medios MS y WPM suplementados con BAP",
        "unit": "brotes por explante",
        "treatments": [
            {"label": "M1 · MS", "response": 5.4, "n": 25, "unit": "brotes/explante", "notes": "Vitrificación 8%"},
            {"label": "M2 · WPM", "response": 4.6, "n": 25, "unit": "brotes/explante", "notes": "Vitrificación 18%"},
            {"label": "M3 · MS + Ca", "response": 5.1, "n": 25, "unit": "brotes/explante", "notes": "Vitrificación 6%"},
        ],
    },
    {
        "slug": "aclimatacion-stevia-sustratos",
        "title": "Aclimatación de Stevia rebaudiana: efecto de tres sustratos y humedad relativa",
        "unit": "% de supervivencia",
        "treatments": [
            {"label": "S1H1 · turba", "response": 96.0, "n": 24, "unit": "% supervivencia", "notes": "Mejor tratamiento"},
            {"label": "S2H1 · coco", "response": 89.0, "n": 24, "unit": "% supervivencia", "notes": "Alternativa local"},
            {"label": "S3H1 · suelo", "response": 78.0, "n": 24, "unit": "% supervivencia", "notes": "Mayor compactación"},
            {"label": "S1H2 · constante", "response": 82.0, "n": 24, "unit": "% supervivencia", "notes": "Testigo de humedad"},
        ],
    },
    {
        "slug": "callo-lenteja-24d-picloram",
        "title": "Inducción de callo en Lens culinaris con 2,4-D y picloram",
        "unit": "% de explantes con callo",
        "treatments": [
            {"label": "A1 · 2,4-D 1.0", "response": 62.0, "n": 20, "unit": "% callo", "notes": "Callo escaso"},
            {"label": "A2 · 2,4-D 2.0", "response": 78.0, "n": 20, "unit": "% callo", "notes": "Mejor friabilidad"},
            {"label": "A3 · 2,4-D 4.0", "response": 85.0, "n": 20, "unit": "% callo", "notes": "Mayor oxidación"},
            {"label": "A4 · picloram 2.0", "response": 70.0, "n": 20, "unit": "% callo", "notes": "Callo compacto"},
            {"label": "A5 · testigo", "response": 0.0, "n": 20, "unit": "% callo", "notes": "Sin inducción"},
        ],
    },
    {
        "slug": "siembra-axenica-sporobolus",
        "title": "Validación de un protocolo de siembra axénica para Sporobolus alterniflorus",
        "unit": "% de germinación",
        "treatments": [
            {"label": "V1 · NaOCl 2.0%", "response": 82.0, "n": 50, "unit": "% germinación", "notes": "Contaminación 5%"},
            {"label": "V2 · NaOCl 3.0%", "response": 76.0, "n": 50, "unit": "% germinación", "notes": "Protocolo validado"},
            {"label": "V3 · testigo", "response": 60.0, "n": 50, "unit": "% germinación", "notes": "Contaminación 45%"},
        ],
    },
    {
        "slug": "conservacion-laelia-crecimiento-minimo",
        "title": "Seguimiento a 12 meses de Laelia anceps en conservación de crecimiento mínimo",
        "unit": "% de supervivencia",
        "treatments": [
            {"label": "C1 · manitol 1%", "response": 92.0, "n": 40, "unit": "% supervivencia", "notes": "Rebrote 88%"},
            {"label": "C2 · manitol 2%", "response": 88.0, "n": 40, "unit": "% supervivencia", "notes": "Rebrote 68%"},
            {"label": "C3 · sacarosa 10", "response": 80.0, "n": 40, "unit": "% supervivencia", "notes": "Amarillamiento"},
        ],
    },
]

LOGO = """<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" fill="none">
<rect width="64" height="64" rx="16" fill="#0B3C3A"/>
<path d="M32 49c0-12 6-20 17-24-1 13-7 21-17 24Z" fill="#BFD98C"/>
<path d="M32 49C32 37 26 29 15 25c1 13 7 21 17 24Z" fill="#E9B44C"/>
<path d="M32 51V29" stroke="#F6F2E7" stroke-width="3" stroke-linecap="round"/>
<circle cx="32" cy="20" r="5" fill="#D96C8A"/>
</svg>
"""
FAVICON = """<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" fill="none">
<rect width="64" height="64" rx="16" fill="#0B3C3A"/>
<path d="M32 49c0-12 6-20 17-24-1 13-7 21-17 24Z" fill="#BFD98C"/>
<path d="M32 49C32 37 26 29 15 25c1 13 7 21 17 24Z" fill="#E9B44C"/>
<path d="M32 51V29" stroke="#F6F2E7" stroke-width="3" stroke-linecap="round"/>
<circle cx="32" cy="20" r="5" fill="#D96C8A"/>
</svg>
"""

if __name__ == "__main__":
    for name in PALETTES:
        case_image(name)
    hero()
    for report in REPORTS:
        report_figure(report)
    (IMG / "logo.svg").write_text(LOGO, encoding="utf-8")
    (IMG / "favicon.svg").write_text(FAVICON, encoding="utf-8")
    print("Imágenes y figuras generadas.")
