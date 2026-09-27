# AGENTS — Proyecto Fincas

Este repositorio (`fincas`) guarda su memoria técnica en **`project-context/`** (esta carpeta).
El código es la verdad de la implementación; `project-context/` es la memoria de alto nivel.

## Reglas

1. Lee este archivo y luego `INDEX.md`. **No leas toda la carpeta**: carga solo los documentos que el INDEX indique para la tarea.
2. Antes de asumir un comportamiento descrito aquí, **verifícalo en el código** (rutas de archivo en cada documento).
3. **Never create or update project documentation outside `project-context/`.** Nada de `AGENTS.md`, `README-AI.md`, `docs/` ni `.md` nuevos en el resto del repositorio. (El `README.md` de la raíz es el README de instalación para humanos: se puede mantener, pero no es memoria para agentes.)
4. Si descubres algo importante (regla de negocio, trampa, decisión), **actualiza o crea el documento correspondiente** aquí antes de terminar. No lo dejes solo en el chat.
5. Lo temporal va en `current/`; lo permanente en el resto de carpetas.
6. Lo que no esté verificado se marca `Pendiente de verificar`. No inventes razones históricas.
7. Nunca documentes credenciales reales (valores de `.env`, claves, contraseñas).

## Flujo

`AGENTS.md` → `INDEX.md` → documento(s) relevante(s) → código afectado → archivos específicos.
