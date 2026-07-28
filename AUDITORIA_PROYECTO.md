# Auditoría técnica del proyecto de farmacia

Fecha de auditoría: 2026-07-26

Alcance: revisión estática completa del repositorio y comprobaciones locales no destructivas. No se modificaron código, migraciones ni datos. La base PostgreSQL de pruebas no estaba disponible, por lo que los resultados dinámicos de integración quedan limitados.

## Resumen ejecutivo

El proyecto es un monolito Laravel 12 con vistas Blade, Eloquent y PostgreSQL. La autenticación, los roles en las rutas, los catálogos de categorías/medicamentos/proveedores, compras y ventas tienen una implementación sustancial. Las compras y ventas se encapsulan en transacciones; las ventas bloquean las filas de medicamentos con `lockForUpdate`, validan stock y rechazan medicamentos vencidos.

El requisito contractual no está completo. El incumplimiento más importante es que no existe administración de usuarios (CRUD, endpoints ni interfaz). También hay un incumplimiento técnico de dinero: los servicios convierten `decimal` a `float` para calcular totales. El esquema usa `decimal`, pero la capa de aplicación puede introducir errores de precisión. No se encontraron índices explícitos en las columnas hijas de las claves foráneas. No existe prueba de concurrencia de ventas.

Severidad principal: crítica, administración de usuarios ausente; alta, aritmética monetaria con `float`, cobertura de pruebas no ejecutable por PostgreSQL no disponible y configuración local con debug habilitado si se reutiliza en despliegue.

## Stack detectado

- Laravel Framework 12.64.0, PHP requerido `^8.2`; runtime auditado PHP 8.4.23.
- Composer 2.10.2, Eloquent ORM, PHPUnit 11.5, Laravel Pint.
- PostgreSQL como conexión documentada y configurada; migraciones usan SQL específico de PostgreSQL (`ilike`, `unaccent`, índices por expresión y `timestampTz`).
- Blade + Bootstrap estático en `public/vendor`; no existe `package.json`, TypeScript, Vite ni pipeline frontend.
- Sesiones: `file` en `.env.example`, `array` en PHPUnit; autenticación por guardia de sesión.

## Matriz de trazabilidad

