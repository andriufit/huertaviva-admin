# Huerta Viva · Admin

Panel de administración interno de **Huerta Viva**, una tienda online de productos de la huerta (empresa ficticia). Lo usa **solo el personal** para gestionar pedidos y productos.

Hecho en **PHP 8** sin framework. Se conecta directamente a la base de datos.

## Cómo se comunica con el resto

```
Navegador del personal ──HTTP/HTTPS──► Apache + PHP (admin) ──MySQL :3306──► Base de datos
```

| Dirección | Con quién | Protocolo / puerto | Para qué |
|---|---|---|---|
| Entrada | Navegador del **personal** de la tienda | HTTP/HTTPS (80/443) | Usar el panel |
| Salida | **Base de datos** MySQL/MariaDB | TCP 3306 | Leer y modificar pedidos y productos, comprobar el login |

- **No usa la API** (`huertaviva-api`) ni se comunica con la web (`huertaviva-web`).
- Comparte la base de datos con la API, pero con **su propio usuario** (`huerta_admin`) y sus propios permisos.
- La base de datos y el usuario `huerta_admin` se crean con los scripts de la carpeta `database/` del repositorio de la API.

### Qué hace

- **Login** con usuario y contraseña. Las contraseñas se guardan con hash (`password_hash`).
- **Pedidos:** ver todos los pedidos y cambiar su estado (pendiente, preparado, entregado, cancelado).
- **Productos:** crear productos, cambiar precio y stock, y ocultarlos o activarlos en la web.

## Requisitos

- Ubuntu Server 22.04/24.04 o Debian 12.
- **Apache 2.4** con **PHP 8.1 o superior** y las extensiones `pdo_mysql` y `mbstring`:

  ```bash
  sudo apt install apache2 php libapache2-mod-php php-mysql php-mbstring
  ```

- La base de datos de Huerta Viva creada, con el usuario `huerta_admin` (ver el README de la API).

## Configuración

Copia la plantilla y edítala:

```bash
cp config.example.php config.local.php
nano config.local.php
```

| Clave | Descripción |
|---|---|
| `db.host`, `db.port` | Dirección de la base de datos |
| `db.name` | Nombre de la base de datos (`huertaviva`) |
| `db.user`, `db.pass` | Usuario `huerta_admin` y su contraseña |
| `cookie_secure` | `true` cuando el panel se sirva por HTTPS. Con `true` y sin HTTPS, el login no funcionará, porque el navegador no envía la cookie |

`config.local.php` contiene la contraseña de la base de datos. No se sube al repositorio y **no debe estar dentro de la carpeta pública**.

Los usuarios del panel se crean por consola. La contraseña se pide por teclado y debe tener 12 caracteres como mínimo:

```bash
php bin/crear_usuario.php nombre_usuario
```

## Comprobaciones

- Entra en `http://admin.huertaviva.local/` (añade el nombre al fichero `hosts` de la máquina desde la que pruebes). Te redirige a `login.php`.
- Inicia sesión con el usuario que has creado. Deberías ver la lista de pedidos.
- Si sale *"Falta config.local.php"*, el fichero no está en la raíz del proyecto (al lado de `public/`, no dentro).
- Si sale un error de conexión con la base de datos, revisa `config.local.php`, que el usuario `huerta_admin` exista para la IP de esta máquina y `bind-address` en MySQL/MariaDB.
- Comprueba que `http://admin.huertaviva.local/config.local.php` y `http://admin.huertaviva.local/src/bootstrap.php` devuelven **404**: solo `public/` debe ser accesible.

## Estructura

```
huertaviva-admin/
├── public/                 # Única carpeta accesible desde el navegador (DocumentRoot)
│   ├── index.php           # Pedidos
│   ├── productos.php       # Productos
│   ├── login.php
│   ├── logout.php
│   └── css/admin.css
├── src/
│   ├── bootstrap.php       # Configuración y conexión a la base de datos
│   └── web.php             # Sesión, CSRF, plantillas y utilidades
├── bin/
│   └── crear_usuario.php   # Alta de usuarios del panel (solo por consola)
└── config.example.php      # Plantilla de configuración
```
