-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 09-06-2026 a las 14:08:38
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `repositorio`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditorias`
--

CREATE TABLE `auditorias` (
  `aud_codigo` bigint(20) NOT NULL,
  `pry_codigo` bigint(20) NOT NULL,
  `aud_accion` varchar(255) NOT NULL,
  `aud_modulo` varchar(255) NOT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `aud_user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `auditorias`
--

INSERT INTO `auditorias` (`aud_codigo`, `pry_codigo`, `aud_accion`, `aud_modulo`, `ip`, `aud_user_agent`, `created_at`, `updated_at`) VALUES
(1, 1, 'registrar', 'proyectos', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-08 13:33:46', '2026-06-08 13:33:46'),
(2, 1, 'aprobar', 'proyectos', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-08 13:33:57', '2026-06-08 13:33:57'),
(3, 1, 'actualizar', 'proyectos', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-08 13:41:24', '2026-06-08 13:41:24'),
(4, 1, 'aprobar', 'proyectos', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-08 13:41:29', '2026-06-08 13:41:29'),
(5, 1, 'actualizar', 'proyectos', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-08 13:44:33', '2026-06-08 13:44:33'),
(6, 1, 'aprobar', 'proyectos', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-08 13:44:38', '2026-06-08 13:44:38');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comentarios_proyecto`
--

CREATE TABLE `comentarios_proyecto` (
  `cop_codigo` int(11) NOT NULL,
  `cop_descripcion` longtext DEFAULT NULL,
  `pry_codigo` bigint(20) NOT NULL,
  `uex_codigo` int(11) DEFAULT NULL,
  `cop_nombre_contacto` varchar(255) DEFAULT NULL,
  `cop_fecha_creacion` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `componentes`
--

CREATE TABLE `componentes` (
  `comp_codigo` bigint(20) UNSIGNED NOT NULL,
  `comp_nombre` varchar(255) NOT NULL,
  `coord_codigo` bigint(20) UNSIGNED DEFAULT NULL,
  `comp_es_obligatorio` tinyint(1) NOT NULL DEFAULT 1,
  `comp_estado_logico` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `componentes`
--

INSERT INTO `componentes` (`comp_codigo`, `comp_nombre`, `coord_codigo`, `comp_es_obligatorio`, `comp_estado_logico`, `created_at`, `updated_at`) VALUES
(1, 'INFORME', 4, 1, 1, '2026-06-06 15:15:01', '2026-06-06 15:15:01'),
(2, 'MANUALES', 4, 1, 1, '2026-06-06 15:19:17', '2026-06-06 15:19:17'),
(3, 'TRABAJO ESCRITO', 4, 1, 1, '2026-06-06 19:57:52', '2026-06-08 09:34:57'),
(4, 'TRABAJO MANUAL', 6, 1, 1, '2026-06-08 13:01:34', '2026-06-08 13:01:34');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comunidades`
--

CREATE TABLE `comunidades` (
  `com_codigo` bigint(20) NOT NULL,
  `dir_codigo` bigint(20) NOT NULL,
  `com_nombre` varchar(255) NOT NULL,
  `com_tipo` enum('Consejo comunal','Comuna','Empresa publica','Empresa privada','Institucion publica') NOT NULL,
  `com_rif` varchar(255) DEFAULT NULL,
  `com_direccion` text DEFAULT NULL,
  `com_correo` varchar(255) DEFAULT NULL,
  `com_numero_telefono` varchar(255) DEFAULT NULL,
  `anio` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `comunidades`
--

INSERT INTO `comunidades` (`com_codigo`, `dir_codigo`, `com_nombre`, `com_tipo`, `com_rif`, `com_direccion`, `com_correo`, `com_numero_telefono`, `anio`, `created_at`, `updated_at`) VALUES
(1, 3, 'TECNOLOGICO', 'Consejo comunal', '', NULL, '', '04243513514', NULL, '2026-06-07 22:24:33', '2026-06-07 23:41:53'),
(2, 2, 'ASOPORTUGUESA', 'Consejo comunal', '', NULL, '', '', NULL, '2026-06-07 23:40:53', '2026-06-07 23:40:53'),
(3, 4, 'LA BARCA', 'Consejo comunal', '', NULL, '', '04245143517', NULL, '2026-06-08 12:54:31', '2026-06-08 12:54:31');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comunidad_contactos`
--

CREATE TABLE `comunidad_contactos` (
  `ccom_codigo` bigint(20) NOT NULL,
  `com_codigo` bigint(20) UNSIGNED NOT NULL,
  `ccon_nombre` varchar(255) NOT NULL,
  `ccon_apellido` varchar(255) DEFAULT NULL,
  `ccon_correo` varchar(255) DEFAULT NULL,
  `ccon_telefono` varchar(255) DEFAULT NULL,
  `ccon_cargo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `departamento`
--

CREATE TABLE `departamento` (
  `dep_codigo` int(11) NOT NULL,
  `dep_nombre` varchar(255) DEFAULT NULL,
  `dep_cargo` longtext NOT NULL,
  `dep_uex_codigo` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `direcciones`
--

CREATE TABLE `direcciones` (
  `dir_codigo` bigint(20) NOT NULL,
  `mun_codigo` bigint(20) NOT NULL,
  `dir_parroquia` varchar(255) NOT NULL,
  `dir_sector` varchar(255) NOT NULL,
  `dir_calle` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `direcciones`
--

INSERT INTO `direcciones` (`dir_codigo`, `mun_codigo`, `dir_parroquia`, `dir_sector`, `dir_calle`, `created_at`, `updated_at`) VALUES
(1, 228, '', '', 'ACARIGUA', '2026-06-07 22:24:33', '2026-06-07 22:24:33'),
(2, 233, '', '', 'ACARIGUA', '2026-06-07 23:40:53', '2026-06-07 23:40:53'),
(3, 228, '', '', 'ACARIGUA ESTADO PORTUGUESA', '2026-06-07 23:41:52', '2026-06-07 23:41:52'),
(4, 228, '', '', 'LA BARCA DE ORO', '2026-06-08 12:54:31', '2026-06-08 12:54:31');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estados`
--

CREATE TABLE `estados` (
  `est_codigo` bigint(20) NOT NULL,
  `est_nombre` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estados`
--

INSERT INTO `estados` (`est_codigo`, `est_nombre`, `created_at`, `updated_at`) VALUES
(1, 'Amazonas', NULL, NULL),
(2, 'Anzoátegui', NULL, NULL),
(3, 'Apure', NULL, NULL),
(4, 'Aragua', NULL, NULL),
(5, 'Barinas', NULL, NULL),
(6, 'Bolívar', NULL, NULL),
(7, 'Carabobo', NULL, NULL),
(8, 'Cojedes', NULL, NULL),
(9, 'Delta Amacuro', NULL, NULL),
(10, 'Distrito Capital', NULL, NULL),
(11, 'Falcón', NULL, NULL),
(12, 'Guárico', NULL, NULL),
(13, 'Lara', NULL, NULL),
(14, 'Mérida', NULL, NULL),
(15, 'Miranda', NULL, NULL),
(16, 'Monagas', NULL, NULL),
(17, 'Nueva Esparta', NULL, NULL),
(18, 'Portuguesa', NULL, NULL),
(19, 'Sucre', NULL, NULL),
(20, 'Táchira', NULL, NULL),
(21, 'Trujillo', NULL, NULL),
(22, 'La Guaira', NULL, NULL),
(23, 'Yaracuy', NULL, NULL),
(24, 'Zulia', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `grupo_proyecto_modulo`
--

CREATE TABLE `grupo_proyecto_modulo` (
  `grp_codigo` bigint(20) UNSIGNED NOT NULL,
  `grp_nombre` varchar(120) NOT NULL,
  `grp_contexto` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`grp_contexto`)),
  `grp_com_codigo` bigint(20) UNSIGNED DEFAULT NULL,
  `grp_creador_cedula` varchar(20) DEFAULT NULL,
  `grp_miembros` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`grp_miembros`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `grupo_proyecto_modulo`
--

INSERT INTO `grupo_proyecto_modulo` (`grp_codigo`, `grp_nombre`, `grp_contexto`, `grp_com_codigo`, `grp_creador_cedula`, `grp_miembros`, `created_at`, `updated_at`) VALUES
(1, 'peru', '{\"lap_codigo\":72,\"sec_codigo\":11588,\"pro_codigo\":4,\"lap_nombre\":\"2026-I\",\"sec_nombre\":\"631R\",\"pro_siglas\":\"PNFINF\",\"pro_nombre\":\"PROGRAMA NACIONAL DE FORMACIÓN EN INFORMÁTICA\",\"tra_codigo\":5,\"trayecto_nombre\":\"IV\"}', 1, '13354832', '[{\"cedula\":\"26836521\",\"rol_id\":2,\"nombre\":\"RAINIEL JHOSUE\",\"apellido\":\"MARTINEZ GIL\"},{\"cedula\":\"31144855\",\"rol_id\":2,\"nombre\":\"MARIA GABRIELA\",\"apellido\":\"RODRIGUEZ SANDOVAL\"},{\"cedula\":\"31187176\",\"rol_id\":1,\"nombre\":\"JOSUE MIGUEL\",\"apellido\":\"FARIAS BARRIOS\"}]', '2026-06-06 16:51:12', '2026-06-09 09:16:05'),
(2, 'repositorio', '{\"lap_codigo\":72,\"sec_codigo\":11587,\"pro_codigo\":4,\"lap_nombre\":\"2026-I\",\"sec_nombre\":\"631\",\"pro_siglas\":\"PNFINF\",\"pro_nombre\":\"PROGRAMA NACIONAL DE FORMACIÓN EN INFORMÁTICA\",\"tra_codigo\":5,\"trayecto_nombre\":\"IV\"}', 1, '13354832', '[{\"cedula\":\"31306741\",\"rol_id\":2,\"nombre\":\"FERNANDO ANTONIO\",\"apellido\":\"LEON LANDER\"},{\"cedula\":\"31057795\",\"rol_id\":2,\"nombre\":\"JHOEL ALEXIS\",\"apellido\":\"LUGO MARTINEZ\"},{\"cedula\":\"30966221\",\"rol_id\":2,\"nombre\":\"EMANUEL ISAI\",\"apellido\":\"PERAZA GONZALEZ\"},{\"cedula\":\"31490175\",\"rol_id\":1,\"nombre\":\"MARIA FERNANDA\",\"apellido\":\"PEREIRA BARCO\"}]', '2026-06-06 21:25:53', '2026-06-09 09:15:52'),
(3, 'planificaciones', '[]', 1, '13354832', '[{\"cedula\":\"31114131\",\"rol_id\":2,\"nombre\":\"ENMANUEL GABRIEL\",\"apellido\":\"SALAS ADANS\"},{\"cedula\":\"31659136\",\"rol_id\":2,\"nombre\":\"MAIKOL DAVID\",\"apellido\":\"RODRIGUEZ OJEDA\"},{\"cedula\":\"31356417\",\"rol_id\":2,\"nombre\":\"HENRY ALEJANDRO\",\"apellido\":\"JIMENEZ LEAL\"},{\"cedula\":\"31009367\",\"rol_id\":2,\"nombre\":\"ALEJANDRO DAVID\",\"apellido\":\"FENOMENO MENDOZA\"},{\"cedula\":\"31215545\",\"rol_id\":1,\"nombre\":\"NASSER JOSE\",\"apellido\":\"DABOIN ROJAS\"},{\"cedula\":\"31306263\",\"rol_id\":2,\"nombre\":\"DIANA ALEJANDRA\",\"apellido\":\"CORDERO TORRES\"},{\"cedula\":\"31162406\",\"rol_id\":2,\"nombre\":\"LUIS FELIPE\",\"apellido\":\"CASTILLO PARRA\"}]', '2026-06-08 12:58:20', '2026-06-08 13:02:25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `linea_investigacions`
--

CREATE TABLE `linea_investigacions` (
  `lin_codigo` bigint(20) NOT NULL,
  `lin_nombre_investigacion` varchar(255) DEFAULT NULL,
  `lin_descripcion` text DEFAULT NULL,
  `lin_area_de_investigacion` varchar(255) DEFAULT NULL,
  `coord_codigo` bigint(20) UNSIGNED DEFAULT NULL,
  `lin_estado` enum('Activo','Inactivo') DEFAULT 'Activo',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `linea_investigacions`
--

INSERT INTO `linea_investigacions` (`lin_codigo`, `lin_nombre_investigacion`, `lin_descripcion`, `lin_area_de_investigacion`, `coord_codigo`, `lin_estado`, `created_at`, `updated_at`) VALUES
(1, 'HOLA', 'PODEMOS AÑADIR ALGO', 'PROBEMOS', 4, 'Activo', '2026-06-06 16:57:37', '2026-06-06 16:57:37'),
(2, 'INVESTIGACION INFORMATICA', 'PROBAREMOS ESTA INVESTIGACION', 'DEPARTAMENTO DE INFORMATICA', 4, 'Activo', '2026-06-08 12:59:57', '2026-06-08 12:59:57');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `metodologia_investigacions`
--

CREATE TABLE `metodologia_investigacions` (
  `mei_codigo` bigint(20) NOT NULL,
  `mei_nombre` varchar(255) NOT NULL,
  `mei_descripcion` text DEFAULT NULL,
  `mei_estado_logico` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `metodologia_investigacions`
--

INSERT INTO `metodologia_investigacions` (`mei_codigo`, `mei_nombre`, `mei_descripcion`, `mei_estado_logico`, `created_at`, `updated_at`) VALUES
(1, 'METODOLOGIA', 'AQUI ESTA LA AREPA', 1, '2026-06-06 16:58:34', '2026-06-06 16:58:34'),
(2, 'METODOLOGIA DE ELECTRICOS', 'ELECTRONICA ENTRE CIRCUITOS', 1, '2026-06-08 13:00:54', '2026-06-08 13:00:54');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(2, '2026_06_05_100000_add_org_correo_to_organizacion_table', 1),
(3, '2026_06_05_100001_add_com_codigo_to_comunidad_contactos_table', 2),
(4, '2026_05_26_100000_create_grupo_proyecto_modulo_table', 3),
(6, '2026_06_06_100001_drop_coordinaciones_tables', 5),
(7, '2026_06_06_100002_add_proyectos_indexes', 6),
(8, '2026_06_07_182722_create_org_contactos_table', 7),
(9, '2026_06_07_190000_fix_proyectos_columns', 8),
(11, '2026_06_07_190010_add_performance_indexes', 9),
(12, '2026_06_08_100000_add_unique_componente_nombre_to_componentes_table', 10),
(13, '2026_06_08_091237_make_classification_fields_nullable', 11);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `municipios`
--

CREATE TABLE `municipios` (
  `mun_codigo` bigint(20) NOT NULL,
  `est_codigo` bigint(20) NOT NULL,
  `mun_nombre` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `municipios`
--

INSERT INTO `municipios` (`mun_codigo`, `est_codigo`, `mun_nombre`, `created_at`, `updated_at`) VALUES
(1, 1, 'Alto Orinoco', NULL, NULL),
(2, 1, 'Atabapo', NULL, NULL),
(3, 1, 'Atures', NULL, NULL),
(4, 1, 'Autana', NULL, NULL),
(5, 1, 'Manapiare', NULL, NULL),
(6, 1, 'Maroa', NULL, NULL),
(7, 1, 'Río Negro', NULL, NULL),
(8, 2, 'Anaco', NULL, NULL),
(9, 2, 'Aragua', NULL, NULL),
(10, 2, 'Bolívar', NULL, NULL),
(11, 2, 'Bruzual', NULL, NULL),
(12, 2, 'Cajigal', NULL, NULL),
(13, 2, 'Carvajal', NULL, NULL),
(14, 2, 'Diego Bautista Urbaneja', NULL, NULL),
(15, 2, 'Freites', NULL, NULL),
(16, 2, 'Guanipa', NULL, NULL),
(17, 2, 'Guanta', NULL, NULL),
(18, 2, 'Independencia', NULL, NULL),
(19, 2, 'Libertad', NULL, NULL),
(20, 2, 'McGregor', NULL, NULL),
(21, 2, 'Miranda', NULL, NULL),
(22, 2, 'Monagas', NULL, NULL),
(23, 2, 'Peñalver', NULL, NULL),
(24, 2, 'Píritu', NULL, NULL),
(25, 2, 'San Juan de Capistrano', NULL, NULL),
(26, 2, 'Santa Ana', NULL, NULL),
(27, 2, 'Simón Rodríguez', NULL, NULL),
(28, 2, 'Sotillo', NULL, NULL),
(29, 3, 'Achaguas', NULL, NULL),
(30, 3, 'Biruaca', NULL, NULL),
(31, 3, 'Muñoz', NULL, NULL),
(32, 3, 'Páez', NULL, NULL),
(33, 3, 'Pedro Camejo', NULL, NULL),
(34, 3, 'Rómulo Gallegos', NULL, NULL),
(35, 3, 'San Fernando', NULL, NULL),
(36, 4, 'Bolívar', NULL, NULL),
(37, 4, 'Camatagua', NULL, NULL),
(38, 4, 'Francisco Linares Alcántara', NULL, NULL),
(39, 4, 'Girardot', NULL, NULL),
(40, 4, 'José Ángel Lamas', NULL, NULL),
(41, 4, 'José Félix Ribas', NULL, NULL),
(42, 4, 'José Rafael Revenga', NULL, NULL),
(43, 4, 'Libertador', NULL, NULL),
(44, 4, 'Mario Briceño Iragorry', NULL, NULL),
(45, 4, 'Ocumare de la Costa de Oro', NULL, NULL),
(46, 4, 'San Casimiro', NULL, NULL),
(47, 4, 'San Sebastián', NULL, NULL),
(48, 4, 'Santiago Mariño', NULL, NULL),
(49, 4, 'Santos Michelena', NULL, NULL),
(50, 4, 'Sucre', NULL, NULL),
(51, 4, 'Tovar', NULL, NULL),
(52, 4, 'Urdaneta', NULL, NULL),
(53, 4, 'Zamora', NULL, NULL),
(54, 5, 'Alberto Arvelo Torrealba', NULL, NULL),
(55, 5, 'Andrés Eloy Blanco', NULL, NULL),
(56, 5, 'Antonio José de Sucre', NULL, NULL),
(57, 5, 'Arismendi', NULL, NULL),
(58, 5, 'Barinas', NULL, NULL),
(59, 5, 'Bolívar', NULL, NULL),
(60, 5, 'Cruz Paredes', NULL, NULL),
(61, 5, 'Ezequiel Zamora', NULL, NULL),
(62, 5, 'Obispos', NULL, NULL),
(63, 5, 'Pedraza', NULL, NULL),
(64, 5, 'Rojas', NULL, NULL),
(65, 5, 'Sosa', NULL, NULL),
(66, 6, 'Caroní', NULL, NULL),
(67, 6, 'Cedeño', NULL, NULL),
(68, 6, 'El Callao', NULL, NULL),
(69, 6, 'Gran Sabana', NULL, NULL),
(70, 6, 'Heres', NULL, NULL),
(71, 6, 'Piar', NULL, NULL),
(72, 6, 'Padre Pedro Chien', NULL, NULL),
(73, 6, 'Roscio', NULL, NULL),
(74, 6, 'Sifontes', NULL, NULL),
(75, 6, 'Sucre', NULL, NULL),
(76, 6, 'Raúl Leoni', NULL, NULL),
(77, 7, 'Bejuma', NULL, NULL),
(78, 7, 'Carlos Arvelo', NULL, NULL),
(79, 7, 'Diego Ibarra', NULL, NULL),
(80, 7, 'Guacara', NULL, NULL),
(81, 7, 'Juan José Mora', NULL, NULL),
(82, 7, 'Libertador', NULL, NULL),
(83, 7, 'Los Guayos', NULL, NULL),
(84, 7, 'Miranda', NULL, NULL),
(85, 7, 'Montalbán', NULL, NULL),
(86, 7, 'Naguanagua', NULL, NULL),
(87, 7, 'Puerto Cabello', NULL, NULL),
(88, 7, 'San Diego', NULL, NULL),
(89, 7, 'San Joaquín', NULL, NULL),
(90, 7, 'Valencia', NULL, NULL),
(91, 8, 'Anzoátegui', NULL, NULL),
(92, 8, 'Ezequiel Zamora', NULL, NULL),
(93, 8, 'Falcón', NULL, NULL),
(94, 8, 'Girardot', NULL, NULL),
(95, 8, 'Lima Blanco', NULL, NULL),
(96, 8, 'Pao de San Juan Bautista', NULL, NULL),
(97, 8, 'Ricaurte', NULL, NULL),
(98, 8, 'Rómulo Gallegos', NULL, NULL),
(99, 8, 'Tinaco', NULL, NULL),
(100, 9, 'Antonio Díaz', NULL, NULL),
(101, 9, 'Casacoima', NULL, NULL),
(102, 9, 'Pedernales', NULL, NULL),
(103, 9, 'Tucupita', NULL, NULL),
(104, 10, 'Libertador', NULL, NULL),
(105, 11, 'Acosta', NULL, NULL),
(106, 11, 'Bolívar', NULL, NULL),
(107, 11, 'Buchivacoa', NULL, NULL),
(108, 11, 'Cacique Manaure', NULL, NULL),
(109, 11, 'Carirubana', NULL, NULL),
(110, 11, 'Colina', NULL, NULL),
(111, 11, 'Dabajuro', NULL, NULL),
(112, 11, 'Democracia', NULL, NULL),
(113, 11, 'Falcón', NULL, NULL),
(114, 11, 'Federación', NULL, NULL),
(115, 11, 'Jacura', NULL, NULL),
(116, 11, 'Los Taques', NULL, NULL),
(117, 11, 'Mauroa', NULL, NULL),
(118, 11, 'Miranda', NULL, NULL),
(119, 11, 'Monseñor Iturriza', NULL, NULL),
(120, 11, 'Palmasola', NULL, NULL),
(121, 11, 'Petit', NULL, NULL),
(122, 11, 'Píritu', NULL, NULL),
(123, 11, 'San Francisco', NULL, NULL),
(124, 11, 'Silva', NULL, NULL),
(125, 11, 'Sucre', NULL, NULL),
(126, 11, 'Tocópero', NULL, NULL),
(127, 11, 'Unión', NULL, NULL),
(128, 11, 'Urumaco', NULL, NULL),
(129, 11, 'Zamora', NULL, NULL),
(130, 12, 'Camaguán', NULL, NULL),
(131, 12, 'Chaguaramas', NULL, NULL),
(132, 12, 'El Socorro', NULL, NULL),
(133, 12, 'Francisco de Miranda', NULL, NULL),
(134, 12, 'José Félix Ribas', NULL, NULL),
(135, 12, 'José Tadeo Monagas', NULL, NULL),
(136, 12, 'Juan Germán Roscio', NULL, NULL),
(137, 12, 'Julián Mellado', NULL, NULL),
(138, 12, 'Las Mercedes', NULL, NULL),
(139, 12, 'Ortiz', NULL, NULL),
(140, 12, 'Pedro Zaraza', NULL, NULL),
(141, 12, 'San Gerónimo de Guayabal', NULL, NULL),
(142, 12, 'San José de Guaribe', NULL, NULL),
(143, 12, 'Santa María de Ipire', NULL, NULL),
(144, 13, 'Andrés Eloy Blanco', NULL, NULL),
(145, 13, 'Crespo', NULL, NULL),
(146, 13, 'Iribarren', NULL, NULL),
(147, 13, 'Jiménez', NULL, NULL),
(148, 13, 'Morán', NULL, NULL),
(149, 13, 'Palavecino', NULL, NULL),
(150, 13, 'Simón Planas', NULL, NULL),
(151, 13, 'Torres', NULL, NULL),
(152, 13, 'Urdaneta', NULL, NULL),
(153, 14, 'Alberto Adriani', NULL, NULL),
(154, 14, 'Andrés Bello', NULL, NULL),
(155, 14, 'Antonio Pinto Salinas', NULL, NULL),
(156, 14, 'Aricagua', NULL, NULL),
(157, 14, 'Arzobispo Chacón', NULL, NULL),
(158, 14, 'Campo Elías', NULL, NULL),
(159, 14, 'Caracciolo Parra Olmedo', NULL, NULL),
(160, 14, 'Cardenal Quintero', NULL, NULL),
(161, 14, 'Guaraque', NULL, NULL),
(162, 14, 'Julio César Salas', NULL, NULL),
(163, 14, 'Justo Briceño', NULL, NULL),
(164, 14, 'Libertador', NULL, NULL),
(165, 14, 'Miranda', NULL, NULL),
(166, 14, 'Obispo Ramos de Lora', NULL, NULL),
(167, 14, 'Padre Noguera', NULL, NULL),
(168, 14, 'Pueblo Llano', NULL, NULL),
(169, 14, 'Rangel', NULL, NULL),
(170, 14, 'Rivas Dávila', NULL, NULL),
(171, 14, 'Santos Marquina', NULL, NULL),
(172, 14, 'Sucre', NULL, NULL),
(173, 14, 'Tovar', NULL, NULL),
(174, 14, 'Tulio Febres Cordero', NULL, NULL),
(175, 14, 'Zea', NULL, NULL),
(176, 15, 'Acevedo', NULL, NULL),
(177, 15, 'Andrés Bello', NULL, NULL),
(178, 15, 'Baruta', NULL, NULL),
(179, 15, 'Brión', NULL, NULL),
(180, 15, 'Buroz', NULL, NULL),
(181, 15, 'Carrizal', NULL, NULL),
(182, 15, 'Chacao', NULL, NULL),
(183, 15, 'Cristóbal Rojas', NULL, NULL),
(184, 15, 'El Hatillo', NULL, NULL),
(185, 15, 'Guaicaipuro', NULL, NULL),
(186, 15, 'Independencia', NULL, NULL),
(187, 15, 'Lander', NULL, NULL),
(188, 15, 'Los Salias', NULL, NULL),
(189, 15, 'Páez', NULL, NULL),
(190, 15, 'Paz Castillo', NULL, NULL),
(191, 15, 'Pedro Gual', NULL, NULL),
(192, 15, 'Plaza', NULL, NULL),
(193, 15, 'Simón Bolívar', NULL, NULL),
(194, 15, 'Sucre', NULL, NULL),
(195, 15, 'Urdaneta', NULL, NULL),
(196, 15, 'Zamora', NULL, NULL),
(197, 16, 'Acosta', NULL, NULL),
(198, 16, 'Aguasay', NULL, NULL),
(199, 16, 'Bolívar', NULL, NULL),
(200, 16, 'Caripe', NULL, NULL),
(201, 16, 'Cedeño', NULL, NULL),
(202, 16, 'Ezequiel Zamora', NULL, NULL),
(203, 16, 'Libertador', NULL, NULL),
(204, 16, 'Maturín', NULL, NULL),
(205, 16, 'Piar', NULL, NULL),
(206, 16, 'Punceres', NULL, NULL),
(207, 16, 'Santa Bárbara', NULL, NULL),
(208, 16, 'Sotillo', NULL, NULL),
(209, 16, 'Uracoa', NULL, NULL),
(210, 17, 'Antolín del Campo', NULL, NULL),
(211, 17, 'Arismendi', NULL, NULL),
(212, 17, 'Díaz', NULL, NULL),
(213, 17, 'García', NULL, NULL),
(214, 17, 'Gómez', NULL, NULL),
(215, 17, 'Maneiro', NULL, NULL),
(216, 17, 'Marcano', NULL, NULL),
(217, 17, 'Mariño', NULL, NULL),
(218, 17, 'Península de Macanao', NULL, NULL),
(219, 17, 'Tubores', NULL, NULL),
(220, 17, 'Villalba', NULL, NULL),
(221, 18, 'Agua Blanca', NULL, NULL),
(222, 18, 'Araure', NULL, NULL),
(223, 18, 'Esteller', NULL, NULL),
(224, 18, 'Guanare', NULL, NULL),
(225, 18, 'Guanarito', NULL, NULL),
(226, 18, 'Monseñor José Vicente de Unda', NULL, NULL),
(227, 18, 'Ospino', NULL, NULL),
(228, 18, 'Páez', NULL, NULL),
(229, 18, 'Papelón', NULL, NULL),
(230, 18, 'San Genaro de Boconoíto', NULL, NULL),
(231, 18, 'San Rafael de Onoto', NULL, NULL),
(232, 18, 'Santa Rosalía', NULL, NULL),
(233, 18, 'Sucre', NULL, NULL),
(234, 18, 'Turén', NULL, NULL),
(235, 19, 'Andrés Eloy Blanco', NULL, NULL),
(236, 19, 'Andrés Mata', NULL, NULL),
(237, 19, 'Arismendi', NULL, NULL),
(238, 19, 'Benítez', NULL, NULL),
(239, 19, 'Bermúdez', NULL, NULL),
(240, 19, 'Bolívar', NULL, NULL),
(241, 19, 'Cajigal', NULL, NULL),
(242, 19, 'Cruz Salmerón Acosta', NULL, NULL),
(243, 19, 'Libertador', NULL, NULL),
(244, 19, 'Mariño', NULL, NULL),
(245, 19, 'Mejía', NULL, NULL),
(246, 19, 'Montes', NULL, NULL),
(247, 19, 'Ribero', NULL, NULL),
(248, 19, 'Sucre', NULL, NULL),
(249, 19, 'Valdéz', NULL, NULL),
(250, 20, 'Andrés Bello', NULL, NULL),
(251, 20, 'Antonio Rómulo Costa', NULL, NULL),
(252, 20, 'Ayacucho', NULL, NULL),
(253, 20, 'Bolívar', NULL, NULL),
(254, 20, 'Cárdenas', NULL, NULL),
(255, 20, 'Córdoba', NULL, NULL),
(256, 20, 'Fernández Feo', NULL, NULL),
(257, 20, 'Francisco de Miranda', NULL, NULL),
(258, 20, 'García de Hevia', NULL, NULL),
(259, 20, 'Guásimos', NULL, NULL),
(260, 20, 'Independencia', NULL, NULL),
(261, 20, 'Jáuregui', NULL, NULL),
(262, 20, 'José María Vargas', NULL, NULL),
(263, 20, 'Junín', NULL, NULL),
(264, 20, 'Libertad', NULL, NULL),
(265, 20, 'Libertador', NULL, NULL),
(266, 20, 'Lobatera', NULL, NULL),
(267, 20, 'Michelena', NULL, NULL),
(268, 20, 'Panamericano', NULL, NULL),
(269, 20, 'Pedro María Ureña', NULL, NULL),
(270, 20, 'Rafael Urdaneta', NULL, NULL),
(271, 20, 'Samuel Darío Maldonado', NULL, NULL),
(272, 20, 'San Cristóbal', NULL, NULL),
(273, 20, 'San Judas Tadeo', NULL, NULL),
(274, 20, 'Seboruco', NULL, NULL),
(275, 20, 'Simón Rodríguez', NULL, NULL),
(276, 20, 'Sucre', NULL, NULL),
(277, 20, 'Torbes', NULL, NULL),
(278, 20, 'Uribante', NULL, NULL),
(279, 21, 'Andrés Bello', NULL, NULL),
(280, 21, 'Boconó', NULL, NULL),
(281, 21, 'Bolívar', NULL, NULL),
(282, 21, 'Candelaria', NULL, NULL),
(283, 21, 'Carache', NULL, NULL),
(284, 21, 'Escuque', NULL, NULL),
(285, 21, 'José Felipe Márquez Cañizales', NULL, NULL),
(286, 21, 'Juan Vicente Campo Elías', NULL, NULL),
(287, 21, 'La Ceiba', NULL, NULL),
(288, 21, 'Miranda', NULL, NULL),
(289, 21, 'Monte Carmelo', NULL, NULL),
(290, 21, 'Motatán', NULL, NULL),
(291, 21, 'Pampán', NULL, NULL),
(292, 21, 'Pampanito', NULL, NULL),
(293, 21, 'Rafael Rangel', NULL, NULL),
(294, 21, 'San Rafael de Carvajal', NULL, NULL),
(295, 21, 'Sucre', NULL, NULL),
(296, 21, 'Trujillo', NULL, NULL),
(297, 21, 'Urdaneta', NULL, NULL),
(298, 21, 'Valera', NULL, NULL),
(299, 22, 'Vargas', NULL, NULL),
(300, 23, 'Arístides Bastidas', NULL, NULL),
(301, 23, 'Bolívar', NULL, NULL),
(302, 23, 'Bruzual', NULL, NULL),
(303, 23, 'Cocorote', NULL, NULL),
(304, 23, 'Independencia', NULL, NULL),
(305, 23, 'José Antonio Páez', NULL, NULL),
(306, 23, 'La Trinidad', NULL, NULL),
(307, 23, 'Manuel Monge', NULL, NULL),
(308, 23, 'Nirgua', NULL, NULL),
(309, 23, 'Peña', NULL, NULL),
(310, 23, 'San Felipe', NULL, NULL),
(311, 23, 'Sucre', NULL, NULL),
(312, 23, 'Urachiche', NULL, NULL),
(313, 23, 'Veroes', NULL, NULL),
(314, 24, 'Almirante Padilla', NULL, NULL),
(315, 24, 'Baralt', NULL, NULL),
(316, 24, 'Cabimas', NULL, NULL),
(317, 24, 'Catatumbo', NULL, NULL),
(318, 24, 'Colón', NULL, NULL),
(319, 24, 'Francisco Javier Pulgar', NULL, NULL),
(320, 24, 'Guajira', NULL, NULL),
(321, 24, 'Jesús Enrique Lossada', NULL, NULL),
(322, 24, 'Jesús María Semprún', NULL, NULL),
(323, 24, 'La Cañada de Urdaneta', NULL, NULL),
(324, 24, 'Lagunillas', NULL, NULL),
(325, 24, 'Machiques de Perijá', NULL, NULL),
(326, 24, 'Mara', NULL, NULL),
(327, 24, 'Maracaibo', NULL, NULL),
(328, 24, 'Miranda', NULL, NULL),
(329, 24, 'Rosario de Perijá', NULL, NULL),
(330, 24, 'San Francisco', NULL, NULL),
(331, 24, 'Santa Rita', NULL, NULL),
(332, 24, 'Simón Bolívar', NULL, NULL),
(333, 24, 'Sucre', NULL, NULL),
(334, 24, 'Valmore Rodríguez', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `organizacion`
--

CREATE TABLE `organizacion` (
  `org_codigo` int(11) NOT NULL,
  `org_nombre` varchar(255) DEFAULT NULL,
  `org_rif` varchar(45) DEFAULT NULL,
  `org_correo` varchar(255) DEFAULT NULL,
  `org_direccion` text DEFAULT NULL,
  `org_dep_codigo` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `organizacion`
--

INSERT INTO `organizacion` (`org_codigo`, `org_nombre`, `org_rif`, `org_correo`, `org_direccion`, `org_dep_codigo`) VALUES
(6, 'organizacion de prueba', '-', 'antoniolander18@gmail.com', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `org_contactos`
--

CREATE TABLE `org_contactos` (
  `oco_codigo` bigint(20) UNSIGNED NOT NULL,
  `org_codigo` int(11) NOT NULL,
  `dep_codigo` int(11) DEFAULT NULL,
  `oco_nombre` varchar(255) NOT NULL,
  `oco_apellido` varchar(255) DEFAULT NULL,
  `oco_correo` varchar(255) DEFAULT NULL,
  `oco_telefono` varchar(255) DEFAULT NULL,
  `oco_cargo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proyectos`
--

CREATE TABLE `proyectos` (
  `pry_codigo` bigint(20) NOT NULL,
  `aud_codigo` bigint(20) DEFAULT NULL,
  `com_codigo` bigint(20) NOT NULL,
  `mei_codigo` bigint(20) DEFAULT NULL,
  `tin_codigo` bigint(20) DEFAULT NULL,
  `lin_codigo` bigint(20) DEFAULT NULL,
  `tpu_codigo` bigint(20) DEFAULT NULL,
  `pry_uex_codigo` int(11) DEFAULT NULL,
  `pry_titulo` varchar(255) DEFAULT NULL,
  `pry_resumen` text NOT NULL,
  `pry_problema` text DEFAULT NULL,
  `pry_objetivo_general` varchar(255) DEFAULT NULL,
  `pry_objetivo_especifico` text DEFAULT NULL,
  `pry_fecha_inicio` date DEFAULT NULL,
  `pry_fecha_fin` date DEFAULT NULL,
  `pry_lapso` varchar(255) DEFAULT NULL,
  `pry_fecha_subida` date DEFAULT NULL,
  `pry_asignacion_ct` tinyint(4) NOT NULL DEFAULT 0,
  `pry_calificacion` tinyint(4) DEFAULT NULL,
  `pry_fecha_aprobacion` date DEFAULT NULL,
  `pry_direccion_logica` varchar(255) DEFAULT NULL,
  `pry_archivo_path` varchar(255) DEFAULT NULL,
  `pry_documentos` text DEFAULT NULL,
  `pry_estado_logico` tinyint(1) NOT NULL DEFAULT 1,
  `pry_estado_validacion` enum('Aprobado','Pendiente','Rechazado') NOT NULL DEFAULT 'Pendiente',
  `pry_estado_` enum('Activo','Inactivo') DEFAULT 'Activo',
  `pry_estado` enum('En desarrollo','Culminado','Rechazado','Suspendido') DEFAULT 'En desarrollo',
  `pry_motivo_rechazo` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `proyectos`
--

INSERT INTO `proyectos` (`pry_codigo`, `aud_codigo`, `com_codigo`, `mei_codigo`, `tin_codigo`, `lin_codigo`, `tpu_codigo`, `pry_uex_codigo`, `pry_titulo`, `pry_resumen`, `pry_problema`, `pry_objetivo_general`, `pry_objetivo_especifico`, `pry_fecha_inicio`, `pry_fecha_fin`, `pry_lapso`, `pry_fecha_subida`, `pry_asignacion_ct`, `pry_calificacion`, `pry_fecha_aprobacion`, `pry_direccion_logica`, `pry_archivo_path`, `pry_documentos`, `pry_estado_logico`, `pry_estado_validacion`, `pry_estado_`, `pry_estado`, `pry_motivo_rechazo`, `created_at`, `updated_at`) VALUES
(1, 6, 1, NULL, NULL, NULL, NULL, NULL, NULL, 'GESTOR DE PLANIFICACIONES ACADEMICAS EN EL TECNOLOGICO', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-08', 1, NULL, NULL, 'EQGRP:3', 'PROYECTOS/1OJF7YK4MZG33JWUB8CPDEIUSK7OHMPR8RD4HB1F.PDF', NULL, 1, 'Aprobado', 'Activo', 'En desarrollo', NULL, '2026-06-08 13:33:46', '2026-06-08 13:44:38');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proyectos_publicados`
--

CREATE TABLE `proyectos_publicados` (
  `pub_codigo` bigint(20) UNSIGNED NOT NULL,
  `pry_codigo` bigint(20) NOT NULL,
  `pub_archivo_path` varchar(500) DEFAULT NULL,
  `pub_estado` varchar(20) NOT NULL DEFAULT 'publicado',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol_externo`
--

CREATE TABLE `rol_externo` (
  `rex_codigo` int(11) NOT NULL,
  `rex_nombre` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rol_externo`
--

INSERT INTO `rol_externo` (`rex_codigo`, `rex_nombre`) VALUES
(1, 'Negocio');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_investigacions`
--

CREATE TABLE `tipo_investigacions` (
  `tin_codigo` bigint(20) NOT NULL,
  `tin_nombre` varchar(255) NOT NULL,
  `tin_descripcion` text DEFAULT NULL,
  `tin_estado_logico` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipo_investigacions`
--

INSERT INTO `tipo_investigacions` (`tin_codigo`, `tin_nombre`, `tin_descripcion`, `tin_estado_logico`, `created_at`, `updated_at`) VALUES
(1, 'TIPO DE INVESTIGACION', 'PROBAREMOS ESTA ALINEA', 1, '2026-06-06 16:58:11', '2026-06-06 16:58:11'),
(2, 'TIPO DE INVESTIGACION PROPORCIONADA', 'PROBEMOS UNA INVESTIGACION', 1, '2026-06-08 13:00:27', '2026-06-08 13:00:27');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_publicacions`
--

CREATE TABLE `tipo_publicacions` (
  `tpu_codigo` bigint(20) NOT NULL,
  `tpu_nombre` varchar(255) NOT NULL,
  `tpu_mencion_honorifica` tinyint(4) NOT NULL DEFAULT 0,
  `tpu_estado_logico` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipo_publicacions`
--

INSERT INTO `tipo_publicacions` (`tpu_codigo`, `tpu_nombre`, `tpu_mencion_honorifica`, `tpu_estado_logico`, `created_at`, `updated_at`) VALUES
(1, 'PUBLICACION DE PROYECTO', 1, 1, '2026-06-06 16:58:49', '2026-06-06 16:58:49'),
(2, 'PODREMOS INVESTIGAR', 1, 1, '2026-06-08 13:01:14', '2026-06-08 13:01:14');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios_externos`
--

CREATE TABLE `usuarios_externos` (
  `uex_codigo` int(11) NOT NULL,
  `uex_nombre` varchar(255) NOT NULL,
  `uex_contrasena` varchar(255) NOT NULL,
  `uex_rex_codigo` int(11) DEFAULT NULL,
  `uex_estado` enum('Activo','Inactivo') DEFAULT 'Activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `auditorias`
--
ALTER TABLE `auditorias`
  ADD PRIMARY KEY (`aud_codigo`),
  ADD KEY `pry_codigo` (`pry_codigo`);

--
-- Indices de la tabla `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indices de la tabla `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indices de la tabla `comentarios_proyecto`
--
ALTER TABLE `comentarios_proyecto`
  ADD PRIMARY KEY (`cop_codigo`),
  ADD KEY `comentarios_proyecto_id_foreign` (`pry_codigo`),
  ADD KEY `comentarios_usuario_id_foreign` (`uex_codigo`),
  ADD KEY `idx_comentarios_pry_codigo` (`pry_codigo`);

--
-- Indices de la tabla `componentes`
--
ALTER TABLE `componentes`
  ADD PRIMARY KEY (`comp_codigo`),
  ADD UNIQUE KEY `uq_componentes_nombre_programa` (`comp_nombre`,`coord_codigo`);

--
-- Indices de la tabla `comunidades`
--
ALTER TABLE `comunidades`
  ADD PRIMARY KEY (`com_codigo`),
  ADD KEY `comunidades_dir_codigo_foreign` (`dir_codigo`);

--
-- Indices de la tabla `comunidad_contactos`
--
ALTER TABLE `comunidad_contactos`
  ADD PRIMARY KEY (`ccom_codigo`);

--
-- Indices de la tabla `departamento`
--
ALTER TABLE `departamento`
  ADD PRIMARY KEY (`dep_codigo`),
  ADD KEY `departamento_usuario_externo_id_foreign` (`dep_uex_codigo`);

--
-- Indices de la tabla `direcciones`
--
ALTER TABLE `direcciones`
  ADD PRIMARY KEY (`dir_codigo`),
  ADD KEY `direcciones_mun_codigo_foreign` (`mun_codigo`);

--
-- Indices de la tabla `estados`
--
ALTER TABLE `estados`
  ADD PRIMARY KEY (`est_codigo`);

--
-- Indices de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indices de la tabla `grupo_proyecto_modulo`
--
ALTER TABLE `grupo_proyecto_modulo`
  ADD PRIMARY KEY (`grp_codigo`),
  ADD KEY `grupo_proyecto_modulo_grp_creador_cedula_index` (`grp_creador_cedula`);

--
-- Indices de la tabla `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indices de la tabla `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `linea_investigacions`
--
ALTER TABLE `linea_investigacions`
  ADD PRIMARY KEY (`lin_codigo`);

--
-- Indices de la tabla `metodologia_investigacions`
--
ALTER TABLE `metodologia_investigacions`
  ADD PRIMARY KEY (`mei_codigo`);

--
-- Indices de la tabla `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `municipios`
--
ALTER TABLE `municipios`
  ADD PRIMARY KEY (`mun_codigo`),
  ADD KEY `municipios_est_codigo_foreign` (`est_codigo`);

--
-- Indices de la tabla `organizacion`
--
ALTER TABLE `organizacion`
  ADD PRIMARY KEY (`org_codigo`),
  ADD KEY `organizacion_departamento_id_foreign` (`org_dep_codigo`);

--
-- Indices de la tabla `org_contactos`
--
ALTER TABLE `org_contactos`
  ADD PRIMARY KEY (`oco_codigo`),
  ADD KEY `org_contactos_org_codigo_foreign` (`org_codigo`),
  ADD KEY `org_contactos_dep_codigo_foreign` (`dep_codigo`);

--
-- Indices de la tabla `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indices de la tabla `proyectos`
--
ALTER TABLE `proyectos`
  ADD PRIMARY KEY (`pry_codigo`),
  ADD KEY `aud_codigo` (`aud_codigo`),
  ADD KEY `com_codigo` (`com_codigo`),
  ADD KEY `proyectos_metodologia_id_foreign` (`mei_codigo`),
  ADD KEY `proyectos_tipo_investigacion_id_foreign` (`tin_codigo`),
  ADD KEY `proyectos_linea_investigacion_id_foreign` (`lin_codigo`),
  ADD KEY `proyectos_tipo_publicacion_id_foreign` (`tpu_codigo`),
  ADD KEY `proyectos_usuario_externo_id_foreign` (`pry_uex_codigo`),
  ADD KEY `idx_proyectos_direccion_logica` (`pry_direccion_logica`),
  ADD KEY `idx_proyectos_validacion` (`pry_estado_validacion`),
  ADD KEY `idx_proyectos_validacion_dir` (`pry_estado_validacion`,`pry_direccion_logica`);
ALTER TABLE `proyectos` ADD FULLTEXT KEY `ft_proyectos_resumen` (`pry_resumen`);

--
-- Indices de la tabla `proyectos_publicados`
--
ALTER TABLE `proyectos_publicados`
  ADD PRIMARY KEY (`pub_codigo`),
  ADD KEY `proyectos_publicados_proyecto_id_foreign` (`pry_codigo`),
  ADD KEY `idx_publicados_estado` (`pub_estado`),
  ADD KEY `idx_publicados_pry_codigo` (`pry_codigo`);

--
-- Indices de la tabla `rol_externo`
--
ALTER TABLE `rol_externo`
  ADD PRIMARY KEY (`rex_codigo`);

--
-- Indices de la tabla `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indices de la tabla `tipo_investigacions`
--
ALTER TABLE `tipo_investigacions`
  ADD PRIMARY KEY (`tin_codigo`);

--
-- Indices de la tabla `tipo_publicacions`
--
ALTER TABLE `tipo_publicacions`
  ADD PRIMARY KEY (`tpu_codigo`);

--
-- Indices de la tabla `usuarios_externos`
--
ALTER TABLE `usuarios_externos`
  ADD PRIMARY KEY (`uex_codigo`),
  ADD KEY `usuarios_rol_externo_id_foreign` (`uex_rex_codigo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `auditorias`
--
ALTER TABLE `auditorias`
  MODIFY `aud_codigo` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `comentarios_proyecto`
--
ALTER TABLE `comentarios_proyecto`
  MODIFY `cop_codigo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `componentes`
--
ALTER TABLE `componentes`
  MODIFY `comp_codigo` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `comunidades`
--
ALTER TABLE `comunidades`
  MODIFY `com_codigo` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `comunidad_contactos`
--
ALTER TABLE `comunidad_contactos`
  MODIFY `ccom_codigo` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `departamento`
--
ALTER TABLE `departamento`
  MODIFY `dep_codigo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `direcciones`
--
ALTER TABLE `direcciones`
  MODIFY `dir_codigo` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `estados`
--
ALTER TABLE `estados`
  MODIFY `est_codigo` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `grupo_proyecto_modulo`
--
ALTER TABLE `grupo_proyecto_modulo`
  MODIFY `grp_codigo` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `linea_investigacions`
--
ALTER TABLE `linea_investigacions`
  MODIFY `lin_codigo` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `metodologia_investigacions`
--
ALTER TABLE `metodologia_investigacions`
  MODIFY `mei_codigo` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `municipios`
--
ALTER TABLE `municipios`
  MODIFY `mun_codigo` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=335;

--
-- AUTO_INCREMENT de la tabla `organizacion`
--
ALTER TABLE `organizacion`
  MODIFY `org_codigo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `org_contactos`
--
ALTER TABLE `org_contactos`
  MODIFY `oco_codigo` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `proyectos`
--
ALTER TABLE `proyectos`
  MODIFY `pry_codigo` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `proyectos_publicados`
--
ALTER TABLE `proyectos_publicados`
  MODIFY `pub_codigo` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `rol_externo`
--
ALTER TABLE `rol_externo`
  MODIFY `rex_codigo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `tipo_investigacions`
--
ALTER TABLE `tipo_investigacions`
  MODIFY `tin_codigo` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `tipo_publicacions`
--
ALTER TABLE `tipo_publicacions`
  MODIFY `tpu_codigo` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuarios_externos`
--
ALTER TABLE `usuarios_externos`
  MODIFY `uex_codigo` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `comentarios_proyecto`
--
ALTER TABLE `comentarios_proyecto`
  ADD CONSTRAINT `comentarios_proyecto_id_foreign` FOREIGN KEY (`pry_codigo`) REFERENCES `proyectos` (`pry_codigo`) ON DELETE CASCADE,
  ADD CONSTRAINT `comentarios_usuario_id_foreign` FOREIGN KEY (`uex_codigo`) REFERENCES `usuarios_externos` (`uex_codigo`);

--
-- Filtros para la tabla `comunidades`
--
ALTER TABLE `comunidades`
  ADD CONSTRAINT `comunidades_dir_codigo_foreign` FOREIGN KEY (`dir_codigo`) REFERENCES `direcciones` (`dir_codigo`) ON DELETE CASCADE;

--
-- Filtros para la tabla `departamento`
--
ALTER TABLE `departamento`
  ADD CONSTRAINT `departamento_usuario_externo_id_foreign` FOREIGN KEY (`dep_uex_codigo`) REFERENCES `usuarios_externos` (`uex_codigo`);

--
-- Filtros para la tabla `direcciones`
--
ALTER TABLE `direcciones`
  ADD CONSTRAINT `direcciones_mun_codigo_foreign` FOREIGN KEY (`mun_codigo`) REFERENCES `municipios` (`mun_codigo`) ON DELETE CASCADE;

--
-- Filtros para la tabla `municipios`
--
ALTER TABLE `municipios`
  ADD CONSTRAINT `municipios_est_codigo_foreign` FOREIGN KEY (`est_codigo`) REFERENCES `estados` (`est_codigo`) ON DELETE CASCADE;

--
-- Filtros para la tabla `organizacion`
--
ALTER TABLE `organizacion`
  ADD CONSTRAINT `organizacion_departamento_id_foreign` FOREIGN KEY (`org_dep_codigo`) REFERENCES `departamento` (`dep_codigo`);

--
-- Filtros para la tabla `org_contactos`
--
ALTER TABLE `org_contactos`
  ADD CONSTRAINT `org_contactos_dep_codigo_foreign` FOREIGN KEY (`dep_codigo`) REFERENCES `departamento` (`dep_codigo`) ON DELETE CASCADE,
  ADD CONSTRAINT `org_contactos_org_codigo_foreign` FOREIGN KEY (`org_codigo`) REFERENCES `organizacion` (`org_codigo`) ON DELETE CASCADE;

--
-- Filtros para la tabla `proyectos`
--
ALTER TABLE `proyectos`
  ADD CONSTRAINT `proyectos_auditoria_id_foreign` FOREIGN KEY (`aud_codigo`) REFERENCES `auditorias` (`aud_codigo`),
  ADD CONSTRAINT `proyectos_comunidad_id_foreign` FOREIGN KEY (`com_codigo`) REFERENCES `comunidades` (`com_codigo`),
  ADD CONSTRAINT `proyectos_linea_investigacion_id_foreign` FOREIGN KEY (`lin_codigo`) REFERENCES `linea_investigacions` (`lin_codigo`),
  ADD CONSTRAINT `proyectos_metodologia_id_foreign` FOREIGN KEY (`mei_codigo`) REFERENCES `metodologia_investigacions` (`mei_codigo`),
  ADD CONSTRAINT `proyectos_tipo_investigacion_id_foreign` FOREIGN KEY (`tin_codigo`) REFERENCES `tipo_investigacions` (`tin_codigo`),
  ADD CONSTRAINT `proyectos_tipo_publicacion_id_foreign` FOREIGN KEY (`tpu_codigo`) REFERENCES `tipo_publicacions` (`tpu_codigo`),
  ADD CONSTRAINT `proyectos_usuario_externo_id_foreign` FOREIGN KEY (`pry_uex_codigo`) REFERENCES `usuarios_externos` (`uex_codigo`);

--
-- Filtros para la tabla `proyectos_publicados`
--
ALTER TABLE `proyectos_publicados`
  ADD CONSTRAINT `proyectos_publicados_proyecto_id_foreign` FOREIGN KEY (`pry_codigo`) REFERENCES `proyectos` (`pry_codigo`) ON DELETE CASCADE;

--
-- Filtros para la tabla `usuarios_externos`
--
ALTER TABLE `usuarios_externos`
  ADD CONSTRAINT `usuarios_rol_externo_id_foreign` FOREIGN KEY (`uex_rex_codigo`) REFERENCES `rol_externo` (`rex_codigo`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
