# ADR-001 - Sin Node, npm ni Vite

Status: Aceptada
Date: 2026-09-24

## Context
El Mac del dueño tiene un Node para procesadores Intel ("Bad CPU type") que usa en otro proyecto y no quiere cambiarlo. El hosting destino es cPanel compartido, donde tampoco hay Node.

## Decision
El proyecto no usa Node. Leaflet y Geoman se cargan por CDN; JS/CSS propios viven en `public/` y se editan sin compilar. Filament funciona con sus assets precompilados (`filament:upgrade` los publica en `public/`).

## Reasons
Evitar tocar el entorno del dueño y simplificar el despliegue en cPanel.

## Consequences
- No hay Tailwind propio: los estilos del sitio y del campo de mapa son CSS plano. No se pueden usar clases de Tailwind que Filament no haya compilado.
- No hay tema personalizado de Filament (requeriría compilar).
- Se eliminaron `package.json`, `vite.config.js`, `resources/js`, `resources/css` del esqueleto.
- Dependencia de unpkg en tiempo de ejecución.

## Affected repositories
fincas
