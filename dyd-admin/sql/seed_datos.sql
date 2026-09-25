-- ============================================================
-- Datos iniciales para revista_digital
-- Ejecutar en phpMyAdmin DESPUÉS de correr el script del docente
-- (el que crea las tablas). Sin esto, la app funciona igual,
-- pero conviene tener un autor "Redacción" para los reportajes
-- que no tienen un autor externo asignado.
-- ============================================================

USE revista_digital;

-- La tabla "reportajes" exige autor_id (NOT NULL), así que creamos
-- un autor genérico "Redacción" para usar cuando el reportaje es
-- propiedad de la empresa y no de un colaborador externo.
INSERT INTO autores (nombres, ap_paterno, ap_materno, nickname, es_nickname)
VALUES ('Redacción', 'Diálogo y Desarrollo', NULL, NULL, 0);

-- (Opcional) un par de autores de ejemplo, bórralos si no los necesitas.
INSERT INTO autores (nombres, ap_paterno, ap_materno) VALUES
('Rocío', 'Huamán', NULL),
('Fernando', 'Quispe', NULL);

-- El primer usuario administrador NO se crea aquí: créalo desde
-- setup_admin.php (usa password_hash() de PHP, más seguro que
-- pegar un hash a mano). Recuerda borrar setup_admin.php después.
