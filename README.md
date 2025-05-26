# Proyecto Final: MyDrugs

# Objetivos del Proyecto de Gestión de Base de Datos

Este proyecto tiene como objetivo principal el diseño e implementación de una base de datos y un sistema de información asociado para resolver una problemática específica. A continuación, se detallan los objetivos clave:

## Objetivos Generales:

* Resolver una problemática específica mediante el diseño y la implementación de una base de datos. [cite: 1]
* Desarrollar un sistema de información en el lenguaje de programación de preferencia para interactuar y alimentar la base de datos. [cite: 2]

## Requisitos Mínimos y Específicos:

El proyecto deberá cumplir con los siguientes requisitos técnicos y funcionales:

* **Estructura de la Base de Datos:**
    * Contar con un mínimo de 10 tablas. [cite: 4]
    * Implementar 5 vistas, donde al menos una debe involucrar la consulta de más de 4 tablas y otra debe mostrar datos estadísticos. [cite: 4]

* **Procedimientos Almacenados (SPs):**
    * Crear un SP por tabla para el registro de información, incluyendo validación para evitar registros duplicados. [cite: 4]
    * Desarrollar un SP por tabla para la eliminación de información, asegurando la validación de relaciones activas con otras tablas. [cite: 4]
    * Implementar un SP por tabla para la actualización de información. [cite: 4]

* **Triggers:**
    * Implementar triggers en cada tabla de la base de datos para mantener una bitácora de movimientos. [cite: 4]

* **Funciones:**
    * Desarrollar 5 funciones que sean utilizadas en alguna vista o SP. [cite: 4]

* **Transacciones:**
    * Implementar 5 transacciones, y cada una debe involucrar una función o un SP. [cite: 4]

* **Gestión de Usuarios y Permisos:**
    * Configurar un usuario administrador. [cite: 4]
    * Crear un usuario con permisos exclusivos para capturar información en todas las tablas, exceptuando la bitácora. [cite: 4]
    * Establecer un usuario con permisos para visualizar, editar y eliminar datos en todas las tablas, excepto la bitácora. [cite: 4]
    * Designar un usuario con permisos para gestionar los permisos de otros usuarios. [cite: 4]
    * Crear un usuario con permisos para crear bases de datos y tablas. [cite: 4]
    * Asegurar que todas las operaciones de inserción, modificación, consulta o eliminación en el sistema de información se realicen exclusivamente a través de vistas, procedimientos almacenados, funciones, triggers o transacciones. [cite: 4]
    * Las conexiones del sistema a la base de datos NO deberán utilizar el usuario `root`; en su lugar, se deberán usar los usuarios creados para cada situación específica. [cite: 4]

## Entregables:

* Se deberá entregar un documento que explique cómo está compuesto cada punto de los requisitos mencionados anteriormente. [cite: 5]
* Para los SPs, funciones y transacciones, el documento deberá mostrar cómo utilizarlos o cómo llamarlos. [cite: 5]
---
## Tecnologías Utilizadas

Este proyecto fue construido utilizando las siguientes tecnologías:

* **Laravel**: Un framework de aplicación web PHP con sintaxis expresiva y elegante.
* **Filament**: Un conjunto de herramientas de desarrollo rápido para Laravel, ideal para construir interfaces de administración elegantes.
* **PHP**: El lenguaje de programación principal utilizado para el backend del proyecto.
* **MySQL**: El sistema de gestión de bases de datos relacionales utilizado para almacenar la información.
* **HTML**: El lenguaje de marcado estándar para la creación de páginas web.
* **CSS**: El lenguaje de hojas de estilo utilizado para el diseño y la presentación.
---

1.  **Clonar el repositorio:**
    Ejecuta el siguiente comando en tu terminal para clonar el proyecto:

    ```bash
    git clone https://github.com/HatCodeDev/MyDrugs.git
    ```

2.  **Acceder al directorio del proyecto:**
    Navega al directorio del proyecto recién clonado:

    ```bash
    cd MyDrugs
    ```

3.  **Descargar ramas remotas:**
    Para asegurarte de tener todas las ramas remotas disponibles, ejecuta:

    ```bash
    git fetch
    ```

4.  **Crear y cambiar a tu rama de desarrollo:**
    Cada miembro del equipo debe trabajar en su propia rama. Reemplaza `tu-nombre-de-rama` tu nombre en minusculas:

    ```bash
    git checkout tu-nombre-de-rama
    ```
5.  **Instalar dependencias de Composer:**
    Una vez en el directorio del proyecto, instala las dependencias de PHP con Composer:

    ```bash
    composer install
    ```

6.  **Instalar dependencias de NPM:**
    Para las dependencias de frontend, usa npm:

    ```bash
    npm install
    ```

7.  **Copiar el archivo de entorno y configurarlo:**
    Copia el archivo de entorno de ejemplo:

    ```bash
    cp .env.example .env
    ```
    Luego, abre el archivo `.env` en tu editor de código y configura los detalles de tu base de datos (asegúrate de que los valores de `DB_USERNAME` y `DB_PASSWORD` coincidan con tu configuración local):

    ```
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=mydrugs
    DB_USERNAME=root
    DB_PASSWORD=
    ```

8.  **Generar la clave de aplicación:**
    Genera la clave de aplicación de Laravel:

    ```bash
    php artisan key:generate
    ```

9.  **Ejecutar migraciones:**
    Crea las tablas de la base de datos:

    ```bash
    php artisan migrate
    ```

10. **Crear un usuario de Filament:**
    Para crear un usuario administrador para el panel de Filament, ejecuta:

    ```bash
    php artisan make:filament-user
    ```
    Sigue las indicaciones en la terminal para ingresar los detalles del usuario:
    * `Name:` admin
    * `Email address:` admin@gmail.com
    * `Password:` admin

11. **Generar permisos y políticas de Shield:**
    Para generar los permisos y políticas para el panel de administración de Filament, ejecuta:

    ```bash
    php artisan shield:generate --all
    ```
    Cuando te pregunte `Which panel do you want to generate permissions/policies for?`, selecciona `admin`.

12. **Asignar el rol de super-administrador:**
    Asigna el rol de super-administrador al usuario que acabas de crear:

    ```bash
    php artisan shield:super-admin
    ```

13. **Iniciar el servidor de desarrollo:**
    Finalmente, inicia el servidor de desarrollo de Laravel:

    ```bash
    php artisan serve
    ```
    Ahora deberías poder acceder al proyecto en tu navegador en `http://127.0.0.1:8000` (o la dirección que te indique la terminal).

## Flujo de Trabajo para el Desarrollo:

* **Trabaja en tu rama:** Asegúrate siempre de estar en tu rama de desarrollo personal antes de hacer cambios.
* **Commits frecuentes:** Realiza commits pequeños y descriptivos de tus cambios.
* **Pull de `main` regularmente:** Para mantener tu rama actualizada con los últimos cambios de `main` y evitar conflictos, haz `git pull origin main` regularmente mientras estés en tu rama.
* **Antes de subir cambios:** Antes de crear un Pull Request, asegúrate de que tu rama esté actualizada con `main` y que todas las pruebas pasen.
* **Subir cambios a tu rama remota:** Una vez que estés listo para subir tus cambios, usa:

    ```bash
    git push origin tu-nombre-de-rama
    ```
    Esto creará o actualizará tu rama en el repositorio remoto.
   
