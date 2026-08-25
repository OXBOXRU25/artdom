#!/usr/bin/env python3
"""Точечная (halftone) карта России с радиальным «радарным» узором из Москвы.

Узор и сетка — одно полотно: за краем колец точки сетки плавно доворачиваются
на продолжение тех же дуг и лишь постепенно распрямляются в прямые ряды.
"""

import json
import numpy as np
import shapely
from shapely.geometry import shape
from shapely.ops import transform as shp_transform
from pyproj import CRS, Transformer

# ----------------------------- НАСТРОЙКИ -----------------------------------
GEOJSON      = "ne50.geojson"        # исходник Natural Earth 1:50m
COUNTRY      = "Russia"

GRID_STEP_KM = 25.0        # шаг регулярной сетки точек
RING_STEP_KM = 25.0        # расстояние между концентрическими окружностями
ARC_STEP_KM  = 25.0        # шаг точек вдоль окружности (у края узора)
CENTER_LONLAT = (37.62, 55.75)   # центр радиального узора — Москва

DENSE_KM     = 800.0       # ближе к центру точки на дугах сгущаются
DENSE_MIN    = 0.45        # предел сгущения (доля от ARC_STEP_KM)
STAGGER      = 0.5         # сдвиг соседних колец, 0.5 = шахматная укладка

DOT_R        = 2.2         # радиус точки, px
DOT_COLOR    = "#111111"
PNG_WIDTH    = 4096
MARGIN_PX    = 40
BACKGROUND   = None        # None = прозрачный, либо "#ffffff"

OUT_SVG      = "russia_dots.svg"
OUT_PNG      = "russia_dots.png"
# ---------------------------------------------------------------------------

KM = 1000.0

crs_ru = CRS.from_proj4(
    "+proj=aea +lat_1=52 +lat_2=64 +lat_0=30 +lon_0=100 "
    "+x_0=0 +y_0=0 +ellps=WGS84 +units=m +no_defs"
)
fwd = Transformer.from_crs("EPSG:4326", crs_ru, always_xy=True).transform

feats = json.load(open(GEOJSON))["features"]
geom_ll = shape(next(f["geometry"] for f in feats
                     if f["properties"].get("ADMIN") == COUNTRY))
geom = shp_transform(fwd, geom_ll).buffer(0)

minx, miny, maxx, maxy = geom.bounds
cx, cy = fwd(*CENTER_LONLAT)

RING = RING_STEP_KM * KM
ARC  = ARC_STEP_KM * KM


def n_dots(r_s):
    """сколько точек на кольце радиуса r_s: к центру шаг по дуге сокращается"""
    arc = ARC * min(max(r_s / (DENSE_KM * KM), DENSE_MIN), 1.0)
    return max(6, int(round(2 * np.pi * r_s / arc)))


# --- одна полярная решётка на всю страну: колец хватает до дальнего угла -----
corners = np.array([[minx, miny], [minx, maxy], [maxx, miny], [maxx, maxy]])
r_max = np.hypot(corners[:, 0] - cx, corners[:, 1] - cy).max()

rings = []
for k in range(1, int(r_max / RING) + 2):
    r_s = k * RING
    n = n_dots(r_s)
    a = np.arange(n) * (2 * np.pi / n) + (k % 2) * STAGGER * (2 * np.pi / n)
    rings.append(np.column_stack([cx + r_s * np.cos(a), cy + r_s * np.sin(a)]))
pts = np.vstack(rings)

# отсекаем всё за пределами рамки страны, потом — по её контуру
box = ((pts[:, 0] > minx - RING) & (pts[:, 0] < maxx + RING) &
       (pts[:, 1] > miny - RING) & (pts[:, 1] < maxy + RING))
pts = pts[box]
pts = pts[shapely.contains_xy(geom, pts[:, 0], pts[:, 1])]
print(f"точек: {len(pts)}")

# --- 5. SVG / PNG ----------------------------------------------------------
scale = (PNG_WIDTH - 2 * MARGIN_PX) / (maxx - minx)
W = PNG_WIDTH
H = int(round((maxy - miny) * scale + 2 * MARGIN_PX))
sx = (pts[:, 0] - minx) * scale + MARGIN_PX
sy = (maxy - pts[:, 1]) * scale + MARGIN_PX

body = [f'<svg xmlns="http://www.w3.org/2000/svg" width="{W}" height="{H}" '
        f'viewBox="0 0 {W} {H}">']
if BACKGROUND:
    body.append(f'<rect width="{W}" height="{H}" fill="{BACKGROUND}"/>')
body.append(f'<g fill="{DOT_COLOR}">')
for x, y in zip(sx, sy):
    body.append(f'<circle cx="{x:.1f}" cy="{y:.1f}" r="{DOT_R}"/>')
body.append("</g></svg>")

svg = "\n".join(body)
open(OUT_SVG, "w").write(svg)

import cairosvg
cairosvg.svg2png(bytestring=svg.encode(), write_to=OUT_PNG, output_width=PNG_WIDTH)
print(f"готово: {OUT_SVG}, {OUT_PNG} ({W}x{H})")