| ID | Requisito | Estado | Evidencia exacta | Riesgo | Acción recomendada |
|---|---|---|---|---|---|
| AUT-01 | Iniciar sesión | CUMPLE | `routes/web.php:15-18`; `LoginController.php:18-37`; `AuthenticationTest.php:18-42` | Medio | Mantener y probar contra PostgreSQL disponible. |
| AUT-02 | Cierre de sesión, sesión y expiración | PARCIAL | `LoginController.php:40-46` invalida sesión y regenera token; `config/session.php:21,35-37,172-202` deja seguridad cookie dependiente del entorno | Alto en despliegue HTTP | Fijar `secure`, `http_only`, `same_site`, lifetime y driver seguro por entorno; probar expiración real. |
| AUT-03 | Crear/editar/eliminar/listar usuarios | NO CUMPLE | `routes/web.php` solo contiene login/logout; no existe `UserController`, `UserRequest` ni vista de usuarios; `rg` no halló CRUD | Crítico | Implementar CRUD administrativo, validación, desactivación segura y pruebas de autorización. |
| AUT-04 | Roles Administrador y Vendedor | CUMPLE | `DatabaseSeeder.php:19-36`; `RoleMiddleware.php:11-17`; `migration:12-27` | Medio | Añadir restricciones/enum o política para impedir roles arbitrarios. |
| AUT-05 | Administrador total / Vendedor solo consulta medicamentos y registra ventas | PARCIAL | `routes/web.php:24-49` separa `rol:Administrador` y `rol:Administrador,Vendedor`; `CatalogoTest.php:47-67`, `CompraTest.php:133-139` | Alto | Añadir CRUD de usuarios y pruebas directas para cada endpoint administrativo faltante. |
| CAT-01 | CRUD de categorías | PARCIAL | `routes/web.php:36`; `CategoriaController.php:14-55`; vistas y `CatalogoTest.php:26-55,141-155` | Medio | Documentar que “eliminar” es desactivación lógica y añadir reactivación o borrado explícito según contrato. |
| MED-01 | Campos mínimos y CRUD | CUMPLE | `migration:37-46`; `MedicamentoController.php:15-68`; `MedicamentoRequest.php:16-25`; `routes/web.php:37` | Medio | Añadir pruebas de actualización y consulta detallada si se requiere “show” separado. |
| MED-02 | Alertas de próximos vencimientos | PARCIAL | `Medicamento.php:38-45`; `DashboardController.php:15-24`; `dashboard.blade.php:32-41` muestra conteos, no listado accionable | Medio | Mostrar listado/filtro de próximos vencimientos y definir formalmente ventana y zona horaria. |
| PRO-01 | Registrar y consultar proveedores | CUMPLE | `ProveedorController.php:14-56`; `routes/web.php:38-40`; vistas con paginación/filtros | Bajo | Añadir pruebas de consulta, edición y estados vacíos. |
| COM-01 | Compra solo administrador, proveedor, fecha y varios medicamentos | PARCIAL | `routes/web.php:34-45`; `CompraRequest.php:15-23`; `CompraService.php:43-48`; fecha se genera con `now()` y no se captura como campo de formulario | Medio | Confirmar si la fecha debe ser introducida por el usuario; si sí, validarla y persistirla. |
| COM-02 | Cabecera/detalle, total y aumento de stock | CUMPLE | `CompraService.php:43-81`; `migration:59-79`; `CompraTest.php:28-50` | Medio | Reemplazar cálculo monetario con decimal exacto y agregar constraint/consulta de total de cabecera. |
| COM-03 | Compra atómica | CUMPLE | `CompraService.php:19-84`; rollback cubierto por `CompraTest.php:52-113` (no ejecutado por DB ausente) | Alto si se cambia el servicio sin pruebas | Mantener transacción y activar pruebas en CI. |
| VEN-01 | Venta por Administrador/Vendedor, cabecera/detalle y precio histórico | CUMPLE | `routes/web.php:27-32`; `VentaRequest.php:9-20`; `VentaService.php:33-74`; `VentaTest.php:22-39` | Medio | Agregar prueba de varios productos y total con valores decimales difíciles. |
| VEN-02 | Stock, vencimiento, total y stock cero | CUMPLE | `VentaService.php:45-72`; `Medicamento.php:53-55`; `VentaTest.php:42-71` | Alto | Añadir explícitamente prueba cantidad igual al stock y verificarla en CI. |
| VEN-03 | Atomicidad y concurrencia sin stock negativo | PARCIAL | `DB::transaction` y `lockForUpdate` en `VentaService.php:18,45`; constraint `stock >= 0` en migración:115; no hay prueba concurrente | Alto | Crear prueba de dos transacciones/requests concurrentes y tratar deadlocks/reintentos si aplica. |
| DB-01 | Nueve entidades mínimas | CUMPLE | `migration:12-100`; `EsquemaFarmaciaTest.php:19-27` | Medio | Ejecutar la prueba en una base autorizada. |
| DB-02 | PK, FK, tipos, restricciones, integridad referencial | PARCIAL | FKs con `restrictOnDelete` en `migration:23,44,61-62,71-72,83,92-93`; checks:115-127; no hay `onUpdate`; no hay índices hijos explícitos | Alto para consultas y borrados | Crear índices en todas las FKs de tablas grandes; definir política `ON UPDATE`; validar índices reales con `pg_indexes`. |
| DB-03 | Totales consistentes en DB | PARCIAL | Checks de subtotal en `migration:122,127`; totales se calculan en servicios `CompraService.php:49,78-81` y `VentaService.php:38,60,69,72`; no existe check de suma de detalles | Medio | No confiar solo en aplicación; usar procedimiento/trigger o auditoría de consistencia, según diseño. |
| SEC-01 | Validación servidor, SQL injection y XSS | PARCIAL | FormRequests en `app/Http/Requests`; consultas parametrizadas en requests; Blade usa `{{ }}`; no hay pruebas de payloads maliciosos | Alto | Añadir pruebas de autorización, entradas largas, HTML, búsqueda con comodines y errores. |
| SEC-02 | CSRF y rate limiting de login | CUMPLE | `@csrf` en vistas; middleware web de Laravel; `routes/web.php:17`; `AppServiceProvider.php:28-31`; `AuthenticationTest.php:44-59` | Medio | Verificar configuración de proxy/HTTPS en producción. |
| SEC-03 | Hash y secretos | PARCIAL | `Usuario.php:23-27` usa cast `hashed`; `DatabaseSeeder.php:22-36` usa passwords de demo que se hashean; README y seeder contienen credenciales demo reutilizables | Alto si se despliega con seed demo | Separar datos demo del despliegue, rotar/eliminar credenciales conocidas y usar secretos de gestor. |
| SEC-04 | Errores, debug, archivos sensibles y despliegue | PARCIAL | `php artisan about` reportó entorno local y Debug ENABLED; no hay Nginx/Dockerfile/configuración de despliegue; `.env` está ignorado y docroot es `public` | Crítico si se publica local tal cual | `APP_DEBUG=false`, servidor apuntando a `public`, negar archivos ocultos y no publicar sourcemaps/secretos; revisar headers. |
| QLT-01 | Paginación, filtros y estados vacíos | PARCIAL | Catálogos y operaciones usan `paginate`; catálogos filtran estado/búsqueda; vistas `@forelse`; ventas/compras no tienen filtros | Bajo/Medio | Añadir filtros por fecha/usuario/proveedor y pruebas de estados vacíos. |
| QLT-02 | TypeScript, strict, any y build | NO VERIFICABLE | No existe `package.json`, `tsconfig`, Vite ni código TS; no aplica verificación TS | Bajo | Si se incorpora frontend, definir strict, lint, typecheck y build en CI. |
| QLT-03 | Accesibilidad/responsive | PARCIAL | Bootstrap responsive, `viewport`, labels y `aria-label` en `layouts/app.blade.php:1-14,31-34`; no hay pruebas automatizadas ni auditoría de contraste/teclado | Medio | Ejecutar axe/Lighthouse y revisar foco, mensajes y formularios dinámicos. |

