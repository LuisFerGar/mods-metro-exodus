# Tienda de Mods de Metro Exodus

## Plan de pruebas y Estado del Sistema:
- 1.- Login / Registro de usuarios.
- 2.- CRUD de Mods (Subir, editar y borrado seguro de un mod de la base de datos, previniendo errores de integridad).
- 3.- Buscador, filtros y carga de categorias dinamicas directas de la base de datos.
- 4.- Carrito de compras / Proceso de Checkout y Lista de Deseos.
- 5.- Sistema de comentarios/bitacora fusionado con el sistema de valoracion por estrellas.
- 6.- Verificar que funcione el tema del envio de mensaje de contacto.

## Registro de ultimos cambios:
- Se integro la logica de valoracion (estrellas) con la bitacora de comentarios usando la conexion local a la base de datos.
- Se implemento la creacion y lectura dinamica de categorias en el panel de administrador y el catalogo.
- Se configuro el manejo de excepciones (Try/Catch) en el borrado de suministros para proteger las llaves foraneas.
- Se restauro y mejoro la interfaz de usuario en la vista del catalogo (tamaño de tipografias y diseño de tarjetas).