# ADR-004 - project-context dentro del repositorio

Status: Aceptada
Date: 2026-09-27

## Context
La plantilla de base de conocimiento que usa el dueño (también en AdminATF) pide una carpeta de contexto **fuera** de los repositorios. Para este proyecto el dueño eligió explícitamente tenerla **dentro** de `fincas/`.

## Decision
La memoria vive en `fincas/project-context/`. La regla de la plantilla se adapta: ningún archivo de documentación para agentes fuera de esa carpeta.

## Reasons
Es un solo repositorio y el dueño prefiere tenerlo todo junto.

## Consequences
- La documentación se versiona con el código.
- `project-context/` no debe desplegarse al hosting (no hace daño, pero no se necesita). Pendiente de verificar cómo se excluirá al subir.

## Affected repositories
fincas