## Hallazgos por severidad

### Crítica

1. Administración de usuarios ausente. El modelo y la tabla existen, pero no hay capacidad operativa para crear, editar, eliminar o listar usuarios. Esto impide cumplir AUT-03 y deja el control de altas/cambios de roles fuera del sistema.
2. No debe desplegarse con la configuración local observada: `php artisan about` reportó Debug ENABLED. La exposición concreta depende del servidor, que no está incluido.

### Alta

1. Dinero calculado con `float`. `CompraService.php:32,49,67,78,81` y `VentaService.php:59-60,69,72` convierten valores monetarios a flotante. La base y casts son `decimal`, pero la exactitud contractual no queda garantizada en aplicación.
2. Integración no verificable: 36 pruebas fallaron por conexión a `farmacia_test`; ninguna prueba de compra/venta, autorización o esquema llegó a ejecutarse.
3. No hay prueba de dos ventas concurrentes. Aunque el bloqueo de fila es correcto como mecanismo visible, el requisito de concurrencia no puede declararse probado.
4. No se observan índices explícitos en FKs hijas. `foreignId()->constrained()` crea la FK, pero no se documenta un índice separado para `usuarios.id_rol`, `medicamentos.id_categoria`, relaciones de cabecera/detalle y operaciones.
5. Credenciales de demostración conocidas en README y seeder. No son secretos del `.env`, pero son credenciales incorporadas y deben quedar limitadas a desarrollo.

### Media

1. “Eliminar” categorías, medicamentos y proveedores se implementa como desactivación lógica (`destroy` actualiza `activo=false`), sin opción de reactivar en las rutas revisadas.
2. Las alertas muestran conteos en dashboard, pero no un listado de medicamentos próximos a vencer.
3. No existe restricción de base que garantice que el total de cabecera sea la suma de detalles; solo se valida el subtotal de cada detalle.
4. Compras y ventas no ofrecen filtros de consulta por fecha/usuario/proveedor.
5. No hay headers de seguridad, política CSP ni configuración de despliegue para confirmar protección de archivos ocultos y errores.

### Baja

1. README sigue siendo el README genérico de Laravel con una sección local mínima; no documenta arquitectura, permisos, despliegue seguro, rollback ni diagnóstico PostgreSQL.
2. No hay herramientas TypeScript ni análisis estático PHP adicional; Pint sí está presente.
3. No hay pruebas automatizadas de accesibilidad, concurrencia, XSS/CSRF de extremo a extremo ni errores de estados vacíos.

## Resultados reales de comandos

- `git status --short --branch`: repositorio sin commits en `main`, todos los archivos del proyecto sin seguimiento al inicio de la auditoría.
- `composer validate --no-check-publish`: correcto.
- `php -l` sobre PHP de `app`, `database`, `routes`, `config`, `bootstrap` y `tests`: todos sin errores de sintaxis.
- `vendor/bin/pint --test`: correcto.
- `php artisan route:list --except-vendor`: correcto, 34 rutas; no aparecen rutas de usuarios.
- `php artisan about --only=environment --no-ansi`: Laravel 12.64.0, PHP 8.4.23, entorno local, debug habilitado, zona `America/La_Paz`.
- `php artisan test --without-tty`: 2 pruebas pasaron y 36 fallaron al intentar consultar `farmacia_test` en `127.0.0.1:5432`; causa: conexión PostgreSQL rechazada/no disponible. No se interpreta como 36 defectos funcionales.
- `composer audit --format=plain`: no verificable; Composer no pudo resolver `repo.packagist.org` por error DNS y además no pudo crear su caché en el directorio del usuario.
- `vendor/bin/phpstan analyse`: no ejecutado; el binario no existe.
- `npm run build`: no ejecutado; no existe `package.json`.
- No se ejecutaron migraciones ni comandos destructivos. No se inspeccionaron valores secretos del `.env`.

## Riesgos de base de datos

- PostgreSQL es obligatorio por SQL y funciones específicas; SQLite no es un sustituto válido para estas pruebas.
- Las FKs son restrictivas, lo que preserva históricos, pero no hay política explícita `ON UPDATE`.
- Faltan índices hijos explícitos de FKs; verificar con `pg_indexes` y `pg_constraint` en la base autorizada.
- Checks de cantidades, precios, stock y subtotales son positivos/no negativos, pero el total de cabecera no se fuerza contra la suma de detalles.
- La extensión `unaccent` y función inmutable requieren permisos y deben incluirse en el procedimiento de despliegue.
- El stock está protegido por lock de fila y check no negativo, pero se debe probar concurrencia real y considerar reintentos ante deadlocks.

## Riesgos de seguridad y despliegue

- Debug activo en la configuración local observada puede revelar stack traces, consultas y contexto.
- No existe configuración Nginx/Apache/container para demostrar docroot, denegación de `.env`, logs, HTTPS, headers o permisos.
- La cookie tiene `http_only=true` y `same_site=lax` por configuración base, pero `secure` queda sin valor explícito.
- El login sí tiene límite 5/minuto por email/IP.
- Blade escapa interpolaciones y las búsquedas usan bindings; no se observó SQL concatenado ejecutable ni almacenamiento de HTML confiable.
- Las credenciales demo están en archivos versionables; eliminarlas o rotarlas antes de cualquier entorno accesible.

## Plan de remediación por fases

1. **Bloqueantes:** añadir CRUD de usuarios exclusivo del Administrador; retirar/rotar credenciales demo; fijar debug false y cookie/HTTPS de producción; habilitar PostgreSQL de pruebas en CI.
2. **Integridad financiera:** eliminar `float` de servicios, normalizar aritmética decimal, añadir pruebas de redondeo y consistencia de totales.
3. **Concurrencia/DB:** añadir prueba concurrente de ventas, índices de FKs, verificación de constraints e instrucciones para `unaccent`.
4. **Cobertura funcional:** pruebas para borrar/desactivar, alertas detalladas, filtros de operaciones, fechas introducidas por usuario si son necesarias y estados vacíos.
5. **Hardening y UX:** headers/CSP, configuración de despliegue, auditoría de accesibilidad, logging seguro y análisis estático/lint en CI.

## Correcciones pequeñas y reversibles priorizadas

1. Configurar `APP_DEBUG=false` y `SESSION_SECURE_COOKIE=true` únicamente en el archivo de entorno del despliegue.
2. Añadir en CI un servicio PostgreSQL y ejecutar `php artisan test` contra una base efímera.
3. Incorporar una prueba de dos ventas concurrentes sobre el mismo medicamento.
4. Añadir índices no destructivos para las columnas FK, previa revisión de tamaño/plan.
5. Reemplazar conversiones `float` en servicios por operaciones decimales exactas y agregar casos de redondeo.
6. Crear el módulo de usuarios con permisos de Administrador y pruebas de escalada vertical/horizontal.
7. Separar datos demo/credenciales de documentación de despliegue.

Estas son recomendaciones; no se aplicó ninguna corrección en esta primera pasada.
